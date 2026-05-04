import json
from superset.app import create_app
app = create_app()
app.app_context().push()

from superset import db
from superset.models.slice import Slice
from superset.charts.data.commands.get_data_command import ChartDataCommand

slc = db.session.query(Slice).first()
if slc:
    print(f"Testing chart: {slc.slice_name}")
    try:
        # Create a basic query context
        form_data = json.loads(slc.params) if slc.params else {}
        form_data['datasource'] = f"{slc.datasource_id}__{slc.datasource_type}"
        
        query_context = {
            "datasource": {"id": slc.datasource_id, "type": slc.datasource_type},
            "force": False,
            "queries": [form_data],
            "result_format": "json",
            "result_type": "full"
        }
        
        command = ChartDataCommand(query_context)
        result = command.run()
        print("Result successful. Data length:", len(result))
    except Exception as e:
        print("Error:", str(e))
else:
    print("No slices found")
