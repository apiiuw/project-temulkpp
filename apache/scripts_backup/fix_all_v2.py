
import json
import os
import uuid

def fix_everything_v2():
    from superset.app import create_app
    app = create_app()
    app.app_context().push()

    from superset import db
    from superset.models.dashboard import Dashboard
    from superset.models.slice import Slice
    from superset.connectors.sqla.models import SqlaTable, TableColumn, SqlMetric

    # 1. Dataset Fix
    dataset = db.session.query(SqlaTable).filter_by(table_name='reservations').first()
    if not dataset:
        print("Dataset 'reservations' not found.")
        return

    # 2. Charts Fix
    target_charts = []
    # Use "No filter" for time range to ensure data shows up even if it's "future" or "old"
    chart_configs = [
        ("Tren Kedatangan", "echarts_timeseries_line", {"metrics": ["count"], "granularity_sqla": "tanggal_jam", "time_range": "No filter"}),
        ("Distribusi Layanan", "echarts_pie", {"metric": "count", "groupby": ["jenis_layanan"], "donut": True, "time_range": "No filter"}),
        ("Rata-rata Durasi", "big_number_total", {"metric": "avg_durasi", "subheader_label": "Menit", "y_axis_format": ".1f", "time_range": "No filter"}),
        ("Status Reservasi", "echarts_pie", {"metric": "count", "groupby": ["status"], "time_range": "No filter"})
    ]

    for name, viz, params in chart_configs:
        c = db.session.query(Slice).filter(Slice.slice_name == name, Slice.datasource_id == dataset.id).first()
        if not c:
            c = Slice(slice_name=name, viz_type=viz, datasource_id=dataset.id, datasource_type='table', created_by_fk=1)
            db.session.add(c)
            db.session.flush()
        
        p = json.loads(c.params) if c.params else {}
        p.update(params)
        p["label_colors"] = {"SPSE": "#dc2626", "Non SPSE": "#f59e0b", "pending": "#78716c", "completed": "#10b981", "in_progress": "#f97316"}
        c.params = json.dumps(p)
        c.viz_type = viz
        target_charts.append(c)

    db.session.commit()

    # 3. Dashboard Fix
    dash_uuid = "dbd88673-b705-40cc-8fa0-b8b8a26e9afd"
    dash = db.session.query(Dashboard).filter_by(uuid=dash_uuid).first()
    if not dash:
        dash = Dashboard(dashboard_title="Dashboard Agent Performance", uuid=dash_uuid, published=True, created_by_fk=1)
        db.session.add(dash)
        db.session.flush()
    
    dash.slices = target_charts
    
    # Create a very standard layout
    layout = {
        "DASHBOARD_VERSION_KEY": "v2",
        "ROOT_ID": {"children": ["GRID_ID"], "id": "ROOT_ID", "type": "ROOT"},
        "GRID_ID": {"children": ["ROW-1", "ROW-2"], "id": "GRID_ID", "parents": ["ROOT_ID"], "type": "GRID"},
    }
    
    layout["ROW-1"] = {"children": ["CHART-1", "CHART-2"], "id": "ROW-1", "meta": {"background": "BACKGROUND_TRANSPARENT"}, "parents": ["GRID_ID"], "type": "ROW"}
    layout["ROW-2"] = {"children": ["CHART-3", "CHART-4"], "id": "ROW-2", "meta": {"background": "BACKGROUND_TRANSPARENT"}, "parents": ["GRID_ID"], "type": "ROW"}

    for i, chart in enumerate(target_charts):
        idx = i + 1
        cid = f"CHART-{idx}"
        layout[cid] = {
            "children": [],
            "id": cid,
            "meta": {
                "chartId": chart.id,
                "height": 50,
                "sliceName": chart.slice_name,
                "width": 6,
                "uuid": str(chart.uuid)
            },
            "parents": ["GRID_ID", f"ROW-{(i // 2) + 1}"],
            "type": "CHART"
        }

    dash.position_json = json.dumps(layout)
    dash.published = True
    
    db.session.commit()
    print(f"Dashboard fixed (V2) with {len(target_charts)} charts and No Filter time range.")

if __name__ == "__main__":
    fix_everything_v2()
