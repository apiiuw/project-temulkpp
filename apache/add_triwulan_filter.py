"""Tambah kolom ``triwulan`` + native filter "Triwulan" (I-IV) di semua dashboard.

Pola filter mengikuti filter "Tahun" yang sudah ada (NATIVE_FILTER-TEMU_TAHUN):
filter_select dengan controlValues identik, defaultDataMask kosong (tanpa
default), dan target dataset yang sama dengan target Tahun pada dashboard tsb.

Kombinasi Tahun + Triwulan digabung Superset dengan AND, jadi
**Tahun 2024 + Triwulan I = Januari-Maret 2024**.

Idempotent & NON-DESTRUCTIVE:
- Dataset 1 (reservations, physical): tambah calculated column ``triwulan``.
- Dataset 4 (reservation_agent_analytics, virtual): tambah kolom ``triwulan``
  ke SQL (rewrite penuh, menambah kolom saja, tidak mengubah kolom lama).
- Dataset 5 (rating_answers, virtual): tambah ``triwulan`` di SELECT luar.
- Tiap dashboard (2,3,4,5): tambah native filter "Triwulan" (filter_select).
- Filter "Tahun" lama TIDAK disentuh sama sekali.

Run inside container::

    python /app/add_triwulan_filter.py
"""

from __future__ import annotations

import json
from datetime import datetime

from superset.app import create_app

app = create_app()
app.app_context().push()

from superset import db  # noqa: E402
from superset.connectors.sqla.models import SqlaTable, TableColumn  # noqa: E402
from superset.models.dashboard import Dashboard  # noqa: E402

TRIWULAN_ID = "NATIVE_FILTER-TEMU_TRIWULAN"
TRIWULAN_NAME = "Triwulan"

# Target dataset per dashboard — meniru target filter Tahun:
#   dash 2 (Agent)      : Tahun -> ds1
#   dash 3 (Superadmin) : Tahun -> ds1
#   dash 4 (Pimpinan)   : Tahun -> ds1 + ds4
#   dash 5 (Rating)     : belum ada filter Tahun; satu-satunya dataset
#                         ber-tanggal di dashboard ini adalah ds5.
DASH_TARGETS = {
    2: [1],
    3: [1],
    4: [1, 4],
    5: [5],
}


def triwulan_case(date_expr: str) -> str:
    """CASE MySQL pengubah tanggal menjadi label triwulan (NULL tetap NULL)."""
    return (
        "CASE "
        f"WHEN QUARTER({date_expr}) = 1 THEN 'Triwulan I' "
        f"WHEN QUARTER({date_expr}) = 2 THEN 'Triwulan II' "
        f"WHEN QUARTER({date_expr}) = 3 THEN 'Triwulan III' "
        f"WHEN QUARTER({date_expr}) = 4 THEN 'Triwulan IV' "
        "END"
    )


# Ekspresi untuk dataset fisik (tanpa alias tabel).
TRIWULAN_EXPR_PHYSICAL = triwulan_case("tanggal_jam")

DS4_SQL = """SELECT
    r.id,
    r.tanggal_jam,
    r.agent_id,
    COALESCE(a.name, 'Tanpa Agent') AS agent_name,
    YEAR(r.tanggal_jam) AS tahun,
    %s AS triwulan,
    CASE
        WHEN r.status = 'completed' THEN 'Selesai'
        ELSE 'Tidak Selesai'
    END AS status_kategori,
    CASE
        WHEN r.source = 'data-uji' OR r.tanggal_jam >= '2026-10-01' THEN 'Data Uji'
        ELSE 'Historis'
    END AS jenis_data
FROM reservations AS r
LEFT JOIN agents AS a ON a.id = r.agent_id""" % triwulan_case("r.tanggal_jam")

DS5_SQL = """SELECT
  t.*,
  CASE t.skor
    WHEN 5 THEN 'Sangat Baik (5)'
    WHEN 4 THEN 'Baik (4)'
    WHEN 3 THEN 'Cukup (3)'
    WHEN 2 THEN 'Kurang (2)'
    ELSE 'Sangat Kurang (1)'
  END AS label_skor,
  CASE
    WHEN t.source = 'data-uji' OR t.tanggal_jam >= '2026-10-01' THEN 'Data Uji'
    ELSE 'Historis'
  END AS jenis_data,
  %s AS triwulan
FROM (
  SELECT r.id, r.kode_reservasi, r.agent_id, r.tanggal_jam, r.tahun, r.rated_at, r.source,
         'Kemudahan Verifikasi' AS pertanyaan, 1 AS urutan, r.rating_kemudahan AS skor
    FROM reservations r WHERE r.rating_kemudahan IS NOT NULL
  UNION ALL
  SELECT r.id, r.kode_reservasi, r.agent_id, r.tanggal_jam, r.tahun, r.rated_at, r.source,
         'Efisiensi Waktu', 2, r.rating_efisiensi
    FROM reservations r WHERE r.rating_efisiensi IS NOT NULL
  UNION ALL
  SELECT r.id, r.kode_reservasi, r.agent_id, r.tanggal_jam, r.tahun, r.rated_at, r.source,
         'Sikap Pelayanan', 3, r.rating_sikap
    FROM reservations r WHERE r.rating_sikap IS NOT NULL
  UNION ALL
  SELECT r.id, r.kode_reservasi, r.agent_id, r.tanggal_jam, r.tahun, r.rated_at, r.source,
         'Kejelasan & Solusi', 4, r.rating_kejelasan
    FROM reservations r WHERE r.rating_kejelasan IS NOT NULL
  UNION ALL
  SELECT r.id, r.kode_reservasi, r.agent_id, r.tanggal_jam, r.tahun, r.rated_at, r.source,
         'Kepuasan Keseluruhan', 5, r.rating_kepuasan
    FROM reservations r WHERE r.rating_kepuasan IS NOT NULL
) t""" % triwulan_case("t.tanggal_jam")


def ensure_calc_column(ds: SqlaTable, name: str, expr: str,
                       ctype: str = "VARCHAR") -> TableColumn:
    col = (db.session.query(TableColumn)
           .filter_by(table_id=ds.id, column_name=name).first())
    if col is None:
        col = TableColumn(table_id=ds.id, column_name=name, type=ctype,
                          expression=expr, groupby=True, filterable=True,
                          is_active=True)
        db.session.add(col)
        db.session.flush()
    return col


def ensure_plain_column(ds: SqlaTable, name: str,
                        ctype: str = "VARCHAR") -> TableColumn:
    col = (db.session.query(TableColumn)
           .filter_by(table_id=ds.id, column_name=name).first())
    if col is None:
        col = TableColumn(table_id=ds.id, column_name=name, type=ctype,
                          groupby=True, filterable=True, is_active=True)
        db.session.add(col)
        db.session.flush()
    return col


def touch(ds: SqlaTable) -> None:
    ds.changed_on = datetime.now()
    ds.changed_by_fk = 1


def charts_for(dash: Dashboard, dataset_ids: list) -> list:
    """Chart pada dashboard yang memakai salah satu dataset target."""
    return sorted({
        sl.id for sl in dash.slices
        if sl.datasource_id in dataset_ids and sl.datasource_type == "table"
    })


def build_triwulan_filter(dash: Dashboard, dataset_ids: list) -> dict:
    """Native filter 'Triwulan' — struktur & controlValues sama seperti 'Tahun'."""
    return {
        "id": TRIWULAN_ID,
        "name": TRIWULAN_NAME,
        "title": TRIWULAN_NAME,
        "filterType": "filter_select",
        "targets": [
            {"datasetId": did, "column": {"name": "triwulan"}}
            for did in dataset_ids
        ],
        # Tanpa default (sama seperti filter Tahun): semua triwulan ditampilkan.
        "defaultDataMask": {
            "extraFormData": {},
            "filterState": {},
            "ownState": {},
        },
        "controlValues": {
            "enableEmptyFilter": False,
            "defaultToFirstItem": False,
            "multiSelect": True,
            "searchAllOptions": False,
            "inverseSelection": False,
        },
        "cascadeParentIds": [],
        "scope": {"rootPath": ["ROOT_ID"], "excluded": []},
        "type": "NATIVE_FILTER",
        "chartsInScope": charts_for(dash, dataset_ids),
        "tabsInScope": [],
    }


def main() -> None:
    # --- Dataset 1: reservations (physical) -> calculated column -------------
    ds1 = db.session.get(SqlaTable, 1)
    ensure_calc_column(ds1, "triwulan", TRIWULAN_EXPR_PHYSICAL)
    touch(ds1)
    print("ds1 reservations: kolom triwulan siap (calculated)")

    # --- Dataset 4: reservation_agent_analytics (virtual) -------------------
    ds4 = db.session.get(SqlaTable, 4)
    if "triwulan" not in (ds4.sql or ""):
        ds4.sql = DS4_SQL
        touch(ds4)
        print("ds4: SQL diperbarui (+ triwulan)")
    ensure_plain_column(ds4, "triwulan")
    print("ds4: kolom triwulan siap")

    # --- Dataset 5: rating_answers (virtual) --------------------------------
    ds5 = db.session.get(SqlaTable, 5)
    if "triwulan" not in (ds5.sql or ""):
        ds5.sql = DS5_SQL
        touch(ds5)
        print("ds5: SQL diperbarui (+ triwulan)")
    ensure_plain_column(ds5, "triwulan")
    print("ds5: kolom triwulan siap")

    db.session.flush()

    # --- Native filter per dashboard ---------------------------------------
    for dash_id, ds_ids in DASH_TARGETS.items():
        dash = db.session.get(Dashboard, dash_id)
        if dash is None:
            print(f"dash {dash_id}: tidak ditemukan, skip")
            continue

        meta = {}
        try:
            meta = json.loads(dash.json_metadata or "{}")
        except (TypeError, ValueError):
            meta = {}

        nfl = meta.get("native_filter_configuration") or []
        nfl = [f for f in nfl if f.get("id") != TRIWULAN_ID]  # hindari duplikat

        # Filter Tahun dan filter lama lainnya (mis. "Jenis Data") dipertahankan
        # apa adanya; Triwulan ditambahkan sebagai filter terakhir.
        nfl.append(build_triwulan_filter(dash, ds_ids))
        meta["native_filter_configuration"] = nfl
        dash.json_metadata = json.dumps(meta)
        dash.changed_on = datetime.now()
        dash.changed_by_fk = 1
        print(f"dash {dash_id} ({dash.dashboard_title}): filter "
              f"'{TRIWULAN_NAME}' ditambahkan, target ds={ds_ids}, "
              f"charts={charts_for(dash, ds_ids)}")

    db.session.commit()
    print("COMMIT OK")


if __name__ == "__main__":
    main()
