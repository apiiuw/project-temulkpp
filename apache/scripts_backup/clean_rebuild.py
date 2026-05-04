import json
from superset import db
from superset.models.slice import Slice
from superset.connectors.sqla.models import SqlaTable, SqlMetric, TableColumn

def clean_and_rebuild():
    dataset = db.session.query(SqlaTable).filter_by(table_name='reservations').first()
    if not dataset: return

    # 1. Bersihkan kolom-kolom lama yang mungkin korup/error
    for col in dataset.columns:
        if col.column_name == 'durasi':
            db.session.delete(col)
    
    # 2. Pastikan metrik SQL murni terdaftar
    metrics = {
        "count": "count(*)",
        "avg_durasi": "avg(TIMESTAMPDIFF(MINUTE, waktu_mulai_tatap_muka, waktu_selesai_tatap_muka))"
    }
    
    for name, expr in metrics.items():
        existing = db.session.query(SqlMetric).filter_by(table_id=dataset.id, metric_name=name).first()
        if existing:
            existing.expression = expr
        else:
            db.session.add(SqlMetric(table_id=dataset.id, metric_name=name, expression=expr))
    
    db.session.commit()

    # 3. Bangun ulang chart dengan tipe paling stabil
    theme_colors = {"SPSE": "#dc2626", "Non SPSE": "#f59e0b", "count": "#dc2626"}

    charts = db.session.query(Slice).filter(Slice.datasource_id == dataset.id).all()
    for chart in charts:
        params = json.loads(chart.params) if chart.params else {}
        params["label_colors"] = theme_colors
        params["granularity_sqla"] = "tanggal_jam"
        params["time_range"] = "No filter"

        if chart.slice_name == "Tren Kedatangan (7 Hari Terakhir)":
            # Gunakan Big Number with Line (Sangat stabil & informatif)
            chart.viz_type = "big_number_count"
            params["viz_type"] = "big_number_count"
            params["metric"] = "count"
            params["compare_lag"] = 1
            params["time_range"] = "Last 7 days"

        elif chart.slice_name == "Distribusi Jenis Layanan":
            # Gunakan Pie standar tapi pastikan format metrik benar
            chart.viz_type = "pie"
            params["viz_type"] = "pie"
            params["metric"] = "count"
            params["groupby"] = ["jenis_layanan"]
            params["donut"] = True

        elif chart.slice_name == "Rata-rata Durasi Konsultasi":
            chart.viz_type = "big_number"
            params["viz_type"] = "big_number"
            params["metric"] = "avg_durasi"
            params["y_axis_format"] = ".1f"

        chart.params = json.dumps(params)
        print(f"Rebuilt: {chart.slice_name} to {chart.viz_type}")

    db.session.commit()
    print("Cleanup and rebuild completed.")

clean_and_rebuild()
