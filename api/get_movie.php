<?php

function movie_data($tmid){
	try {
	  	$client = new \GuzzleHttp\Client();
		$response = $client->request('GET', 'https://api.themoviedb.org/3/movie/'.$tmid.'?language=en-US', auth_key());
		return json_decode($response->getBody());
	} catch(Exception $e) {
	  return false;
	}

	// $ch = curl_init('https://api.themoviedb.org/3/movie/'.$tmid.'?language=en-US');

	// curl_setopt($ch, CURLOPT_HTTPHEADER, array(get_api_key(), 'accept: application/json'));
	// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);


	// $response = curl_exec($ch);

	// if (curl_errno($ch)) {
	//   echo 'Error:' . curl_error($ch);
	// } else {
	//   $data = json_decode($response, true);
	//   // Process the response data (e.g., print it)
	//   var_dump($data);
	// }

	// curl_close($ch);
}

function movie_tags($tmid){
	try {
	  	$client = new \GuzzleHttp\Client();
		$response = $client->request('GET', 'https://api.themoviedb.org/3/movie/'.$tmid.'/keywords', auth_key());
		$data = json_decode($response->getBody());
		if ($data) {
			return array_map(function($tag) { return ucwords($tag->name); }, $data->keywords);
		}
	} catch(Exception $e) {
	  return false;
	}
}

function movie_images($tmid){
	try {
	  	$client = new \GuzzleHttp\Client();
		$response = $client->request('GET', 'https://api.themoviedb.org/3/movie/'.$tmid.'/images', auth_key());
		$data = json_decode($response->getBody());
		if ($data && $data->backdrops) {
			return array_map(function($backdrop) { return 'https://image.tmdb.org/t/p/original'.$backdrop->file_path; }, $data->backdrops);
// 			return array_merge(
// 				array_map(function($poster) { return 'https://image.tmdb.org/t/p/original'.$poster->file_path; }, $data->posters),
// 				array_map(function($backdrop) { return 'https://image.tmdb.org/t/p/original'.$backdrop->file_path; }, $data->backdrops)
// 			);
		}
	} catch(Exception $e) {
	  return false;
	}
}

function movie_certification($tmid){
	try {
	  	$client = new \GuzzleHttp\Client();
		$response = $client->request('GET', 'https://api.themoviedb.org/3/movie/'.$tmid.'/release_dates', auth_key());
		$data = json_decode($response->getBody());
		$release_dates = $data->results ?? [];
		$releases_info = ''; $certification=''; $extra_release = '';
		foreach ($release_dates as $release) {
			if ($release->iso_3166_1 == 'US') {
				$releases_info = $release->release_dates ?? [];
			}
			if ($temp = $release->release_dates[0]->certification) {
				$extra_release = $temp;
			}
		}
		if ($releases_info) {
			foreach ($releases_info as $cert) {
				if ($cert->certification) {
					return $cert->certification;
				}
			}
		}
		
		return $extra_release;
	} catch(Exception $e) {
	  return false;
	}
}

function movie_theater_release($tmid){
	try {
	  	$client = new \GuzzleHttp\Client();
		$response = $client->request('GET', 'https://api.themoviedb.org/3/movie/'.$tmid.'/release_dates', auth_key());
		$data = json_decode($response->getBody());
		$release_dates = $data->results ?? [];
		$releases_info = ''; $certification=''; $extra_release = '';
		foreach ($release_dates as $release) {
			if ($release->iso_3166_1 == 'US') {
				$releases_info = $release->release_dates ?? [];
			}
			if ($temp = $release->release_dates[0]->certification) {
				$extra_release = $temp;
			}
		}
		if ($releases_info) {
			foreach ($releases_info as $cert) {
				if ($cert->type == 3) {
					return $cert->release_date ? $cert->release_date : '';
				}
			}
		}
	} catch(Exception $e) {
	  return false;
	}
}