import json
from superset import db
from superset.models.dashboard import Dashboard

dash_name = "Dashboard Agent Performance"
dash = db.session.query(Dashboard).filter_by(dashboard_title=dash_name).first()

if dash:
    metadata = json.loads(dash.json_metadata) if dash.json_metadata else {}
    
    # Custom colors matching the Laravel theme
    metadata["label_colors"] = {
        "SPSE": "#dc2626",          # Red
        "Non SPSE": "#f59e0b",      # Amber
        "completed": "#10b981",     # Emerald
        "pending": "#78716c",       # Stone
        "in_progress": "#f97316",   # Orange
        "checked_in_front_desk": "#d97706" # Amber dark
    }
    # Set a clean layout or other params if needed
    metadata["shared_label_colors"] = metadata["label_colors"]
    
    dash.json_metadata = json.dumps(metadata)
    db.session.commit()
    print("Dashboard theme updated with web colors.")
else:
    print("Dashboard not found.")
