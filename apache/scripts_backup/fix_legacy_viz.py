
import json
import os
import uuid

def fix_to_legacy_viz():
    from superset.app import create_app
    app = create_app()
    app.app_context().push()

    from superset import db
    from superset.models.dashboard import Dashboard
    from superset.models.slice import Slice
    from superset.connectors.sqla.models import SqlaTable

    dataset = db.session.query(SqlaTable).filter_by(table_name='reservations').first()
    if not dataset:
        print("Dataset 'reservations' not found.")
        return

    # Use legacy viz types: line, pie, big_number
    chart_configs = [
        ("Tren Kedatangan", "line", {"metrics": ["count"], "granularity_sqla": "tanggal_jam", "time_range": "No filter", "viz_type": "line"}),
        ("Distribusi Layanan", "pie", {"metric": "count", "groupby": ["jenis_layanan"], "donut": True, "time_range": "No filter", "viz_type": "pie"}),
        ("Rata-rata Durasi", "big_number", {"metric": "avg_durasi", "subheader": "Menit", "y_axis_format": ".1f", "time_range": "No filter", "viz_type": "big_number"}),
        ("Status Reservasi", "pie", {"metric": "count", "groupby": ["status"], "donut": False, "time_range": "No filter", "viz_type": "pie"})
    ]

    target_charts = []
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

    # Sync Dashboard
    dash_uuid = "dbd88673-b705-40cc-8fa0-b8b8a26e9afd"
    dash = db.session.query(Dashboard).filter_by(uuid=dash_uuid).first()
    if dash:
        dash.slices = target_charts
        
        # Simple position layout
        layout = {
            "DASHBOARD_VERSION_KEY": "v2",
            "ROOT_ID": {"children": ["GRID_ID"], "id": "ROOT_ID", "type": "ROOT"},
            "GRID_ID": {"children": ["ROW-1", "ROW-2"], "id": "GRID_ID", "parents": ["ROOT_ID"], "type": "GRID"},
        }
        layout["ROW-1"] = {"children": ["CHART-1", "CHART-2"], "id": "ROW-1", "meta": {"background": "BACKGROUND_TRANSPARENT"}, "parents": ["GRID_ID"], "type": "ROW"}
        layout["ROW-2"] = {"children": ["CHART-3", "CHART-4"], "id": "ROW-2", "meta": {"background": "BACKGROUND_TRANSPARENT"}, "parents": ["GRID_ID"], "type": "ROW"}
        for i, chart in enumerate(target_charts):
            cid = f"CHART-{i+1}"
            layout[cid] = {
                "children": [],
                "id": cid,
                "meta": {"chartId": chart.id, "height": 50, "sliceName": chart.slice_name, "width": 6},
                "parents": ["GRID_ID", f"ROW-{(i // 2) + 1}"],
                "type": "CHART"
            }
        dash.position_json = json.dumps(layout)
        db.session.commit()
        print(f"Reverted to legacy viz types for {len(target_charts)} charts.")

if __name__ == "__main__":
    fix_to_legacy_viz()
