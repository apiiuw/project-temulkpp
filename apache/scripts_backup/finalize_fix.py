import json
from superset import db
from superset.models.slice import Slice
from superset.connectors.sqla.models import SqlaTable

def finalize_fix():
    dataset = db.session.query(SqlaTable).filter_by(table_name='reservations').first()
    if not dataset: return

    # Comprehensive color mapping
    theme_colors = {
        "SPSE": "#dc2626",
        "Non SPSE": "#f59e0b",
        "pending": "#78716c",
        "completed": "#10b981",
        "in_progress": "#f97316",
        "count": "#dc2626", # Default line color
        "COUNT(*)": "#dc2626",
        "avg_durasi": "#dc2626"
    }

    charts = db.session.query(Slice).filter(Slice.datasource_id == dataset.id).all()
    for chart in charts:
        params = json.loads(chart.params) if chart.params else {}
        params["label_colors"] = theme_colors
        params["color_scheme"] = "supersetColors"
        
        if chart.viz_type == "echarts_timeseries_line":
            params["metrics"] = ["count"] # Use named metric
            params["seriesType"] = "line"
            params["show_extra_controls"] = False

        if chart.slice_name == "Distribusi Jenis Layanan":
            chart.viz_type = "echarts_pie"
            params["viz_type"] = "echarts_pie"
            params["metric"] = "count"
            params["donut"] = True

        if chart.slice_name == "Rata-rata Durasi Konsultasi":
            chart.viz_type = "big_number_total"
            params["viz_type"] = "big_number_total"
            params["metric"] = "avg_durasi"
            params["y_axis_format"] = ".1f"

        chart.params = json.dumps(params)
    
    db.session.commit()
    print("Final visualization and color fix applied.")

finalize_fix()
