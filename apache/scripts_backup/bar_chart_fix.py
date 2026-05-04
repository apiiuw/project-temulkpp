import json
from superset import db
from superset.models.slice import Slice
from superset.connectors.sqla.models import SqlaTable

def bar_chart_fix():
    dataset = db.session.query(SqlaTable).filter_by(table_name='reservations').first()
    if not dataset: return

    charts = db.session.query(Slice).filter(Slice.datasource_id == dataset.id).all()
    for chart in charts:
        params = json.loads(chart.params) if chart.params else {}
        
        # Samakan skema warna ke Merah LKPP
        params["label_colors"] = {
            "SPSE": "#dc2626",
            "Non SPSE": "#f59e0b",
            "count": "#dc2626",
            "avg_durasi": "#dc2626"
        }
        params["color_scheme"] = "supersetColors"

        if chart.slice_name == "Distribusi Jenis Layanan":
            # Ganti ke Bar Chart (dist_bar) yang lebih stabil
            chart.viz_type = "dist_bar"
            params["viz_type"] = "dist_bar"
            params["metrics"] = ["count"]
            params["groupby"] = ["jenis_layanan"]
            params["show_legend"] = False
            params["show_bar_value"] = True
            params["bar_stacked"] = False
            params["order_bars_by"] = "value"

        chart.params = json.dumps(params)
        print(f"Updated chart visualization: {chart.slice_name} to {chart.viz_type}")

    db.session.commit()

bar_chart_fix()
