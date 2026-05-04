import json
from superset import db
from superset.models.slice import Slice
from superset.connectors.sqla.models import SqlaTable

def ultimate_fix():
    dataset = db.session.query(SqlaTable).filter_by(table_name='reservations').first()
    if not dataset: return

    charts = db.session.query(Slice).filter(Slice.datasource_id == dataset.id).all()
    for chart in charts:
        params = json.loads(chart.params) if chart.params else {}
        
        # Standarisasi warna
        params["label_colors"] = {
            "SPSE": "#dc2626",
            "Non SPSE": "#f59e0b",
            "pending": "#78716c",
            "completed": "#10b981",
            "in_progress": "#f97316",
            "count": "#dc2626",
            "avg_durasi": "#dc2626"
        }

        # Gunakan format array untuk metrics (lebih stabil untuk guest user)
        if "metric" in params:
            m = params.pop("metric")
            params["metrics"] = [m] if isinstance(m, str) else [m]
        
        if "metrics" not in params:
            params["metrics"] = ["count"]

        # Pastikan viz_type konsisten
        if chart.slice_name == "Tren Kedatangan (7 Hari Terakhir)":
            chart.viz_type = "echarts_timeseries_line"
        elif chart.slice_name == "Distribusi Jenis Layanan":
            chart.viz_type = "echarts_pie"
            params["donut"] = True
        elif chart.slice_name == "Rata-rata Durasi Konsultasi":
            chart.viz_type = "big_number_total"
            params["subheader_label"] = "Menit"

        chart.params = json.dumps(params)
        print(f"Fixed: {chart.slice_name} as {chart.viz_type}")

    db.session.commit()

ultimate_fix()
