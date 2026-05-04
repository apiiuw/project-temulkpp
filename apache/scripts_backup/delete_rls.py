from superset.app import create_app
app = create_app()
app.app_context().push()

from superset import db
from superset.connectors.sqla.models import RowLevelSecurityFilter

def delete_native_rls():
    name = "Agent Data Isolation"
    existing = db.session.query(RowLevelSecurityFilter).filter_by(name=name).first()
    if existing:
        db.session.delete(existing)
        db.session.commit()
        print("Native RLS deleted.")
    else:
        print("No native RLS to delete.")

delete_native_rls()
