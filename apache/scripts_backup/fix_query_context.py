from superset.app import create_app
app = create_app()
app.app_context().push()

from superset import db
from superset.models.slice import Slice
import json

def fix_query_context():
    slices = db.session.query(Slice).all()
    
    for slc in slices:
        print(f"Processing {slc.slice_name}...")
        try:
            # We can use the get_query_context method if available, or just mock it by
            # letting Superset construct it from params
            form_data = json.loads(slc.params) if slc.params else {}
            
            # The easiest way to generate query_context is to use the ChartDataCommand
            # or just populate it from form_data manually for simple charts.
            # But wait, Superset's slice model has a method `get_query_context_factory()`
            # Let's try to just build a basic query_context
            
            # A simpler approach: we can just copy form_data into query_context like this:
            # query_context is a json string containing form_data and queries
            
            query_context = {
                "datasource": {"id": slc.datasource_id, "type": slc.datasource_type},
                "force": False,
                "queries": [form_data], # The form_data contains all the query instructions
                "result_format": "json",
                "result_type": "full",
                "form_data": form_data
            }
            slc.query_context = json.dumps(query_context)
            print(f"Updated query_context for {slc.slice_name}")
            
        except Exception as e:
            print(f"Error on {slc.slice_name}: {e}")
            
    db.session.commit()
    print("Done fixing query contexts.")

if __name__ == "__main__":
    fix_query_context()
