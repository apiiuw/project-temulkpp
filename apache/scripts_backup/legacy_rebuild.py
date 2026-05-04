import json
from superset import db
from superset.models.slice import Slice
from superset.connectors.sqla.models import SqlaTable

def legacy_rebuild():
    dataset = db.session.query(SqlaTable).filter_by(table_name='reservations').first()
    if not dataset: return

    charts = db.session.query(Slice).filter(Slice.datasource_id == dataset.id).all()
    for chart in charts:
        params = json.loads(chart.params) if chart.params else {}
        
        # Gunakan parameter waktu standar MySQL
        params["granularity_sqla"] = "tanggal_jam"
        
        if chart.slice_name == "Tren Kedatangan (7 Hari Terakhir)":
            chart.viz_type = "line"
            params["viz_type"] = "line"
            params["metrics"] = ["count"]
            params["time_range"] = "Last 7 days"
            params["show_brush"] = False

        elif chart.slice_name == "Distribusi Jenis Layanan":
            chart.viz_type = "dist_bar"
            params["viz_type"] = "dist_bar"
            params["metrics"] = ["count"]
            params["groupby"] = ["jenis_layanan"]

        elif chart.slice_name == "Rata-rata Durasi Konsultasi":
            chart.viz_type = "big_number"
            params["viz_type"] = "big_number"
            params["metric"] = "avg_durasi"
            params["y_axis_format"] = ".1f"

        chart.params = json.dumps(params)
        print(f"Rebuilt to Legacy: {chart.slice_name} ({chart.viz_type})")

    db.session.commit()

legacy_rebuild()
