<?php

function process_cast($table, $post_id, $cast, $year){
	global $wpdb;
	$table_name = $wpdb->prefix.'tmu_'.$table; $table = $table === 'movies' ? 'movie' : $table;
	$person = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM {$wpdb->prefix}tmu_people WHERE tmdb_id = %d", $cast->id) );
	if(!$person) { $person = insert_credit($cast); }
	if($person) {
		// $tv_series_count = $table === 'tv_series' ? ',count' : '';
	  	// $existing_record = $wpdb->get_row($wpdb->prepare("SELECT `ID`,`job`{$tv_series_count} FROM {$table_name}_cast WHERE $table = %d AND person = %d", $post_id, $person ));
	  	
	  	if (!isset($cast->roles)) {
	  		$job = $cast->character ? stripslashes($cast->character) : '';
	  		$wpdb->insert( $table_name . '_cast',  [ $table => $post_id, 'person' => $person, 'job' => $job, 'release_year' => $year ], ['%d', '%d', '%s', '%d']);
	  	} else { 
	  		$job = is_array($cast->roles) ? implode(' | ', array_map(function($role){ return $role->character; }, $cast->roles)) : '';
	  		$wpdb->insert( $table_name . '_cast',  [ $table => $post_id, 'person' => $person, 'job' => $job, 'release_year' => $year, 'count' => $cast->total_episode_count ], ['%d', '%d', '%s', '%d', '%d']);
	  	}

	  	return ['person' => $person, 'department' => 'Acting', 'acting_job' => $job];
	}
}

function process_crew($table, $post_id, $crew, $year){
	global $wpdb;
	$table_name = $wpdb->prefix.'tmu_'.$table; $table = $table === 'movies' ? 'movie' : $table;
	$person = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM {$wpdb->prefix}tmu_people WHERE tmdb_id = %d", $crew->id) );
	if(!$person) { $person = insert_credit($crew); }
	if($person) {
		// $tv_series_count = $table === 'tv_series' ? ',count' : '';
		$job_key = clean_job_string($crew->department) . '_job';
	  	// $existing_record = $wpdb->get_row($wpdb->prepare("SELECT `ID`,`job`{$tv_series_count} FROM {$table_name}_crew WHERE $table = %d AND person = %d", $post_id, $person ));

	  	if (!isset($crew->jobs)) {
	  		$job = $crew->job ? stripslashes($crew->job) : '';
	  		$wpdb->insert( $table_name . '_crew',  [ $table => $post_id, 'person' => $person, 'department' => $crew->department, 'job' => $job, 'release_year' => $year ], ['%d', '%d', '%s', '%s', '%d']);
	  	} else {
	  		$job = is_array($crew->jobs) ? implode(' | ', array_map(function($role){ return $role->job; }, $crew->jobs)) : '';
	  		$wpdb->insert( $table_name . '_crew',  [ $table => $post_id, 'person' => $person, 'department' => $crew->department, 'job' => $job, 'release_year' => $year, 'count' => $crew->total_episode_count ], ['%d', '%d', '%s', '%s', '%d', '%d']);
	  	}

	  	return ['person' => $person, 'department' => $crew->department, $job_key => $job];
	}
}


function process_custom_cast($table, $post_id, $cast, $year){
	if($cast['person']) {
		global $wpdb; $table_name = $wpdb->prefix.'tmu_'.$table; $table = $table === 'movies' ? 'movie' : $table;
		$existing_record = $wpdb->get_row($wpdb->prepare("SELECT `ID`,`job` FROM {$table_name}_cast WHERE $table = %d AND person = %d", $post_id, $cast['person'] ));
		if ($existing_record) {
			if ($existing_record->job !== $cast['acting_job'] || !str_contains($cast['acting_job'], $existing_record->job)) {
				$wpdb->update($table_name.'_cast', [ 'job' => $cast['acting_job'].', '.$existing_record->job, 'release_year' => $year ], ['ID' => $existing_record->ID], [ '%s', '%d' ], ['%d']);
			}
			if (isset($existing_record->release_year) && $existing_record->release_year !== $year) {
				$wpdb->update($table_name.'_cast', [ 'release_year' => $year ], ['ID' => $existing_record->ID], [ '%d' ], ['%d']);
			}
		}
		if (!$existing_record) $wpdb->insert( $table_name . '_cast',  [ $table => $post_id, 'person' => $cast['person'], 'job' => $cast['acting_job'], 'release_year' => $year ], ['%d', '%d', '%s', '%d']);
	}
}

function process_custom_crew($table, $post_id, $crew, $year){
	if($crew['person']) {
		global $wpdb; $table_name = $wpdb->prefix.'tmu_'.$table; $table = $table === 'movies' ? 'movie' : $table;
		$dep = clean_job_string($crew['department']);
		$job = isset($crew[$dep . '_job']) ? $crew[$dep . '_job'] : '';
		if ($job) {
			$existing_record = $wpdb->get_var($wpdb->prepare("SELECT `ID` FROM {$table_name}_crew WHERE $table = %d AND person = %d AND job = %s", $post_id, $crew['person'], $job ));
			// if ($existing_record) $wpdb->update($table_name.'_crew', [ 'department' => $crew['department'], 'job' => $job, 'release_year' => $year ], ['ID' => $existing_record->ID], [ '%s', '%s', '%d' ], ['%d']);
	    	if (!$existing_record) $wpdb->insert( $table_name . '_crew',  [ $table => $post_id, 'person' => $crew['person'], 'department' => $crew['department'], 'job' => $job, 'release_year' => $year ], ['%d', '%d', '%s', '%s', '%d']);
		}
	}
}