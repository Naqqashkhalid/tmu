<?php

function tv_data($tmid){
	try {
	  	$client = new \GuzzleHttp\Client();
		$response = $client->request('GET', 'https://api.themoviedb.org/3/tv/'.$tmid.'?language=en-US', auth_key());
		return json_decode($response->getBody());
	} catch(Exception $e) {
	  return false;
	}
}

function tv_tags($tmid){
	try {
	  	$client = new \GuzzleHttp\Client();
		$response = $client->request('GET', 'https://api.themoviedb.org/3/tv/'.$tmid.'/keywords', auth_key());
		$data = json_decode($response->getBody());
		if ($data) {
			return array_map(function($tag) { return ucwords($tag->name); }, $data->results);
		}
	} catch(Exception $e) {
	  return false;
	}
}

function tv_images($tmid){
	try {
	  	$client = new \GuzzleHttp\Client();
		$response = $client->request('GET', 'https://api.themoviedb.org/3/tv/'.$tmid.'/images', auth_key());
		$data = json_decode($response->getBody());
		if ($data && $data->backdrops) {
			return array_map(function($backdrop) { return 'https://image.tmdb.org/t/p/original'.$backdrop->file_path; }, $data->backdrops);
		}
	} catch(Exception $e) {
	  return false;
	}
}

function tv_certification($tmid){
	try {
	  	$client = new \GuzzleHttp\Client();
		$response = $client->request('GET', 'https://api.themoviedb.org/3/tv/'.$tmid.'/content_ratings', auth_key());
		$data = json_decode($response->getBody());
		$certifications = $data->results ?? []; $rating = ''; $additional_rating = '';
		foreach ($certifications as $certification) {
			if ($certification->iso_3166_1 == 'US') {
				$rating = $certification->rating ?? '';
			}
      if ($certification->rating) {
        $additional_rating = $certification->rating;
      }
		}
    return $certification->rating ?? $additional_rating;
	} catch(Exception $e) {
	  return false;
	}
}

function full_cast_crew($seriesId, $main_credits=[]) {
  global $wpdb;
  $sql = "SELECT ID, credits FROM {$wpdb->prefix}tmu_tv_series_episodes WHERE tv_series = %d";
  $prepared_sql = $wpdb->prepare($sql, $seriesId);
  $episodes = $wpdb->get_results($prepared_sql, ARRAY_A);



  $cast = [];
  $crew = [];

  if ($main_credits) {
  	// Process Cast
    foreach ($main_credits['cast'] as $castMember) {
      $castKey = isset($castMember['person']) ? $castMember['person'] : ''; // Use person ID as key
      if ($castKey && !isset($cast[$castKey])) {
        $cast[$castKey] = [
          'person' => $castMember['person'],
          'department' => $castMember['department'],
          'job' => $castMember['acting_job'],
          'count' => 0,
        ];
      }
    }

    // Process Crew
    foreach ($main_credits['crew'] as $crewMember) {
      $crewKey = isset($crewMember['person']) ? $crewMember['person'] : ''; // Use person ID as key
      if ($crewKey && !isset($crew[$crewKey])) {
        $crew[$crewKey] = [
          'person' => $crewMember['person'],
          'department' => $crewMember['department'],
          'job' => $crewMember[clean_job_string($crewMember['department']) . '_job'],
          'count' => 0,
        ];
      }
    }
  }

  foreach ($episodes as $episode) {
    $credits = unserialize($episode['credits']); // Assuming credits are serialized

    // Process Cast
    if ($credits['cast']) {
      foreach ($credits['cast'] as $castMember) {
        $castKey = isset($castMember['person']) ? $castMember['person'] : ''; // Use person ID as key
        if ($castKey && !isset($cast[$castKey])) {
          $cast[$castKey] = [
            'person' => $castMember['person'],
            'department' => $castMember['department'],
            'job' => $castMember['acting_job'],
            'count' => 0,
          ];
        }
        if(isset($cast[$castKey])) $cast[$castKey]['count']++;
      }
    }
    

    if ($credits['crew']) {
      // Process Crew
      foreach ($credits['crew'] as $crewMember) {
        $crewKey = isset($crewMember['person']) ? $crewMember['person'] : ''; // Use person ID as key
        if ($crewKey && !isset($crew[$crewKey])) {
          $crew[$crewKey] = [
            'person' => $crewMember['person'],
            'department' => $crewMember['department'],
            'job' => $crewMember[clean_job_string($crewMember['department']) . '_job'],
            'count' => 0,
          ];
        }
        if (isset($crew[$crewKey])) $crew[$crewKey]['count']++;
      }
    }
  }

  // Sort cast and crew by count (descending)
  usort($cast, function($a, $b) {
    return $b['count'] <=> $a['count'];
  });
  usort($crew, function($a, $b) {
    return $b['count'] <=> $a['count'];
  });

  return ['cast'=>$cast, 'crew'=>$crew];
}