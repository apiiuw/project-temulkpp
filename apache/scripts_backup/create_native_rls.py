from superset import db
from superset.connectors.sqla.models import RowLevelSecurityFilter, SqlaTable
from flask_appbuilder.security.sqla.models import Role

def create_native_rls():
    dataset = db.session.query(SqlaTable).filter_by(table_name='reservations').first()
    role = db.session.query(Role).filter_by(name='Public').first()
    
    if not dataset or not role:
        print("Dataset or Public role not found.")
        return

    # Check if already exists
    name = "Agent Data Isolation"
    existing = db.session.query(RowLevelSecurityFilter).filter_by(name=name).first()
    
    clause = "agent_id = '{{ current_username() }}'"
    
    if not existing:
        print(f"Creating native RLS filter: {name}")
        rls = RowLevelSecurityFilter(
            name=name,
            filter_type="Regular",
            tables=[dataset],
            roles=[role],
            clause=clause,
            group_key="agent_id"
        )
        db.session.add(rls)
    else:
        print(f"Updating native RLS filter: {name}")
        existing.clause = clause
        existing.tables = [dataset]
        existing.roles = [role]
    
    db.session.commit()
    print("Native RLS configured successfully.")

create_native_rls()
