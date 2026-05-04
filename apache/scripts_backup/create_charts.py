import os
import json
from superset.app import create_app
from superset import db
from superset.models.slice import Slice
from superset.connectors.sqla.models import SqlaTable, TableColumn

# Setup app context
app = create_app()
app.app_context().push()

def create_charts():
    # Find dataset
    dataset = db.session.query(SqlaTable).filter_by(table_name='reservations').first()
    if not dataset:
        print("Dataset 'reservations' not found.")
        return

    # 1. Add calculated column 'durasi' if it doesn't exist
    col_name = 'durasi'
    existing_col = db.session.query(TableColumn).filter_by(table_id=dataset.id, column_name=col_name).first()
    
    if not existing_col:
        print(f"Adding column: {col_name}")
        col = TableColumn(
            column_name=col_name,
            expression="TIMESTAMPDIFF(MINUTE, waktu_mulai_tatap_muka, waktu_selesai_tatap_muka)",
            is_dttm=False,
            type="INT",
            verbose_name="Durasi Konsultasi",
            table_id=dataset.id
        )
        db.session.add(col)
        db.session.commit()
    else:
        print(f"Column '{col_name}' already exists.")

    # Define Charts to create
    charts = [
        {
            "slice_name": "Tren Kedatangan (7 Hari Terakhir)",
            "viz_type": "echarts_timeseries_line",
            "params": {
                "datasource": f"{dataset.id}__table",
                "viz_type": "echarts_timeseries_line",
                "granularity_sqla": "tanggal_jam",
                "time_range": "Last 7 days",
                "time_grain_sqla": "P1D",
                "metrics": ["count"],
                "show_brush": "auto",
                "show_legend": True,
                "rich_tooltip": True,
            }
        },
        {
            "slice_name": "Distribusi Jenis Layanan",
            "viz_type": "pie",
            "params": {
                "datasource": f"{dataset.id}__table",
                "viz_type": "pie",
                "groupby": ["jenis_layanan"],
                "metric": "count",
                "donut": True,
                "show_legend": True,
                "label_type": "key",
            }
        },
        {
            "slice_name": "Rata-rata Durasi Konsultasi",
            "viz_type": "big_number_total",
            "params": {
                "datasource": f"{dataset.id}__table",
                "viz_type": "big_number_total",
                "metric": {
                    "aggregate": "AVG",
                    "column": {"column_name": "durasi"},
                    "expressionType": "SIMPLE",
                    "label": "AVG(durasi)"
                },
                "subheader": "Menit",
            }
        }
    ]

    for c in charts:
        # Check if already exists
        existing = db.session.query(Slice).filter_by(slice_name=c['slice_name']).first()
        if existing:
            print(f"Chart '{c['slice_name']}' already exists. Updating...")
            existing.params = json.dumps(c['params'])
            existing.viz_type = c['viz_type']
            existing.datasource_id = dataset.id
            existing.datasource_type = 'table'
        else:
            print(f"Creating chart: {c['slice_name']}")
            new_slice = Slice(
                slice_name=c['slice_name'],
                viz_type=c['viz_type'],
                datasource_id=dataset.id,
                datasource_type='table',
                params=json.dumps(c['params']),
                created_by_fk=1 # Admin
            )
            db.session.add(new_slice)
    
    db.session.commit()
    print("Done!")

if __name__ == "__main__":
    create_charts()
