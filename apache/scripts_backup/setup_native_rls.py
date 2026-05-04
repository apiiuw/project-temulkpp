from superset.app import create_app
app = create_app()
app.app_context().push()

from superset import db, security_manager
from superset.connectors.sqla.models import SqlaTable, RowLevelSecurityFilter

def setup_native_rls():
    # Find the reservations table
    dataset = db.session.query(SqlaTable).filter_by(table_name='reservations').first()
    if not dataset:
        print("Dataset 'reservations' not found.")
        return

    # Find the Public role (which the guest user is assigned to)
    public_role = security_manager.find_role("Public")
    if not public_role:
        print("Role Public not found.")
        return

    # Check if an RLS filter already exists for this table and role
    existing_rls = db.session.query(RowLevelSecurityFilter).filter_by(filter_type="Regular").all()
    for rls in existing_rls:
        if dataset in rls.tables and public_role in rls.roles:
            print("Native RLS already exists. Updating it...")
            rls.clause = "agent_id = '{{ current_username() }}'"
            db.session.commit()
            print("Updated existing RLS.")
            return

    # Create a new RLS filter
    print("Creating new Native RLS filter for Public role...")
    rls = RowLevelSecurityFilter(
        name="Agent Performance Guest Filter",
        filter_type="Regular",
        clause="agent_id = '{{ current_username() }}'"
    )
    rls.tables.append(dataset)
    rls.roles.append(public_role)
    
    db.session.add(rls)
    db.session.commit()
    print("Successfully created Native RLS filter.")

if __name__ == "__main__":
    setup_native_rls()
