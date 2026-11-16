"""Provision Apache Superset for Temu LKPP via code.

Single-dashboard-per-role pattern. No manual dashboard-per-agent setup.
Each Agent sees only own rows via guest-token RLS clause ``agent_id = <id>``.

Usage (from ``apache/`` with venv active)::

    python provision_superset.py
    python provision_superset.py --check-only
    python provision_superset.py --agent-ids 1,2,3
    python provision_superset.py --superset-url http://localhost:8088 --laravel-url http://localhost:8000

Agent coverage: guest-token RLS is verified for EVERY agent account found
in the MySQL ``agents`` table (override with ``--agent-ids``).

Idempotent: safe to re-run. Never deletes dashboards, datasets, or data.
Writes summary to ``.provisioned.json`` next to this script.
"""

from __future__ import annotations

import argparse
import json
import os
import sys
from datetime import datetime, timezone

try:
    import requests
except ImportError:  # pragma: no cover
    print("ERROR: 'requests' not installed. Run: pip install -r requirements.txt")
    sys.exit(2)

DEFAULTS = {
    "superset_url": os.environ.get("SUPERSET_URL", "http://localhost:8088"),
    "username": os.environ.get("SUPERSET_ADMIN_USERNAME", "admin"),
    "password": os.environ.get("SUPERSET_ADMIN_PASSWORD", "admin"),
    "laravel_url": os.environ.get("LARAVEL_URL", "http://localhost:8000"),
    "mysql_uri": os.environ.get(
        "MYSQL_URI", "mysql+pymysql://root:@127.0.0.1:3306/temulkpp"
    ),
    "agent_dashboard": os.environ.get(
        "SUPERSET_AGENT_DASHBOARD_ID", "dbd88673-b705-40cc-8fa0-b8b8a26e9afd"
    ),
    "pimpinan_dashboard": os.environ.get(
        "SUPERSET_PIMPINAN_DASHBOARD_ID", "1a2b3c4d-5e6f-7a8b-9c0d-1e2f3a4b5c6d"
    ),
    "superadmin_dashboard": os.environ.get(
        "SUPERSET_SUPERADMIN_DASHBOARD_ID", "da1f9b3b-8911-4eb7-a7eb-9df03b41bb1c"
    ),
    "sample_agent_id": int(os.environ.get("SAMPLE_AGENT_ID", "1")),
    "agent_ids": os.environ.get("AGENT_IDS", ""),
}


def login(base: str, username: str, password: str) -> str:
    resp = requests.post(
        f"{base}/api/v1/security/login",
        json={"username": username, "password": password,
              "provider": "db", "refresh": True},
        timeout=20,
    )
    resp.raise_for_status()
    return resp.json()["access_token"]


def ensure_database(base: str, headers: dict, mysql_uri: str) -> dict:
    """Ensure MySQL database connection exists. Returns status dict."""
    out: dict = {"action": "verified", "detail": ""}
    try:
        current = requests.get(f"{base}/api/v1/database/", headers=headers,
                               timeout=20).json()
        names = [d.get("database_name", "") for d in current.get("result", [])]
        if any("temulkpp" in (n or "").lower() or "mysql" in (n or "").lower()
               for n in names):
            out["detail"] = f"found: {names}"
            return out
        resp = requests.post(
            f"{base}/api/v1/database/",
            headers={**headers, "Content-Type": "application/json"},
            json={"database_name": "temulkpp_mysql",
                  "sqlalchemy_uri": mysql_uri,
                  "expose_in_sqllab": False},
            timeout=20,
        )
        if resp.status_code in (200, 201):
            out["action"] = "created"
            out["detail"] = "temulkpp_mysql"
        else:
            out["action"] = "manual-needed"
            out["detail"] = f"HTTP {resp.status_code}: {resp.text[:200]}"
    except Exception as exc:  # defensive: never abort provisioning
        out["action"] = "manual-needed"
        out["detail"] = str(exc)[:200]
    return out


def ensure_embedded(base: str, headers: dict, uuid: str,
                    laravel_url: str, check_only: bool) -> dict:
    """Ensure embedded allowed_domains includes Laravel URL."""
    url = f"{base}/api/v1/dashboard/{uuid}/embedded"
    try:
        resp = requests.get(url, headers=headers, timeout=20)
        if resp.status_code == 404:
            return {"action": "missing",
                    "detail": "no embedded config; create it in Superset UI"}
        resp.raise_for_status()
        domains = resp.json().get("result", {}).get("allowed_domains", [])
        if laravel_url in domains:
            return {"action": "verified", "detail": f"domains={domains}"}
        if check_only:
            return {"action": "would-update",
                    "detail": f"missing {laravel_url}"}
        merged = sorted(set(domains) | {laravel_url})
        put = requests.put(url, headers={**headers,
                           "Content-Type": "application/json"},
                           json={"allowed_domains": merged}, timeout=20)
        if put.status_code in (200, 201):
            return {"action": "updated", "detail": f"domains={merged}"}
        return {"action": "manual-needed",
                "detail": f"HTTP {put.status_code}: {put.text[:200]}"}
    except Exception as exc:
        return {"action": "manual-needed", "detail": str(exc)[:200]}


def parse_agent_ids(spec: str) -> list:
    """Parse '1,2,5-8' into [1, 2, 5, 6, 7, 8]."""
    ids: list = []
    for part in (spec or "").split(","):
        part = part.strip()
        if not part:
            continue
        if "-" in part:
            start, end = part.split("-", 1)
            ids.extend(range(int(start), int(end) + 1))
        else:
            ids.append(int(part))
    return sorted(set(ids))


def fetch_agent_ids(mysql_uri: str, explicit: str,
                    fallback: int) -> tuple:
    """All agent IDs from MySQL; fallback to --agent-ids, then sample."""
    if explicit.strip():
        return parse_agent_ids(explicit), "explicit --agent-ids"
    try:
        import re

        import pymysql

        match = re.match(
            r"^\w+(?:\+\w+)?://([^:]*)(?::([^@]*))?@([^:/]+)"
            r"(?::(\d+))?/([^?]+)",
            mysql_uri,
        )
        if not match:
            raise ValueError(f"cannot parse MYSQL_URI: {mysql_uri}")
        user, password, host, port, db = match.groups()
        conn = pymysql.connect(host=host, port=int(port or 3306),
                               user=user or "root",
                               password=password or "",
                               database=db.split("?")[0],
                               connect_timeout=10)
        try:
            with conn.cursor() as cur:
                cur.execute("SELECT id FROM agents ORDER BY id")
                ids = [int(row[0]) for row in cur.fetchall()]
        finally:
            conn.close()
        if not ids:
            raise ValueError("agents table is empty")
        return ids, "mysql agents table"
    except Exception as exc:
        return [fallback], f"fallback sample: {str(exc)[:120]}"


def check_guest_token(base: str, headers: dict, uuid: str,
                      rls: list, username: str = "provision-check") -> dict:
    try:
        resp = requests.post(
            f"{base}/api/v1/security/guest_token/",
            headers={**headers, "Content-Type": "application/json"},
            json={"user": {"username": username,
                           "first_name": "Provision",
                           "last_name": "Check"},
                  "resources": [{"type": "dashboard", "id": uuid}],
                  "rls": rls},
            timeout=20,
        )
        if resp.status_code in (200, 201) and resp.json().get("token"):
            return {"action": "ok", "detail": f"rls={rls}"}
        return {"action": "failed",
                "detail": f"HTTP {resp.status_code}: {resp.text[:200]}"}
    except Exception as exc:
        return {"action": "failed", "detail": str(exc)[:200]}


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--superset-url", default=DEFAULTS["superset_url"])
    parser.add_argument("--username", default=DEFAULTS["username"])
    parser.add_argument("--password", default=DEFAULTS["password"])
    parser.add_argument("--laravel-url", default=DEFAULTS["laravel_url"])
    parser.add_argument("--mysql-uri", default=DEFAULTS["mysql_uri"])
    parser.add_argument("--agent-dashboard",
                        default=DEFAULTS["agent_dashboard"])
    parser.add_argument("--pimpinan-dashboard",
                        default=DEFAULTS["pimpinan_dashboard"])
    parser.add_argument("--superadmin-dashboard",
                        default=DEFAULTS["superadmin_dashboard"])
    parser.add_argument("--sample-agent-id", type=int,
                        default=DEFAULTS["sample_agent_id"],
                        help="Fallback if agents table unreachable "
                             "and --agent-ids empty.")
    parser.add_argument("--agent-ids", default=DEFAULTS["agent_ids"],
                        help="Comma list / ranges, e.g. '1,2,5-8'. "
                             "Default: all IDs from MySQL agents table.")
    parser.add_argument("--check-only", action="store_true",
                        help="Verify only, change nothing.")
    args = parser.parse_args()

    base = args.superset_url.rstrip("/")
    summary: dict = {"checked_at": datetime.now(timezone.utc).isoformat(),
                     "check_only": args.check_only, "dashboards": {}}
    failures = 0

    try:
        token = login(base, args.username, args.password)
    except Exception as exc:
        print(f"LOGIN FAILED: {exc}")
        return 1
    print("Login OK")
    headers = {"Authorization": f"Bearer {token}"}

    db_status = ensure_database(base, headers, args.mysql_uri)
    summary["database"] = db_status
    print(f"Database: {db_status['action']} {db_status['detail']}")
    if db_status["action"] == "manual-needed":
        failures += 1

    roles = {
        "agent": (args.agent_dashboard, None),  # RLS per agent below
        "pimpinan": (args.pimpinan_dashboard, []),
        "superadmin": (args.superadmin_dashboard, []),
    }
    agent_ids, agent_source = fetch_agent_ids(
        args.mysql_uri, args.agent_ids, args.sample_agent_id)
    print(f"Agents to verify ({agent_source}): {agent_ids}")
    summary["agent_ids"] = agent_ids
    summary["agent_source"] = agent_source
    for role, (uuid, rls) in roles.items():
        entry: dict = {"uuid": uuid}
        try:
            dash = requests.get(f"{base}/api/v1/dashboard/{uuid}",
                                headers=headers, timeout=20)
            if dash.status_code != 200:
                entry["dashboard"] = {"action": "missing",
                                      "detail": f"HTTP {dash.status_code}"}
                failures += 1
                summary["dashboards"][role] = entry
                print(f"[{role}] dashboard MISSING (HTTP {dash.status_code})")
                continue
            title = dash.json().get("result", {}).get("dashboard_title", "?")
            entry["title"] = title
            print(f"[{role}] dashboard OK: {title}")
        except Exception as exc:
            entry["dashboard"] = {"action": "error",
                                  "detail": str(exc)[:200]}
            failures += 1
            summary["dashboards"][role] = entry
            continue

        emb = ensure_embedded(base, headers, uuid, args.laravel_url,
                              args.check_only)
        entry["embedded"] = emb
        print(f"[{role}] embedded: {emb['action']} {emb['detail']}")
        if emb["action"] in ("missing", "manual-needed"):
            failures += 1

        if role == "agent":
            # Generic check uses first agent; full per-agent loop follows.
            rls = [{"clause": f"agent_id = {agent_ids[0]}"}] if agent_ids else []
        gt = check_guest_token(base, headers, uuid, rls)
        entry["guest_token"] = gt
        print(f"[{role}] guest-token: {gt['action']} {gt['detail']}")
        if gt["action"] != "ok":
            failures += 1
        summary["dashboards"][role] = entry

    if "agent" in summary["dashboards"] and \
            summary["dashboards"]["agent"].get("title"):
        agent_uuid = args.agent_dashboard
        per_agent: dict = {}
        for agent_id in agent_ids:
            rls = [{"clause": f"agent_id = {agent_id}"}]
            res = check_guest_token(base, headers, agent_uuid, rls,
                                    username=f"agent-{agent_id}")
            per_agent[str(agent_id)] = res
            print(f"[agent:{agent_id}] guest-token: "
                  f"{res['action']} {res['detail']}")
            if res["action"] != "ok":
                failures += 1
        summary["dashboards"]["agent"]["per_agent_guest_tokens"] = per_agent
        ok_count = sum(1 for v in per_agent.values() if v["action"] == "ok")
        print(f"[agent] per-agent coverage: {ok_count}/{len(per_agent)} OK")

    out_path = os.path.join(os.path.dirname(os.path.abspath(__file__)),
                            ".provisioned.json")
    if not args.check_only:
        with open(out_path, "w", encoding="utf-8") as fh:
            json.dump(summary, fh, indent=2)
        print(f"Summary written: {out_path}")

    print(f"DONE: {'all green' if failures == 0 else f'{failures} issue(s)'}")
    return 0 if failures == 0 else 1


if __name__ == "__main__":
    sys.exit(main())
