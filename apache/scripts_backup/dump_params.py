from superset.app import create_app
import json
app = create_app()
app.app_context().push()
from superset import db
from superset.models.slice import Slice

slc_agent = db.session.query(Slice).filter_by(slice_name="Tren Kedatangan").first()
slc_pim = db.session.query(Slice).filter_by(slice_name="Pimpinan Tren Kedatangan").first()

print("AGENT:", slc_agent.params)
print("PIMP :", slc_pim.params)
