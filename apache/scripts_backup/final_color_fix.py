import json
from superset import db
from superset.models.slice import Slice

def final_color_fix():
    theme_colors = {
        "SPSE": "#dc2626",
        "Non SPSE": "#f59e0b",
        "count": "#dc2626",
        "avg_durasi": "#dc2626"
    }
    
    charts = db.session.query(Slice).all()
    for chart in charts:
        if chart.slice_name in ["Tren Kedatangan (7 Hari Terakhir)", "Distribusi Jenis Layanan", "Rata-rata Durasi Konsultasi"]:
            params = json.loads(chart.params) if chart.params else {}
            params["label_colors"] = theme_colors
            params["color_scheme"] = "supersetColors"
            chart.params = json.dumps(params)
    
    db.session.commit()
    print("Final colors applied to legacy charts.")

final_color_fix()
