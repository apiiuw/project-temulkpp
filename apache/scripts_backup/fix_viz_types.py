import json
from superset import db
from superset.models.slice import Slice
from superset.connectors.sqla.models import SqlaTable

def fix_viz_types():
    dataset = db.session.query(SqlaTable).filter_by(table_name='reservations').first()
    if not dataset: return

    charts = db.session.query(Slice).filter(Slice.datasource_id == dataset.id).all()
    for chart in charts:
        params = json.loads(chart.params) if chart.params else {}
        
        # Kembalikan Pie ke versi standar (legacy) karena ECharts Pie tidak terdaftar
        if chart.slice_name == "Distribusi Jenis Layanan":
            chart.viz_type = "pie"
            params["viz_type"] = "pie"
            params["metric"] = "count"
            params["groupby"] = ["jenis_layanan"]
            params["donut"] = True
            params["show_legend"] = True
            params["label_type"] = "percent"

        # Pastikan Big Number tetap standar
        if chart.slice_name == "Rata-rata Durasi Konsultasi":
            chart.viz_type = "big_number_total"
            params["viz_type"] = "big_number_total"
            params["metric"] = "avg_durasi"
            params["y_axis_format"] = ".1f"

        chart.params = json.dumps(params)
        print(f"Fixed Viz Type: {chart.slice_name} to {chart.viz_type}")

    db.session.commit()

fix_viz_types()
