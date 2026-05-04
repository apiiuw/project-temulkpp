from superset.app import create_app
app = create_app()
app.app_context().push()
from superset import db
from superset.connectors.sqla.models import SqlaTable

tables = db.session.query(SqlaTable).all()
for t in tables:
    print(t.table_name)
