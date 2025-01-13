<?php

function add_tv($post_id){

	$timestamp = (isset($_POST['release_date']) && $_POST['release_date']) ? strtotime($_POST['release_date']) : '';
	
	global $wpdb;
	$table_name = $wpdb->prefix . 'tmu_tv_series';
	$existing_record = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} WHERE ID = %d", $post_id));

	if (isset($_POST['tmdb_id']) && $_POST['tmdb_id']) {
		$old_tv = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $table_name WHERE `tmdb_id` = %d AND NOT `ID` = %d", $_POST['tmdb_id'], $post_id));
		if($old_tv){ remove_action('save_post', 'tmdb_data'); wp_delete_post( $post_id ); add_action('save_post', 'tmdb_data'); return; }

	  	$tv = tv_data($_POST['tmdb_id']); if(!$tv){return;} $update = false;

	  	$number_of_seasons = $tv->number_of_seasons;

	    if ( !has_post_thumbnail($post_id) ) { set_post_feature_image($post_id, $tv->poster_path, $tv->name); }

	    $timestamp = $timestamp ? $timestamp : ($tv->first_air_date ? strtotime($tv->first_air_date) : NULL);
		$release_year = $timestamp ? date('Y', $timestamp) : NULL;

	    if( !isset($_POST['original_title']) || $_POST['original_title']==='' )  { $_POST['original_title'] = $tv->original_name; $update=true; }
	    if( !isset($_POST['release_date']) || $_POST['release_date']==='' ) { $_POST['release_date'] = $tv->first_air_date; $update=true; }
	    if( !isset($_POST['runtime']) || $_POST['runtime']=='' || !$_POST['runtime']) { $_POST['runtime'] = is_array($tv->episode_run_time) && isset($tv->episode_run_time[0]) ? $tv->episode_run_time[0] : $tv->episode_run_time; $update=true; }
	    if( !isset($_POST['tagline']) || $_POST['tagline']==='' ) { $_POST['tagline'] = $tv->tagline; $update=true; }
	    if( !isset($_POST['production_house']) || $_POST['production_house']==='' ) { $_POST['production_house'] = productions($tv->production_companies); $update=true; }
	    if( !isset($_POST['certification']) || $_POST['certification']==='' || $_POST['certification']==='U/A' ) { $_POST['certification'] = tv_certification($_POST['tmdb_id']); $update=true; }
	    if( !isset($_POST['popularity']) || $_POST['popularity']==='' ) { $_POST['popularity'] = $tv->popularity; $update=true; }
	    if( !isset($_POST['finished']) || $_POST['finished'] == '' || (($tv->status=='Ended' || $tv->status=='Canceled') && $_POST['finished']==0) ) { $_POST['finished'] = $tv->status=='Ended' || $tv->status=='Canceled' ? 1 : 0; $update=true; }

	    $last_episode = $tv->next_episode_to_air ? $tv->next_episode_to_air : $tv->last_episode_to_air;

	    if( !isset($_POST['last_season']) || $_POST['last_season']==='' ) { $_POST['last_season'] = $last_episode ? $last_episode->season_number : ''; $update=true; }
	    if( !isset($_POST['last_episode']) || $_POST['last_episode']==='' ) { $_POST['last_episode'] = $last_episode ? $last_episode->episode_number : ''; $update=true; }
	    // $_POST['credits'] = $_POST['credits'] ? $_POST['credits'] : (isset($existing_record->credits) ? $existing_record->credits : ['cast' => [], 'crew' => []]);


	    if( !has_term('', 'genre') ) {
	        $genres = array_map(function($genre) { return $genre->name; }, $tv->genres);
	        wp_set_post_terms( $post_id, $genres, 'genre', true );
	    };
	    if( !has_term('', 'keyword') ) {
	        $tags = tv_tags($_POST['tmdb_id']);
	        wp_set_post_terms( $post_id, $tags, 'keyword', true );
	    };
	    if( !has_term('', 'by-year') ) {
	        if($tv->first_air_date) wp_set_post_terms( $post_id, [$release_year], 'by-year', true );
	    };
	    if( !has_term('', 'country') ) {
	        $countries = array_map(function($country) { return $country->name; }, $tv->production_countries);
	        wp_set_post_terms( $post_id, $countries, 'country', true );
	    };
	    if( !has_term('', 'language') ) {
	        $languages = array_map(function($language) { return $language->english_name; }, $tv->spoken_languages);
	        wp_set_post_terms( $post_id, $languages, 'language', true );
	    };

	    if (!has_term('', 'network')) {
	    	foreach ($tv->networks as $network) {
	    		wp_set_post_terms( $post_id, [$network->name], 'network', true );
	    		$term_id = get_term_by( 'slug', $network->name, 'network' )->term_id;
	    		if(!get_term_meta( $term_id, 'logo', true )) {
	    			$logo_url = 'https://image.tmdb.org/t/p/original'.$network->logo_path;
	    			$logo_id = upload_images_from_urls($logo_url, $term_id, $network->name);
	    			update_term_meta( $term_id, 'logo', $logo_id);
	    		}
	    	}
	    }

	    remove_action('save_post', 'tmdb_data');
	    
	    $season_nu = isset($_POST['get_season_no']) ? $_POST['get_season_no'] : '';
	    if( (isset($_POST['get_seasons']) && $_POST['get_seasons']) || $season_nu ) { $_POST['seasons'] = $season_nu ? insert_seasons($post_id, $tv->name, $tv->seasons, $_POST['tmdb_id'], $season_nu) : insert_seasons($post_id, $tv->name, $tv->seasons, $_POST['tmdb_id']); $update=true; }
		
	    if( !isset($_POST['post_title']) || !$_POST['post_title'] ) {
	        wp_update_post( ['ID' => $post_id, 'post_title' => $tv->name] );
	    }

	    if( ! $_POST['content'] ) {
	        wp_update_post( ['ID' => $post_id, 'post_content' => $tv->overview ]);
	    }

		if( (!isset($_POST['images']) || $_POST['images']=='') && (isset($_POST['get_images']) && $_POST['get_images']) ) {
	    	if (isset($existing_record->images) && $existing_record->images) {
	    		$_POST['images'] = $existing_record->images;
	    	} else {
	    		$_POST['images'] = upload_images_from_urls(tv_images($_POST['tmdb_id']), $post_id, $tv->name);
	    	}
	    	if ($existing_record) {
	    		$images = is_array($_POST['images']) ? serialize($_POST['images']) : $_POST['images'];
			    $wpdb->update($table_name, [ 'images' => $images ], ['ID' => $post_id], ['%s'], ['%d']);
			} else {
				$wpdb->insert($table_name, [ 'ID' => $post_id, 'images' => $images ], ['%d', '%s']);
			}
		}
	  if( (!isset($_POST['videos']) || $_POST['videos']==='') && (isset($_POST['get_videos']) && $_POST['get_videos']) ) {
	    	if (isset($existing_record->videos) && $existing_record->videos) {
	    		$_POST['videos'] = $existing_record->videos;
	    	} else {
	    		$_POST['videos'] = tv_videos($_POST['tmdb_id'], $post_id, $number_of_seasons);
	    	}
	    	if ($existing_record) {
	    		$wpdb->update($table_name, [ 'videos' => is_array($_POST['videos']) ? serialize($_POST['videos']) : $_POST['videos']], ['ID' => $post_id], [ '%s' ], ['%d']);
	    	} else {
	    		$wpdb->insert($table_name, [ 'ID' => $post_id, 'videos' => is_array($_POST['videos']) ? serialize($_POST['videos']) : $_POST['videos'] ], ['%d', '%s']);
			}
		}
		
		if((!isset($_POST['credits']['cast'][0]['person'])  || !isset($_POST['credits']['crew'][0]['person']) || !$_POST['credits']['cast'][0]['person'] || !$_POST['credits']['crew'][0]['person']) && (isset($_POST['get_credits']) && $_POST['get_credits'])) {
	        $credits = insert_tv_credits($post_id, $_POST['tmdb_id'], $timestamp);
	        // if ($existing_record) {
	    	// 	$wpdb->update($table_name, [ 'credits' => $credits ], ['ID' => $post_id], ['%s'], ['%d']);
			// } else {
			// 	$wpdb->insert($table_name, [ 'ID' => $post_id, 'credits' => $credits ], ['%d', '%s']);
			// }
	  }
		
	  if((!isset($_POST['star_cast'][0]['person']) || !$_POST['star_cast'][0] || !$_POST['star_cast'][0]['person']) && (isset($_POST['get_credits']) && $_POST['get_credits'])) {
	        $_POST['star_cast'] = array_map(function($cast){ return ['person' => $cast['person'], 'character' => $cast['acting_job']]; }, array_slice($credits['cast'], 0, 4)); $update=true; //insert_tv_star_casts($tmdb_tv_id)
	  }

	  // remove_action('save_post', 'tmdb_data');
      // wp_update_post( ['ID' => $post_id, 'post_content' => serialize($credits) ]);

    add_action('save_post', 'tmdb_data');

		
		if ($update) {
		  $data = [
		  	'ID' => $post_id,
		    'release_date' => $_POST['release_date'],
		    'release_timestamp' => $timestamp,
		    'star_cast' => is_array($_POST['star_cast']) ? serialize($_POST['star_cast']) : $_POST['star_cast'],
		    'credits' => is_array($_POST['credits']) ? serialize($_POST['credits']) : $_POST['credits'],
		    'original_title' => stripslashes($_POST['original_title']),
		    'schedule_time' => $_POST['schedule_time'],
		    'certification' => $_POST['certification'],
		    'runtime' => $_POST['runtime'],
		    'revenue' => $_POST['revenue'],
		    'budget' => $_POST['budget'],
		    'tagline' => stripslashes($_POST['tagline']),
		    'production_house' => stripslashes($_POST['production_house']),
		    'streaming_platforms' => $_POST['streaming_platforms'],
		    'tmdb_id' => $_POST['tmdb_id'],
		    'seasons' => isset($_POST['seasons']) ? (is_array($_POST['seasons']) ? serialize($_POST['seasons']) : $_POST['seasons']) : '',
		    'last_season' => $_POST['last_season'],
		    'last_episode' => $_POST['last_episode'],
		    'finished' => $_POST['finished'],
		    'average_rating' => (float)$tv->vote_average,
		    'vote_count' => (float)$tv->vote_count,
		    'popularity' => $_POST['popularity']
		  ];

		  $format = ['%d', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%d', '%d', '%d', '%f', '%d', '%f'];

		  if ($existing_record) {
		    // Update existing row
		    $where = ['ID' => $post_id];
		    $where_format = ['%d'];
		    $wpdb->update($table_name, $data, $where, $format, $where_format);
		  } else {
		    // Insert new row
		    $wpdb->insert($table_name, $data, $format);
		  }
		}
	} else {
		if(isset($_POST['videos'])) videos_post_id_update($_POST['videos'], $post_id);

		if ($existing_record) { $wpdb->update($table_name, [ 'release_timestamp' => $timestamp ], ['ID' => $post_id], [ '%d' ], ['%d']);
    	} else { $wpdb->insert($table_name, [ 'ID' => $post_id, 'release_timestamp' => $timestamp ], ['%d', '%d']); }

		if ($_POST['credits'] && $existing_record) {
			$existing_credits = $existing_record->credits_temp ? unserialize($existing_record->credits_temp) : '';
			if ($existing_credits && $_POST['credits'] !== $existing_credits) {
				foreach ($existing_credits['cast'] as $credit) $wpdb->query( "DELETE FROM {$table_name}_cast  WHERE dramas = $post_id AND person = {$credit['person']}" );
				foreach ($existing_credits['crew'] as $credit) $wpdb->query( "DELETE FROM {$table_name}_crew  WHERE dramas = $post_id AND person = {$credit['person']}" );
			}
			$wpdb->update($table_name, [ 'credits_temp' => is_array($_POST['credits']) ? serialize($_POST['credits']) : '' ], ['ID' => $post_id], ['%s'], ['%d']);
		}
	}

	if( $timestamp ) {
		$current_time = time();
		$publish_timestamp = $timestamp-1209600;
		$publish_date = $publish_timestamp < $current_time ? date("Y-m-d H:i:s", $publish_timestamp) : '';
    	if($publish_date) $wpdb->update($wpdb->prefix.'posts', ['post_date' => $publish_date, 'post_date_gmt' => $publish_date], ['ID' => $post_id], ['%s','%s'], ['%d']);
    }

	if ($_POST['credits']) {
		$cast = isset($_POST['credits']['cast']) ? $_POST['credits']['cast'] : [];
		$crew = isset($_POST['credits']['crew']) ? $_POST['credits']['crew'] : [];
		foreach ($cast as $credit) { process_custom_cast('tv_series', $post_id, $credit, $timestamp); }
		foreach ($crew as $credit) { process_custom_crew('tv_series', $post_id, $credit, $timestamp); }

		if (!isset($_POST['star_cast'][3]['person']) || !$_POST['star_cast'][3]['person'] && $cast) {
			$star_casts = array_slice($cast, 0, 4);
	    	$_POST['star_cast'] = array_map(function($star_cast){ return ['person' => $star_cast['person'], 'character' => $star_cast['acting_job']]; }, $star_casts);
	    	$wpdb->update($table_name, [ 'star_cast' => is_array($_POST['star_cast']) ? serialize($_POST['star_cast']) : '' ], ['ID' => $post_id], ['%s'], ['%d']);
		}
	}

	if ($_POST['star_cast']) {
		foreach ($_POST['star_cast'] as $credit) {
			$person_id = $credit['person'];
			$known_for = $person_id ? $wpdb->get_var("SELECT known_for FROM {$wpdb->prefix}tmu_people WHERE `ID` = $person_id") : '';
			$known_for = $known_for ? unserialize($known_for) : [];
			if (!$known_for || !in_array($post_id, $known_for)) {
				$known_for[] = $post_id;
				$wpdb->update($wpdb->prefix.'tmu_people', [ 'known_for' => serialize($known_for) ], ['ID' => $person_id], ['%s'], ['%d']);
			}
		}
	}

	// $year = $timestamp ?? NULL;
	// $year = $year ?? ($tv->first_air_date ? strtotime($tv->first_air_date) : NULL);
	// update_tv_series_credits($post_id, $_POST['credits'], $year);
}

function insert_tv_credits($post_id, $tmdb_tv_id, $year) {
	try {
		global $wpdb; $table_name =  $wpdb->prefix.'tmu_tv_series';
		$wpdb->query( "DELETE FROM {$table_name}_cast  WHERE tv_series = $post_id" );
		$wpdb->query( "DELETE FROM {$table_name}_crew  WHERE tv_series = $post_id" );

	  	$client = new \GuzzleHttp\Client();
		// $response_credits = $client->request('GET', 'https://api.themoviedb.org/3/tv/'.$tmdb_tv_id.'/credits', auth_key());
		$response_aggregate_credits = $client->request('GET', 'https://api.themoviedb.org/3/tv/'.$tmdb_tv_id.'/aggregate_credits', auth_key());
		
		// $credits = json_decode($response_credits->getBody());
		$aggregate_credits = json_decode($response_aggregate_credits->getBody());

		// $cast = $credits->cast ?? [];
		// $crew = $credits->crew ?? [];

		$aggregate_cast = $aggregate_credits->cast;
		$aggregate_crew = $aggregate_credits->crew;
		
		$result = ['cast' => [], 'crew' => []];


		// foreach ($cast as $credit) { $result['cast'][] = process_cast('tv_series', $post_id, $credit, $year); }
		foreach ($aggregate_cast as $credit) { $result['cast'][] = process_cast('tv_series', $post_id, $credit, $year); }
		// foreach ($crew as $credit) { $result['crew'][] = process_crew('tv_series', $post_id, $credit, $year); }
		foreach ($aggregate_crew as $credit) { process_crew('tv_series', $post_id, $credit, $year); }

		// $processed_credits = array_map(function($credit) use ($wpdb, $table_name) {
		// 	$post_id = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM $table_name WHERE tmdb_id = %d", $credit->id) );
			
		// 	if(!$post_id){ insert_credit($credit); }
			
		// 	if ($post_id) {
        //         if (isset($credit->character)) {
        //             return ['person' => $post_id, 'department' => 'Acting', 'acting_job' => stripslashes($credit->character)];
        //         } elseif ($credit->department) {
        //         	$job = clean_job_string($credit->department) . '_job';
        //             return ['person' => $post_id, 'department' => $credit->department, $job => stripslashes($credit->job)];
        //         }
        //     }
		// }, $credits);
		
		// $result = [
        //     'cast' => array_filter($processed_credits, fn($item) => $item && $item['department'] === 'Acting'),
        //     'crew' => array_filter($processed_credits, fn($item) => $item && $item['department'] !== 'Acting'),
        // ];

        return $result;
	} catch(Exception $e) {
	  return false;
	}
}


///  unused
function insert_tv_star_casts($tmdb_tv_id) {
	try {
	  	$client = new \GuzzleHttp\Client();
		$response = $client->request('GET', 'https://api.themoviedb.org/3/tv/'.$tmdb_tv_id.'/credits', auth_key());
		$data = json_decode($response->getBody());
		$casts = $data->cast ?? [];
		$credits = array_slice($casts, 0, 4);
		global $wpdb;
		$table_name = $wpdb->prefix.'tmu_people';
		return serialize(array_map(function($credit) use ($wpdb, $table_name) {
			$post_id = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM $table_name WHERE tmdb_id = %d", $credit->id) );
			
			if(!$post_id){ $post_id = insert_credit($credit); }
			
			if($post_id) return ['person' => $post_id, 'character' => stripslashes($credit->character)];
		}, $credits));
	} catch(Exception $e) {
	  return false;
	}
}

function tv_videos($tmid, $tv_series_id, $number_of_seasons){
	try {
	  $client = new \GuzzleHttp\Client();
		$response = $client->request('GET', 'https://api.themoviedb.org/3/tv/'.$tmid.'/videos', auth_key());
		$data = json_decode($response->getBody());
		$videos = $data->results ?? [];

		for ($i=1; $i <= $number_of_seasons; $i++) { 
			$response_t = $client->request('GET', 'https://api.themoviedb.org/3/tv/'.$tmid.'/season/'.$i.'/videos?language=en-US', auth_key());
			$data_t = json_decode($response_t->getBody());
			$videos = isset($data_t->results) && $data_t->results ? array_merge($videos, $data_t->results) : $videos;
		}

		
		global $wpdb;
		$table_name = $wpdb->prefix.'tmu_videos';
		return array_map(function($video) use ($wpdb, $table_name, $tv_series_id) {
			if($video->site == 'YouTube'){
				$post_id = wp_insert_post([
					'post_title'    => ($video->name),
					'post_status'   => 'publish',
					'post_type'     => 'video'
				]);

				if($post_id) wp_update_post( array( 'ID' => $post_id, 'post_name' => $post_id.'-'.sanitize_title($video->name) ) );

				$ytVideoId = $video->key;
				$url = 'http://img.youtube.com/vi/'.$ytVideoId.'/maxresdefault.jpg';
        		$ytImgUrl = image_exist($url) ? $url : (image_exist('http://img.youtube.com/vi/'.$ytVideoId.'/sddefault.jpg') ? 'http://img.youtube.com/vi/'.$ytVideoId.'/sddefault.jpg' : 'http://img.youtube.com/vi/'.$ytVideoId.'/hqdefault.jpg');
				$filename = $video->name.'-'.generateRandomString().'.webp';
				$filetitle = $video->name;

				$context = stream_context_create(['http' => ['ignore_errors' => true]]);
				$fileContents = file_get_contents($ytImgUrl, false, $context);

				if (!$fileContents || (strlen($fileContents) < 1024)) {
					$fileContents = file_get_contents();
				}

				if ( !$fileContents || strlen($fileContents) < 1024 ) {
					$fileContents = wp_remote_get($ytImgUrl);
					if (is_array( $fileContents ) && ! is_wp_error( $fileContents )) $fileContents = $fileContents['body'];
				}

				if ($fileContents && strlen($fileContents) > 1024) {
					$gd_image = imagecreatefromstring($fileContents);
	    			if ($gd_image) {
				        ob_start(); // Start output buffering

				        // Convert to WebP with quality 50 and maximum compression (lossy)
				        imagewebp($gd_image, null, 50);

				        $webp_data = ob_get_contents(); // Get WebP data from output buffer
				        ob_end_clean(); // Clear output buffer

				        imagedestroy($gd_image);
				    }
					$upload_file = wp_upload_bits($filename, null, $webp_data);
				}

				if (!$upload_file['error']) {
					$wp_filetype = wp_check_filetype($filename, null );
					$attachment = array(
						'post_mime_type' => 'image/jpeg',
						'post_title' => preg_replace('/\.[^.]+$/', '', $filetitle),
						'post_content' => '',
						'post_status' => 'inherit',
						'post_parent' => $post_id
					);
					$attachment_id = wp_insert_attachment( $attachment, $upload_file['file'], 0 );
					if (!is_wp_error($attachment_id)) {
						require_once(ABSPATH . "wp-admin" . '/includes/image.php');
						$attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );
						wp_update_attachment_metadata( $attachment_id,  $attachment_data );
						add_post_meta($post_id,'_thumbnail_id',$attachment_id);
						update_post_meta($attachment_id, '_wp_attachment_image_alt', $filetitle );
					}
				}
				
				$wpdb->replace( $table_name,  [ 'ID' => $post_id, 'video_data' => serialize(['source' => $video->key, 'content_type' => $video->type]), 'post_id' => $tv_series_id ], ['%d', '%s', '%d']);
				return $post_id;
			}
		}, $videos);
	} catch(Exception $e) {
	  return false;
	}
}

function update_tv_series_credits($post_id, $credits, $year) {
	$credits2 = full_cast_crew($post_id, $credits);

	if (isset($credits2['cast']) && is_array($credits2['cast'])) {
		foreach ($credits2['cast'] as $cast) {
			insert_tv_series_cast($post_id, $cast, $year);
		}
	} else {
		foreach ($credits['cast'] as $cast) {
			insert_tv_series_cast($post_id, $cast, $year);
		}
	}

	if (isset($credits['crew']) && is_array($credits['crew'])) {
		foreach ($credits['crew'] as $crew) {
			insert_tv_series_crew($post_id, $crew, $year);
		}
	}
	if (isset($credits2['crew']) && is_array($credits2['crew'])) {
		foreach ($credits2['crew'] as $crew) {
			insert_tv_series_crew($post_id, $crew, $year);
		}
	}
}

function insert_tv_series_cast($post_id, $cast, $year) {
  global $wpdb;
  $table_name = $wpdb->prefix . 'tmu_tv_series_cast';

  if ($cast['person']) {
  	$job = isset($cast['job']) ? $cast['job'] : '';
  	$existing_record = $wpdb->get_row($wpdb->prepare("SELECT `ID`,`job` FROM {$table_name} WHERE tv_series = %d AND person = %d", $post_id, $cast['person'] ));
  	if ($existing_record) {
	  	$wpdb->replace( $table_name,  [
	  		'ID' => $existing_record->ID,
			'tv_series' => $post_id,
			'person' => $cast['person'],
			'job' => ($job === $existing_record->job || !isset($existing_record->job) || !$existing_record->job) ? $job : (str_contains($existing_record->job, $job) ? $existing_record->job : $existing_record->job.', '.$job),
		    'release_year' => $year,
			'count' => isset($cast['count']) ? $cast['count'] : 0
		  ], ['%d', '%d', '%d', '%s', '%d', '%d']);
	  } else {
	  	$wpdb->replace( $table_name,  [
			'tv_series' => $post_id,
			'person' => $cast['person'],
			'job' => $job,
		    'release_year' => $year,
			'count' => isset($cast['count']) ? $cast['count'] : 0
		  ], ['%d', '%d', '%s', '%d', '%d']);
	  }
  }
}

function insert_tv_series_crew($post_id, $crew, $year) {
  if ($crew['person']) {
  	global $wpdb;
	$table_name = $wpdb->prefix . 'tmu_tv_series_crew';

	if ($crew && $crew['person'] && $crew['job']) {
		
		$existing_record = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$table_name} WHERE tv_series = %d AND person = %d AND job = %s", $post_id, $crew['person'], $crew['job']));

		if ($existing_record) {
			$wpdb->replace( $table_name,  [
			'ID' => $existing_record,
			'tv_series' => $post_id,
			'person' => $crew['person'],
		    'department' => $crew['department'],
		    'job' => $crew['job'],
		    'release_year' => $year,
			'count' => isset($crew['count']) ? $crew['count'] : 0
		  ], ['%d', '%d', '%d', '%s', '%s', '%d', '%d']);
		} else {
			$wpdb->insert( $table_name,  [
			'tv_series' => $post_id,
			'person' => $crew['person'],
		    'department' => $crew['department'],
		    'job' => $crew['job'],
		    'release_year' => $year,
			'count' => isset($crew['count']) ? $crew['count'] : 0
		  ], ['%d', '%d', '%s', '%s', '%d', '%d']);
		}

	}
  }
}













// ////////////////////  New Process  //////////////////////////////////
// function process_cast($post_id, $cast){
// 	global $wpdb;
// 	$person = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM $wpdb->prefix.'tmu_people' WHERE tmdb_id = %d", $cast->id) );
// 	if(!$person) { $person = insert_credit($cast); }
// 	if($person) {
// 		$job = $cast->character ? stripslashes($cast->character) : '';
// 	  	$existing_record = $wpdb->get_row($wpdb->prepare("SELECT `ID`,`job` FROM {$table_name} WHERE tv_series = %d AND person = %d", $post_id, $person ));
	  	
// 	  	if ($existing_record) {
// 	  		if (stripslashes($existing_record->job) !== $job) {
// 	  			$wpdb->replace( $wpdb->prefix . 'tv_series_cast',  [
// 			  		'ID' => $existing_record->ID,
// 					'tv_series' => $post_id,
// 					'person' => $person,
// 					'job' => $job,
// 				    'release_year' => $year
// 				], ['%d', '%d', '%d', '%s', '%d']);
// 	  		}
// 		} else {
// 		  	$wpdb->replace( $wpdb->prefix . 'tmu_tv_series_cast',  [
// 				'tv_series' => $post_id,
// 				'person' => $person,
// 				'job' => $job,
// 			    'release_year' => $year
// 			], ['%d', '%d', '%s', '%d']);
// 		}

// 		return ['person' => $person, 'department' => 'Acting', 'acting_job' => $job];
// 	}
// }


// function process_crew($post_id, $crew){
// 	global $wpdb;
// 	$person = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM $wpdb->prefix.'tmu_people' WHERE tmdb_id = %d", $crew->id) );
// 	if(!$person) { $person = insert_credit($crew); }
// 	if($person) {
// 		$job = $credit->job ? stripslashes($credit->job) : '';
// 	  	$existing_record = $wpdb->get_row($wpdb->prepare("SELECT `ID`,`job` FROM {$table_name} WHERE tv_series = %d AND person = %d", $post_id, $person ));
	  	
// 	  	if ($existing_record) {
// 	  		if (stripslashes($existing_record->job) !== $job) {
// 	  			$wpdb->replace( $wpdb->prefix . 'tmu_tv_series_crew',  [
// 			  		'ID' => $existing_record->ID,
// 					'tv_series' => $post_id,
// 					'person' => $person,
// 					'department' => $credit->department,
// 					'job' => $job,
// 				    'release_year' => $year
// 				], ['%d', '%d', '%d', '%s', '%d']);
// 	  		}
// 		} else {
// 		  	$wpdb->replace( $wpdb->prefix . 'tmu_tv_series_crew',  [
// 				'tv_series' => $post_id,
// 				'person' => $person,
// 				'department' => $credit->department,
// 				'job' => $job,
// 			    'release_year' => $year
// 			], ['%d', '%d', '%s', '%d']);
// 		}
// 		$job_key = clean_job_string($credit->department) . '_job';
// 		return ['person' => $person, 'department' => $credit->department, $job_key => $job];
// 	}
// }