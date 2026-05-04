import uuid
from superset import db
from superset.models.dashboard import Dashboard
from superset.models.embedded_dashboard import EmbeddedDashboard

dash_name = "Dashboard Agent Performance"
dash = db.session.query(Dashboard).filter_by(dashboard_title=dash_name).first()

if dash:
    embedded = db.session.query(EmbeddedDashboard).filter_by(dashboard_id=dash.id).first()
    
    if not embedded:
        print(f"Enabling embedding for dashboard: {dash_name}")
        embedded = EmbeddedDashboard(
            dashboard_id=dash.id,
            uuid=str(uuid.uuid4()),
            allow_domain_list="localhost,127.0.0.1"
        )
        db.session.add(embedded)
        db.session.commit()
    else:
        print(f"Embedding already enabled.")
    
    print(f"EMBEDDED_ID: {embedded.uuid}")
else:
    print("Dashboard not found.")
