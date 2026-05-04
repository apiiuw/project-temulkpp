from superset.app import create_app
app = create_app()
app.app_context().push()

from superset import db
from superset.models.dashboard import Dashboard
from superset.models.embedded_dashboard import EmbeddedDashboard

def fix_embed():
    dash_uuid = "da1f9b3b-8911-4eb7-a7eb-9df03b41bb1c"
    dash = db.session.query(Dashboard).filter_by(uuid=dash_uuid).first()
    
    if not dash:
        print("Dashboard not found!")
        return

    embedded = db.session.query(EmbeddedDashboard).filter_by(dashboard_id=dash.id).first()
    
    if not embedded:
        print("Creating new embedded configuration...")
        embedded = EmbeddedDashboard(
            dashboard_id=dash.id,
            uuid=dash_uuid,
            allow_domain_list="localhost,127.0.0.1,localhost:8000"
        )
        db.session.add(embedded)
    else:
        print("Updating existing embedded configuration...")
        embedded.uuid = dash_uuid
        embedded.allow_domain_list = "localhost,127.0.0.1,localhost:8000"
        
    db.session.commit()
    print(f"Embedding enabled. EMBEDDED_ID: {embedded.uuid}")

if __name__ == "__main__":
    fix_embed()
