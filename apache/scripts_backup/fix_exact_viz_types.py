import json
from superset.app import create_app
app = create_app()
app.app_context().push()

from superset import db
from superset.models.slice import Slice

def fix_exact_viz_types():
    # We want these exact keys
    # Line: echarts_timeseries_line
    # Pie: pie
    # Big Number: big_number_total
    
    chart_configs = {
        "Tren Kedatangan": "echarts_timeseries_line",
        "Distribusi Layanan": "pie",
        "Rata-rata Durasi": "big_number_total",
        "Status Reservasi": "pie",
        "Agent Chart": "echarts_timeseries_line"
    }

    slices = db.session.query(Slice).all()
    count = 0
    for s in slices:
        for key_name, correct_viz in chart_configs.items():
            if key_name in s.slice_name:
                s.viz_type = correct_viz
                if s.params:
                    p = json.loads(s.params)
                    p["viz_type"] = correct_viz
                    s.params = json.dumps(p)
                print(f"Fixed {s.slice_name} to {correct_viz}")
                count += 1
                break
                
    db.session.commit()
    print(f"Successfully fixed {count} charts.")

if __name__ == "__main__":
    fix_exact_viz_types()
