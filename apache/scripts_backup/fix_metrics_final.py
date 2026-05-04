from superset.app import create_app
app = create_app()
app.app_context().push()

from superset import db
from superset.models.slice import Slice
from superset.connectors.sqla.models import SqlaTable, SqlMetric
import json

def fix_charts_metrics():
    dataset = db.session.query(SqlaTable).filter_by(table_name='reservations').first()
    if not dataset:
        print("Dataset reservations not found!")
        return

    # 1. Check existing calculated metrics
    existing_metrics = {m.metric_name: m for m in dataset.metrics}
    print("Existing metrics:", list(existing_metrics.keys()))

    # 2. Create avg_durasi metric if it doesn't exist
    if 'avg_durasi' not in existing_metrics:
        print("Creating avg_durasi metric...")
        avg_durasi_metric = SqlMetric(
            metric_name='avg_durasi',
            expression='AVG(TIMESTAMPDIFF(MINUTE, waktu_mulai_tatap_muka, waktu_selesai_tatap_muka))',
            metric_type='avg',
            verbose_name='Rata-rata Durasi (menit)',
            description='Rata-rata durasi layanan dalam menit',
            table=dataset
        )
        db.session.add(avg_durasi_metric)
        print("avg_durasi metric created!")
    else:
        print("avg_durasi metric already exists")

    # 3. Also ensure 'count' metric exists
    if 'count' not in existing_metrics:
        print("Creating count metric...")
        count_metric = SqlMetric(
            metric_name='count',
            expression='COUNT(*)',
            metric_type='count',
            verbose_name='Count',
            description='Total records',
            table=dataset
        )
        db.session.add(count_metric)

    db.session.commit()
    print("\nMetrics setup done!")

    # 4. Fix Tren Kedatangan: needs x_axis column (tanggal_jam) properly set
    tren = db.session.query(Slice).filter_by(slice_name='Tren Kedatangan').first()
    if tren:
        params = json.loads(tren.params) if tren.params else {}
        params['metrics'] = ['count']
        params['groupby'] = []
        params['x_axis'] = 'tanggal_jam'
        params['time_range'] = 'No filter'
        params['granularity_sqla'] = 'tanggal_jam'
        params['granularity'] = 'month'
        params['adhoc_filters'] = []
        tren.params = json.dumps(params)
        tren.query_context = None
        print("\nFixed Tren Kedatangan params")

    # 5. Fix Distribusi Layanan: pie chart by jenis_layanan
    distribusi = db.session.query(Slice).filter_by(slice_name='Distribusi Layanan').first()
    if distribusi:
        params = json.loads(distribusi.params) if distribusi.params else {}
        params['metrics'] = ['count']
        params['groupby'] = ['jenis_layanan']
        params['time_range'] = 'No filter'
        params['adhoc_filters'] = []
        distribusi.params = json.dumps(params)
        distribusi.query_context = None
        print("Fixed Distribusi Layanan params")

    # 6. Fix Status Reservasi: pie chart by status
    status = db.session.query(Slice).filter_by(slice_name='Status Reservasi').first()
    if status:
        params = json.loads(status.params) if status.params else {}
        params['metrics'] = ['count']
        params['groupby'] = ['status']
        params['time_range'] = 'No filter'
        params['adhoc_filters'] = []
        status.params = json.dumps(params)
        status.query_context = None
        print("Fixed Status Reservasi params")

    # 7. Fix Rata-rata Durasi: big number chart
    durasi = db.session.query(Slice).filter_by(slice_name='Rata-rata Durasi').first()
    if durasi:
        params = json.loads(durasi.params) if durasi.params else {}
        params['metrics'] = ['avg_durasi']
        params['groupby'] = []
        params['time_range'] = 'No filter'
        params['adhoc_filters'] = []
        durasi.params = json.dumps(params)
        durasi.query_context = None
        print("Fixed Rata-rata Durasi params")

    db.session.commit()
    print("\n=== All charts fixed! ===")
    print("Now open localhost:8088, Edit + Save each chart to regenerate query_context.")

if __name__ == "__main__":
    fix_charts_metrics()
