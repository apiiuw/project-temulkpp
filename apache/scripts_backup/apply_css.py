import json
from superset.app import create_app
app = create_app()
app.app_context().push()

from superset import db
from superset.models.dashboard import Dashboard

def apply_dashboard_css():
    dash_uuid = "dbd88673-b705-40cc-8fa0-b8b8a26e9afd"
    dash = db.session.query(Dashboard).filter_by(uuid=dash_uuid).first()
    
    if not dash:
        print("Dashboard not found!")
        return

    custom_css = """
/* 1. Latar Belakang Dashboard (Transparan agar menyatu dengan background Laravel) */
.dashboard {
    background-color: transparent !important;
}
.dashboard-content {
    background: transparent !important;
}

/* 2. Desain Card Grafik (Menyesuaikan rounded-2xl dan soft-shadow Laravel) */
.dashboard-component-chart-holder {
    background-color: #ffffff !important;
    border: 1px solid #f5f5f4 !important; /* border-stone-100 */
    border-radius: 28px !important; /* Sudut melengkung besar */
    box-shadow: 0 18px 45px rgba(28, 25, 23, 0.06) !important; /* Shadow premium */
    padding: 16px 20px !important;
    overflow: hidden !important;
    transition: transform 0.3s ease, box-shadow 0.3s ease !important;
}

/* Efek interaktif saat hover pada Card */
.dashboard-component-chart-holder:hover {
    box-shadow: 0 24px 60px rgba(120, 53, 15, 0.08) !important;
}

/* 3. Mengatur Judul Grafik (Sesuai text-xl font-black text-stone-900) */
.dashboard .chart-header .header-title {
    color: #1c1917 !important; /* text-stone-900 */
    font-weight: 900 !important;
    font-size: 1.15rem !important;
    letter-spacing: -0.02em !important;
    padding-bottom: 8px !important;
}

/* 4. Desain Komponen Big Number (Angka Utama) */
.header-line {
    color: #1c1917 !important; /* text-stone-900 */
    font-weight: 900 !important;
    font-size: 2.5rem !important;
}

/* Sub-teks pada Big Number (Menyesuaikan text-xs font-bold uppercase text-red-600) */
.subheader-line {
    color: #dc2626 !important; /* text-red-600 */
    text-transform: uppercase !important;
    letter-spacing: 0.22em !important;
    font-size: 0.75rem !important;
    font-weight: 800 !important;
    margin-top: 8px !important;
}

/* 5. Mengatur Warna Label Teks, Sumbu X/Y, dan Legend Echarts */
text {
    fill: #78716c !important; /* text-stone-500 */
    font-family: inherit !important;
}

/* 6. Hilangkan border bawaan dari superset chart grid */
.chart-container {
    border: none !important;
}
.slice_container {
    background: transparent !important;
}
"""
    
    dash.css = custom_css
    db.session.commit()
    print("CSS applied successfully to dashboard!")

if __name__ == "__main__":
    apply_dashboard_css()
