"""Tambah kolom computed ``jenis_data`` + native filter "Jenis Data" (default Historis).

Tujuan: memisahkan data historis dari data uji (source='data-uji' ATAU
tanggal_jam >= 2026-10-01) di seluruh dashboard Superset.

Idempotent & NON-DESTRUCTIVE:
- Dataset 1 (reservations, physical): tambah calculated column ``jenis_data``.
- Dataset 4 (reservation_agent_analytics, virtual): tambah kolom ``jenis_data``
  ke SQL (rewrite penuh, menambah kolom saja, tidak mengubah kolom lama).
- Dataset 5 (rating_answers, virtual): tambah ``r.source`` ke tiap branch UNION
  + kolom ``jenis_data`` di SELECT luar.
- Tiap dashboard (2,3,4,5): tambah native filter "Jenis Data" (filter_select)
  dengan default value "Historis" via ``defaultDataMask``. Filter lama (Tahun)
  tidak disentuh.

Run inside container::

    python /app/add_jenis_data_filter.py
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

FILTER_ID = "NATIVE_FILTER-TEMU_JENIS_DATA"
FILTER_NAME = "Jenis Data"

# Default value native filter per dashboard.
# - "Historis"  -> sembunyikan data uji secara default.
# - "Data Uji"  -> tampilkan data uji sebagai default.
# - None        -> tanpa default (menampilkan semua baris).
# Dashboard Rating (5) defaultnya "Data Uji" karena semua baris yang dinilai
# adalah data uji; kalau defaultnya "Historis" dashboard jadi kosong.
DASHBOARD_DEFAULT = {
    2: "Historis",
    3: "Historis",
    4: "Historis",
    5: "Data Uji",
}

# Ekspresi untuk dataset fisik (tanpa alias tabel).
JENIS_EXPR_PHYSICAL = (
    "CASE WHEN source = 'data-uji' OR tanggal_jam >= '2026-10-01' "
    "THEN 'Data Uji' ELSE 'Historis' END"
)

DS4_SQL = """SELECT
    r.id,
    r.tanggal_jam,
    r.agent_id,
    COALESCE(a.name, 'Tanpa Agent') AS agent_name,
    YEAR(r.tanggal_jam) AS tahun,
    CASE
        WHEN r.status = 'completed' THEN 'Selesai'
        ELSE 'Tidak Selesai'
    END AS status_kategori,
    CASE
        WHEN r.source = 'data-uji' OR r.tanggal_jam >= '2026-10-01' THEN 'Data Uji'
        ELSE 'Historis'
    END AS jenis_data
FROM reservations AS r
LEFT JOIN agents AS a ON a.id = r.agent_id"""

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
  END AS jenis_data
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
) t"""


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


def build_filter(target_dataset_ids: list, chart_ids: list,
                 default_value: str | None) -> dict:
    # Saat default_value None → tanpa defaultDataMask (menampilkan semua baris).
    if default_value is None:
        default_mask = {
            "extraFormData": {},
            "filterState": {},
            "ownState": {},
        }
    else:
        default_mask = {
            "extraFormData": {
                "filters": [
                    {"col": "jenis_data", "op": "IN", "val": [default_value]}
                ]
            },
            "filterState": {
                "label": default_value,
                "value": [default_value],
                "validateStatus": False,
                "validateMessage": "",
            },
            "ownState": {},
        }
    return {
        "id": FILTER_ID,
        "name": FILTER_NAME,
        "title": FILTER_NAME,
        "filterType": "filter_select",
        "targets": [
            {"datasetId": did, "column": {"name": "jenis_data"}}
            for did in target_dataset_ids
        ],
        "defaultDataMask": default_mask,
        "controlValues": {
            "enableEmptyFilter": False,
            "defaultToFirstItem": False,
            "multiSelect": True,
            "searchAllOptions": False,
            "inverseSelection": False,
            "sortMetric": None,
            "sortCollectionCriterion": "metric",
            "sortCollectionAscending": True,
        },
        "cascadeParentIds": [],
        "scope": {"rootPath": ["ROOT_ID"], "excluded": []},
        "type": "NATIVE_FILTER",
        "chartsInScope": sorted(set(chart_ids)),
        "tabsInScope": [],
    }


def main() -> None:
    # --- Dataset 1: reservations (physical) -> calculated column -------------
    ds1 = db.session.query(SqlaTable).get(1)
    ensure_calc_column(ds1, "jenis_data", JENIS_EXPR_PHYSICAL)
    touch(ds1)
    print(f"ds1 reservations: kolom jenis_data siap (calculated)")

    # --- Dataset 4: reservation_agent_analytics (virtual) -------------------
    ds4 = db.session.query(SqlaTable).get(4)
    if "jenis_data" not in (ds4.sql or ""):
        ds4.sql = DS4_SQL
        touch(ds4)
        print("ds4: SQL diperbarui (+ jenis_data)")
    ensure_plain_column(ds4, "jenis_data")
    print("ds4: kolom jenis_data siap")

    # --- Dataset 5: rating_answers (virtual) --------------------------------
    ds5 = db.session.query(SqlaTable).get(5)
    if "jenis_data" not in (ds5.sql or ""):
        ds5.sql = DS5_SQL
        touch(ds5)
        print("ds5: SQL diperbarui (+ source & jenis_data)")
    ensure_plain_column(ds5, "jenis_data")
    print("ds5: kolom jenis_data siap")

    db.session.flush()

    # --- Native filter per dashboard ---------------------------------------
    # Peta: dashboard_id -> (dataset target, ds yang dipakai untuk cek chart)
    dash_targets = {
        2: [1],           # Agent Performance  -> ds1
        3: [1, 4],        # Superadmin Overview-> ds1 + ds4 (ds3 virtual_roles diabaikan)
        4: [1, 4],        # Pimpinan Overview  -> ds1 + ds4
        5: [5],           # Rating             -> ds5
    }

    for dash_id, ds_ids in dash_targets.items():
        dash = db.session.query(Dashboard).get(dash_id)
        if dash is None:
            print(f"dash {dash_id}: tidak ditemukan, skip")
            continue

        # Chart ids di dashboard ini yang memakai salah satu dataset target.
        chart_ids = [
            sl.id for sl in dash.slices
            if sl.datasource_id in ds_ids and sl.datasource_type == "table"
        ]

        meta = {}
        try:
            meta = json.loads(dash.json_metadata or "{}")
        except (TypeError, ValueError):
            meta = {}

        nfl = meta.get("native_filter_configuration") or []
        nfl = [f for f in nfl if f.get("id") != FILTER_ID]  # hindari duplikat
        default_value = DASHBOARD_DEFAULT.get(dash_id, "Historis")
        nfl.append(build_filter(ds_ids, chart_ids, default_value))
        meta["native_filter_configuration"] = nfl
        dash.json_metadata = json.dumps(meta)
        dash.changed_on = datetime.now()
        dash.changed_by_fk = 1
        print(f"dash {dash_id} ({dash.dashboard_title}): filter "
              f"'{FILTER_NAME}' ditambahkan, target ds={ds_ids}, "
              f"charts={sorted(set(chart_ids))}, default="
              f"{default_value or 'SEMUA BARIS'}")

    db.session.commit()
    print("COMMIT OK")


if __name__ == "__main__":
    main()
