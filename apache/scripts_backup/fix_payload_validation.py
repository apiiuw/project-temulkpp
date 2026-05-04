from superset.app import create_app
app = create_app()
app.app_context().push()

from superset import db
from superset.models.slice import Slice
import json

def fix_params_and_query_context():
    slices = db.session.query(Slice).all()
    
    for slc in slices:
        print(f"Fixing {slc.slice_name}...")
        try:
            params = json.loads(slc.params) if slc.params else {}

            params["slice_id"] = slc.id
            params["datasource"] = f"{slc.datasource_id}__{slc.datasource_type}"
            params["viz_type"] = slc.viz_type
            
            # Ensure "metrics" list exists for subset validation
            metrics = params.get("metrics", [])
            if not isinstance(metrics, list):
                metrics = [metrics]
            if "metric" in params and params["metric"] not in metrics:
                metrics.append(params["metric"])
            if "size" in params and params["size"] not in metrics:
                metrics.append(params["size"])
                
            params["metrics"] = metrics
            
            # Ensure "columns" list exists
            columns = params.get("columns", [])
            if "groupby" in params:
                for gb in params["groupby"]:
                    if gb not in columns:
                        columns.append(gb)
            params["columns"] = columns

            if params.get("metric") and not params.get("orderby"):
                params["orderby"] = [[params["metric"], False]]
            
            slc.params = json.dumps(params)
            
            # Now build a matching query_context
            query_context = {
                "datasource": {"id": slc.datasource_id, "type": slc.datasource_type},
                "force": False,
                "queries": [
                    {
                        "metrics": params.get("metrics", []),
                        "columns": params.get("columns", []),
                        "groupby": params.get("groupby", []),
                        "orderby": params.get("orderby", []),
                    }
                ],
                "result_format": "json",
                "result_type": "full",
                "form_data": params
            }
            slc.query_context = json.dumps(query_context)
            
            print(f"  Fixed params & query_context")
            
        except Exception as e:
            print(f"  Error: {e}")
            
    db.session.commit()
    print("All charts fixed!")

if __name__ == "__main__":
    fix_params_and_query_context()
