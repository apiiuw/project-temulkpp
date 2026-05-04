from superset.app import create_app
app = create_app()
app.app_context().push()
from superset import db
from superset.models.slice import Slice
slc = db.session.query(Slice).first()
print(dir(slc))
