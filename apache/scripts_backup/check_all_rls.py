from superset.app import create_app
app = create_app()
app.app_context().push()

from superset import db
from superset.connectors.sqla.models import RowLevelSecurityFilter

all_rls = db.session.query(RowLevelSecurityFilter).all()
for rls in all_rls:
    print(f"RLS: {rls.name}, Clause: {rls.clause}")
    db.session.delete(rls)

db.session.commit()
print("All RLS deleted.")
