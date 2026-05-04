import uuid
from superset.app import create_app
app = create_app()
app.app_context().push()

from superset import db
from superset.models.dashboard import Dashboard
from superset.models.embedded_dashboard import EmbeddedDashboard

def fix_domain_list():
    dash_uuid = "dbd88673-b705-40cc-8fa0-b8b8a26e9afd"
    dash = db.session.query(Dashboard).filter_by(uuid=dash_uuid).first()
    
    if not dash:
        print("Dashboard not found!")
        return

    embedded = db.session.query(EmbeddedDashboard).filter_by(dashboard_id=dash.id).first()
    if embedded:
        # Some versions use * to allow all domains, or you might need the scheme
        # Let's add variations just to be safe, or just use *
        embedded.allow_domain_list = "*"
        db.session.commit()
        print(f"allow_domain_list updated to '*' for embedded dashboard: {embedded.uuid}")
    else:
        print("Embedded configuration not found.")

if __name__ == "__main__":
    fix_domain_list()
