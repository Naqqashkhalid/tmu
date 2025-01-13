<?php

function update_released_movies(){
	global $wpdb;
	$table_name = $wpdb->prefix.'tmu_movies';
	if ($table_name) {
		$released = $wpdb->get_results("SELECT `ID`,`tmdb_id`,`budget`,`revenue`,`release_date` FROM $table_name WHERE `release_timestamp`<unix_timestamp(DATE_ADD(NOW(),interval 3 hour))");
		if ($released) {
			foreach ($released as $result) {
				$movie = $result->tmdb_id ? movie_data($result->tmdb_id) : '';

				if ($movie) {

					$tags = movie_tags($result->tmdb_id);
	        		if($tags) wp_set_post_terms( $result->ID, $tags, 'keyword', true );

					if ($movie->budget && ($movie->budget != $result->budget)) {
						$wpdb->update($table_name, [ 'budget' => $movie->budget], ['ID' => $result->ID], ['%s'], ['%d']);
					}

					if ($movie->vote_average || $movie->vote_count) {
						$wpdb->update($table_name, [ 'average_rating' => $movie->vote_average, 'vote_count' => $movie->vote_count ], ['ID' => $result->ID], ['%f', '%f'], ['%d']);
					}

					if ($movie->revenue && ($movie->revenue != $result->revenue)) {
						$wpdb->update($table_name, [ 'revenue' => $movie->revenue], ['ID' => $result->ID], ['%s'], ['%d']);
					}
				}
			}
		}
	}
}

function update_upcoming_movies(){
	global $wpdb; $table_name = $wpdb->prefix.'tmu_movies';
	$results = $wpdb->get_results("SELECT * FROM $table_name WHERE `release_timestamp`>unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) OR `release_date`=''");
	if ($results) {
		foreach ($results as $result) {
			$movie = $result->tmdb_id ? movie_data($result->tmdb_id) : '';

			if ($movie) {
				$tags = movie_tags($result->tmdb_id);
	        	if($tags) wp_set_post_terms( $result->ID, $tags, 'keyword', true );
				
				$theatr_release = movie_theater_release($result->tmdb_id);
				$timestamp = $theatr_release ? strtotime($theatr_release) : ($movie && $movie->release_date ? strtotime($movie->release_date) : ($timestamp ? $timestamp : NULL));
	    		$release_date = $timestamp ? date('Y-m-d', $timestamp) : NULL;
				
				if ( !has_post_thumbnail($result->ID) ) { set_post_feature_image($result->ID, $movie->poster_path, $movie->title); }

				if ($movie->release_date && ($release_date != $result->release_date)) {
					$wpdb->update($table_name, [ 'release_date' => $release_date, 'release_timestamp' => $timestamp ], ['ID' => $result->ID], ['%s', '%d'], ['%d']);
				}

				if ($movie->vote_average || $movie->vote_count) {
					$wpdb->update($table_name, [ 'average_rating' => $movie->vote_average, 'vote_count' => $movie->vote_count ], ['ID' => $result->ID], ['%f', '%f'], ['%d']);
				}

				if (!$result->star_cast) {
					$star_cast = insert_movie_star_casts($result->tmdb_id);
					$wpdb->update($table_name, [ 'star_cast' => is_array($star_cast) ? serialize($star_cast) : $star_cast ], ['ID' => $result->ID], ['%s'], ['%d']);
				}
			}
		}
	}
}

function total_upcoming_movies(){
	global $wpdb; $table_name = $wpdb->prefix.'tmu_movies';
	$total = $wpdb->get_var("SELECT COUNT(*) AS total_count FROM $table_name WHERE `release_timestamp`>unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) OR `release_date`=''");

	echo 'Total Upcoming Movies: '.$total.'<br>';

	$table_name = $wpdb->prefix.'tmu_tv_series';
	$total = $wpdb->get_results("SELECT * FROM $table_name WHERE `finished`='0'");
	$total = count($total);
	echo '<br>Total Unfinished Tv Series: '.$total;
}

function update_upcoming_movies_credits($page){
	$page_no = (int)$page;
	$offset = $page_no>1 ? ($page_no-1)*10 : 0;
	global $wpdb; $table_name = $wpdb->prefix.'tmu_movies';
	// $results = $wpdb->get_results("SELECT * FROM wp_movies JOIN (SELECT wp_movies_cast.movie FROM wp_movies_cast WHERE job IS NULL GROUP BY wp_movies_cast.movie) AS movie ON (wp_movies.ID = movie) ORDER BY wp_movies.ID LIMIT 10 OFFSET $offset");
	$results = $wpdb->get_results("SELECT * FROM $table_name WHERE `release_timestamp`>unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) OR `release_date`='' LIMIT 10 OFFSET $offset");
	// $results = $wpdb->get_results("SELECT * FROM $table_name LIMIT 10 OFFSET $offset");

	if ($results) {
		foreach ($results as $result) {
			$movie = $result->tmdb_id ? movie_data($result->tmdb_id) : '';
			if ($movie) {
				$timestamp = strtotime($movie->release_date);
				// $year = $movie->release_date ? date('Y', $timestamp) : NULL;

				$credits = insert_movie_credits($result->ID, $result->tmdb_id, $timestamp);
				// update_movie_credits($result->ID, $credits, $timestamp);
				$wpdb->update($table_name, [ 'credits' => is_array($credits) ? serialize($credits) : $credits ], ['ID' => $result->ID], ['%s'], ['%d']);
			}
		}
	}
}

function update_upcoming_movies_images(){
	global $wpdb; $table_name = $wpdb->prefix.'tmu_movies';
	$results = $wpdb->get_results("SELECT * FROM $table_name WHERE `release_timestamp`>unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) OR `release_date`=''");
	if ($results) {
		foreach ($results as $result) {
			$movie = $result->tmdb_id ? movie_data($result->tmdb_id) : '';
			if ($movie) {
				$result->images = $result->images ? unserialize($result->images) : '';
				$images = process_images_data($result->images, $result->tmdb_id, $result->ID);
		    	$wpdb->update($table_name, [ 'images' => $images ], ['ID' => $result->ID], ['%s'], ['%d']);
			}
		}
	}
}

function update_upcoming_movies_videos(){
	global $wpdb; $table_name = $wpdb->prefix.'tmu_movies';
	$results = $wpdb->get_results("SELECT ID,tmdb_id,videos FROM $table_name WHERE `release_timestamp`>unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) OR `release_date`=''");
	if ($results) {
		foreach ($results as $result) {
			$result->videos = $result->videos ? unserialize($result->videos) : $result->videos;
	    	$videos = process_videos_data($result->videos, $result->tmdb_id, $result->ID);
	    	$wpdb->update($table_name, [ 'videos' => $videos ], ['ID' => $result->ID], ['%s'], ['%d']);
		}
	}
}

// function update_people_name(){
// 	global $wpdb; $table_name = $wpdb->prefix.'people';
// 	$results = $wpdb->get_col("SELECT ID FROM $table_name");
// 	if ($results) {
// 		foreach ($results as $result) {
// 			$name = get_the_title($result);
// 			if ($name) {
// 		    	$wpdb->update($table_name, [ 'name' => $name ], ['ID' => $result], ['%s'], ['%d']);
// 			}
// 		}
// 	}
// }

function update_people_movies(){
	global $wpdb; $table_name = $wpdb->prefix.'tmu_people';
	$results = $wpdb->get_col("SELECT ID FROM $table_name");

	if ($results) {
		foreach ($results as $result) {
			$movies_cast_table = $wpdb->prefix.'tmu_movies_cast';
			$movies_crew_table = $wpdb->prefix.'tmu_movies_crew';

			$credits_acting_movie = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $movies_cast_table WHERE `person`=%d ORDER BY ID DESC", $result));
			$credits_production_movie = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $movies_crew_table WHERE `person` = %d AND `job` IN ('Director', 'Producer') ORDER BY ID DESC", $result));

			
			// $tv_series_cast_table = $wpdb->prefix.'tmu_tv_series_cast';
			// $tv_series_crew_table = $wpdb->prefix.'tmu_tv_series_crew';
			
			// $credits_acting_tv = $wpdb->get_col($wpdb->prepare("SELECT movie FROM $tv_series_cast_table WHERE `person`=%d ORDER BY `release_year` DESC", $result));
			// $credits_production_tv = $wpdb->get_col($wpdb->prepare("SELECT movie FROM $tv_series_crew_table WHERE `person` = %d ORDER BY `release_year` DESC", $result));
			// $all_movies = array_unique(array_merge($credits_acting_movie, $credits_production_movie));

			// $total_movies = $all_movies ? count($all_movies) : 0;
			$total = $credits_acting_movie+$credits_production_movie;
			$wpdb->update($table_name, [ 'no_movies' => $total ], ['ID' => $result], ['%d'], ['%d']);
			// echo 'Movies: '.$total.'<br>';
		}
	}
}

function update_people_tv_series(){
	global $wpdb; $table_name = $wpdb->prefix.'tmu_people';
	$results = $wpdb->get_col("SELECT ID FROM $table_name");

	if ($results) {
		foreach ($results as $result) {
			$tv_series_cast_table = $wpdb->prefix.'tmu_tv_series_cast';
			$tv_series_crew_table = $wpdb->prefix.'tmu_tv_series_crew';

			$credits_acting_tv = $wpdb->get_col($wpdb->prepare("SELECT tv_series FROM $tv_series_cast_table WHERE `person`=%d", $result));
			$credits_production_tv = $wpdb->get_col($wpdb->prepare("SELECT tv_series FROM $tv_series_crew_table WHERE `person` = %d", $result));
			$all_tv_series = array_unique(array_merge($credits_acting_tv, $credits_production_tv));

			$total_tv_series = $all_tv_series ? count($all_tv_series) : 0;
			$wpdb->update($table_name, [ 'no_tv_series' => $total_tv_series ], ['ID' => $result], ['%d'], ['%d']);
			echo 'TV Series: '.$total_tv_series.'<br>';
		}
	}
}

function update_tv_series(){
	// $page_no = (int)$page;
	// $offset = $page_no>1 ? ($page_no-1)*10 : 0;
	global $wpdb;
	$table_name = $wpdb->prefix.'tmu_tv_series';
	$tv_series = $wpdb->get_results("SELECT * FROM $table_name");
	// $tv_series = $wpdb->get_results("SELECT * FROM $table_name WHERE `finished`='0' LIMIT 25 OFFSET $offset");
	if ($tv_series) {
		foreach ($tv_series as $result) {
			$tv = $result->tmdb_id ? tv_data($result->tmdb_id) : '';
			if($tv) {
				$timestamp = $result->release_date ? strtotime($result->release_date) : 0;
				$year = $tv->first_air_date ? date('Y', $timestamp) : NULL;

				if ( !has_post_thumbnail($result->ID) ) { set_post_feature_image($result->ID, $tv->poster_path, $tv->name); }

				if( !isset($result->original_title) || $result->original_title==='' )  { $result->original_title = $tv->original_name; }
			    if( !isset($result->release_date) || $result->release_date==='' ) { $result->release_date = $tv->first_air_date; }
			    if( !isset($result->runtime) || $result->runtime=='' || !$result->runtime) { $result->runtime = isset($tv->episode_run_time[0]) ? $tv->episode_run_time[0] : (isset($tv->episode_run_time) ? $tv->episode_run_time : ''); }
			    if( !isset($result->production_house) || $result->production_house==='' ) { $result->production_house = productions($tv->production_companies); }
			    if( !isset($result->certification) || $result->certification==='' || $result->certification==='U/A' ) { $result->certification = tv_certification($result->tmdb_id); }
			    if( !isset($result->finished) || $result->finished==='' || (($tv->status=='Ended' || $tv->status=='Canceled') && $result->finished==0)) { $result->finished = $tv->status=='Ended' || $tv->status=='Canceled' ? 1 : 0; }

			    $last_episode = (isset($tv->next_episode_to_air) && $tv->next_episode_to_air) ? $tv->next_episode_to_air : $tv->last_episode_to_air;
			    if ($last_episode) {
			    	$result->last_season = $last_episode->season_number;
			    	$result->last_episode = $last_episode->episode_number;
			    }

			    if (!has_term('', 'network', $result->ID)) {
			    	$channels = array_map(function($network) { return $network->name; }, $tv->networks);
			    	if($channels) wp_set_post_terms( $result->ID, $channels, 'network', true );
			    }


			    if( !has_term('', 'genre', $result->ID) ) {
			        $genres = array_map(function($genre) { return $genre->name; }, $tv->genres);
			        wp_set_post_terms( $result->ID, $genres, 'genre', true );
			    };
			    // if( !has_term('', 'keyword', $result->ID) ) {
			        $tags = tv_tags($result->tmdb_id);
			        wp_set_post_terms( $result->ID, $tags, 'keyword', true );
			    // };
			    if( !has_term('', 'by-year', $result->ID) ) {
			        if($tv->first_air_date) wp_set_post_terms( $result->ID, [$year], 'by-year', true );
			    };
			    if( !has_term('', 'country', $result->ID) ) {
			        $countries = array_map(function($country) { return $country->name; }, $tv->production_countries);
			        wp_set_post_terms( $result->ID, $countries, 'country', true );
			    };
			    if( !has_term('', 'language', $result->ID) ) {
			        $languages = array_map(function($language) { return $language->english_name; }, $tv->spoken_languages);
			        wp_set_post_terms( $result->ID, $languages, 'language', true );
			    };
				
				if(!isset($result->star_cast[0]['person']) || !$result->star_cast[0] || !$result->star_cast[0]['person']) {
			        $result->star_cast = insert_tv_star_casts($result->tmdb_id);
				}

				$data = [
					'ID' => $result->ID,
					'release_date' => $result->release_date,
					'release_timestamp' => $timestamp,
					'star_cast' => is_array($result->star_cast) ? serialize($result->star_cast) : $result->star_cast,
					'original_title' => stripslashes($result->original_title),
					'certification' => $result->certification,
					'runtime' => is_array($result->runtime) ? (isset($result->runtime[0]) ? $result->runtime[0] : '') : $result->runtime,
					'tagline' => stripslashes($tv->tagline),
					'production_house' => stripslashes($result->production_house),
					'last_season' => $result->last_season,
					'last_episode' => $result->last_episode,
				    'average_rating' => $tv->vote_average,
				    'vote_count' => $tv->vote_count
				];
				$format = ['%d', '%s', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%d', '%d', '%d'];
			    $wpdb->update($table_name, $data, ['ID' => $result->ID], $format, ['%d']);
			}
			// update_tv_series_credits($result->ID, $result->credits, $timestamp);
		}
	}
}


function update_tv_series_credits_cron($page){
	$page_no = (int)$page;
	$offset = $page_no>1 ? ($page_no-1)*25 : 0;
	global $wpdb;
	$table_name = $wpdb->prefix.'tmu_tv_series';
	$tv_series = $wpdb->get_results("SELECT * FROM $table_name LIMIT 25 OFFSET $offset");
	// $tv_series = $wpdb->get_results("SELECT * FROM $table_name WHERE `finished`='0' LIMIT 25 OFFSET $offset");
	if ($tv_series) {
		foreach ($tv_series as $result) {
			$result->credits = insert_tv_credits($result->ID, $result->tmdb_id, $timestamp);
			$wpdb->update($table_name, [ 'credits' => is_array($result->credits) ? serialize($result->credits) : $result->credits ], ['ID' => $result->ID], ['%s'], ['%d']);		
		}
	}
}

function update_tv_series_season_cron($page){
	$page_no = (int)$page;
	$offset = $page_no>1 ? ($page_no-1)*50 : 0;
	global $wpdb;
	$table_name = $wpdb->prefix.'tmu_tv_series';
	$tv_series = $wpdb->get_results("SELECT * FROM $table_name LIMIT 50 OFFSET $offset");
	// $tv_series = $wpdb->get_results("SELECT * FROM $table_name WHERE `finished`='0' LIMIT 25 OFFSET $offset");
	if ($tv_series) {
		foreach ($tv_series as $result) {
			$tv = $result->tmdb_id ? tv_data($result->tmdb_id) : '';
			if($tv){
				$title = get_the_title($result->ID);
				$result->seasons = insert_seasons($result->ID, $tv->name, $tv->seasons, $result->tmdb_id, $result->last_season);
				$wpdb->update($table_name, [ 'credits' => is_array($result->seasons) ? serialize($result->seasons) : $result->seasons ], ['ID' => $result->ID], ['%s'], ['%d']);
			}
		}
	}
}

function update_tv_series_media_cron($page){
	$page_no = (int)$page;
	$offset = $page_no>1 ? ($page_no-1)*50 : 0;
	global $wpdb;
	$table_name = $wpdb->prefix.'tmu_tv_series';
	$tv_series = $wpdb->get_results("SELECT * FROM $table_name LIMIT 50 OFFSET $offset");
	// $tv_series = $wpdb->get_results("SELECT * FROM $table_name WHERE `finished`='0' LIMIT 25 OFFSET $offset");
	if ($tv_series) {
		foreach ($tv_series as $result) {
			if ($result->images) $result->images = unserialize($result->images); 
			if( !$result->images) {
				$title = get_the_title($result->ID);
		    	$result->images = upload_images_from_urls(tv_images($result->tmdb_id), $result->ID, $title);
		    	$wpdb->update($table_name, [ 'images' => is_array($result->images) ? serialize($result->images) : $result->images ], ['ID' => $result->ID], ['%s'], ['%d']);
			}

			if ($result->videos) $result->videos = unserialize($result->videos);
			if(!$result->videos) {
		    	$result->videos = tv_videos($result->tmdb_id, $result->ID);
		    	$wpdb->update($table_name, [ 'videos' => is_array($result->videos) ? serialize($result->videos) : $result->videos], ['ID' => $result->ID], [ '%s' ], ['%d']);
			}
		}
	}
}


function process_images_data($old_images, $tmdb_id, $post_id){
	if (isset($old_images) && $old_images) {
		return $images = $old_images;
	} else {
		$attachments = get_posts( array(
            'post_type' => 'attachment',
            'posts_per_page' => -1,
            'post_parent' => $post_id,
            'exclude'     => get_post_thumbnail_id($post_id),
	        'fields' => 'ids'
        ) );

        if ($attachments) {
        	$images = $attachments;
        } else { $images = upload_images_from_urls(movie_images($tmdb_id), $post_id, $movie->title); }
	}

	return is_array($images) ? serialize($images) : $images;
}

function process_videos_data($old_videos, $tmdb_id, $post_id){
	if (isset($old_videos) && $old_videos) {
		$videos = $old_videos;
	} else {
		global $wpdb;
		$videos = $wpdb->get_col("SELECT `ID` FROM {$wpdb->prefix}tmu_videos WHERE post_id = $post_id");
		if (!$videos) { $videos = movie_videos($tmdb_id, $post_id); }
	}
	return is_array($videos) ? serialize($videos) : $videos;
}

function update_credits_year(){
	global $wpdb; $table_name = $wpdb->prefix.'tmu_movies_crew';
	$results = $wpdb->get_results("SELECT ID,movie,release_year FROM $table_name ORDER BY `release_year` ASC");
	if ($results) {
		foreach ($results as $result) {
			// echo '<br>existed year: '.$result->release_year.'<br>';
			// if (!$result->release_year) {
				// $year = wp_get_object_terms( $result->movie, 'by-year', array('fields' => 'names') );
				// $year = isset($year[0]) ? $year[0] : '';
				global $wpdb;
				$year_table = $wpdb->prefix . 'tmu_movies';
				$year = $wpdb->get_var("SELECT release_timestamp FROM $year_table WHERE ID = $result->movie");
				if ($year) {
			    	$wpdb->update($table_name, [ 'release_year' => $year ], ['ID' => $result->ID], ['%s'], ['%d']);
			    	// echo 'Year: '.$year.'   ';
				}
			// }
		}
	}
}