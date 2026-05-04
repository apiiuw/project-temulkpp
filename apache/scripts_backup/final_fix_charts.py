from superset.app import create_app
app = create_app()
app.app_context().push()

from superset import db
from superset.models.slice import Slice
import json

def final_fix_all_charts():
    charts_config = {
        'Agent Chart': {
            'viz_type': 'echarts_timeseries_line',
            'datasource_type': 'table',
        },
        'Tren Kedatangan': {
            'viz_type': 'echarts_timeseries_line',
            'datasource_type': 'table',
        },
        'Distribusi Layanan': {
            'viz_type': 'pie',
            'datasource_type': 'table',
        },
        'Rata-rata Durasi': {
            'viz_type': 'big_number_total',
            'datasource_type': 'table',
        },
        'Status Reservasi': {
            'viz_type': 'pie',
            'datasource_type': 'table',
        },
    }

    slices = db.session.query(Slice).all()
    for slc in slices:
        print(f"\nFixing: {slc.slice_name}")
        
        # Fix viz_type if needed
        config = charts_config.get(slc.slice_name)
        if config and slc.viz_type != config['viz_type']:
            slc.viz_type = config['viz_type']
            print(f"  viz_type fixed to: {slc.viz_type}")
        
        # Fix params: remove adhoc_filters, ensure 'No filter' for time_range
        if slc.params:
            params = json.loads(slc.params)
        else:
            params = {}
        
        params['adhoc_filters'] = []
        if 'time_range' not in params or params['time_range'] == 'N/A':
            params['time_range'] = 'No filter'
        
        slc.params = json.dumps(params)
        
        # CRITICAL: Set query_context to NULL so Superset regenerates it
        slc.query_context = None
        print(f"  query_context cleared to NULL")
        print(f"  params.adhoc_filters cleared")

    db.session.commit()
    print("\n=== All charts finalized! ===")
    print("Now open each chart at localhost:8088, Edit, and Save to regenerate query_context.")

if __name__ == "__main__":
    final_fix_all_charts()
