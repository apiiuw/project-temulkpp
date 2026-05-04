import json
from superset import db
from superset.models.slice import Slice
from superset.connectors.sqla.models import SqlaTable, SqlMetric

def fix_charts_and_theme():
    dataset = db.session.query(SqlaTable).filter_by(table_name='reservations').first()
    if not dataset:
        print("Dataset not found.")
        return

    # 1. Register explicit metrics
    metrics_to_add = [
        {"metric_name": "count", "expression": "count(*)", "verbose_name": "Total"},
        {"metric_name": "avg_durasi", "expression": "avg(TIMESTAMPDIFF(MINUTE, waktu_mulai_tatap_muka, waktu_selesai_tatap_muka))", "verbose_name": "Rata-rata Durasi"}
    ]
    
    for m in metrics_to_add:
        existing = db.session.query(SqlMetric).filter_by(table_id=dataset.id, metric_name=m['metric_name']).first()
        if not existing:
            new_metric = SqlMetric(table_id=dataset.id, **m)
            db.session.add(new_metric)
    
    db.session.commit()

    # 2. Colors matching Laravel theme
    theme_colors = {
        "SPSE": "#dc2626",      # Red-600
        "Non SPSE": "#f59e0b",  # Amber-500
        "pending": "#78716c",   # Stone-500
        "completed": "#10b981", # Emerald-500
        "in_progress": "#f97316", # Orange-500
        "checked_in_front_desk": "#d97706" # Amber-600
    }

    charts = db.session.query(Slice).filter(Slice.datasource_id == dataset.id).all()
    for chart in charts:
        params = json.loads(chart.params) if chart.params else {}
        
        # Apply theme colors
        params["label_colors"] = theme_colors
        params["color_scheme"] = "supersetColors"
        
        # Specific fixes
        if chart.slice_name == "Distribusi Jenis Layanan":
            params["metric"] = "count"
            params["donut"] = True
            params["show_legend"] = True
        
        if chart.slice_name == "Rata-rata Durasi Konsultasi":
            params["metric"] = "avg_durasi"
            params["y_axis_format"] = ".1f"
            
        chart.params = json.dumps(params)
        print(f"Updated chart: {chart.slice_name}")

    db.session.commit()
    print("Done! Theme and Pie chart fix applied.")

fix_charts_and_theme()
