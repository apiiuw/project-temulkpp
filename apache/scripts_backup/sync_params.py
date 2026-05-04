import json
from superset.app import create_app
app = create_app()
app.app_context().push()

from superset import db
from superset.models.slice import Slice

def sync_params():
    # Load all slices
    slices = {slc.slice_name: slc for slc in db.session.query(Slice).all()}
    
    mapping = {
        # Pimpinan
        "Pimpinan Tren Kedatangan": ("Tren Kedatangan", {}),
        "Pimpinan Rata-rata Durasi": ("Rata-rata Durasi", {}),
        "Pimpinan Durasi Tercepat": ("Durasi Tercepat", {}),
        "Pimpinan Durasi Terlama": ("Durasi Terlama", {}),
        "Pimpinan Status Reservasi": ("Status Reservasi", {}),
        "Pimpinan Total Reservasi": ("Rata-rata Durasi", {"metric": "count", "subheader_label": "Reservasi", "y_axis_format": "SMART_NUMBER"}),
        "Pimpinan Beban Agent": ("Distribusi Layanan", {"groupby": ["agent_id"]}),
        
        # Superadmin (Global)
        "Global Tren Kedatangan": ("Tren Kedatangan", {}),
        "Global Rata-rata Durasi": ("Rata-rata Durasi", {}),
        "Global Durasi Tercepat": ("Durasi Tercepat", {}),
        "Global Durasi Terlama": ("Durasi Terlama", {}),
        "Global Status Reservasi": ("Status Reservasi", {}),
        "Global Distribusi Layanan": ("Distribusi Layanan", {}),
        "Global Kategori Terbanyak": ("Kategori Terbanyak", {}),
    }

    for target_name, (source_name, overrides) in mapping.items():
        if target_name in slices and source_name in slices:
            target_slc = slices[target_name]
            source_slc = slices[source_name]
            
            params = json.loads(source_slc.params)
            
            # Remove slice-specific and dashboard-specific fields
            if "slice_id" in params:
                del params["slice_id"]
            if "dashboards" in params:
                del params["dashboards"]
                
            # Apply overrides
            params.update(overrides)
            
            # Apply visual changes specific to big numbers if overridden
            if overrides.get("metric") == "count" and "y_axis_format" in overrides:
                params["y_axis_format"] = overrides["y_axis_format"]
                
            target_slc.params = json.dumps(params)
            print(f"Synced {target_name} from {source_name}")
            
    db.session.commit()
    print("Done syncing params.")

if __name__ == "__main__":
    sync_params()
