<?php

function insert_season_episodes($series_id, $season_title, $season, $season_id, $tmdb_series_id){
	$season_title = htmlspecialchars($season_title);
	global $wpdb;
	$table_name = $wpdb->prefix.'tmu_tv_series_episodes';
	$episodes_exist = $wpdb->get_col("SELECT ID FROM $table_name WHERE `season_id`=$season_id");
	
	$season_title2 = str_replace("’","",$season_title);$season_title2 = str_replace("'","",$season_title);
	// if($episodes_exist && $episodes_exist['count'] == $season->episode_count) return; // return if all episodes already exists
	
	$season_no = $season->season_number;
	$season_data = season_data($tmdb_series_id, $season_no);
	$episodes = $season_data ? $season_data->episodes : [];

	$total_episodes = 0;
	$total_average_rating = 0;
	foreach ($episodes as $episode) {
		$episode_title = $season_title2.' Episode '.$episode->episode_number; $episode_id = false;
		$episode_id = $wpdb->get_var("SELECT ID FROM $table_name WHERE `episode_no`=$episode->episode_number AND `season_no`=$season_no AND `tv_series` = $series_id");
		if (!$episode_id) {
			// $episode_posts = delete_episodes_with_title($episode_title);
			$episode_id = wp_insert_post([ 'post_title' => $episode_title, 'post_status' => 'publish', 'post_type' => 'episode' ]);

			$release_date = $episode->air_date ? strtotime($episode->air_date) : '';
			$current_time = time();
			$publish_date = $release_date && $release_date < $current_time ? date("Y-m-d H:i:s", $release_date) : '';
			$data = $publish_date ? ['post_date' => $publish_date, 'post_date_gmt' => $publish_date] : [];
			$data['post_name'] = $series_id.'-'.sanitize_title($episode_title);
			$data_format = $publish_date ? ['%s', '%s' , '%s'] : ['%s'];

			if($episode_id) $wpdb->update($wpdb->prefix.'posts', $data, ['ID' => $episode_id], $data_format, ['%d']);
		}

		if (!has_post_thumbnail($episode_id)) {
			if($episode->still_path){
				$posterUrl = 'https://media.themoviedb.org/t/p/w640_and_h360_bestv2'.$episode->still_path;
				$attachment_id = upload_images_from_urls($posterUrl, $episode_id, $episode_title);
				if (!is_wp_error($attachment_id)) add_post_meta($episode_id,'_thumbnail_id',$attachment_id);
			}
		}

		$credits = insert_episode_credits($tmdb_series_id, $season->season_number, $episode->episode_number);

		$wpdb->replace( $table_name,  [
			'ID' => $episode_id,
			'tv_series' => $series_id,
			'season_no' => $season_no,
			'episode_no' => $episode->episode_number,
			'episode_title' => $episode->name,
			'air_date' => $episode->air_date,
			'air_date_timestamp' => $episode->air_date ? strtotime($episode->air_date) : 0,
			'episode_type' => $episode->episode_type,
			'runtime' => $episode->runtime,
			'overview' => $episode->overview,
			'credits' => $credits,
			'average_rating' => $episode->vote_average,
			'vote_count' => $episode->vote_count,
			'season_id' => $season_id
		], ['%d', '%d', '%d', '%d', '%s', '%s', '%d', '%s', '%d', '%s', '%s', '%d', '%f', '%d']);
		++$total_episodes;
		
		$comments = get_comments(array('post_id' => $episode_id, 'status' => 'approve'));
		$tmdb_rating = ['average' => (float)$episode->vote_average, 'count' => (int)$episode->vote_count];
  		$average_ratings = get_average_ratings($comments, $tmdb_rating);
		$total_average_rating += $tmdb_rating['average'] ?? 0;
	}

	return ['total' => $total_episodes, 'average' => $total_average_rating/$total_episodes];
}


function add_episode($post_id){
	global $wpdb;
	$table_name = $wpdb->prefix.'tmu_tv_series_episodes';
	$season_id = '';
	if( isset($_POST['season_no']) && $_POST['season_no'] && isset($_POST['tv_series']) && $_POST['tv_series'] )  {
		$season_id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}tmu_tv_series_seasons WHERE `tv_series` = {$_POST['tv_series']} AND `season_no` = {$_POST['season_no']}");
	}

	$old_episode = $wpdb->get_var("SELECT ID FROM $table_name WHERE `ID`=$post_id");

	$release_date = isset($_POST['air_date']) && $_POST['air_date'] ? strtotime($_POST['air_date']) : NULL;
	$current_time = time();
	$publish_date = $release_date && $release_date < $current_time ? date("Y-m-d H:i:s", $release_date) : '';
	$data = $publish_date ? ['post_date' => $publish_date, 'post_date_gmt' => $publish_date] : [];
	$data['post_name'] = $_POST['tv_series'].'-'.sanitize_title(get_the_title($post_id));
	$data_format = $publish_date ? ['%s', '%s' , '%s'] : ['%s'];

	$wpdb->update($wpdb->prefix.'posts', $data, ['ID' => $post_id], $data_format, ['%d']);
	
	$wpdb->replace( $table_name,  [
		'ID' => $post_id,
		'tv_series' => isset($_POST['tv_series']) ? $_POST['tv_series'] : '',
		'season_no' => isset($_POST['season_no']) ? $_POST['season_no'] : '',
		'episode_no' => isset($_POST['episode_no']) ? $_POST['episode_no'] : '',
		'episode_title' => $_POST['episode_title'],
		'air_date' => $_POST['air_date'],
		'air_date_timestamp' => $release_date,
		'episode_type' => $_POST['episode_type'],
		'runtime' => $_POST['runtime'],
		'overview' => $_POST['overview'],
		'credits' => is_array($_POST['credits']) ? serialize($_POST['credits']) : $_POST['credits'],
		'season_id' => $season_id
	], ['%d', '%d', '%d', '%d', '%s', '%s', '%d', '%s', '%d', '%s', '%s', '%d']);
}

function insert_episode_credits($tmdb_series_id, $season_no, $episode_no) {
	try {
	  	$client = new \GuzzleHttp\Client();
		$response = $client->request('GET', 'https://api.themoviedb.org/3/tv/'.$tmdb_series_id.'/season/'.$season_no.'/episode/'.$episode_no.'?language=en-US', auth_key());
		$data = json_decode($response->getBody());
		$crew = $data->crew ?? [];
		$guest_stars = $data->guest_stars ?? [];
		global $wpdb;
		$table_name = $wpdb->prefix.'tmu_people';
		$processed_casts = array_map(function($credit) use ($wpdb, $table_name) {
			$post_id = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM $table_name WHERE tmdb_id = %d", $credit->id) );
			
			if(!$post_id){ insert_credit($credit); }
			
			if ($post_id) {
                return ['person' => $post_id, 'department' => 'Acting', 'acting_job' => $credit->character];
            }
		}, $guest_stars);

		$processed_crew = array_map(function($credit) use ($wpdb, $table_name) {
			$post_id = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM $table_name WHERE tmdb_id = %d", $credit->id) );
			
			if(!$post_id){ insert_credit($credit); }
			
			if ($post_id) {
                return ['person' => $post_id, 'department' => $credit->department, clean_job_string($credit->department) . '_job' => $credit->job];
            }
		}, $crew);

        return serialize(['cast' => $processed_casts, 'crew' => $processed_crew ]);
	} catch(Exception $e) {
	  return false;
	}
}