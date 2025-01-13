<?php

function popular_dramas(){
	global $wpdb;
	$table_name = $wpdb->prefix.'tmu_dramas';
	$current_timestamp = time() + (148 * 60 * 60);
	$results = $wpdb->get_col("SELECT $table_name.`ID` FROM $table_name JOIN {$wpdb->prefix}posts AS posts ON ({$table_name}.ID = posts.ID) WHERE release_timestamp<$current_timestamp AND `finished`='0' AND posts.post_status = 'publish' ORDER BY `popularity` DESC LIMIT 30");
	if ($results) {
		$data = '';
		foreach ($results as $result) $data .= popular_template($result);
		return $data;
	}
}

function popular_tv_series(){
	global $wpdb;
	$current_timestamp = time() + (148 * 60 * 60);
	$series_table = $wpdb->prefix.'tmu_tv_series';
	$season_table = $wpdb->prefix.'tmu_tv_series_seasons';
	$ep_table = $wpdb->prefix.'tmu_tv_series_episodes';
	$results = $wpdb->get_col("SELECT tv.ID FROM $series_table AS tv INNER JOIN $season_table as season ON tv.ID=season.tv_series AND tv.last_season=season.season_no INNER JOIN $ep_table as ep ON season.ID=ep.season_id AND ep.episode_no=(SELECT MAX(episode_no) FROM $ep_table AS ep2 WHERE ep2.season_id = ep.season_id) JOIN {$wpdb->prefix}posts AS posts ON (tv.ID = posts.ID) WHERE tv.finished='0' AND season.air_date_timestamp<$current_timestamp AND (ep.air_date_timestamp>$current_timestamp OR ep.episode_type!='finale') AND posts.post_status = 'publish' ORDER BY tv.popularity DESC LIMIT 30");
	if ($results) {
		$data = '';
		foreach ($results as $result) $data .= popular_template($result);
		return $data;
	}
}

function popular_movies(){
	global $wpdb;
	$table_name = $wpdb->prefix.'tmu_movies';
	$current_timestamp = time() + (148 * 60 * 60);
	$results = $wpdb->get_col("SELECT $table_name.`ID` FROM $table_name JOIN {$wpdb->prefix}posts AS posts ON ({$table_name}.ID = posts.ID) WHERE release_timestamp<$current_timestamp AND posts.post_status = 'publish' ORDER BY `popularity` DESC LIMIT 30");
	if ($results) {
		$data = '';
		foreach ($results as $result) $data .= popular_template($result);
		return $data;
	}
}

function popular_tv_movies(){
	global $wpdb;
	$table_name = $wpdb->prefix.'tmu_movies';
	$current_timestamp = time() + (148 * 60 * 60);
	$results1 = $wpdb->get_col("SELECT $table_name.`ID` FROM $table_name JOIN {$wpdb->prefix}posts AS posts ON ({$table_name}.ID = posts.ID) WHERE release_timestamp<$current_timestamp AND posts.post_status = 'publish' ORDER BY `popularity` DESC LIMIT 30");

	$series_table = $wpdb->prefix.'tmu_tv_series';
	$season_table = $wpdb->prefix.'tmu_tv_series_seasons';
	$ep_table = $wpdb->prefix.'tmu_tv_series_episodes';
	$results = $wpdb->get_col("SELECT tv.ID FROM $series_table AS tv JOIN {$wpdb->prefix}posts AS posts ON (tv.ID = posts.ID) INNER JOIN $season_table as season ON tv.ID=season.tv_series AND tv.last_season=season.season_no INNER JOIN $ep_table as ep ON season.ID=ep.season_id AND ep.episode_no=(SELECT MAX(episode_no) FROM $ep_table AS ep2 WHERE ep2.season_id = ep.season_id) WHERE tv.finished='0' AND season.air_date_timestamp<$current_timestamp AND ep.episode_type!='finale' AND posts.post_status = 'publish' ORDER BY tv.popularity DESC LIMIT 30");
	$data = '';
	if ($results1) foreach ($results1 as $result) $data .= popular_template($result);
	if ($results2) foreach ($results2 as $result) $data .= popular_template($result);
	return $data;
}

function popular_template($result){
	$permalink = get_permalink($result);
	$title = get_the_title($result);
	return '<a class="upcoming-movies-box" href="'.get_permalink($result).'">
			<div class="upcoming-movies-poster">
				<img '.(has_post_thumbnail($result) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url($result, 'full').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ).' alt="'.$title.'" width="100%" height="100%">
			</div>
			<div class="upcoming-movies-details" href="'.$permalink.'">
				<h3>'.$title.'</h3>
			</div>
		</a>';
}