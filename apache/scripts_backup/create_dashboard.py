import json
from superset import db
from superset.models.dashboard import Dashboard
from superset.models.slice import Slice

# Find the charts
chart_names = [
    "Status Reservasi Hari Ini",
    "Tren Kedatangan (7 Hari Terakhir)",
    "Distribusi Jenis Layanan",
    "Rata-rata Durasi Konsultasi"
]
slices = db.session.query(Slice).filter(Slice.slice_name.in_(chart_names)).all()

if not slices:
    print("No charts found.")
else:
    # Check if dashboard already exists
    dash_name = "Dashboard Agent Performance"
    dash = db.session.query(Dashboard).filter_by(dashboard_title=dash_name).first()

    if not dash:
        print(f"Creating dashboard: {dash_name}")
        dash = Dashboard(
            dashboard_title=dash_name,
            published=True,
            created_by_fk=1
        )
        db.session.add(dash)
        db.session.flush() # Get ID
    else:
        print(f"Found existing dashboard: {dash_name}")

    # Associate charts with dashboard
    dash.slices = slices

    db.session.commit()
    print(f"DASHBOARD_UUID: {dash.uuid}")
