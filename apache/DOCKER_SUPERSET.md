# Superset via Docker

Docker setup keeps existing Superset metadata. Dashboard, chart, dataset, embedded-dashboard, user, and database-connection metadata remain in `apache/superset-data/superset.db`.

## Important preservation rule

Do not delete or recreate `apache/superset-data/superset.db`. The original root `superset.db` is kept untouched as a backup. Before first start:

```text
superset.db                 original metadata backup
apache/superset-data/superset.db   Docker working copy
```

`SUPERSET_SECRET_KEY` must be the same key used when the existing metadata was created. If it changes, Superset may report `Invalid decryption key` and encrypted database credentials cannot be read.

## First setup

1. Copy environment template:

```powershell
Copy-Item apache/.env.docker.example apache/.env.docker
```

2. Edit `.env.docker`:

- set existing `SUPERSET_SECRET_KEY`;
- set existing Superset admin password;
- set MySQL credentials in `MYSQL_URI`;
- keep `host.docker.internal` as MySQL host when MySQL runs on Windows host.

3. Ensure MySQL is running and database `temulkpp` exists.

4. Start Superset from `apache/`:

```powershell
cd apache
docker compose up -d --build
```

5. Check status:

```powershell
docker compose ps
docker compose logs -f superset
```

Open `http://localhost:8088`.

## Existing MySQL connection

The Superset metadata database is preserved, but a connection URI stored previously as `127.0.0.1` points inside the container after migration. On every container start, Compose synchronizes existing MySQL metadata to `MYSQL_URI` from `apache/.env.docker`.

Manual fallback if needed:

```powershell
docker compose exec superset python /app/fix_windows.py
```

The startup sync and repair script use `MYSQL_URI`; no dashboard, chart, dataset, or metadata is deleted.

## Editing dashboards in the Superset UI

All dashboards (Agent, Superadmin, Pimpinan) can be edited directly in the
Superset UI at `http://localhost:8088`. Log in as the admin user, open a
dashboard, then click **Edit** to rearrange charts, change chart configuration,
add/remove charts, and adjust filters. Charts keep reading live from MySQL, so
the embedded web view stays in sync with the database automatically.

Edits **persist across container restarts**. The startup script
`apache/add_pimpinan_charts.py` is now non-destructive: it only bootstraps the
Pimpinan analytics dataset/charts/dashboard if they are missing, prunes orphan
or duplicate layout nodes left by older versions, and assigns the admin user as
owner of every dashboard and chart. It never overwrites an existing chart's
`params`, a dashboard's `position_json`, or `json_metadata`, so anything you
change in the UI is preserved.

The admin password stored in the Superset metadata database is what the UI
login uses. Keep `SUPERSET_ADMIN_PASSWORD` in `apache/.env.docker` aligned with
it; if they differ, the database value wins for UI logins.

## Laravel integration

Laravel remains outside Docker and continues using:

```text
http://localhost:8000
http://localhost:8088
```

Its `.env` must use:

```text
SUPERSET_URL=http://localhost:8088
```

The browser origin is allowed by `CORS_ORIGINS=http://localhost:8000`.

## Stop without deleting data

```powershell
docker compose down
```

Do not use `docker compose down -v`; the metadata volume/data must be retained.

## Size choices

- pinned Superset base image; no full Python virtualenv copied into image;
- `pip --no-cache-dir` removes pip cache;
- metadata stays in a bind-mounted data directory, not an image layer;
- only one Gunicorn worker and four threads for local/small deployment;
- MySQL remains external, so no duplicate database container/data is created.
