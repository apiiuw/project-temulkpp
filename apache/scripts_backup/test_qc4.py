from superset.app import create_app
import json
app = create_app()
app.app_context().push()
from superset import db
from superset.models.slice import Slice
slc = db.session.query(Slice).first()
qc = slc.get_query_context()
if qc:
    print(type(qc))
    print(dir(qc))
