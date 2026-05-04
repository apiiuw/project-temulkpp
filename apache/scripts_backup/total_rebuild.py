import json
from superset import db
from superset.models.slice import Slice
from superset.connectors.sqla.models import SqlaTable

def total_rebuild_fix():
    dataset = db.session.query(SqlaTable).filter_by(table_name='reservations').first()
    if not dataset: return

    charts = db.session.query(Slice).filter(Slice.datasource_id == dataset.id).all()
    for chart in charts:
        params = json.loads(chart.params) if chart.params else {}
        
        # Tambahkan parameter waktu wajib (Wajib ada di Superset)
        params["granularity_sqla"] = "tanggal_jam"
        params["time_range"] = "No filter"
        
        # Warna tema
        params["label_colors"] = {
            "SPSE": "#dc2626",
            "Non SPSE": "#f59e0b",
            "pending": "#78716c",
            "completed": "#10b981",
            "in_progress": "#f97316",
            "count": "#dc2626",
            "avg_durasi": "#dc2626"
        }
        params["color_scheme"] = "supersetColors"

        if chart.slice_name == "Distribusi Jenis Layanan":
            chart.viz_type = "echarts_pie"
            params["metrics"] = ["count"]
            params["groupby"] = ["jenis_layanan"]
            params["donut"] = True
            params["show_legend"] = True
            params["show_labels"] = True
            params["time_range"] = "No filter"

        elif chart.slice_name == "Rata-rata Durasi Konsultasi":
            chart.viz_type = "big_number_total"
            params["metric"] = "avg_durasi" # Big number total usually uses 'metric' object or string
            params["subheader_label"] = "Menit"
            params["y_axis_format"] = ".1f"
            params["time_range"] = "No filter"

        elif chart.slice_name == "Tren Kedatangan (7 Hari Terakhir)":
            chart.viz_type = "echarts_timeseries_line"
            params["metrics"] = ["count"]
            params["time_range"] = "Last 7 days"
            params["time_grain_sqla"] = "P1D"

        chart.params = json.dumps(params)
        print(f"Rebuilt: {chart.slice_name} ({chart.viz_type})")

    db.session.commit()

total_rebuild_fix()
