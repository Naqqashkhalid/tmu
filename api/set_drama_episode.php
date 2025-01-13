<?php

function insert_drama_episodes($drama_id, $season_no, $tmdb_season_id){
	$drama_title = htmlspecialchars(get_the_title($drama_id));
	global $wpdb;
	$table_name = $wpdb->prefix.'tmu_dramas_episodes';
	$season_data = season_data($tmdb_season_id, $season_no);
	$episodes = $season_data ? $season_data->episodes : [];

	foreach ($episodes as $episode) {
		$episode_title = $drama_title.' Episode '.$episode->episode_number; $episode_id = false;
		$old_episode = $wpdb->get_var("SELECT ID FROM $table_name WHERE `episode_no`=$episode->episode_number AND `dramas`=$drama_id");
		if ($old_episode && $episode_title === get_the_title($old_episode)) $episode_id = $old_episode;
		if (!$episode_id) {
			$episode_posts = delete_episodes_with_title($episode_title);
			$episode_url = $drama_id.'-'.sanitize_title($episode_title);
			$episode_id = wp_insert_post([ 'post_title' => $episode_title, 'post_status' => 'publish', 'post_type' => 'drama-episode' ]);

			$release_date = $episode->air_date ? strtotime($episode->air_date) : '';
			$current_time = time();
			$publish_date = $release_date && $release_date < $current_time ? date("Y-m-d H:i:s", $release_date) : '';
			$data = $publish_date ? ['post_date' => $publish_date, 'post_date_gmt' => $publish_date] : [];
			$data['post_name'] = $episode_url;
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

		$credits = insert_episode_credits($tmdb_season_id, $season_no, $episode->episode_number);

		$wpdb->replace( $table_name,  [
			'ID' => $episode_id,
			'episode_title' => $episode->name,
			'air_date' => $episode->air_date,
			'episode_type' => $episode->episode_type,
			'runtime' => $episode->runtime,
			'overview' => $episode->overview,
			'credits' => $credits,
			'episode_no' => $episode->episode_number,
			'dramas' => $drama_id,
			'average_rating' => $episode->vote_average,
			'vote_count' => $episode->vote_count

		], ['%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%d']);

	}
}

function add_drama_episode($post_id){
	$current_time = time();
	$release_date = isset($_POST['air_date']) && $_POST['air_date'] ? strtotime($_POST['air_date']) : '';

	$publish_date = $release_date && $release_date < $current_time ? date("Y-m-d H:i:s", $release_date) : '';

	if($publish_date) {
		remove_action('save_post', 'tmdb_data');
		wp_update_post( ['ID' => $post_id, 'post_date' => $publish_date, 'post_date_gmt' => get_gmt_from_date( $publish_date )] );
		add_action('save_post', 'tmdb_data');
	}

	return $post_id;
}