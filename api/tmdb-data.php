<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/api-authorization.php';

function set_post_feature_image($post_id, $thumb_id, $title=''){
	if ($thumb_id) {
		$posterUrl = 'https://image.tmdb.org/t/p/w600_and_h900_bestv2'.$thumb_id;
		$attachment_id = upload_images_from_urls($posterUrl, $post_id, $title);
		if (!is_wp_error($attachment_id)) {
			add_post_meta($post_id,'_thumbnail_id',$attachment_id);
		}
	}
}

function productions($companies){
	return implode(', ', array_map(function ($company) {
        return $company->name;
    }, $companies));
}

function videos_post_id_update($videos, $post_id){
	global $wpdb;
	$table_name = $wpdb->prefix.'tmu_videos';
	foreach ($videos as $video_id) {
		$video = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} WHERE `ID` = %d", $video_id));
		if ($video) {
			$wpdb->replace( $table_name,  [ 'ID' => $video->ID, 'video_data' => $video->video_data, 'post_id' => $post_id ], ['%d', '%s', '%d']);
		}
	}
}