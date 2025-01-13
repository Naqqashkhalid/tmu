<?php

function insert_seasons($series_id, $series_title, $seasons, $tmdb_series_id, $season_nu=''){
	$seasons = is_array($seasons) ? $seasons : [$seasons];
	global $wpdb;
	$table_name = $wpdb->prefix.'tmu_tv_series_seasons';
	$seasons_array = array_map(function($season) use ($series_id, $series_title, $tmdb_series_id, $season_nu, $wpdb, $table_name) {
		if ((!$season_nu || ($season_nu == $season->season_number)) && $season->season_number != 0) {
			$season_title = $series_title.' - Season '.$season->season_number;
			$season_id = $wpdb->get_var("SELECT ID FROM $table_name WHERE `tv_series` = $series_id AND `season_no` = $season->season_number");
			
			if (!$season_id) {
				$season_id = wp_insert_post([ 'post_title' => $season_title, 'post_status' => 'publish', 'post_type' => 'season' ]);

				$release_date = $season->air_date ? strtotime($season->air_date) : '';
				$current_time = time();
				$publish_date = $release_date && $release_date < $current_time ? date("Y-m-d H:i:s", $release_date) : '';
				$data = $publish_date ? ['post_date' => $publish_date, 'post_date_gmt' => $publish_date] : [];
				$data['post_name'] = $series_id.'-'.sanitize_title($season_title);
				$data_format = $publish_date ? ['%s', '%s' , '%s'] : ['%s'];

				if($season_id) $wpdb->update($wpdb->prefix.'posts', $data, ['ID' => $season_id], $data_format, ['%d']);
			}

			if (!has_post_thumbnail($season_id)) {
				if($season->poster_path){
					$posterUrl = 'https://media.themoviedb.org/t/p/w600_and_h900_bestv2'.$season->poster_path;
					$attachment_id = upload_images_from_urls($posterUrl, $season_id, $season_title);
					if (!is_wp_error($attachment_id)) add_post_meta($season_id,'_thumbnail_id',$attachment_id);
				}
			}
			
			if ($season_id) {
				$episodes = insert_season_episodes($series_id, $season_title, $season, $season_id, $tmdb_series_id);

				$wpdb->replace( $table_name,  [
					'ID' => $season_id,
					'season_no' => $season->season_number,
					'season_name' => $season->name,
					'tv_series' => $series_id,
					'air_date' => $season->air_date,
					'air_date_timestamp' => $season->air_date ? strtotime($season->air_date) : 0,
					'total_episodes' => $episodes['total'] ?? 0,
					'average_rating' => $episodes['average'] ?? 0
					
				], ['%d', '%d', '%s', '%d', '%s', '%d', '%d', '%f']);
				
				return $season_id;
			}
		}
		
	}, $seasons);

	$all_seasons = $wpdb->get_col("SELECT ID FROM $table_name WHERE `tv_series`=$series_id");
	return $all_seasons ?? [];
}

function add_season($post_id){
	global $wpdb;
	$current_time = time();
	$release_date = isset($_POST['air_date']) && $_POST['air_date'] ? strtotime($_POST['air_date']) : '';

	$publish_date = $release_date && $release_date < $current_time ? date("Y-m-d H:i:s", $release_date) : '';
	if($publish_date) $wpdb->update($wpdb->prefix.'post', [ 'post_date' => $publish_date, 'post_date_gmt' => $publish_date ], ['ID' => $post_id], ['%s', '%s'], ['%d']);
	if($release_date) $wpdb->update($wpdb->prefix.'tmu_tv_series_seasons', [ 'air_date_timestamp' =>  $release_date ], ['ID' => $post_id], ['%s'], ['%d']);

	return $post_id;
}