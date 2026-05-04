from superset.app import create_app
app = create_app()
app.app_context().push()

from superset import db
from superset.models.core import Database
from sqlalchemy import text

def check_mysql():
    mysql_db = db.session.query(Database).filter(Database.database_name.like('%MySQL%')).first()
    if not mysql_db:
        print("MySQL database not found in Superset.")
        return

    engine = mysql_db.get_sqla_engine()
    with engine.connect() as conn:
        res = conn.execute(text("SELECT count(*) FROM reservations")).fetchone()
        print(f"TOTAL_RESERVATIONS_IN_MYSQL: {res[0]}")
        
        # Check specific agent data
        res_agent = conn.execute(text("SELECT count(*) FROM reservations WHERE agent_id = 1")).fetchone()
        print(f"RESERVATIONS_FOR_AGENT_1: {res_agent[0]}")

check_mysql()
