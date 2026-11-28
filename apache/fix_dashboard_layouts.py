"""Rapikan layout dashboard Superset: baris KPI seragam + rebuild Superadmin.

Dua perilaku berbeda dalam satu script (idempotent, dijalankan SETELAH
``add_triwulan_filter.py``):

C1 -- Dashboard 2 (Agent) & 4 (Pimpinan): NORMALISASI baris KPI
    Keempat kartu KPI dipindahkan ke satu ``ROW-KPI`` sebagai anak pertama
    GRID, masing-masing ``width=3, height=15``, urutan
    Total Reservasi -> Rata-rata Durasi -> Durasi Tercepat -> Durasi Terlama.
    Baris lain TIDAK disentuh. Sudah benar -> tidak diubah (no-op).

C2 -- Dashboard 3 (Superadmin): REBUILD PENUH (sekali, lalu dikunci)
    Menambahkan slice "Tren Permasalahan per Tahun" (35) dan "Beban Agent"
    (28), lalu menulis ulang ``position_json`` menjadi 5 baris / 11 chart:

      ROW-KPI      : Total Reservasi, Rata-rata Durasi, Durasi Tercepat,
                     Durasi Terlama                    (w=3 x4, h=15)
      ROW-TREN     : Tren Kedatangan (per hari)        (w=12,   h=25)
      ROW-ANALISIS : Tren Permasalahan per Tahun,
                     Kinerja Semua Agent               (w=6 x2, h=50)
      ROW-PIE-1    : Distribusi Layanan, Status Reservasi (w=6 x2, h=50)
      ROW-PIE-2    : Komposisi Role, Beban Agent       (w=6 x2, h=50)

C3 -- Filter Tahun & Triwulan dashboard 3: target ``ds1 + ds4`` dan
    ``chartsInScope`` = 11 chart. Ini dijalankan SETIAP START karena
    ``add_triwulan_filter.py`` menulis ulang filter Triwulan dengan target
    ``[ds1]`` saja setiap restart.

C4 -- Warna chart (dashboard 2, 3, 4): ``map_label_colors`` yang menumpuk
    membuat satu warna dipakai beberapa kategori, sehingga sebagian chart
    lolos dari urutan palet baku dan warnanya tidak seragam. Bila terdeteksi
    bentrok, map dibersihkan superset kembali memberi warna per urutan palet
    ``supersetColors`` (cyan -> navy -> green -> orange -> gray -> red).

Pengaman: layout C2 hanya ditulis bila ``json_metadata`` belum punya penanda
``temulkpp_layout_v`` (argumen ``--force`` untuk mengulang), supaya edit manual
di UI Superset tidak di-revert tiap restart. C1 selalu dijalankan (compare &
skip) karena sifatnya normalisasi, bukan rebuild.

Run inside container::

    python /app/fix_dashboard_layouts.py [--force]
"""

from __future__ import annotations

import argparse
import json
from datetime import datetime

from superset.app import create_app

app = create_app()
app.app_context().push()

from superset import db  # noqa: E402
from superset.models.dashboard import Dashboard  # noqa: E402
from superset.models.slice import Slice  # noqa: E402
from flask_appbuilder.security.sqla.models import User  # noqa: E402

# ---------------------------------------------------------------------------
# C1 -- baris KPI
# ---------------------------------------------------------------------------
LAYOUT_VERSION_KEY = "temulkpp_layout_v"
LAYOUT_VERSION = 1

KPI_ORDER = (
    "Total Reservasi",
    "Rata-rata Durasi",
    "Durasi Tercepat",
    "Durasi Terlama",
)
KPI_DASH_IDS = (2, 4)
KPI_WIDTH, KPI_HEIGHT = 3, 15
KPI_ROW_ID = "ROW-KPI"
KPI_VIZ_TYPES = ("big_number_total", "big_number")

# ---------------------------------------------------------------------------
# C2 -- dashboard Superadmin
# ---------------------------------------------------------------------------
SUPERADMIN_ID = 3
SUPERADMIN_ROWS = (
    ("ROW-KPI", (
        ("Total Reservasi", 3, 15),
        ("Rata-rata Durasi", 3, 15),
        ("Durasi Tercepat", 3, 15),
        ("Durasi Terlama", 3, 15),
    )),
    ("ROW-TREN", (("Tren Kedatangan", 12, 25),)),
    ("ROW-ANALISIS", (
        ("Tren Permasalahan per Tahun", 6, 50),
        ("Kinerja Semua Agent", 6, 50),
    )),
    ("ROW-PIE-1", (
        ("Distribusi Layanan", 6, 50),
        ("Status Reservasi", 6, 50),
    )),
    ("ROW-PIE-2", (
        ("Komposisi Role", 6, 50),
        ("Beban Agent", 6, 50),
    )),
)

# Filter native beserta kolom targetnya.
FILTER_COLUMNS = {
    "NATIVE_FILTER-TEMU_TAHUN": "tahun",
    "NATIVE_FILTER-TEMU_TRIWULAN": "triwulan",
}
# Dataset yang dipakai filter di dashboard Superadmin: ds1 (reservations)
# dan ds4 (reservation_agent_analytics). ds3 (virtual_roles) sengaja tidak
# dimasukkan -- tidak punya kolom tahun/triwulan.
SUPERADMIN_FILTER_DATASETS = (1, 4)

# ---------------------------------------------------------------------------
# C4 -- seragamkan warna chart
# ---------------------------------------------------------------------------
# Dashboard yang warnanya dinormalkan (dashboard Rating sudah bersih).
COLOR_DASH_IDS = (2, 3, 4)
COLOR_SCHEME = "supersetColors"

# Palet baku Superset "Superset Colors" -- urutan inilah yang membuat chart
# konsisten: cyan -> navy -> green -> orange -> gray -> red -> dst.
PALETTE = (
    "#1FA8C9", "#454E7C", "#5AC189", "#FF7F44", "#666666",
    "#E04355", "#FCC700", "#A868B7", "#3CCCCB", "#A38F79",
    "#8FD3E4", "#A1A6BD", "#ACE1C4", "#FEC0A1", "#B2B2B2",
    "#EFA1AA", "#FDE380", "#D3B3DA", "#9EE5E5", "#D1C6BC",
)


# ---------------------------------------------------------------------------
# utilitas
# ---------------------------------------------------------------------------
def load_json(raw, default):
    try:
        value = json.loads(raw or "{}")
    except (TypeError, ValueError):
        return default
    return value if isinstance(value, dict) else default


def reachable(position: dict) -> set:
    seen: set = set()
    stack = ["ROOT_ID"]
    while stack:
        node_id = stack.pop()
        if node_id in seen:
            continue
        seen.add(node_id)
        node = position.get(node_id)
        if isinstance(node, dict):
            stack.extend(node.get("children") or [])
    return seen


def chart_nodes(position: dict) -> dict:
    """slice_id -> (node_key, node) untuk CHART node yang terjangkau."""
    live = reachable(position)
    out = {}
    for key, node in position.items():
        if key not in live or not isinstance(node, dict):
            continue
        if node.get("type") != "CHART":
            continue
        chart_id = (node.get("meta") or {}).get("chartId")
        if isinstance(chart_id, int):
            out[chart_id] = (key, node)
    return out


def ensure_skeleton(position: dict, title: str) -> None:
    position.setdefault("DASHBOARD_VERSION_KEY", "v2")
    position.setdefault(
        "ROOT_ID", {"id": "ROOT_ID", "type": "ROOT", "children": ["GRID_ID"]}
    )
    grid = position.get("GRID_ID")
    if not isinstance(grid, dict):
        grid = {"id": "GRID_ID", "type": "GRID", "children": [], "parents": ["ROOT_ID"]}
        position["GRID_ID"] = grid
    grid["type"] = "GRID"
    grid["parents"] = ["ROOT_ID"]
    grid.setdefault("children", [])
    if "HEADER_ID" not in position:
        position["HEADER_ID"] = {
            "id": "HEADER_ID", "type": "HEADER", "meta": {"text": title},
        }


def touch(dashboard: Dashboard) -> None:
    dashboard.changed_on = datetime.now()
    dashboard.changed_by_fk = 1


def ensure_owner(obj, admin) -> None:
    if admin is not None and admin not in obj.owners:
        obj.owners.append(admin)


# ---------------------------------------------------------------------------
# C1 -- normalisasi baris KPI
# ---------------------------------------------------------------------------
def kpi_charts(dashboard: Dashboard, position: dict) -> dict:
    """nama KPI -> (node_key, node) pada dashboard ini."""
    found = {}
    for chart_id, (key, node) in chart_nodes(position).items():
        sl = db.session.get(Slice, chart_id)
        if sl is None:
            continue
        if sl.slice_name in KPI_ORDER and sl.viz_type in KPI_VIZ_TYPES:
            found[sl.slice_name] = (key, node)
    return found


def kpi_row_is_correct(position: dict, kpi: dict) -> bool:
    grid = position.get("GRID_ID")
    if not isinstance(grid, dict):
        return False
    children = grid.get("children") or []
    if not children:
        return False
    first = position.get(children[0])
    if not isinstance(first, dict) or first.get("type") != "ROW":
        return False
    expected_keys = [kpi[name][0] for name in KPI_ORDER]
    if (first.get("children") or []) != expected_keys:
        return False
    for name in KPI_ORDER:
        meta = kpi[name][1].get("meta") or {}
        if meta.get("width") != KPI_WIDTH or meta.get("height") != KPI_HEIGHT:
            return False
    return True


def normalize_kpi_row(dashboard: Dashboard, position: dict) -> bool:
    """Kumpulkan 4 kartu KPI ke satu ROW-KPI di posisi paling atas."""
    ensure_skeleton(position, dashboard.dashboard_title or "")
    kpi = kpi_charts(dashboard, position)

    if len(kpi) != len(KPI_ORDER):
        missing = [n for n in KPI_ORDER if n not in kpi]
        print(f"  dash {dashboard.id}: kartu KPI tidak lengkap "
              f"({len(kpi)}/{len(KPI_ORDER)}, kurang {missing}) -- skip")
        return False

    if kpi_row_is_correct(position, kpi):
        return False

    kpi_keys = {key for key, _ in kpi.values()}

    # Lepas kartu KPI dari baris lamanya.
    for node in position.values():
        if isinstance(node, dict) and node.get("type") == "ROW":
            node["children"] = [
                c for c in (node.get("children") or []) if c not in kpi_keys
            ]

    # Buang baris yang kini kosong (termasuk referensinya di GRID).
    for key in [
        k for k, n in position.items()
        if isinstance(n, dict) and n.get("type") == "ROW"
        and not (n.get("children") or [])
    ]:
        del position[key]

    grid = position["GRID_ID"]
    grid_children = [c for c in (grid.get("children") or []) if c in position]

    row = position.get(KPI_ROW_ID)
    if not isinstance(row, dict):
        row = {
            "id": KPI_ROW_ID, "type": "ROW", "children": [],
            "parents": ["ROOT_ID", "GRID_ID"],
            "meta": {"background": "BACKGROUND_TRANSPARENT"},
        }
        position[KPI_ROW_ID] = row
    row["type"] = "ROW"
    row["parents"] = ["ROOT_ID", "GRID_ID"]
    row["meta"] = {"background": "BACKGROUND_TRANSPARENT"}
    row["children"] = [kpi[name][0] for name in KPI_ORDER]

    grid["children"] = [KPI_ROW_ID] + [c for c in grid_children if c != KPI_ROW_ID]

    for name in KPI_ORDER:
        key, node = kpi[name]
        meta = node.setdefault("meta", {})
        meta["sliceName"] = name
        meta["width"] = KPI_WIDTH
        meta["height"] = KPI_HEIGHT
        node["id"] = key
        node["type"] = "CHART"
        node["parents"] = ["ROOT_ID", "GRID_ID", KPI_ROW_ID]

    return True


# ---------------------------------------------------------------------------
# C2 -- rebuild dashboard Superadmin
# ---------------------------------------------------------------------------
def resolve_superadmin_slices(dashboard: Dashboard) -> dict:
    """nama -> Slice, diprioritaskan dari dashboard; fallback lookup global."""
    by_name = {sl.slice_name: sl for sl in dashboard.slices}
    missing = [
        name
        for _, items in SUPERADMIN_ROWS
        for name, _, _ in items
        if name not in by_name
    ]
    for name in missing:
        matches = db.session.query(Slice).filter_by(slice_name=name).all()
        if len(matches) != 1:
            ids = [m.id for m in matches]
            raise RuntimeError(
                f"slice {name!r} tidak unik secara global ({ids}) -- "
                f"tidak bisa dipilih otomatis"
            )
        by_name[name] = matches[0]
    return by_name


def attach_slices(dashboard: Dashboard, by_name: dict, admin) -> list:
    wanted = [
        by_name[name] for _, items in SUPERADMIN_ROWS for name, _, _ in items
    ]
    current = list(dashboard.slices)
    added = []
    for sl in wanted:
        if sl not in current:
            current.append(sl)
            added.append(sl)
        ensure_owner(sl, admin)
    dashboard.slices = current
    return added


def build_superadmin_layout(dashboard: Dashboard, by_name: dict) -> dict:
    try:
        old = json.loads(dashboard.position_json or "{}")
    except (TypeError, ValueError):
        old = {}
    if not isinstance(old, dict):
        old = {}
    header = old.get("HEADER_ID")
    if not isinstance(header, dict):
        header = {
            "id": "HEADER_ID", "type": "HEADER",
            "meta": {"text": dashboard.dashboard_title or ""},
        }

    position = {
        "DASHBOARD_VERSION_KEY": "v2",
        "ROOT_ID": {"id": "ROOT_ID", "type": "ROOT", "children": ["GRID_ID"]},
        "HEADER_ID": header,
        "GRID_ID": {
            "id": "GRID_ID", "type": "GRID",
            "children": [row_id for row_id, _ in SUPERADMIN_ROWS],
            "parents": ["ROOT_ID"],
        },
    }

    for row_id, items in SUPERADMIN_ROWS:
        chart_keys = []
        for name, width, height in items:
            sl = by_name[name]
            key = f"CHART-{sl.id}"
            chart_keys.append(key)
            position[key] = {
                "id": key,
                "type": "CHART",
                "meta": {
                    "chartId": sl.id,
                    "height": height,
                    "width": width,
                    "sliceName": sl.slice_name,
                },
                "parents": ["ROOT_ID", "GRID_ID", row_id],
            }
        position[row_id] = {
            "id": row_id,
            "type": "ROW",
            "children": chart_keys,
            "parents": ["ROOT_ID", "GRID_ID"],
            "meta": {"background": "BACKGROUND_TRANSPARENT"},
        }

    return position


# ---------------------------------------------------------------------------
# C3 -- filter Tahun & Triwulan dashboard Superadmin
# ---------------------------------------------------------------------------
def _target_signature(targets) -> list:
    return sorted(
        (t.get("datasetId"), (t.get("column") or {}).get("name"))
        for t in (targets or [])
        if isinstance(t, dict)
    )


def fix_superadmin_filters(meta: dict, chart_ids: list) -> bool:
    """Target ds1+ds4 dan chartsInScope = seluruh chart dashboard."""
    filters = meta.get("native_filter_configuration") or []
    if not filters:
        return False

    changed = False
    for filt in filters:
        column = FILTER_COLUMNS.get(filt.get("id"))
        if not column:
            continue  # filter lain (mis. "Jenis Data") tidak disentuh

        want_targets = [
            {"datasetId": dataset_id, "column": {"name": column}}
            for dataset_id in SUPERADMIN_FILTER_DATASETS
        ]
        if _target_signature(filt.get("targets")) != _target_signature(want_targets):
            filt["targets"] = want_targets
            changed = True

        scope = filt.get("chartsInScope")
        if not isinstance(scope, list) or sorted(scope) != sorted(chart_ids):
            filt["chartsInScope"] = list(chart_ids)
            changed = True

    if changed:
        meta["native_filter_configuration"] = filters
    return changed


# ---------------------------------------------------------------------------
# C4 -- seragamkan warna chart
# ---------------------------------------------------------------------------
def normalize_chart_colors(meta: dict) -> bool:
    """Samakan palet warna seluruh chart pada satu dashboard.

    Penyebab warna chart berbeda-beda: ``map_label_colors`` adalah state yang
    dibangun otomatis saat render dan menumpuk entri lintas waktu, sehingga
    SATU warna dipakai untuk BEBERAPA kategori berbeda (mis. ``#1FA8C9``
    dipakai 'Agent', 'Tidak Selesai', 'count', ... sekaligus). Chart yang
    kategorinya tercatat di map itu terkena warna warisan dan tidak lagi
    mengikuti urutan palet baku -- akibatnya donut atas dan bawah terlihat
    beda.

    Bila terdeteksi bentrok, map dibersihkan. Superset lalu kembali memberi
    warna mengikuti urutan palet (cyan -> navy -> green -> orange -> gray ->
    red -> ...), sehingga SEMUA chart mulai dari cyan dan seragam.

    Idempotent: map yang sudah sehat (tanpa bentrok) tidak disentuh.
    """
    changed = False

    old_map = meta.get("map_label_colors")
    if isinstance(old_map, dict) and old_map:
        seen = set()
        collision = False
        for color in old_map.values():
            if color in seen:
                collision = True
                break
            seen.add(color)
        if collision:
            meta["map_label_colors"] = {}
            changed = True

    if meta.get("color_scheme") != COLOR_SCHEME:
        meta["color_scheme"] = COLOR_SCHEME
        changed = True

    if list(meta.get("color_scheme_domain") or []) != list(PALETTE):
        meta["color_scheme_domain"] = list(PALETTE)
        changed = True

    return changed


# ---------------------------------------------------------------------------
def main() -> None:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument(
        "--force", action="store_true",
        help="tulis ulang layout dashboard Superadmin walau sudah pernah diterapkan",
    )
    args = parser.parse_args()

    admin = db.session.query(User).filter_by(id=1).first()
    db.session.flush()

    # --- C1: baris KPI pada dashboard 2 & 4 -------------------------------
    for dash_id in KPI_DASH_IDS:
        dashboard = db.session.get(Dashboard, dash_id)
        if dashboard is None:
            print(f"dash {dash_id}: tidak ditemukan -- skip")
            continue
        position = load_json(dashboard.position_json, {})
        if not position:
            print(f"dash {dash_id}: position_json kosong -- skip")
            continue
        if normalize_kpi_row(dashboard, position):
            dashboard.position_json = json.dumps(position)
            touch(dashboard)
            print(f"dash {dash_id} ({dashboard.dashboard_title}): baris KPI "
                  f"diseragamkan -> 1 baris x 4, w={KPI_WIDTH} h={KPI_HEIGHT}, "
                  f"urutan {' -> '.join(KPI_ORDER)}")
        else:
            print(f"dash {dash_id} ({dashboard.dashboard_title}): baris KPI "
                  f"sudah benar -- tidak diubah")

    # --- C2 + C3: dashboard Superadmin ------------------------------------
    dashboard = db.session.get(Dashboard, SUPERADMIN_ID)
    if dashboard is None:
        print(f"dash {SUPERADMIN_ID}: tidak ditemukan -- skip")
    else:
        by_name = resolve_superadmin_slices(dashboard)
        added = attach_slices(dashboard, by_name, admin)
        if added:
            print(f"dash {SUPERADMIN_ID}: +{len(added)} chart ditambahkan -> "
                  f"{[sl.slice_name for sl in added]}")

        chart_ids = sorted({
            by_name[name].id
            for _, items in SUPERADMIN_ROWS
            for name, _, _ in items
        })

        meta = load_json(dashboard.json_metadata, {})
        meta_changed = fix_superadmin_filters(meta, chart_ids)
        if meta_changed:
            print(f"dash {SUPERADMIN_ID}: filter Tahun & Triwulan -> target "
                  f"ds{'+'.join(str(d) for d in SUPERADMIN_FILTER_DATASETS)}, "
                  f"chartsInScope={chart_ids}")

        version = meta.get(LAYOUT_VERSION_KEY, 0)
        if args.force or not isinstance(version, int) or version < LAYOUT_VERSION:
            dashboard.position_json = json.dumps(
                build_superadmin_layout(dashboard, by_name)
            )
            meta[LAYOUT_VERSION_KEY] = LAYOUT_VERSION
            meta_changed = True
            touch(dashboard)
            print(f"dash {SUPERADMIN_ID} ({dashboard.dashboard_title}): layout "
                  f"direbuild -> {len(SUPERADMIN_ROWS)} baris / "
                  f"{len(chart_ids)} chart")
        else:
            print(f"dash {SUPERADMIN_ID}: layout sudah diterapkan "
                  f"(v{version}) -- tidak diubah (gunakan --force untuk mengulang)")

        if meta_changed:
            dashboard.json_metadata = json.dumps(meta)
            touch(dashboard)

    # --- C4: seragamkan warna chart pada semua dashboard ------------------
    for dash_id in COLOR_DASH_IDS:
        colored = db.session.get(Dashboard, dash_id)
        if colored is None:
            continue
        meta = load_json(colored.json_metadata, {})
        if normalize_chart_colors(meta):
            colored.json_metadata = json.dumps(meta)
            touch(colored)
            print(f"dash {dash_id} ({colored.dashboard_title}): palet warna "
                  f"diseragamkan -> {COLOR_SCHEME}, map_label_colors dibersihkan "
                  f"dari bentrok warna")

    db.session.commit()
    print("COMMIT OK")


if __name__ == "__main__":
    main()
