from superset.app import create_app
app = create_app()
app.app_context().push()

from superset import security_manager
from superset import db

def fix_public_permissions():
    role = security_manager.find_role("Public")
    if not role:
        print("Role Public not found")
        return
        
    view_menu = security_manager.find_view_menu("CurrentUserRestApi")
    if not view_menu:
        print("CurrentUserRestApi view menu not found. Attempting to create it.")
        view_menu = security_manager.add_view_menu("CurrentUserRestApi")
        
    permission = security_manager.find_permission("can_read")
    if not permission:
        print("can_read permission not found. Attempting to create it.")
        permission = security_manager.add_permission("can_read")
        
    perm_view = security_manager.find_permission_view_menu("can_read", "CurrentUserRestApi")
    if not perm_view:
        print("PermissionView for can_read on CurrentUserRestApi not found. Creating it.")
        perm_view = security_manager.add_permission_view_menu("can_read", "CurrentUserRestApi")
        
    if perm_view not in role.permissions:
        print("Adding CurrentUserRestApi.can_read to Public role.")
        role.permissions.append(perm_view)
        db.session.commit()
        print("Successfully added permission!")
    else:
        print("Public role already has CurrentUserRestApi.can_read")

if __name__ == "__main__":
    fix_public_permissions()
