<?php

function get_seo_title($title){
	global $wpdb;
    $table_name = $wpdb->prefix.'tmu_seo_options';
    $section = ''; $post_id = '';
    $post_type = get_post_type();

    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $segments = explode('/', $uri);
    if(isset($segments[3]) && ($segments[3] == 'seasons' || ($segments[1] === 'drama' && $segments[3] === 'episodes'))):
		if ( $segments[1] === 'drama' ):
			$page = get_page_by_path( $segments[2], OBJECT, 'drama' );
			$title = $wpdb->get_var("SELECT title FROM {$wpdb->prefix}tmu_seo_options WHERE name = 'drama-episodes'");
		else:
			$page = get_page_by_path( $segments[2], OBJECT, 'tv' );
			$title = $wpdb->get_var("SELECT title FROM {$wpdb->prefix}tmu_seo_options WHERE name = 'all-season'");
		endif;
		$title = $title ? str_replace('%title%', $page->post_title, $title) : $page->post_title;
	endif;

	if (is_front_page()) { $title = $wpdb->get_var("SELECT title FROM {$table_name} WHERE name = 'homepage'"); }
	if (is_archive()) {
		$section = 'archive';
		$title = $wpdb->get_var("SELECT title FROM {$table_name} WHERE post_type = '{$post_type}' AND section = '{$section}'");
		$title = $title ? str_replace('%title%', ucfirst($post_type), $title) : ucfirst($post_type);
	} elseif (is_single() || $post_type === 'page') {
		$section = 'single';
		$post_id = get_the_ID();
		$title = $wpdb->get_var("SELECT seo_title FROM {$wpdb->prefix}posts WHERE `ID` = {$post_id}");
		$title = $title ?? $wpdb->get_var("SELECT title FROM {$table_name} WHERE post_type = '{$post_type}' AND section = '{$section}'");
	}

	if (is_tax() || is_tag() || is_category()) {
		$section = 'taxonomy';
		$term = get_queried_object();
		$post_id = $term->term_id;
		$taxonomy = $term->taxonomy;
		$name = $term->name;

		$title = $wpdb->get_var("SELECT title FROM {$table_name} WHERE post_type = '{$taxonomy}' AND section = '{$section}'");
		$title = $title ? str_replace('%title%', $name, $title) : $name;
	}

	if(isset($segments[1]) && (!isset($segments[2]) || !$segments[2])) {
		if(taxonomy_exists($segments[1]) && $segments[1]!='post_tag') {
			$name = ($segments[1]==='by-year' ? 'Year' : ucfirst($segments[1])).'s';
			$title = $wpdb->get_var("SELECT title FROM {$table_name} WHERE name = 'tax-{$segments[1]}'");
			$title = $title ? str_replace('%title%', $name, $title) : $name;
		}
	}


	$title = $title ? replace_tags($title, $section, $post_type, $post_id) : '';
	return $title;
}