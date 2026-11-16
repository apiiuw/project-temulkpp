"""Sync existing Superset MySQL metadata connection from MYSQL_URI."""

from __future__ import annotations

import os

from superset.app import create_app


def main() -> None:
    mysql_uri = os.environ.get("MYSQL_URI", "").strip()
    if not mysql_uri:
        raise RuntimeError("MYSQL_URI is required")

    app = create_app()
    with app.app_context():
        from superset import db
        from superset.models.core import Database

        databases = (
            db.session.query(Database)
            .filter(Database.database_name.ilike("%mysql%"))
            .all()
        )
        if not databases:
            print("No existing MySQL metadata connection found; skipping sync.")
            return

        for database in databases:
            database.sqlalchemy_uri = mysql_uri
            print(f"Updated Superset connection: {database.database_name}")

        db.session.commit()


if __name__ == "__main__":
    main()
