import json
from superset.app import create_app
app = create_app()
app.app_context().push()

from superset import db
from superset.models.dashboard import Dashboard

def revert_dashboard_css():
    dash_uuid = "dbd88673-b705-40cc-8fa0-b8b8a26e9afd"
    dash = db.session.query(Dashboard).filter_by(uuid=dash_uuid).first()
    
    if not dash:
        print("Dashboard not found!")
        return

    dash.css = ""
    db.session.commit()
    print("CSS reverted successfully!")

if __name__ == "__main__":
    revert_dashboard_css()
