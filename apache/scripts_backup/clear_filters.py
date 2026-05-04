from superset.app import create_app
app = create_app()
app.app_context().push()

from superset import db
from superset.models.dashboard import Dashboard
from superset.models.slice import Slice
import json

def clear_native_filters():
    # 1. Clear Native Filters from Dashboard
    dash = db.session.query(Dashboard).filter_by(dashboard_title='Dashboard Agent Performance').first()
    if dash and dash.json_metadata:
        metadata = json.loads(dash.json_metadata)
        if 'native_filter_configuration' in metadata:
            print("Removing native filters from dashboard...")
            metadata['native_filter_configuration'] = []
        if 'filter_scopes' in metadata:
            metadata['filter_scopes'] = {}
        if 'default_filters' in metadata:
            metadata['default_filters'] = "{}"
            
        # remove anything related to URL sync
        dash.json_metadata = json.dumps(metadata)
        print("Dashboard metadata cleaned.")

    # 2. Clear ad-hoc and time-range filters from Slices
    slices = db.session.query(Slice).all()
    for slc in slices:
        if slc.params:
            params = json.loads(slc.params)
            modified = False
            
            if 'adhoc_filters' in params and params['adhoc_filters']:
                print(f"Removing adhoc_filters from {slc.slice_name}")
                params['adhoc_filters'] = []
                modified = True
                
            if 'time_range' in params and params['time_range'] == 'No filter':
                pass # This is fine, but let's be sure
                
            if modified:
                slc.params = json.dumps(params)
                print(f"Saved cleaned params for {slc.slice_name}")

    db.session.commit()
    print("All filters cleared.")

if __name__ == "__main__":
    clear_native_filters()
