from superset.app import create_app
app = create_app()
app.app_context().push()
from superset import db
from flask_appbuilder.security.sqla.models import User

users = db.session.query(User).all()
for u in users:
    print(f"User ID: {u.id}, Username: {u.username}")
