from superset.app import create_app
app = create_app()
app.app_context().push()

from superset import db
from superset.connectors.sqla.models import RowLevelSecurityFilter

def fix_rls():
    rls = db.session.query(RowLevelSecurityFilter).filter_by(name="Agent Performance Guest Filter").first()
    if not rls:
        print("RLS filter not found!")
        return

    # Fix clause: cast current_username() to unsigned integer to match agent_id column type
    # In MySQL, CAST('1' AS UNSIGNED) = 1
    # current_username() returns the username field from the guest token
    rls.clause = "agent_id = CAST('{{ current_username() }}' AS UNSIGNED)"
    db.session.commit()
    print(f"Updated RLS clause to: {rls.clause}")

if __name__ == "__main__":
    fix_rls()
