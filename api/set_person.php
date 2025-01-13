<?php

function add_person($post_id){
	if(!$_POST['tmdb_id']) return;
    $person = person_data($_POST['tmdb_id']); $update = false;

    global $wpdb;
	$table_name = $wpdb->prefix.'tmu_people';

	$old_person = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $table_name WHERE `tmdb_id` = %d AND NOT `ID` = %d", $_POST['tmdb_id'], $post_id));

	$movies_cast_table = $wpdb->prefix.'tmu_movies_cast';
	$movies_crew_table = $wpdb->prefix.'tmu_movies_crew';

	$credits_acting_movie = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $movies_cast_table WHERE `person`=%d ORDER BY ID DESC", $post_id));
	$credits_production_movie = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $movies_crew_table WHERE `person` = %d AND `job` IN ('Director', 'Producer') ORDER BY ID DESC", $post_id));

	$no_movies = $credits_acting_movie+$credits_production_movie;

	if ($old_person) {
		remove_action('save_post', 'tmdb_data');
		wp_delete_post( $post_id );
		// delete_post_images( $post_id );
		// wp_update_post( ['ID' => $post_id, 'post_content' => $old_person] );
		add_action('save_post', 'tmdb_data');
		return;
	}
    
    if ( !has_post_thumbnail($post_id) ) {
    	if($person->profile_path){ set_post_feature_image($post_id, $person->profile_path,  $person->name); } // else { add_post_meta($post_id,'_thumbnail_id',$no_image_id); } 
    }

    if( ! $_POST['post_title'] ) {
        remove_action('save_post', 'tmdb_data');
        wp_update_post( ['ID' => $post_id, 'post_title' => $person->name] );
        $_POST['post_title'] = $person->name;
        add_action('save_post', 'tmdb_data');
    }

    if( ! $_POST['content'] ) {
        remove_action('save_post', 'tmdb_data');
        wp_update_post( ['ID' => $post_id, 'post_content' => $person->biography] );
        add_action('save_post', 'tmdb_data');
    }
	
	if( !isset($_POST['date_of_birth']) || $_POST['date_of_birth']==='' )  { $_POST['date_of_birth'] = $person->birthday; $update=true; }
	if( !isset($_POST['gender']) || ($_POST['gender']==='Male' && $person->gender != 2 && $person->gender != 0 && $person->gender != 3))  { $_POST['gender'] = $person->gender == 1 ? 'Female' : 'Not Specified'; $update=true; }
	if( !isset($_POST['profession']) || $_POST['profession']==='' )  { $_POST['profession'] = $person->known_for_department; $update=true; }
	if( !isset($_POST['birthplace']) || $_POST['birthplace']==='' )  { $_POST['birthplace'] = $person->place_of_birth; $update=true; }
	if( !isset($_POST['dead_on']) || $_POST['dead_on']==='' )  { $_POST['dead_on'] = $person->deathday; $update=true; }
	if( !isset($_POST['social_media_account']) || $_POST['social_media_account']==='' || !isset($_POST['social_media_account'][0]) || !isset($_POST['social_media_account'][0]['platform']) || !$_POST['social_media_account'][0]['platform'] )  { $_POST['social_media_account'] = person_social_accounts($_POST['tmdb_id']); $update=true; }
	if( !isset($_POST['name']) || $_POST['name']==='' )  { $_POST['name'] = $_POST['post_title']; $update=true; }
	if( !isset($_POST['popularity']) || $_POST['popularity']==='' || $_POST['popularity'] == '0' ) { $_POST['popularity'] = $person->popularity; $update=true; }

	if($update) {
		$wpdb->replace( $table_name,  [
			'ID' => $post_id,
			'date_of_birth' => $_POST['date_of_birth'],
			'gender' => $_POST['gender'],
			'nick_name' => $_POST['nick_name'],
			'marital_status' => $_POST['marital_status'],
			'basic' => is_array($_POST['basic']) ? serialize($_POST['basic']) : $_POST['basic'],
			'videos' => isset($_POST['videos']) && is_array($_POST['videos']) ? serialize($_POST['videos']) : '',
			'photos' => is_array($_POST['photos']) ? serialize($_POST['photos']) : $_POST['photos'],
			'profession' => $_POST['profession'],
			'net_worth' => $_POST['net_worth'],
			'tmdb_id' => $_POST['tmdb_id'],
			'birthplace' => $_POST['birthplace'],
			'dead_on' => $_POST['dead_on'],
			'social_media_account' => is_array($_POST['social_media_account']) ? serialize($_POST['social_media_account']) : $_POST['social_media_account'],
			'name' => $_POST['name'],
			'no_movies' => $no_movies,
			'known_for' => isset($_POST['known_for']) && is_array($_POST['known_for']) ? serialize($_POST['known_for']) : '',
			'popularity' => $_POST['popularity']
		], ['%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%f']);
	}
}

function insert_credit($credit){
	if(!$credit) return;

	global $wpdb;
	$table_name = $wpdb->prefix.'tmu_people';

	$old_person = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $table_name WHERE `tmdb_id` = %d", $credit->id));

	if ($old_person) {
		wp_delete_post( $old_person );
		$wpdb->query( "DELETE FROM $table_name  WHERE ID = $old_person" );
		delete_post_images( $old_person );
	}

	$person = isset($credit->id) ? person_data($credit->id) : '';

    $post_id = isset($person->name) ? wp_insert_post([
		'post_title'    => ($person->name),
		'post_status'   => 'publish',
		'post_type'     => 'people'
	]) : '';

	if(!$post_id) return;

	wp_update_post( array( 'ID' => $post_id, 'post_name' => $post_id.'-'.sanitize_title($person->name) ) );

	if( !has_post_thumbnail($post_id) ) {
		if($person->profile_path){
			set_post_feature_image($post_id, $person->profile_path,  $person->name);
		}
	}

	$wpdb->insert( $table_name,  [
		'ID' => $post_id,
		'date_of_birth' => $person->birthday,
		'gender' => ($person->gender == 1 ? 'Female' : ($person->gender == 2 ? 'Male' : 'Not Specified')),
		'profession' => $person->known_for_department,
		'tmdb_id' => $person->id,
		'birthplace' => $person->place_of_birth,
		'dead_on' => $person->deathday,
		'social_media_account' => person_social_accounts($person->id),
		'name' => $person->name,
		'popularity' => $_POST['popularity']
	], ['%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%f']);

	return $post_id;
}