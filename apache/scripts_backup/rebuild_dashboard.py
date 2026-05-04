from superset.app import create_app
app = create_app()
app.app_context().push()

from superset import db
from superset.models.dashboard import Dashboard
from superset.models.slice import Slice
from superset.models.embedded_dashboard import EmbeddedDashboard
import json

def rebuild_agent_dashboard():
    # Target dashboard
    dash = db.session.query(Dashboard).filter_by(uuid='dbd88673-b705-40cc-8fa0-b8b8a26e9afd').first()
    if not dash:
        print("Dashboard Agent Performance not found!")
        return

    print(f"Dashboard: {dash.dashboard_title}")
    print(f"Current slices: {[s.slice_name for s in dash.slices]}")

    # Get all relevant slices (those using dataset 1 = reservations)
    all_slices = db.session.query(Slice).filter_by(datasource_id=1).all()
    print(f"\nSlices using reservations dataset:")
    for slc in all_slices:
        print(f"  - {slc.slice_name} (viz: {slc.viz_type})")

    # Ensure all reservations slices are in the dashboard
    current_slice_ids = {s.id for s in dash.slices}
    for slc in all_slices:
        if slc.id not in current_slice_ids:
            dash.slices.append(slc)
            print(f"Added {slc.slice_name} to dashboard")

    # Build a proper grid layout with all 4 charts
    # Dashboard layout positions: each row = 256 units, each column cell = 64 units wide
    slice_list = db.session.query(Slice).filter_by(datasource_id=1).all()
    
    layout = {
        "DASHBOARD_VERSION_KEY": "v2",
        "ROOT_ID": {
            "id": "ROOT_ID",
            "type": "ROOT",
            "children": ["GRID_ID"]
        },
        "GRID_ID": {
            "id": "GRID_ID",
            "type": "GRID",
            "children": ["ROW-top", "ROW-bottom"],
            "parents": ["ROOT_ID"]
        }
    }

    top_row_children = []
    bottom_row_children = []

    for i, slc in enumerate(slice_list):
        chart_id = f"CHART-{slc.id}"
        layout[chart_id] = {
            "id": chart_id,
            "type": "CHART",
            "meta": {
                "chartId": slc.id,
                "width": 6,
                "height": 50,
                "sliceName": slc.slice_name
            },
            "parents": ["ROOT_ID", "GRID_ID", "ROW-top" if i < 2 else "ROW-bottom"]
        }
        if i < 2:
            top_row_children.append(chart_id)
        else:
            bottom_row_children.append(chart_id)

    layout["ROW-top"] = {
        "id": "ROW-top",
        "type": "ROW",
        "children": top_row_children,
        "parents": ["ROOT_ID", "GRID_ID"],
        "meta": {"background": "BACKGROUND_TRANSPARENT"}
    }
    layout["ROW-bottom"] = {
        "id": "ROW-bottom",
        "type": "ROW",
        "children": bottom_row_children,
        "parents": ["ROOT_ID", "GRID_ID"],
        "meta": {"background": "BACKGROUND_TRANSPARENT"}
    }

    # Update position_json
    dash.position_json = json.dumps(layout)
    
    # Clean metadata
    if dash.json_metadata:
        meta = json.loads(dash.json_metadata)
    else:
        meta = {}
    
    meta['native_filter_configuration'] = []
    meta['filter_scopes'] = {}
    meta['default_filters'] = '{}'
    dash.json_metadata = json.dumps(meta)

    # Make sure it's published
    dash.published = True
    
    db.session.commit()
    print(f"\nDashboard rebuilt with {len(slice_list)} charts")
    print(f"Slices now: {[s.slice_name for s in dash.slices]}")

    # Verify embedded config
    embedded = db.session.query(EmbeddedDashboard).filter_by(dashboard_id=dash.id).first()
    if embedded:
        embedded.allow_domain_list = "*"
        db.session.commit()
        print(f"Embedded config updated (allow_domain_list: *)")
    
    print("\nDone! Now go to localhost:8088, open each chart, Edit and Save.")

if __name__ == "__main__":
    rebuild_agent_dashboard()
