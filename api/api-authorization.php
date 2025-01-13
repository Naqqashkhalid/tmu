<?php

function auth_key(){
	return [
			'headers' => [
				'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiIwMTljMWM5ZjRlYWYwMWFiMmYzMGI1NTNhM2MzOTVjMSIsInN1YiI6IjY1OTJjMGY0ZjVmMWM1Nzc2NzAxMGE0OSIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.0eph75SDjWFSEdJsBNK9nmpwBBRVtvDVUU2weFUtn-0',
				'accept' => 'application/json',
			],
		];
}

function get_api_key(){
	return 'Authorization: Bearer eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiIwMTljMWM5ZjRlYWYwMWFiMmYzMGI1NTNhM2MzOTVjMSIsInN1YiI6IjY1OTJjMGY0ZjVmMWM1Nzc2NzAxMGE0OSIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.0eph75SDjWFSEdJsBNK9nmpwBBRVtvDVUU2weFUtn-0';
}