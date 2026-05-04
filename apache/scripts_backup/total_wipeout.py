from superset import db
from superset.models.slice import Slice
from superset.models.dashboard import Dashboard
from superset.connectors.sqla.models import SqlaTable

def total_wipeout():
    # 1. Hapus Dashboard Agent Performance
    dash = db.session.query(Dashboard).filter_by(dashboard_title='Dashboard Agent Performance').first()
    if dash:
        db.session.delete(dash)
        print("Deleted existing dashboard.")

    # 2. Hapus Chart-chart terkait dataset reservations
    dataset = db.session.query(SqlaTable).filter_by(table_name='reservations').first()
    if dataset:
        charts = db.session.query(Slice).filter(Slice.datasource_id == dataset.id).all()
        for chart in charts:
            db.session.delete(chart)
        print(f"Deleted {len(charts)} charts.")

    db.session.commit()
    print("Cleanup successful. Ready to rebuild.")

total_wipeout()
