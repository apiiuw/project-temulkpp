"""Bootstrap + self-repair for the Pimpinan analytics dashboard.

Idempotent and NON-DESTRUCTIVE. It only ever:

1. Creates the analytics dataset, its metrics/columns, and the two Pimpinan
   charts *if they do not exist yet* (bootstrap for a fresh database).
2. Creates the Pimpinan dashboard and its initial layout *only when the
   dashboard has no ``position_json`` at all* (fresh bootstrap).
3. Removes unreachable / dangling ``CHART`` nodes that older versions of this
   script duplicated on every container restart. This cleanup never touches a
   chart that is actually placed on the dashboard.
4. Ensures the admin user owns every dashboard and chart so they can be edited
   through the Superset UI.

It deliberately NEVER overwrites ``chart.params``, ``dashboard.position_json``
of an existing layout, or ``dashboard.json_metadata``. Anything you edit in the
Superset UI therefore survives container restarts.

Usage (runs automatically inside the container at startup)::

    python /app/add_pimpinan_charts.py
"""

from __future__ import annotations

import json

from superset.app import create_app

app = create_app()
app.app_context().push()

from superset import db  # noqa: E402
from superset.connectors.sqla.models import SqlaTable, SqlMetric, TableColumn  # noqa: E402
from superset.models.dashboard import Dashboard  # noqa: E402
from superset.models.slice import Slice  # noqa: E402
from flask_appbuilder.security.sqla.models import User  # noqa: E402

DASHBOARD_UUID = "1a2b3c4d-5e6f-7a8b-9c0d-1e2f3a4b5c6d"
ANALYTICS_TABLE = "reservation_agent_analytics"
STATUS_COLORS = {
    "Selesai": "#16a34a",
    "Tidak Selesai": "#dc2626",
}

ANALYTICS_SQL = """
SELECT
    r.id,
    r.tanggal_jam,
    r.agent_id,
    COALESCE(a.name, 'Tanpa Agent') AS agent_name,
    YEAR(r.tanggal_jam) AS tahun,
    CASE
        WHEN r.status = 'completed' THEN 'Selesai'
        ELSE 'Tidak Selesai'
    END AS status_kategori
FROM reservations AS r
LEFT JOIN agents AS a ON a.id = r.agent_id
""".strip()


def ensure_column(dataset: SqlaTable, name: str, column_type: str,
                  is_dttm: bool = False) -> None:
    column = (
        db.session.query(TableColumn)
        .filter_by(table_id=dataset.id, column_name=name)
        .first()
    )
    if column is None:
        db.session.add(
            TableColumn(
                table_id=dataset.id,
                column_name=name,
                type=column_type,
                is_dttm=is_dttm,
                groupby=True,
                filterable=True,
            )
        )


def ensure_count_metric(dataset: SqlaTable) -> None:
    metric = (
        db.session.query(SqlMetric)
        .filter_by(table_id=dataset.id, metric_name="count")
        .first()
    )
    if metric is None:
        db.session.add(
            SqlMetric(
                table_id=dataset.id,
                metric_name="count",
                expression="COUNT(*)",
            )
        )


def ensure_analytics_dataset(reservations: SqlaTable) -> SqlaTable:
    dataset = (
        db.session.query(SqlaTable)
        .filter_by(table_name=ANALYTICS_TABLE)
        .first()
    )
    if dataset is None:
        dataset = SqlaTable(
            table_name=ANALYTICS_TABLE,
            schema=reservations.schema,
            database_id=reservations.database_id,
            sql=ANALYTICS_SQL,
            is_sqllab_view=True,
            created_by_fk=1,
        )
        db.session.add(dataset)
        db.session.flush()
    # NOTE: never overwrite dataset.sql of an existing dataset so that any
    # manual SQL edits made in the Superset UI are preserved.

    ensure_column(dataset, "id", "BIGINT")
    ensure_column(dataset, "tanggal_jam", "DATETIME", is_dttm=True)
    ensure_column(dataset, "agent_id", "BIGINT")
    ensure_column(dataset, "agent_name", "VARCHAR")
    ensure_column(dataset, "tahun", "INTEGER")
    ensure_column(dataset, "status_kategori", "VARCHAR")
    db.session.flush()
    return dataset


def ensure_chart(name: str, viz_type: str, dataset: SqlaTable, params: dict) -> Slice:
    """Create the chart only if it does not exist.

    If it already exists, its ``params`` and ``query_context`` are left exactly
    as they are so that UI edits persist across restarts.
    """
    chart = db.session.query(Slice).filter_by(slice_name=name).first()
    if chart is not None:
        return chart

    chart = Slice(
        slice_name=name,
        viz_type=viz_type,
        datasource_id=dataset.id,
        datasource_type="table",
        created_by_fk=1,
        params=json.dumps({**params, "datasource": f"{dataset.id}__table",
                           "viz_type": viz_type}),
    )
    db.session.add(chart)
    db.session.flush()
    return chart


def reachable_nodes(position: dict) -> set:
    """IDs reachable from ROOT_ID by walking ``children`` links."""
    seen: set = set()
    stack = ["ROOT_ID"]
    while stack:
        node_id = stack.pop()
        if node_id in seen:
            continue
        seen.add(node_id)
        node = position.get(node_id)
        if isinstance(node, dict):
            for child in node.get("children", []):
                stack.append(child)
    return seen


def prune_orphan_charts(dashboard: Dashboard) -> int:
    """Remove unreachable or dangling CHART nodes from ``position_json``.

    Older versions of this script appended a duplicate CHART node on every
    container restart without deleting the old ones, and left a dangling node
    after a chart was deleted. This cleans those up without touching any chart
    that is genuinely placed on the dashboard. Returns number removed.
    """
    if not dashboard.position_json:
        return 0
    try:
        position = json.loads(dashboard.position_json)
    except (TypeError, ValueError):
        return 0

    existing_slice_ids = {
        row[0] for row in db.session.query(Slice.id).all()
    }
    reachable = reachable_nodes(position)

    prune: set = set()
    for key, node in position.items():
        if not isinstance(node, dict) or node.get("type") != "CHART":
            continue
        chart_id = node.get("meta", {}).get("chartId")
        if key not in reachable or chart_id not in existing_slice_ids:
            prune.add(key)

    if not prune:
        return 0

    cleaned = {k: v for k, v in position.items() if k not in prune}
    for node in cleaned.values():
        if isinstance(node, dict) and node.get("children"):
            node["children"] = [c for c in node["children"] if c not in prune]

    dashboard.position_json = json.dumps(cleaned)
    return len(prune)


def ensure_owner(obj, admin: User) -> None:
    if admin is not None and admin not in obj.owners:
        obj.owners.append(admin)


def build_initial_layout(dashboard: Dashboard, trend: Slice,
                         performance: Slice) -> None:
    """Create the first-ever layout. Only called when position_json is empty."""
    row_id = "ROW-PIMPINAN"
    position = {
        "DASHBOARD_VERSION_KEY": "v2",
        "ROOT_ID": {"id": "ROOT_ID", "type": "ROOT", "children": ["GRID_ID"]},
        "HEADER_ID": {
            "id": "HEADER_ID",
            "type": "HEADER",
            "meta": {"text": dashboard.dashboard_title},
        },
        "GRID_ID": {
            "id": "GRID_ID", "type": "GRID", "children": [row_id],
            "parents": ["ROOT_ID"],
        },
        row_id: {
            "id": row_id, "type": "ROW", "children": ["CHART-1", "CHART-2"],
            "parents": ["ROOT_ID", "GRID_ID"],
            "meta": {"background": "BACKGROUND_TRANSPARENT"},
        },
        "CHART-1": {
            "id": "CHART-1", "type": "CHART",
            "meta": {"chartId": trend.id, "height": 30, "width": 6,
                     "sliceName": trend.slice_name},
            "parents": ["ROOT_ID", "GRID_ID", row_id],
        },
        "CHART-2": {
            "id": "CHART-2", "type": "CHART",
            "meta": {"chartId": performance.id, "height": 30, "width": 6,
                     "sliceName": performance.slice_name},
            "parents": ["ROOT_ID", "GRID_ID", row_id],
        },
    }
    dashboard.position_json = json.dumps(position)


def repair_all_dashboards(admin: User | None) -> dict:
    """Prune orphan chart nodes + ensure admin ownership on EVERY dashboard.

    This runs regardless of whether the dashboard was created by this script
    or manually in the UI, so all dashboards stay clean and editable.
    """
    totals = {"dashboards": 0, "pruned": 0, "owned_dash": 0, "owned_chart": 0}
    for dashboard in db.session.query(Dashboard).all():
        totals["dashboards"] += 1
        if admin is not None and admin not in dashboard.owners:
            dashboard.owners.append(admin)
            totals["owned_dash"] += 1
        totals["pruned"] += prune_orphan_charts(dashboard)
        for chart in dashboard.slices:
            if admin is not None and admin not in chart.owners:
                chart.owners.append(admin)
                totals["owned_chart"] += 1
    return totals


def main() -> None:
    admin = db.session.query(User).filter_by(id=1).first()

    # Repair pass runs regardless of whether the MySQL dataset is reachable:
    # pruning orphan nodes and assigning ownership never depend on it.
    db.session.flush()
    totals = repair_all_dashboards(admin)
    if totals["pruned"]:
        print(f"REPAIR: removed {totals['pruned']} orphan/duplicate chart node(s) "
              f"across {totals['dashboards']} dashboard(s)")
    print(f"OWNERSHIP: +{totals['owned_dash']} dashboard(s), "
          f"+{totals['owned_chart']} chart(s) assigned to admin")

    reservations = (
        db.session.query(SqlaTable).filter_by(table_name="reservations").first()
    )
    if reservations is None:
        print("SKIP bootstrap: dataset reservations tidak ditemukan (perlu MySQL)")
        db.session.commit()
        return

    ensure_column(reservations, "tahun", "INTEGER")
    analytics = ensure_analytics_dataset(reservations)
    ensure_count_metric(analytics)
    db.session.flush()

    trend = ensure_chart(
        "Tren Permasalahan per Tahun",
        "echarts_timeseries_bar",
        analytics,
        {
            "metrics": ["count"],
            "groupby": ["status_kategori"],
            "x_axis": "tahun",
            "orientation": "horizontal",
            "time_range": "No filter",
            "stack": "Stack",
            "show_legend": True,
            "show_value": True,
            "x_axis_label": "Tahun",
            "y_axis_label": "Jumlah Reservasi",
            "y_axis_format": "SMART_NUMBER",
            "sort_series_type": "name",
            "sort_series_ascending": True,
            "label_colors": STATUS_COLORS,
            "color_scheme": "supersetColors",
        },
    )
    performance = ensure_chart(
        "Kinerja Semua Agent",
        "echarts_timeseries_bar",
        analytics,
        {
            "metrics": ["count"],
            "groupby": ["status_kategori"],
            "x_axis": "agent_name",
            "orientation": "horizontal",
            "stack": "Stack",
            "show_legend": True,
            "show_value": True,
            "x_axis_label": "Agent",
            "y_axis_label": "Jumlah Reservasi",
            "y_axis_format": "SMART_NUMBER",
            "x_axis_sort": "agent_name",
            "x_axis_sort_asc": False,
            "sort_series_type": "name",
            "sort_series_ascending": False,
            "adhoc_filters": [
                {
                    "clause": "WHERE",
                    "comparator": "Tanpa Agent",
                    "expressionType": "SIMPLE",
                    "fromFormData": True,
                    "operator": "!=",
                    "subject": "agent_name",
                }
            ],
            "label_colors": STATUS_COLORS,
            "color_scheme": "supersetColors",
            "row_limit": 1000,
            "time_range": "No filter",
        },
    )

    dashboard = (
        db.session.query(Dashboard).filter_by(uuid=DASHBOARD_UUID).first()
    )
    if dashboard is None:
        dashboard = Dashboard(
            dashboard_title="Dashboard Pimpinan Overview",
            uuid=DASHBOARD_UUID,
            published=True,
            created_by_fk=1,
        )
        db.session.add(dashboard)
        db.session.flush()

    ensure_owner(dashboard, admin)
    ensure_owner(trend, admin)
    ensure_owner(performance, admin)

    # Attach the two charts to the dashboard's slice association.
    slices = list(dashboard.slices)
    for chart in (trend, performance):
        if chart not in slices:
            slices.append(chart)
    dashboard.slices = slices

    # Bootstrap the layout only when there is none yet; otherwise leave the
    # (possibly UI-edited) layout untouched apart from orphan cleanup.
    if not dashboard.position_json:
        build_initial_layout(dashboard, trend, performance)
        print("BOOTSTRAP: created initial Pimpinan layout")

    db.session.commit()
    print(f"OK dataset={analytics.id} trend={trend.id} performance={performance.id}")
    print("OK all dashboards are editable in the Superset UI; edits persist across restarts")


if __name__ == "__main__":
    main()
