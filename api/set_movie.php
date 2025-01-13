<?php

function add_movie($post_id){
	
	$timestamp = (isset($_POST['release_date']) && $_POST['release_date']) ? strtotime($_POST['release_date']) : NULL;
	global $wpdb;
	$table_name = $wpdb->prefix . 'tmu_movies';

	if($_POST['tmdb_id']) {

		$old_movie = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $table_name WHERE `tmdb_id` = %d AND NOT `ID` = %d", $_POST['tmdb_id'], $post_id));
		if($old_movie){ remove_action('save_post', 'tmdb_data'); wp_delete_post( $post_id ); add_action('save_post', 'tmdb_data'); return; }

		$existing_record = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} WHERE ID = %d", $post_id));

	    $movie = movie_data($_POST['tmdb_id']); $update = false;
	    
	    if ( !has_post_thumbnail($post_id) ) { set_post_feature_image($post_id, $movie->poster_path, $movie->title); }

	    $theatr_release = movie_theater_release($_POST['tmdb_id']);
	    $timestamp = $theatr_release ? strtotime($theatr_release) : ($movie && $movie->release_date ? strtotime($movie->release_date) : ($timestamp ? $timestamp : NULL));
	    $release_year = $timestamp ? date('Y', $timestamp) : NULL;

	    $release_date = $timestamp ? date('Y-m-d', $timestamp) : NULL;

	    if( !isset($_POST['original_title']) || $_POST['original_title']==='' )  { $_POST['original_title'] = $movie->original_title; $update=true; }
	    if( !isset($_POST['release_date']) || $_POST['release_date']==='' || $_POST['release_date']!==$release_date ) { $_POST['release_date'] = $release_date; $update=true; }
	    if( !isset($_POST['runtime']) || $_POST['runtime']=='' || $_POST['runtime']==0 ) { $_POST['runtime'] = is_array($movie->runtime) ? $movie->runtime[0] : $movie->runtime; $update=true; }
	    if( !isset($_POST['tagline']) || $_POST['tagline']==='' ) { $_POST['tagline'] = stripslashes($movie->tagline); $update=true; }
	    if( !isset($_POST['revenue']) || $_POST['revenue']=='' || $_POST['revenue']==0 ) { $_POST['revenue'] = $movie->revenue; $update=true; }
	    if( !isset($_POST['budget']) || $_POST['budget']=='' || $_POST['budget']==0 ) { $_POST['budget'] = $movie->budget; $update=true; }
	    if( !isset($_POST['production_house']) || $_POST['production_house']==='' ) { $_POST['production_house'] = stripslashes(productions($movie->production_companies)); $update=true; }
	    if( !isset($_POST['popularity']) || $_POST['popularity']=='' || $_POST['popularity']==0 ) { $_POST['popularity'] = $movie->popularity; $update=true; }
	    if( !isset($_POST['certification']) || $_POST['certification']==='' || $_POST['certification']==='U/A' ) { $_POST['certification'] = movie_certification($_POST['tmdb_id']); $update=true; }

	    if( !has_term('', 'genre') ) {
	        $genres = array_map(function($genre) { return $genre->name; }, $movie->genres);
	        wp_set_post_terms( $post_id, $genres, 'genre', true );
	    };
	    if( !has_term('', 'keyword') ) {
	        $tags = movie_tags($_POST['tmdb_id']);
	        wp_set_post_terms( $post_id, $tags, 'keyword', true );
	    };
	    if( !has_term('', 'by-year') ) {
	        if($release_date) wp_set_post_terms( $post_id, [$release_year], 'by-year', true );
	    };
	    if( !has_term('', 'country') ) {
	        $countries = array_map(function($country) { return $country->name; }, $movie->production_countries);
	        wp_set_post_terms( $post_id, $countries, 'country', true );
	    };
	    if( !has_term('', 'language') ) {
	        $languages = array_map(function($language) { return $language->english_name; }, $movie->spoken_languages);
	        wp_set_post_terms( $post_id, $languages, 'language', true );
	    };
		
	    remove_action('save_post', 'tmdb_data');

	    if( !isset($_POST['post_title']) || ! $_POST['post_title'] ) {
	        wp_update_post( ['ID' => $post_id, 'post_title' => $movie->title] );
	    }

	    if( ! $_POST['content'] ) {
	        wp_update_post( ['ID' => $post_id, 'post_content' => $movie->overview ]); 
	    }
		
		if((!isset($_POST['credits']['cast'][0]['person']) || !$_POST['credits']['cast'][0]['person'] || !isset($_POST['credits']['crew'][0]['person']) || !$_POST['credits']['crew'][0]['person']) && (isset($_POST['get_credits']) && $_POST['get_credits'])) {
	    
	    $credits = insert_movie_credits($post_id, $_POST['tmdb_id'], $timestamp);
      // $credits = is_array($_POST['credits']) ? serialize($_POST['credits']) : $_POST['credits'];
      
      	// if ($existing_record) {
    		// 	$wpdb->update($table_name, [ 'credits' => $credits ], ['ID' => $post_id], ['%s'], ['%d']);
				// } else {
				// 	$wpdb->insert($table_name, [ 'ID' => $post_id, 'credits' => $credits ], ['%d', '%s']);
				// }
	    }
		
	    if( (!isset($_POST['images']) || $_POST['images']=='') && (isset($_POST['get_images']) && $_POST['get_images']) ) {
	    	if (isset($existing_record->images) && $existing_record->images) {
	    		$_POST['images'] = $existing_record->images;
	    	} else {
	    		$attachments = get_posts( array(
		            'post_type' => 'attachment',
		            'posts_per_page' => -1,
		            'post_parent' => $post_id,
		            'exclude'     => get_post_thumbnail_id(),
			        'fields' => 'ids'
		        ) );

		        if ($attachments) {
		        	$_POST['images'] = $attachments;
		        } else { $_POST['images'] = upload_images_from_urls(movie_images($_POST['tmdb_id']), $post_id, $movie->title); }
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
	    		$results = $wpdb->get_results("SELECT `ID` FROM 'wp_videos' WHERE post_id = $post_id");
	    		if ($results) {
	    			$videos = [];
	    			foreach ($results as $row) $videos[] = $row['ID'];
					$_POST['videos'] = $videos;
	    		} else { $_POST['videos'] = movie_videos($_POST['tmdb_id'], $post_id); }
	    	}
	    	$wpdb->update($table_name, [ 'videos' => is_array($_POST['videos']) ? serialize($_POST['videos']) : $_POST['videos']], ['ID' => $post_id], [ '%s' ], ['%d']);
		}
		
		if((!isset($_POST['star_cast'][0]['person']) || !$_POST['star_cast'][0]['person'])  && (isset($_POST['get_credits']) && $_POST['get_credits'])) {
	        $_POST['star_cast'] = array_map(function($cast){ return ['person' => $cast['person'], 'character' => $cast['acting_job']]; }, array_slice($credits['cast'], 0, 4)); $update=true;  //  insert_movie_star_casts($_POST['tmdb_id'])
	    }

	    add_action('save_post', 'tmdb_data');

		if ($update) {

		  $data = [
		  	'ID' => $post_id,
		    'release_date' => $_POST['release_date'],
		    'release_timestamp' => $timestamp,
		    'star_cast' => is_array($_POST['star_cast']) ? serialize($_POST['star_cast']) : $_POST['star_cast'],
		    'credits' => is_array($_POST['credits']) ? serialize($_POST['credits']) : $_POST['credits'],
		    'original_title' => $_POST['original_title'],
		    'certification' => $_POST['certification'],
		    'runtime' => $_POST['runtime'],
		    'revenue' => $_POST['revenue'],
		    'budget' => $_POST['budget'],
		    'tagline' => $_POST['tagline'],
		    'production_house' => $_POST['production_house'],
		    'streaming_platforms' => $_POST['streaming_platforms'],
		    'tmdb_id' => $_POST['tmdb_id'],
		    'average_rating' => (float)$movie->vote_average,
		    'vote_count' => (float)$movie->vote_count,
		    'popularity' => $_POST['popularity']
		  ];

		  $format = [ '%d', '%s', '%d', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%f', '%d', '%f' ];

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
		foreach ($cast as $credit) { process_custom_cast('movies', $post_id, $credit, $timestamp); }
		foreach ($crew as $credit) { process_custom_crew('movies', $post_id, $credit, $timestamp); }

		if (!isset($_POST['star_cast'][3]['person']) || !$_POST['star_cast'][3]['person'] && $cast) {
			$star_casts = array_slice($cast, 0, 4);
	    	$_POST['star_cast'] = array_map(function($star_cast){ return ['person' => $star_cast['person'], 'character' => $star_cast['acting_job']]; }, $star_casts);
	    	$wpdb->update($table_name, [ 'star_cast' => is_array($_POST['star_cast']) ? serialize($_POST['star_cast']) : '' ], ['ID' => $post_id], ['%s'], ['%d']);
		}
	}

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
	// $year = $year ?? ($release_date ? strtotime($release_date) : NULL);
	// update_movie_credits($post_id, $_POST['credits'], $year);
}



function insert_movie_credits($post_id, $tmdb_movie_id, $year) {
	try {
		global $wpdb; $table_name =  $wpdb->prefix.'tmu_movies';
		$wpdb->query( "DELETE FROM {$table_name}_cast  WHERE movie = $post_id" );
		$wpdb->query( "DELETE FROM {$table_name}_crew  WHERE movie = $post_id" );

	  $client = new \GuzzleHttp\Client();
		$response = $client->request('GET', 'https://api.themoviedb.org/3/movie/'.$tmdb_movie_id.'/credits', auth_key());
		$data = json_decode($response->getBody());
		
		$cast = $data->cast ?? [];
		$crew = $data->crew ?? [];

		$result = ['cast' => [], 'crew' => []];

		foreach ($cast as $credit) { $result['cast'][] = process_cast('movies', $post_id, $credit, $year); }
		foreach ($crew as $credit) { $result['crew'][] = process_crew('movies', $post_id, $credit, $year); }
		
		// $credits = array_merge($cast, $crew);
		// global $wpdb;
		// $table_name = $wpdb->prefix.'tmu_people';
		// $processed_credits = array_map(function($credit) use ($wpdb, $table_name) {
		// 	$post_id = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM $table_name WHERE tmdb_id = %d", $credit->id) );
		// 	$post_id2 = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . $credit->name . "'" );
			
		// 	$post_id = $post_id ? $post_id : $post_id2;
		// 	if(!$post_id){ $post_id = insert_credit($credit); }
			
		// 	if ($post_id) {
        //         if (isset($credit->character)) {
        //             return ['person' => $post_id, 'department' => 'Acting', 'acting_job' => stripslashes($credit->character)];
        //         } elseif ($credit->department) {
        //             return ['person' => $post_id, 'department' => $credit->department, clean_job_string($credit->department) . '_job' => stripslashes($credit->job)];
        //         } else {
        //             return null; // Skip credits that don't fit
        //         }
        //     }
		// }, $credits);
		
		// $result = [
        //     'cast' => array_filter($processed_credits, fn($item) => ($item && $item['department'] === 'Acting')),
        //     'crew' => array_filter($processed_credits, fn($item) => ($item && $item['department'] !== 'Acting')),
        // ];

        return $result;
	} catch(Exception $e) {
	  return false;
	}
}

function insert_movie_star_casts($tmdb_movie_id) {
	try {
	  	$client = new \GuzzleHttp\Client();
		$response = $client->request('GET', 'https://api.themoviedb.org/3/movie/'.$tmdb_movie_id.'/credits', auth_key());
		$data = json_decode($response->getBody());
		$casts = $data->cast ?? [];
		$credits = array_slice($casts, 0, 4);
		global $wpdb;
		$table_name = $wpdb->prefix.'tmu_people';
		return serialize(array_map(function($credit) use ($wpdb, $table_name) {
			$post_id = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM $table_name WHERE tmdb_id = %d", $credit->id) );
			$post_id2 = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . $credit->name . "'" );
			
			$post_id = $post_id ? $post_id : $post_id2;

			if(!$post_id){ $post_id = insert_credit($credit); }
			
			if($post_id) return ['person' => $post_id, 'character' => stripslashes($credit->character)];
		}, $credits));
	} catch(Exception $e) {
	  return false;
	}
}

function movie_videos($tmid, $movie_id){
	try {
	  	$client = new \GuzzleHttp\Client();
		$response = $client->request('GET', 'https://api.themoviedb.org/3/movie/'.$tmid.'/videos', auth_key());
		$data = json_decode($response->getBody());
		$videos = $data->results ?? [];
		global $wpdb;
		$table_name = $wpdb->prefix.'tmu_videos';
		return array_map(function($video) use ($wpdb, $table_name, $movie_id) {
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
				
				if ( !$fileContents ) {
					$fileContents = wp_remote_get($ytImgUrl);
					if (is_array( $fileContents ) && ! is_wp_error( $fileContents )) $fileContents = $fileContents['body'];
				}

				if ($fileContents) {
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

				$upload_file = wp_upload_bits($filename, null, $fileContents);
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
				
				$wpdb->replace( $table_name,  [ 'ID' => $post_id, 'video_data' => serialize(['source' => $video->key, 'content_type' => $video->type]), 'post_id' => $movie_id ], ['%d', '%s', '%d']);
				return $post_id;
			}
		}, $videos);
	} catch(Exception $e) {
	  return false;
	}
}


function update_movie_credits($post_id, $credits, $year) {
	global $wpdb;

	if (isset($credits['cast']) && is_array($credits['cast'])) {
		$new_casts = count($credits['cast']);
		
		$existing_casts = $wpdb->get_results($wpdb->prepare("SELECT ID,person FROM $wpdb->prefix.'tmu_movies_cast' WHERE movie = %d", $post_id));
		$cast_array = array();
		if (count($existing_casts) !== $new_casts) {
			foreach ($existing_casts as $row) {
			    $cast_array[$row->ID] = $row->person;
			}
		}

		foreach ($credits['cast'] as $cast) {
			insert_movie_cast($post_id, $cast, $year);
			if ($cast_array && ($key = array_search($cast['person'], $cast_array)) !== false) { unset($cast_array[$key]); }
		}

		if ($cast_array) {
			foreach ($cast_array as $key => $value) {
				$where = array('ID' => $key);
				$wpdb->delete($wpdb->prefix.'tmu_movies_cast', $where);
			}
		}
	}

	if (isset($credits['crew']) && is_array($credits['crew'])) {
		$new_crew = count($credits['crew']);
		
		$existing_crew = $wpdb->get_results($wpdb->prepare("SELECT ID,person FROM $wpdb->prefix.'tmu_movies_crew' WHERE movie = %d", $post_id));
		$crew_array = array();
		if (count($existing_crew) !== $new_crew) {
			foreach ($existing_crew as $row) {
			    $crew_array[$row->ID] = $row->person;
			}
		}

		foreach ($credits['crew'] as $crew) {
			insert_movie_crew($post_id, $crew, $year);
			if ($crew_array && ($key = array_search($crew['person'], $crew_array)) !== false) { unset($crew_array[$key]); }
		}

		if ($crew_array) {
			foreach ($crew_array as $key => $value) {
				$where = array('ID' => $key);
				$wpdb->delete($wpdb->prefix.'tmu_movies_crew', $where);
			}
		}
	}
}

function insert_movie_cast($post_id, $cast, $year) {
  if ($cast['person']) {
  	global $wpdb;
	$table_name = $wpdb->prefix . 'tmu_movies_cast';

	$existing_record = $wpdb->get_row($wpdb->prepare("SELECT `ID`,`job` FROM {$table_name} WHERE movie = %d AND person = %d", $post_id, $cast['person']));

	$cast['acting_job'] = $cast['acting_job'] ? stripslashes(htmlspecialchars_decode(html_entity_decode($cast['acting_job']))) : '';

	if ($existing_record) {
		$existing_record->job = $existing_record->job ? stripslashes(htmlspecialchars_decode(html_entity_decode($existing_record->job))) : '';
		$wpdb->replace( $table_name,  [
		'ID' => $existing_record->ID,
		'movie' => $post_id,
		'person' => $cast['person'],
		'job' => (($cast['acting_job'] === $existing_record->job || !isset($existing_record->job) || !$existing_record->job) ? $cast['acting_job'] : (str_contains($existing_record->job, $cast['acting_job']) ? $existing_record->job : $existing_record->job.', '.$cast['acting_job'])),
	    'release_year' => $year
	  ], ['%d', '%d', '%d', '%s', '%d']);
	} else {
		$wpdb->replace( $table_name,  [
		'movie' => $post_id,
		'person' => $cast['person'],
		'job' => $cast['acting_job'],
	    'release_year' => $year
	  ], ['%d', '%d', '%s', '%d']);
	}
  }
}

function insert_movie_crew($post_id, $crew, $year) {
  if ($crew['person']) {
  	global $wpdb;
	$table_name = $wpdb->prefix . 'tmu_movies_crew';
	$dep = clean_job_string($crew['department']);
	$job = isset($crew[$dep . '_job']) ? $crew[$dep . '_job'] : '';
	$existing_record = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$table_name} WHERE movie = %d AND person = %d AND job = %s", $post_id, $crew['person'], $job));

	if ($existing_record) {
		$wpdb->replace( $table_name,  [
			'ID' => $existing_record,
			'movie' => $post_id,
			'person' => $crew['person'],
		    'department' => $crew['department'],
		    'job' => $job,
		    'release_year' => $year
		], ['%d', '%d', '%d', '%s', '%s', '%d']);
	} else {
		$wpdb->replace( $table_name,  [
			'movie' => $post_id,
			'person' => $crew['person'],
		    'department' => $crew['department'],
		    'job' => $job,
		    'release_year' => $year
		], ['%d', '%d', '%s', '%s', '%d']);
	}
  }
}