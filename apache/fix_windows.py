import os
from superset.app import create_app
from superset import db
from superset.models.core import Database
from superset.models.dashboard import Dashboard
from superset.models.embedded_dashboard import EmbeddedDashboard
from sqlalchemy import text

# Load Superset App
app = create_app()
app.app_context().push()

def fix_all():
    print("--- Memulai Perbaikan Superset di Windows ---")

    # 1. Pastikan Database Connection Benar (Laragon Default: root tanpa password)
    mysql_db = db.session.query(Database).filter(Database.database_name.like('%MySQL%')).first()
    if mysql_db:
        # Update ke URI default Windows (Laragon/XAMPP)
        # Jika Anda punya password, ubah bagian 'root:' menjadi 'root:PASSWORD_ANDA'
        mysql_db.sqlalchemy_uri = "mysql+pymysql://root:@127.0.0.1:3306/temulkpp"
        db.session.commit()
        print("✅ Koneksi Database MySQL telah disesuaikan ke root (tanpa password).")
    else:
        print("❌ Database MySQL tidak ditemukan di metadata.")

    # 2. Fix Embedded Domain
    dashboards = db.session.query(Dashboard).all()
    for dash in dashboards:
        embedded = db.session.query(EmbeddedDashboard).filter_by(dashboard_id=dash.id).first()
        if embedded:
            embedded.allow_domain_list = "*"
            db.session.commit()
            print(f"✅ Domain embedding untuk Dashboard '{dash.dashboard_title}' telah diatur ke '*'")

    # 3. Refresh Izin Role Public
    print("🔄 Sedang merefresh izin role Public (ini mungkin butuh waktu)...")
    os.system("superset init")
    
    print("\n--- Perbaikan Selesai! ---")
    print("Silakan jalankan ulang server superset dengan perintah:")
    print("superset run -p 8088 --with-threads --reload --debugger")

if __name__ == "__main__":
    fix_all()
