import json
from datetime import datetime
from superset.app import create_app
app = create_app()
app.app_context().push()
from superset import db
from superset.models.dashboard import Dashboard

FILTER_ID = "NATIVE_FILTER-TEMU_JENIS_DATA"
for dash_id in [3, 4]:
    dash = db.session.query(Dashboard).get(dash_id)
    if dash is None:
        print(f"dash {dash_id}: tidak ditemukan")
        continue
    try:
        meta = json.loads(dash.json_metadata or "{}")
    except (TypeError, ValueError):
        meta = {}
    nfl = meta.get("native_filter_configuration") or []
    before = len(nfl)
    nfl = [f for f in nfl if f.get("id") != FILTER_ID]
    if len(nfl) != before:
        meta["native_filter_configuration"] = nfl
        dash.json_metadata = json.dumps(meta)
        dash.changed_on = datetime.now()
        dash.changed_by_fk = 1
        print(f"dash {dash_id}: filter '{FILTER_ID}' dihapus ({before}->{len(nfl)})")
    else:
        print(f"dash {dash_id}: filter tidak ditemukan (skip)")
db.session.commit()
print("COMMIT OK")
