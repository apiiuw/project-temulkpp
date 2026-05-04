from superset import db
from flask_appbuilder.security.sqla.models import Role, PermissionView

role = db.session.query(Role).filter_by(name='Public').first()
if role:
    print(f"Permissions for {role.name}:")
    for pv in role.permissions:
        if pv.permission and pv.view_menu:
            print(f" - {pv.permission.name} on {pv.view_menu.name}")
else:
    print("Role 'Public' not found.")
