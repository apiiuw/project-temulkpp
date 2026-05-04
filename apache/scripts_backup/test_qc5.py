import json
from superset.app import create_app
app = create_app()
app.app_context().push()
from superset import db
from superset.models.slice import Slice
from superset.common.query_context_factory import QueryContextFactory

factory = QueryContextFactory()
slc = db.session.query(Slice).first()
form_data = json.loads(slc.params)
form_data['datasource'] = f"{slc.datasource_id}__{slc.datasource_type}"

qc = factory.create(
    datasource=form_data['datasource'],
    queries=[form_data],
    form_data=form_data,
    result_format="json",
    result_type="full"
)
print([x for x in dir(qc) if not x.startswith('_')])
