import json
from superset.app import create_app
app = create_app()
app.app_context().push()

from superset import db
from superset.models.dashboard import Dashboard
from superset.models.slice import Slice
from superset.connectors.sqla.models import SqlaTable, SqlMetric
from superset.daos.dashboard import EmbeddedDashboardDAO

def create_superadmin_dashboard():
    dataset = db.session.query(SqlaTable).filter_by(table_name='reservations').first()
    
    if not dataset:
        print("Dataset not found!")
        return

    target_charts = []
    chart_configs = [
        ("Global Tren Kedatangan", "echarts_timeseries_line", {"metrics": ["count"], "granularity_sqla": "tanggal_jam", "time_range": "No filter"}),
        ("Global Distribusi Layanan", "echarts_pie", {"metric": "count", "groupby": ["jenis_layanan"], "donut": True, "time_range": "No filter"}),
        ("Global Rata-rata Durasi", "big_number_total", {"metric": "avg_durasi", "subheader_label": "Menit", "y_axis_format": ".1f", "time_range": "No filter"}),
        ("Global Status Reservasi", "echarts_pie", {"metric": "count", "groupby": ["status"], "time_range": "No filter"}),
        ("Global Durasi Tercepat", "big_number_total", {"metric": "min_durasi", "subheader_label": "Menit", "time_range": "No filter"}),
        ("Global Durasi Terlama", "big_number_total", {"metric": "max_durasi", "subheader_label": "Menit", "time_range": "No filter"}),
        ("Global Kategori Terbanyak", "table", {"metrics": ["count"], "groupby": ["jenis_layanan"], "row_limit": 1, "time_range": "No filter"})
    ]

    for name, viz, params in chart_configs:
        c = db.session.query(Slice).filter(Slice.slice_name == name, Slice.datasource_id == dataset.id).first()
        if not c:
            c = Slice(slice_name=name, viz_type=viz, datasource_id=dataset.id, datasource_type='table', created_by_fk=1)
            db.session.add(c)
            db.session.flush()
        
        p = json.loads(c.params) if c.params else {}
        p.update(params)
        if "pie" in viz:
            p["label_colors"] = {"SPSE": "#dc2626", "Non SPSE": "#f59e0b", "pending": "#78716c", "completed": "#10b981", "in_progress": "#f97316"}
        c.params = json.dumps(p)
        c.viz_type = viz
        target_charts.append(c)

    db.session.commit()

    # Dashboard
    dash_uuid = "da1f9b3b-8911-4eb7-a7eb-9df03b41bb1c"
    dash = db.session.query(Dashboard).filter_by(uuid=dash_uuid).first()
    if not dash:
        dash = Dashboard(dashboard_title="Dashboard Superadmin Overview", uuid=dash_uuid, published=True, created_by_fk=1)
        db.session.add(dash)
        db.session.flush()
    
    dash.slices = target_charts
    
    # Layout
    position = {
        "DASHBOARD_VERSION_KEY": "v2",
        "ROOT_ID": {"children": ["GRID_ID"], "id": "ROOT_ID", "type": "ROOT"},
        "GRID_ID": {"children": ["ROW-1", "ROW-2", "ROW-3"], "id": "GRID_ID", "parents": ["ROOT_ID"], "type": "GRID"},
        "HEADER_ID": {"id": "HEADER_ID", "meta": {"text": dash.dashboard_title}, "type": "HEADER"},
    }
    
    position["ROW-1"] = {"children": ["CHART-3", "CHART-5", "CHART-6", "CHART-7"], "id": "ROW-1", "meta": {"background": "BACKGROUND_TRANSPARENT"}, "parents": ["GRID_ID"], "type": "ROW"}
    position["ROW-2"] = {"children": ["CHART-1"], "id": "ROW-2", "meta": {"background": "BACKGROUND_TRANSPARENT"}, "parents": ["GRID_ID"], "type": "ROW"}
    position["ROW-3"] = {"children": ["CHART-2", "CHART-4"], "id": "ROW-3", "meta": {"background": "BACKGROUND_TRANSPARENT"}, "parents": ["GRID_ID"], "type": "ROW"}

    position["CHART-3"] = {"children": [], "id": "CHART-3", "meta": {"chartId": target_charts[2].id, "height": 15, "sliceName": target_charts[2].slice_name, "width": 3}, "parents": ["GRID_ID", "ROW-1"], "type": "CHART"}
    position["CHART-5"] = {"children": [], "id": "CHART-5", "meta": {"chartId": target_charts[4].id, "height": 15, "sliceName": target_charts[4].slice_name, "width": 3}, "parents": ["GRID_ID", "ROW-1"], "type": "CHART"}
    position["CHART-6"] = {"children": [], "id": "CHART-6", "meta": {"chartId": target_charts[5].id, "height": 15, "sliceName": target_charts[5].slice_name, "width": 3}, "parents": ["GRID_ID", "ROW-1"], "type": "CHART"}
    position["CHART-7"] = {"children": [], "id": "CHART-7", "meta": {"chartId": target_charts[6].id, "height": 15, "sliceName": target_charts[6].slice_name, "width": 3}, "parents": ["GRID_ID", "ROW-1"], "type": "CHART"}

    position["CHART-1"] = {"children": [], "id": "CHART-1", "meta": {"chartId": target_charts[0].id, "height": 25, "sliceName": target_charts[0].slice_name, "width": 12}, "parents": ["GRID_ID", "ROW-2"], "type": "CHART"}
    
    position["CHART-2"] = {"children": [], "id": "CHART-2", "meta": {"chartId": target_charts[1].id, "height": 25, "sliceName": target_charts[1].slice_name, "width": 6}, "parents": ["GRID_ID", "ROW-3"], "type": "CHART"}
    position["CHART-4"] = {"children": [], "id": "CHART-4", "meta": {"chartId": target_charts[3].id, "height": 25, "sliceName": target_charts[3].slice_name, "width": 6}, "parents": ["GRID_ID", "ROW-3"], "type": "CHART"}

    dash.position_json = json.dumps(position)
    db.session.commit()
    
    # Enable embedding
    try:
        EmbeddedDashboardDAO.upsert(dash, allowed_domains=["localhost:8000", "http://localhost:8000"])
    except Exception as e:
        print(f"Failed to enable embedding: {e}")

    print(f"Superadmin Dashboard created with UUID: {dash_uuid}")

if __name__ == "__main__":
    create_superadmin_dashboard()
