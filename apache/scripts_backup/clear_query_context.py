from superset.app import create_app
app = create_app()
app.app_context().push()

from superset import db
from superset.models.slice import Slice

def clear_query_context():
    slices = db.session.query(Slice).all()
    count = 0
    for slc in slices:
        if slc.query_context is not None:
            slc.query_context = None
            count += 1
            print(f"Cleared query_context for {slc.slice_name}")
            
    db.session.commit()
    print(f"Successfully cleared query_context for {count} charts.")

if __name__ == "__main__":
    clear_query_context()
