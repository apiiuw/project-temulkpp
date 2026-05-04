from superset.app import create_app
app = create_app()
app.app_context().push()
from superset import db
from superset.connectors.sqla.models import SqlaTable, TableColumn

ds = db.session.query(SqlaTable).filter_by(table_name='virtual_roles').first()
if ds:
    print(f"Dataset ID: {ds.id}")
    for col in ds.columns:
        print(f"Column: {col.column_name}")
else:
    print("Dataset not found")
