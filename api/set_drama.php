<?php

function add_drama($post_id){

	$timestamp = (isset($_POST['release_date']) && $_POST['release_date']) ? strtotime($_POST['release_date']) : '';
	
	global $wpdb;
	$table_name = $wpdb->prefix . 'tmu_dramas';
	$existing_record = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} WHERE ID = %d", $post_id));

	if (isset($_POST['tmdb_id']) && $_POST['tmdb_id']) {
		
		$old_drama = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $table_name WHERE `tmdb_id` = %d AND NOT `ID` = %d", $_POST['tmdb_id'], $post_id));
		if($old_drama){ remove_action('save_post', 'tmdb_data'); wp_delete_post( $post_id ); add_action('save_post', 'tmdb_data'); return; }

	  	$drama = tv_data($_POST['tmdb_id']); if(!$drama){return;} $update = false;

	  	$number_of_seasons = $drama->number_of_seasons;

	    if ( !has_post_thumbnail($post_id) ) { set_post_feature_image($post_id, $drama->poster_path, $drama->name); }

	    $timestamp = $timestamp ? $timestamp : ($drama->first_air_date ? strtotime($drama->first_air_date) : NULL);
		$release_year = $timestamp ? date('Y', $timestamp) : NULL;

	    if( !isset($_POST['original_title']) || $_POST['original_title']==='' )  { $_POST['original_title'] = $drama->original_name; $update=true; }
	    if( !isset($_POST['release_date']) || $_POST['release_date']==='' ) { $_POST['release_date'] = $drama->first_air_date; $update=true; }
	    if( !isset($_POST['runtime']) || $_POST['runtime']=='' || !$_POST['runtime']) { $_POST['runtime'] = is_array($drama->episode_run_time) && isset($drama->episode_run_time[0]) ? $drama->episode_run_time[0] : $drama->episode_run_time; $update=true; }
	    if( !isset($_POST['tagline']) || $_POST['tagline']==='' ) { $_POST['tagline'] = $drama->tagline; $update=true; }
	    if( !isset($_POST['production_house']) || $_POST['production_house']==='' ) { $_POST['production_house'] = productions($drama->production_companies); $update=true; }
	    if( !isset($_POST['certification']) || $_POST['certification']==='' || $_POST['certification']==='U/A' ) { $_POST['certification'] = tv_certification($_POST['tmdb_id']); $update=true; }
	    if( !isset($_POST['popularity']) || $_POST['popularity']==='' || $_POST['popularity'] == '0' ) { $_POST['popularity'] = $drama->popularity; $update=true; }
	    if( !isset($_POST['finished']) || $_POST['finished'] == '' || (($drama->status=='Ended' || $drama->status=='Canceled') && $_POST['finished']==0) ) { $_POST['finished'] = $drama->status=='Ended' || $drama->status=='Canceled' ? 1 : 0; $update=true; }

	    $last_episode = $drama->next_episode_to_air ? $drama->next_episode_to_air : $drama->last_episode_to_air;

	    if( !has_term('', 'genre') ) {
	        $genres = array_map(function($genre) { return $genre->name; }, $drama->genres);
	        wp_set_post_terms( $post_id, $genres, 'genre', true );
	    };
	    if( !has_term('', 'keyword') ) {
	        $tags = tv_tags($_POST['tmdb_id']);
	        wp_set_post_terms( $post_id, $tags, 'keyword', true );
	    };
	    if( !has_term('', 'by-year') ) {
	        if($drama->first_air_date) wp_set_post_terms( $post_id, [$release_year], 'by-year', true );
	    };
	    if( !has_term('', 'country') ) {
	        $countries = array_map(function($country) { return $country->name; }, $drama->production_countries);
	        wp_set_post_terms( $post_id, $countries, 'country', true );
	    };
	    if( !has_term('', 'language') ) {
	        $languages = array_map(function($language) { return $language->english_name; }, $drama->spoken_languages);
	        wp_set_post_terms( $post_id, $languages, 'language', true );
	    };
	    if (!has_term('', 'channel')) {
	    	foreach ($drama->networks as $channel) {
	    		wp_set_post_terms( $post_id, [$channel->name], 'channel', true );
	    		$term_id = get_term_by( 'slug', $channel->name, 'channel' )->term_id;
	    		if(!get_term_meta( $term_id, 'logo', true )) {
	    			$logo_url = 'https://image.tmdb.org/t/p/original'.$channel->logo_path;
	    			$logo_id = upload_images_from_urls($logo_url, $term_id, $channel->name);
	    			update_term_meta( $term_id, 'logo', $logo_id);
	    		}
	    	}
	    }

	    remove_action('save_post', 'tmdb_data');
		
	    if( !isset($_POST['post_title']) || !$_POST['post_title'] ) {
	        wp_update_post( ['ID' => $post_id, 'post_title' => $drama->name] );
	    }

	    if( ! $_POST['content'] ) {
	        wp_update_post( ['ID' => $post_id, 'post_content' => $drama->overview ]);
	    }

		if( (!isset($_POST['images']) || $_POST['images']=='') && (isset($_POST['get_images']) && $_POST['get_images']) ) {
	    	if (isset($existing_record->images) && $existing_record->images) {
	    		$_POST['images'] = $existing_record->images;
	    	} else {
	    		$_POST['images'] = upload_images_from_urls(tv_images($_POST['tmdb_id']), $post_id, $drama->name);
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
	    		$_POST['videos'] = drama_videos($_POST['tmdb_id'], $post_id, $number_of_seasons);
	    	}
	    	if ($existing_record) {
	    		$wpdb->update($table_name, [ 'videos' => is_array($_POST['videos']) ? serialize($_POST['videos']) : $_POST['videos']], ['ID' => $post_id], [ '%s' ], ['%d']);
	    	} else {
	    		$wpdb->insert($table_name, [ 'ID' => $post_id, 'videos' => is_array($_POST['videos']) ? serialize($_POST['videos']) : $_POST['videos'] ], ['%d', '%s']);
			}
		}
		
		if(isset($_POST['get_credits']) && $_POST['get_credits']) {
	        $credits = insert_drama_credits($post_id, $_POST['tmdb_id'], $timestamp);
	        // if ($existing_record && $credits) {
	    	// 	$wpdb->update($table_name, [ 'credits' => is_array($credits) ? serialize($credits) : $credits ], ['ID' => $post_id], ['%s'], ['%d']);
			// } else {
			// 	$wpdb->insert($table_name, [ 'ID' => $post_id, 'credits' => is_array($credits) ? serialize($credits) : $credits ], ['%d', '%s']);
			// }
	    }

	    if(isset($_POST['get_episodes']) && $_POST['get_episodes']) { insert_drama_episodes($post_id, 1, $_POST['tmdb_id']); }
		
	    if((!isset($_POST['star_cast'][0]['person']) || !$_POST['star_cast'][0] || !$_POST['star_cast'][0]['person']) && (isset($_POST['get_credits']) && $_POST['get_credits'])) {
	    	$cast = isset($_POST['credits']['cast']) ? $_POST['credits']['cast'] : [];
	        $_POST['star_cast'] = array_map(function($cast){ return ['person' => $cast['person'], 'character' => $cast['acting_job']]; }, array_slice(isset($credits['cast']) ? $credits['cast'] : $_POST['credits']['cast'], 0, 4)); $update=true; //insert_drama_star_casts($tmdb_drama_id)
	    }

	  // remove_action('save_post', 'tmdb_data');
      // wp_update_post( ['ID' => $post_id, 'post_content' => json_encode($videos) ]);

    	add_action('save_post', 'tmdb_data');

		
		if ($update) {

		  $data = [
		  	'ID' => $post_id,
		    'release_date' => $_POST['release_date'],
		    'release_timestamp' => $timestamp,
		    'star_cast' => is_array($_POST['star_cast']) ? serialize($_POST['star_cast']) : $_POST['star_cast'],
		    'original_title' => stripslashes($_POST['original_title']),
		    'schedule_day' => isset($_POST['schedule_day']) ? (is_array($_POST['schedule_day']) ? serialize($_POST['schedule_day']) : $_POST['schedule_day']) : '',
		    'certification' => $_POST['certification'],
		    'runtime' => $_POST['runtime'],
		    'tagline' => stripslashes($_POST['tagline']),
		    'production_house' => stripslashes($_POST['production_house']),
		    'tmdb_id' => $_POST['tmdb_id'],
		    'finished' => $_POST['finished'],
		    'average_rating' => (float)$drama->vote_average,
		    'vote_count' => $drama->vote_count,
		    'popularity' => $_POST['popularity']
		  ];

		  $format = ['%d', '%s', '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%d', '%f', '%d', '%f'];

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
		videos_post_id_update($_POST['videos'], $post_id);
		
		if ($existing_record) { $wpdb->update($table_name, [ 'release_timestamp' => $timestamp ], ['ID' => $post_id], [ '%d' ], ['%d']);
    	} else { $wpdb->insert($table_name, [ 'ID' => $post_id, 'release_timestamp' => $timestamp ], ['%d', '%d']); }

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
		foreach ($cast as $credit) { process_custom_cast('dramas', $post_id, $credit, $timestamp); }
		foreach ($crew as $credit) { process_custom_crew('dramas', $post_id, $credit, $timestamp); }

		if ($existing_record) {
			$existing_credits = $existing_record->credits_temp ? unserialize($existing_record->credits_temp) : '';
			if ($existing_credits && $_POST['credits'] !== $existing_credits) {
				foreach ($existing_credits['cast'] as $credit) $wpdb->query( "DELETE FROM {$table_name}_cast  WHERE dramas = $post_id AND person = {$credit['person']}" );
				foreach ($existing_credits['crew'] as $credit) $wpdb->query( "DELETE FROM {$table_name}_crew  WHERE dramas = $post_id AND person = {$credit['person']}" );
			}
			$wpdb->update($table_name, [ 'credits_temp' => is_array($_POST['credits']) ? serialize($_POST['credits']) : '' ], ['ID' => $post_id], ['%s'], ['%d']);
		}

		if (!isset($_POST['star_cast'][3]['person']) || !$_POST['star_cast'][3]['person'] && $cast) {
			$star_casts = array_slice($cast, 0, 4);
	    	$_POST['star_cast'] = array_map(function($star_cast){ return ['person' => $star_cast['person'], 'character' => $star_cast['acting_job']]; }, $star_casts);
	    	$wpdb->update($table_name, [ 'star_cast' => is_array($_POST['star_cast']) ? serialize($_POST['star_cast']) : '' ], ['ID' => $post_id], ['%s'], ['%d']);
		}
	}
	if ($_POST['schedule_time']) {
		$time = strtotime($_POST['schedule_time']);
		if ($existing_record) { $wpdb->update($table_name, [ 'schedule_time' => $_POST['schedule_time'], 'schedule_timestamp' => $time ], ['ID' => $post_id], [ '%s', '%d' ], ['%d']);
    	} else { $wpdb->insert($table_name, [ 'ID' => $post_id, 'schedule_time' => $_POST['schedule_time'], 'schedule_timestamp' => $time ], ['%d', '%s', '%d']); }
	}

	if (isset($_POST['where_to_watch'][0]['channel']) && $_POST['where_to_watch'][0]['channel']) {
		if ($existing_record) { $wpdb->update($table_name, [ 'where_to_watch' => (is_array($_POST['where_to_watch']) ? serialize($_POST['where_to_watch']) : $_POST['where_to_watch']) ], ['ID' => $post_id], [ '%s' ], ['%d']);
    	} else { $wpdb->insert($table_name, [ 'ID' => $post_id, 'where_to_watch' => (is_array($_POST['where_to_watch']) ? serialize($_POST['where_to_watch']) : $_POST['where_to_watch']) ], ['%d', '%s']); }
	}

	// remove_action('save_post', 'tmdb_data');
    // wp_update_post( ['ID' => $post_id, 'post_content' => 'test' ]); 
    // add_action('save_post', 'tmdb_data');

	if ($_POST['star_cast']) {
		foreach ($_POST['star_cast'] as $credit) {
			$person_id = $credit['person'];
			$known_for = $wpdb->get_var("SELECT known_for FROM {$wpdb->prefix}tmu_people WHERE `ID` = $person_id");
			$known_for = $known_for ? unserialize($known_for) : [];
			if (!$known_for || !in_array($post_id, $known_for)) {
				$known_for[] = $post_id;
				$wpdb->update($wpdb->prefix.'tmu_people', [ 'known_for' => serialize($known_for) ], ['ID' => $person_id], ['%s'], ['%d']);
			}
		}
	}

	// $year = $timestamp ?? NULL;
	// $year = $year ?? ($drama->first_air_date ? strtotime($drama->first_air_date) : NULL);
	// update_dramas_credits($post_id, $_POST['credits'], $year);
}

function insert_drama_credits($post_id, $tmdb_drama_id, $year) {
	try {
		global $wpdb; $table_name =  $wpdb->prefix.'tmu_dramas';
		$wpdb->query( "DELETE FROM {$table_name}_cast  WHERE dramas = $post_id" );
		$wpdb->query( "DELETE FROM {$table_name}_crew  WHERE dramas = $post_id" );

	  	$client = new \GuzzleHttp\Client();
		$response_aggregate_credits = $client->request('GET', 'https://api.themoviedb.org/3/tv/'.$tmdb_drama_id.'/aggregate_credits', auth_key());
		
		$aggregate_credits = json_decode($response_aggregate_credits->getBody());

		$aggregate_cast = $aggregate_credits->cast;
		$aggregate_crew = $aggregate_credits->crew;
		
		$result = ['cast' => [], 'crew' => []];

		foreach ($aggregate_cast as $credit) { $result['cast'][] = process_cast('dramas', $post_id, $credit, $year); }
		foreach ($aggregate_crew as $credit) { $result['crew'][] = process_crew('dramas', $post_id, $credit, $year); }

        return $result;
	} catch(Exception $e) {
	  return false;
	}
}


function drama_videos($tmid, $drama_id, $number_of_seasons){
	try {
	  $client = new \GuzzleHttp\Client();
		$response = $client->request('GET', 'https://api.themoviedb.org/3/tv/'.$tmid.'/videos?language=en-US', auth_key());
		$data = json_decode($response->getBody());
		$videos = $data->results ?? [];

		for ($i=1; $i <= $number_of_seasons; $i++) { 
			$response_t = $client->request('GET', 'https://api.themoviedb.org/3/tv/'.$tmid.'/season/'.$i.'/videos?language=en-US', auth_key());
			$data_t = json_decode($response_t->getBody());
			$videos = isset($data_t->results) && $data_t->results ? array_merge($videos, $data_t->results) : $videos;
		}

		
		global $wpdb;
		$table_name = $wpdb->prefix.'tmu_videos';
		return array_map(function($video) use ($wpdb, $table_name, $drama_id) {
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
				
				$wpdb->replace( $table_name,  [ 'ID' => $post_id, 'video_data' => serialize(['source' => $video->key, 'content_type' => $video->type]), 'post_id' => $drama_id ], ['%d', '%s', '%d']);
				return $post_id;
			}
		}, $videos);
	} catch(Exception $e) {
	  return false;
	}
}