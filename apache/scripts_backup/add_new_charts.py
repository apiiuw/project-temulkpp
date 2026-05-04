import json
from superset.app import create_app
app = create_app()
app.app_context().push()

from superset import db
from superset.models.dashboard import Dashboard
from superset.models.slice import Slice
from superset.connectors.sqla.models import SqlaTable, TableColumn, SqlMetric

def add_new_charts():
    # 1. Create Virtual Dataset for Roles
    # Using database_id=2 as found previously
    roles_ds = db.session.query(SqlaTable).filter_by(table_name='virtual_roles').first()
    if not roles_ds:
        roles_ds = SqlaTable(
            table_name='virtual_roles',
            database_id=2,
            sql="SELECT 'Agent' as role, COUNT(*) as total FROM agents UNION ALL SELECT 'Pimpinan' as role, COUNT(*) as total FROM pimpinans",
            is_sqllab_view=True,
            created_by_fk=1
        )
        db.session.add(roles_ds)
        db.session.flush()

    # Ensure columns exist for virtual_roles
    for col_name, col_type in [('role', 'VARCHAR'), ('total', 'INTEGER')]:
        c = db.session.query(TableColumn).filter_by(table_id=roles_ds.id, column_name=col_name).first()
        if not c:
            c = TableColumn(table_id=roles_ds.id, column_name=col_name, type=col_type, groupby=True, filterable=True)
            db.session.add(c)
    
    db.session.commit()

    # 2. Add 'status_kategori' to reservations dataset
    res_ds = db.session.query(SqlaTable).filter_by(table_name='reservations').first()
    col = db.session.query(TableColumn).filter_by(table_id=res_ds.id, column_name='status_kategori').first()
    if not col:
        col = TableColumn(
            table_id=res_ds.id,
            column_name='status_kategori',
            expression="CASE WHEN status = 'completed' THEN 'Selesai' ELSE 'Tidak Selesai' END",
            type="VARCHAR",
            groupby=True,
            filterable=True
        )
        db.session.add(col)
    
    db.session.commit()

    # 3. Create the charts
    charts_to_add = [
        {
            "name": "Global Komposisi Role",
            "viz": "echarts_pie",
            "dataset": roles_ds,
            "params": {
                "metric": "sum__total",
                "groupby": ["role"],
                "color_scheme": "supersetColors",
                "show_legend": True,
                "label_type": "key_value",
                "donut": False,
                "time_range": "No filter"
            }
        },
        {
            "name": "Global Tren Selesai",
            "viz": "echarts_timeseries_bar",
            "dataset": res_ds,
            "params": {
                "metrics": ["count"],
                "groupby": ["status_kategori"],
                "granularity_sqla": "tanggal_jam",
                "time_range": "No filter",
                "color_scheme": "supersetColors",
                "stack": "Stack",
                "x_axis_time_format": "smart_date",
                "y_axis_format": "SMART_NUMBER"
            }
        },
        {
            "name": "Pimpinan Tren Selesai",
            "viz": "echarts_timeseries_bar",
            "dataset": res_ds,
            "params": {
                "metrics": ["count"],
                "groupby": ["status_kategori"],
                "granularity_sqla": "tanggal_jam",
                "time_range": "No filter",
                "color_scheme": "supersetColors",
                "stack": "Stack",
                "x_axis_time_format": "smart_date",
                "y_axis_format": "SMART_NUMBER"
            }
        }
    ]

    # ensure metric sum__total exists for virtual_roles
    m = db.session.query(SqlMetric).filter_by(table_id=roles_ds.id, metric_name='sum__total').first()
    if not m:
        m = SqlMetric(metric_name='sum__total', expression='SUM(total)', table_id=roles_ds.id)
        db.session.add(m)
        db.session.flush()

    new_slices = {}
    for c in charts_to_add:
        slc = db.session.query(Slice).filter_by(slice_name=c["name"]).first()
        if not slc:
            slc = Slice(slice_name=c["name"], viz_type=c["viz"], datasource_id=c["dataset"].id, datasource_type='table', created_by_fk=1)
            db.session.add(slc)
            db.session.flush()
            
        p = c["params"].copy()
        p["datasource"] = f"{c['dataset'].id}__table"
        p["viz_type"] = c["viz"]
        # Add common required fields for echarts
        if "echarts" in c["viz"]:
            p.update({
                "x_axis": p.get("granularity_sqla", ""),
                "time_grain_sqla": "P1D",
            })
            
        slc.params = json.dumps(p)
        new_slices[c["name"]] = slc

    db.session.commit()

    # 4. Update Superadmin Dashboard Layout
    dash_sa = db.session.query(Dashboard).filter_by(uuid="da1f9b3b-8911-4eb7-a7eb-9df03b41bb1c").first()
    if dash_sa:
        pos = json.loads(dash_sa.position_json)
        # We need a new ROW-4 to hold the new charts
        if "ROW-4" not in pos:
            pos["ROW-4"] = {"children": ["CHART-8", "CHART-9"], "id": "ROW-4", "meta": {"background": "BACKGROUND_TRANSPARENT"}, "parents": ["GRID_ID"], "type": "ROW"}
            pos["GRID_ID"]["children"].append("ROW-4")
        
        pos["CHART-8"] = {"children": [], "id": "CHART-8", "meta": {"chartId": new_slices["Global Komposisi Role"].id, "height": 25, "sliceName": "Global Komposisi Role", "width": 6}, "parents": ["GRID_ID", "ROW-4"], "type": "CHART"}
        pos["CHART-9"] = {"children": [], "id": "CHART-9", "meta": {"chartId": new_slices["Global Tren Selesai"].id, "height": 25, "sliceName": "Global Tren Selesai", "width": 6}, "parents": ["GRID_ID", "ROW-4"], "type": "CHART"}
        
        dash_sa.position_json = json.dumps(pos)
        
        # add slices to dashboard slices list if not present
        dash_slices = list(dash_sa.slices)
        if new_slices["Global Komposisi Role"] not in dash_slices:
            dash_slices.append(new_slices["Global Komposisi Role"])
        if new_slices["Global Tren Selesai"] not in dash_slices:
            dash_slices.append(new_slices["Global Tren Selesai"])
        dash_sa.slices = dash_slices

    # 5. Update Pimpinan Dashboard Layout
    dash_pim = db.session.query(Dashboard).filter_by(uuid="1a2b3c4d-5e6f-7a8b-9c0d-1e2f3a4b5c6d").first()
    if dash_pim:
        pos = json.loads(dash_pim.position_json)
        # Create ROW-4 for the new chart
        if "ROW-4" not in pos:
            pos["ROW-4"] = {"children": ["CHART-8"], "id": "ROW-4", "meta": {"background": "BACKGROUND_TRANSPARENT"}, "parents": ["GRID_ID"], "type": "ROW"}
            pos["GRID_ID"]["children"].append("ROW-4")
        
        pos["CHART-8"] = {"children": [], "id": "CHART-8", "meta": {"chartId": new_slices["Pimpinan Tren Selesai"].id, "height": 25, "sliceName": "Pimpinan Tren Selesai", "width": 12}, "parents": ["GRID_ID", "ROW-4"], "type": "CHART"}
        
        dash_pim.position_json = json.dumps(pos)
        
        dash_slices = list(dash_pim.slices)
        if new_slices["Pimpinan Tren Selesai"] not in dash_slices:
            dash_slices.append(new_slices["Pimpinan Tren Selesai"])
        dash_pim.slices = dash_slices

    db.session.commit()
    print("New charts created and layouts updated successfully.")

if __name__ == "__main__":
    add_new_charts()
