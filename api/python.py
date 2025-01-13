import requests

url = "https://api.themoviedb.org/3/movie/456483?language=en-US"

headers = {
    "accept": "application/json",
    "Authorization": "Bearer eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiIwMTljMWM5ZjRlYWYwMWFiMmYzMGI1NTNhM2MzOTVjMSIsInN1YiI6IjY1OTJjMGY0ZjVmMWM1Nzc2NzAxMGE0OSIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.0eph75SDjWFSEdJsBNK9nmpwBBRVtvDVUU2weFUtn-0"
}

response = requests.get(url, headers=headers)

print(response.text)