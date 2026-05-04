from superset import db
from superset.connectors.sqla.models import SqlaTable, TableColumn

dataset = db.session.query(SqlaTable).filter_by(table_name='reservations').first()
if dataset:
    for col in dataset.columns:
        col.is_active = True
        col.filterable = True
        col.groupby = True
    db.session.commit()
    print("Dataset columns synchronized and made filterable.")
else:
    print("Dataset not found.")
