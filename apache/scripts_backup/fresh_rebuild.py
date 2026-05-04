import json
import uuid
from superset import db
from superset.models.slice import Slice
from superset.models.dashboard import Dashboard
from superset.connectors.sqla.models import SqlaTable, SqlMetric
from superset.models.embedded_dashboard import EmbeddedDashboard

def fresh_rebuild():
    dataset = db.session.query(SqlaTable).filter_by(table_name='reservations').first()
    if not dataset:
        print("Dataset reservations not found.")
        return

    # Ensure Metrics exist
    metrics = [
        {"metric_name": "count", "expression": "count(*)"},
        {"metric_name": "avg_durasi", "expression": "avg(TIMESTAMPDIFF(MINUTE, waktu_mulai_tatap_muka, waktu_selesai_tatap_muka))"}
    ]
    for m in metrics:
        if not db.session.query(SqlMetric).filter_by(table_id=dataset.id, metric_name=m['metric_name']).first():
            db.session.add(SqlMetric(table_id=dataset.id, **m))
    db.session.commit()

    theme_colors = {"SPSE": "#dc2626", "Non SPSE": "#f59e0b", "count": "#dc2626"}
    
    new_charts = []
    
    # 1. Tren Chart
    c1 = Slice(
        slice_name="Tren Kedatangan",
        viz_type="line",
        datasource_type="table",
        datasource_id=dataset.id,
        params=json.dumps({
            "granularity_sqla": "tanggal_jam",
            "time_range": "Last 7 days",
            "metrics": ["count"],
            "label_colors": theme_colors,
            "color_scheme": "supersetColors"
        })
    )
    new_charts.append(c1)

    # 2. Distribusi Chart
    c2 = Slice(
        slice_name="Distribusi Layanan",
        viz_type="pie",
        datasource_type="table",
        datasource_id=dataset.id,
        params=json.dumps({
            "granularity_sqla": "tanggal_jam",
            "time_range": "No filter",
            "groupby": ["jenis_layanan"],
            "metric": "count",
            "donut": True,
            "label_colors": theme_colors,
            "color_scheme": "supersetColors"
        })
    )
    new_charts.append(c2)

    # 3. Durasi Chart
    c3 = Slice(
        slice_name="Rata-rata Durasi",
        viz_type="big_number",
        datasource_type="table",
        datasource_id=dataset.id,
        params=json.dumps({
            "granularity_sqla": "tanggal_jam",
            "time_range": "No filter",
            "metric": "avg_durasi",
            "y_axis_format": ".1f",
            "label_colors": theme_colors,
            "color_scheme": "supersetColors"
        })
    )
    new_charts.append(c3)

    for c in new_charts:
        db.session.add(c)
    db.session.commit()

    # Create Dashboard
    dash = Dashboard(
        dashboard_title="Dashboard Agent Performance",
        slug="agent-performance-new",
        published=True,
        json_metadata=json.dumps({
            "color_scheme": "supersetColors",
            "label_colors": theme_colors,
            "timed_refresh_immune_slices": [],
            "expanded_slices": {},
            "refresh_frequency": 0,
            "default_filters": "{}",
            "shared_label_colors": theme_colors
        })
    )
    
    # Add charts to dashboard
    for c in new_charts:
        dash.slices.append(c)
    
    db.session.add(dash)
    db.session.commit()

    # Enable Embedding
    embedded = EmbeddedDashboard(
        dashboard_id=dash.id,
        uuid=str(uuid.uuid4()),
        allow_domain_list="localhost,127.0.0.1"
    )
    db.session.add(embedded)
    db.session.commit()

    print(f"REBUILD SUCCESSFUL.")
    print(f"DASHBOARD_UUID: {dash.uuid}")
    print(f"EMBEDDED_ID: {embedded.uuid}")

fresh_rebuild()
