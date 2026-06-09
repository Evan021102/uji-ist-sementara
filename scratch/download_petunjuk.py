import requests
from bs4 import BeautifulSoup

def main():
    session = requests.Session()
    session.headers.update({
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
    })

    # Step 1: Get Portal Page
    res = session.get("https://test.gosyenpolinator.com/")
    soup = BeautifulSoup(res.text, 'html.parser')
    token_input = soup.find('input', {'name': '_token'})
    if not token_input:
        print("CSRF token not found.")
        return
    csrf_token = token_input['value']

    # Step 2: Start Exam
    data = {
        '_token': csrf_token,
        'nama': 'Live Diagnoser',
        'posisi': 'ACCOUNTING (A)'
    }
    res = session.post("https://test.gosyenpolinator.com/ujian/start", data=data, allow_redirects=True)
    
    # We are now on /ujian/petunjuk/1
    print(f"Final URL: {res.url}")
    print(f"Status Code: {res.status_code}")
    with open("scratch/petunjuk_live.html", "w") as f:
        f.write(res.text)
    print("Saved to scratch/petunjuk_live.html")

if __name__ == '__main__':
    main()
