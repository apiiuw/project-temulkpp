
import json
import os

def fix_dataset_and_charts():
    from superset.app import create_app
    app = create_app()
    app.app_context().push()

    from superset import db
    from superset.connectors.sqla.models import SqlaTable, SqlMetric, TableColumn
    from superset.models.slice import Slice

    dataset = db.session.query(SqlaTable).filter_by(table_name='reservations').first()
    if not dataset:
        print("Dataset 'reservations' not found.")
        return

    print(f"Dataset found: {dataset.table_name} (ID: {dataset.id})")

    # 1. Ensure Columns exist
    columns_to_ensure = [
        {
            "column_name": "durasi",
            "expression": "TIMESTAMPDIFF(MINUTE, waktu_mulai_tatap_muka, waktu_selesai_tatap_muka)",
            "type": "INT",
            "verbose_name": "Durasi Konsultasi"
        }
    ]

    for col_data in columns_to_ensure:
        existing_col = db.session.query(TableColumn).filter_by(table_id=dataset.id, column_name=col_data['column_name']).first()
        if not existing_col:
            col = TableColumn(
                column_name=col_data['column_name'],
                expression=col_data['expression'],
                is_dttm=False,
                type=col_data['type'],
                verbose_name=col_data['verbose_name'],
                table_id=dataset.id
            )
            db.session.add(col)
        else:
            existing_col.expression = col_data['expression']

    # 2. Ensure Metrics exist
    metrics_to_ensure = [
        {
            "metric_name": "count",
            "expression": "count(*)",
            "verbose_name": "Jumlah Reservasi"
        },
        {
            "metric_name": "avg_durasi",
            "expression": "AVG(TIMESTAMPDIFF(MINUTE, waktu_mulai_tatap_muka, waktu_selesai_tatap_muka))",
            "verbose_name": "Rata-rata Durasi"
        }
    ]

    for metric_data in metrics_to_ensure:
        existing_metric = db.session.query(SqlMetric).filter_by(table_id=dataset.id, metric_name=metric_data['metric_name']).first()
        if not existing_metric:
            metric = SqlMetric(
                metric_name=metric_data['metric_name'],
                expression=metric_data['expression'],
                table_id=dataset.id,
                verbose_name=metric_data['verbose_name']
            )
            db.session.add(metric)
        else:
            existing_metric.expression = metric_data['expression']

    db.session.commit()

    # 3. Define standard charts
    standard_charts = [
        {
            "name": "Tren Kedatangan",
            "viz_type": "echarts_timeseries_line",
            "params": {
                "metrics": ["count"],
                "granularity_sqla": "tanggal_jam",
                "time_range": "Last 7 days",
                "time_grain_sqla": "P1D",
                "show_legend": True,
            }
        },
        {
            "name": "Distribusi Layanan",
            "viz_type": "echarts_pie",
            "params": {
                "metric": "count",
                "groupby": ["jenis_layanan"],
                "donut": True,
                "show_legend": True,
            }
        },
        {
            "name": "Rata-rata Durasi",
            "viz_type": "big_number_total",
            "params": {
                "metric": "avg_durasi",
                "subheader_label": "Menit",
                "y_axis_format": ".1f"
            }
        },
        {
            "name": "Status Reservasi",
            "viz_type": "echarts_pie",
            "params": {
                "metric": "count",
                "groupby": ["status"],
                "donut": False,
                "show_legend": True,
            }
        }
    ]

    theme_colors = {
        "SPSE": "#dc2626",
        "Non SPSE": "#f59e0b",
        "pending": "#78716c",
        "completed": "#10b981",
        "in_progress": "#f97316",
        "count": "#dc2626",
        "avg_durasi": "#dc2626"
    }

    for chart_def in standard_charts:
        chart = db.session.query(Slice).filter(Slice.slice_name.like(f"%{chart_def['name']}%"), Slice.datasource_id == dataset.id).first()
        if not chart:
            print(f"Creating missing chart: {chart_def['name']}")
            chart = Slice(
                slice_name=chart_def['name'],
                viz_type=chart_def['viz_type'],
                datasource_id=dataset.id,
                datasource_type='table',
                created_by_fk=1
            )
            db.session.add(chart)
            db.session.flush()
        
        params = json.loads(chart.params) if chart.params else {}
        params.update(chart_def['params'])
        params["label_colors"] = theme_colors
        params["color_scheme"] = "supersetColors"
        
        chart.params = json.dumps(params)
        chart.viz_type = chart_def['viz_type']
        print(f"Synced Chart: {chart.slice_name}")

    db.session.commit()
    print("All fixes applied successfully.")

if __name__ == "__main__":
    fix_dataset_and_charts()
