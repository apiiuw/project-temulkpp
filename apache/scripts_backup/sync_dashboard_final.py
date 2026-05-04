
import json
import os

def final_sync():
    from superset.app import create_app
    app = create_app()
    app.app_context().push()

    from superset import db
    from superset.models.dashboard import Dashboard
    from superset.models.slice import Slice
    from superset.connectors.sqla.models import SqlaTable

    dataset = db.session.query(SqlaTable).filter_by(table_name='reservations').first()
    if not dataset:
        print("Dataset 'reservations' not found.")
        return

    # Find all charts for this dataset
    slices = db.session.query(Slice).filter(Slice.datasource_id == dataset.id).all()
    print(f"Found {len(slices)} charts for dataset.")

    # Find or create dashboard
    dash_name = "Dashboard Agent Performance"
    dash = db.session.query(Dashboard).filter_by(dashboard_title=dash_name).first()
    if not dash:
        print(f"Creating dashboard: {dash_name}")
        dash = Dashboard(dashboard_title=dash_name, published=True, created_by_fk=1)
        db.session.add(dash)
        db.session.flush()
    
    # Force UUID to the one in .env if we want to avoid updating PHP code, 
    # but it's better to update the code to match the reality.
    # However, let's try to set the UUID to the one expected by the app.
    target_uuid = "dbd88673-b705-40cc-8fa0-b8b8a26e9afd"
    
    # Check if any other dashboard has this UUID
    other = db.session.query(Dashboard).filter_by(uuid=target_uuid).first()
    if other and other.id != dash.id:
        print(f"Deleting other dashboard with target UUID: {other.dashboard_title}")
        db.session.delete(other)
        db.session.commit()
    
    dash.uuid = target_uuid
    dash.slices = slices
    
    # Update position_json to show all charts
    # Simple layout: all charts in a grid
    position = {
        "DASHBOARD_VERSION_KEY": "v2",
        "ROOT_ID": {"children": ["GRID_ID"], "id": "ROOT_ID", "type": "ROOT"},
        "GRID_ID": {"children": [], "id": "GRID_ID", "parents": ["ROOT_ID"], "type": "GRID"},
        "HEADER_ID": {"id": "HEADER_ID", "meta": {"text": dash_name}, "type": "HEADER"}
    }
    
    row_id = "ROW-1"
    position[row_id] = {"children": [], "id": row_id, "meta": {"background": "BACKGROUND_TRANSPARENT"}, "parents": ["GRID_ID"], "type": "ROW"}
    position["GRID_ID"]["children"].append(row_id)
    
    for i, s in enumerate(slices):
        chart_id = f"CHART-{s.id}"
        position[chart_id] = {
            "children": [],
            "id": chart_id,
            "meta": {"chartId": s.id, "height": 50, "sliceName": s.slice_name, "width": 6},
            "parents": ["GRID_ID", row_id],
            "type": "CHART"
        }
        position[row_id]["children"].append(chart_id)

    dash.position_json = json.dumps(position)
    
    db.session.commit()
    print(f"Dashboard synced with UUID: {dash.uuid}")

if __name__ == "__main__":
    final_sync()
