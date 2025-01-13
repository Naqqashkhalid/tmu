<?php

function season_data($tmdb_series_id, $season_no){
	try {
	  	$client = new \GuzzleHttp\Client();
		$response = $client->request('GET', 'https://api.themoviedb.org/3/tv/'.$tmdb_series_id.'/season/'.$season_no.'?language=en-US', auth_key());
		return json_decode($response->getBody());
	} catch(Exception $e) {
	  return false;
	}
}