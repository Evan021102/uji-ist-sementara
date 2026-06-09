import requests
from bs4 import BeautifulSoup

def test_flow():
    session = requests.Session()
    session.headers.update({
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
    })

    print("Step 1: Get Portal Page")
    res = session.get("https://test.gosyenpolinator.com/")
    print(f"Response code: {res.status_code}")
    if res.status_code != 200:
        print("Failed to load portal page.")
        return

    # Extract CSRF token
    soup = BeautifulSoup(res.text, 'html.parser')
    token_input = soup.find('input', {'name': '_token'})
    if not token_input:
        print("CSRF token not found in portal page.")
        return
    csrf_token = token_input['value']
    print(f"CSRF Token: {csrf_token}")

    print("\nStep 2: Submit Portal Form (Start Exam)")
    data = {
        '_token': csrf_token,
        'nama': 'Test User AGY',
        'posisi': 'ACCOUNTING (A)'
    }
    res = session.post("https://test.gosyenpolinator.com/ujian/start", data=data, allow_redirects=True)
    print(f"Final URL: {res.url}")
    print(f"Response code: {res.status_code}")
    
    # Check if we got redirected to instructions page or back to index
    if "petunjuk" in res.url:
        print("Successfully reached instructions page!")
    else:
        print("Did not reach instructions page. Maybe validation failed or redirect loop?")
        # Print errors if any
        soup = BeautifulSoup(res.text, 'html.parser')
        alert_danger = soup.find(class_='alert-danger')
        if alert_danger:
            print(f"Validation errors: {alert_danger.text.strip()}")
        return

    # Extract CSRF token from instructions page
    soup = BeautifulSoup(res.text, 'html.parser')
    
    print("\nStep 3: Go to Sesi 1 Questions")
    res = session.get("https://test.gosyenpolinator.com/ujian/sesi/1", allow_redirects=True)
    print(f"Final URL: {res.url}")
    print(f"Response code: {res.status_code}")
    
    if "sesi/1" in res.url:
        print("Successfully reached Sesi 1 questions!")
        soup = BeautifulSoup(res.text, 'html.parser')
        questions = soup.find_all(class_='question-card')
        print(f"Found {len(questions)} question cards.")
    elif "petunjuk" in res.url:
        print("Redirected back to instructions.")
    else:
        print(f"Redirected/went to: {res.url}")
        
if __name__ == '__main__':
    test_flow()
