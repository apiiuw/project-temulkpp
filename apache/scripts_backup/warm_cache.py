import requests
import json
from bs4 import BeautifulSoup

def warm_up_charts():
    base_url = "http://localhost:8088"
    session = requests.Session()
    
    # Get CSRF from login page
    res = session.get(f"{base_url}/login/")
    soup = BeautifulSoup(res.text, 'html.parser')
    csrf_input = soup.find('input', {'id': 'csrf_token'})
    if not csrf_input:
        print("No CSRF token found on login page")
        return
    csrf_token = csrf_input.get('value')
    
    # Login
    login_data = {
        "csrf_token": csrf_token,
        "username": "admin",
        "password": "admin"
    }
    login_res = session.post(f"{base_url}/login/", data=login_data)
    
    if login_res.status_code != 200:
        print("Failed to login")
        return
        
    print("Logged in successfully.")
    
    # Get API CSRF
    csrf_api_res = session.get(f"{base_url}/api/v1/security/csrf_token/")
    if csrf_api_res.status_code == 200:
        api_csrf = csrf_api_res.json().get("result")
    else:
        api_csrf = csrf_token
        
    headers = {
        "Content-Type": "application/json",
        "X-CSRFToken": api_csrf,
        "Referer": base_url
    }
    
    # Get all charts
    charts_res = session.get(f"{base_url}/api/v1/chart/?q=(page_size:100)", headers=headers)
    if charts_res.status_code != 200:
        print("Failed to fetch charts", charts_res.text)
        return
        
    charts = charts_res.json()["result"]
    
    print(f"Found {len(charts)} charts. Triggering native save...")
    
    for c in charts:
        chart_id = c["id"]
        name = c["slice_name"]
        
        detail_res = session.get(f"{base_url}/api/v1/chart/{chart_id}", headers=headers)
        if detail_res.status_code == 200:
            detail = detail_res.json()["result"]
            params_str = detail.get("params", "{}")
            
            try:
                params = json.loads(params_str)
                put_data = {"params": json.dumps(params)}
                put_res = session.put(f"{base_url}/api/v1/chart/{chart_id}", json=put_data, headers=headers)
                if put_res.status_code == 200:
                    print(f"Successfully re-saved {name}")
                else:
                    print(f"Failed to save {name}: {put_res.text}")
            except Exception as e:
                print(f"Error on {name}: {e}")

if __name__ == "__main__":
    warm_up_charts()
