from superset.app import create_app
app = create_app()
app.app_context().push()
from superset.common.query_context_factory import QueryContextFactory
print(QueryContextFactory)
