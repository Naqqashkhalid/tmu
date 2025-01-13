<?php

add_action( 'wp_ajax_seo_options', 'seo_options_handler' );
add_action( 'wp_ajax_nopriv_seo_options', 'seo_options_handler' );

function seo_options_handler(){
	global $wpdb;
	$table_name = $wpdb->prefix.'tmu_seo_options';

	$post_type = $_POST[ 'btn_type' ];
	$section = $_POST[ 'section' ];
	$selector = $_POST[ 'selector' ];
	$title = $_POST[ 'title' ];
	$description = is_array($_POST[ 'description' ]) ? serialize($_POST[ 'description' ]) : $_POST[ 'description' ];
	$keywords = $_POST[ 'keywords' ];
	$robots = $_POST[ 'robots' ];
	$email = $_POST[ 'email' ];
	if($selector === 'homepage' && $email) update_option( 'tmu_email', $email );

	$option_id = $wpdb->get_var("SELECT ID FROM {$table_name} WHERE name = '{$selector}' AND post_type = '{$post_type}' AND section = '{$section}'");

	if ($option_id) {
		$wpdb->update($table_name, [ 'title' => $title, 'description' => $description, 'keywords' => $keywords, 'robots' => $robots ], ['ID' => $option_id], ['%s', '%s', '%s', '%s'], ['%d']);
	} else {
		echo $wpdb->insert($table_name, [ 'name' => $selector, 'title' => $title, 'description' => $description, 'keywords' => $keywords, 'robots' => $robots, 'post_type' => $post_type, 'section' => $section ], ['%s', '%s', '%s', '%s', '%s', '%s', '%s']);
	}



	die;
}