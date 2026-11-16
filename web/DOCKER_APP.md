# Temu LKPP Docker Stack

Services:

- `backend`: Laravel 13 + PHP 8.3 + Apache, port `8000`, memory limit 768 MB.
- `frontend`: Vite asset server, port `5173`, memory limit 384 MB.
Superset runs separately from `../apache/` on port `8088`.

## First setup

Create Laravel Docker environment from existing local environment. Run from `web/`:

```powershell
Copy-Item .env.docker.example .env.docker
```

Use existing Laravel `APP_KEY`, MySQL credentials, and Superset credentials. If `.env.docker` already exists, keep it.

Ensure these Docker values exist:

```env
DB_HOST=host.docker.internal
SUPERSET_INTERNAL_URL=http://host.docker.internal:8088
SUPERSET_PUBLIC_URL=http://localhost:8088
```

`SUPERSET_INTERNAL_URL` is used by Laravel container. `SUPERSET_PUBLIC_URL` is used by browser embed.

## Start

Run from `web/`:

```powershell
cd web
docker compose up -d --build
```

Check:

```powershell
docker compose ps
docker compose logs -f backend
docker compose logs -f frontend

```

URLs:

```text
Laravel:  http://localhost:8000
Vite:    http://localhost:5173
Superset: http://localhost:8088
```

## Memory choices

- Backend uses Apache with one PHP request worker per process model; no queue, scheduler, or debug watcher container.
- Frontend uses Alpine Node and only Vite dependencies; no production Node process duplicates Laravel.

- CPU/memory limits prevent runaway local containers.
- Superset and MySQL stay outside this Compose project; no duplicate data copy.

## Stop

```powershell
docker compose down
```

Never use `docker compose down -v`; Superset metadata remains in `../apache/superset-data/superset.db`.
