from superset.app import create_app
app = create_app()
app.app_context().push()
from superset import db
from superset.connectors.sqla.models import SqlaTable

ds = db.session.query(SqlaTable).filter_by(table_name='reservations').first()
print(f"Database ID: {ds.database_id}")
