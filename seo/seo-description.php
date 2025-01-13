<?php

function get_seo_description($description='', $post_id=NULL){
	if ($post_id) { global $post; $post = get_post($post_id); setup_postdata( $post ); }
	global $wpdb;
    $table_name = $wpdb->prefix.'tmu_seo_options';
    $section = '';
    $post_type = get_post_type();

    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $segments = explode('/', $uri);
    if(isset($segments[3]) && ($segments[3] == 'seasons' || ($segments[1] === 'drama' && $segments[3] === 'episodes'))):
		if ( $segments[1] === 'drama' ):
			$page = get_page_by_path( $segments[2], OBJECT, 'drama' );
			$description = $wpdb->get_var("SELECT description FROM {$wpdb->prefix}tmu_seo_options WHERE name = 'drama-episodes'");
		else:
			$page = get_page_by_path( $segments[2], OBJECT, 'tv' );
			$description = $wpdb->get_var("SELECT description FROM {$wpdb->prefix}tmu_seo_options WHERE name = 'all-season'");
		endif;
		$description = $description ? str_replace('%title%', $page->post_title, $description) : $page->post_title;
	endif;

	if (is_front_page()) { $description = $wpdb->get_var("SELECT description FROM {$table_name} WHERE name = 'homepage'"); $section = 'archive'; }
	if (is_archive()) {
		$section = 'archive';
		$description = $wpdb->get_var("SELECT description FROM {$table_name} WHERE post_type = '{$post_type}' AND section = '{$section}'");
		$description = $description ? str_replace('%title%', ucfirst($post_type), $description) : ucfirst($post_type);
	} elseif (is_single() || $post_type === 'page') {
		$section = 'single';
		$post_id = get_the_ID();
		$description = $wpdb->get_var("SELECT seo_description FROM {$wpdb->prefix}posts WHERE `ID` = {$post_id}");
		if(!$description){
			$description = $wpdb->get_var("SELECT description FROM {$table_name} WHERE post_type = '{$post_type}' AND section = '{$section}'");
			if ($post_type === 'movie' || $post_type === 'tv' || $post_type === 'drama') {
				$description = $description ? unserialize($description) : '';
				$db_sec = $wpdb->prefix.($post_type === 'movie' ? 'tmu_movies' : ($post_type === 'tv' ? 'tmu_tv_series' : ($post_type === 'drama' ? 'tmu_dramas' : '')));
				$release_date = $wpdb->get_var("SELECT release_timestamp FROM {$db_sec} WHERE ID = $post_id");
				if (time() > $release_date) {
					$description = isset($description['released']) ? $description['released'] : '';
				} else {
					$description = isset($description['upcoming']) ? $description['upcoming'] : '';
				}
			}
		}
		if ($post_type === 'people') {
			$content = get_the_content( null, false, $post_id );
			if($content) $description = stripslashes(trim(preg_replace('/\s+/', ' ', str_replace('"', ' ', wp_trim_words(wp_strip_all_tags($content), 160)))));
		}
	}

	if (is_tax() || is_tag() || is_category()) {
		$section = 'taxonomy';
		$term = get_queried_object();
		$post_id = $term->term_id;
		$taxonomy = get_queried_object()->taxonomy;
		$name = get_queried_object()->name;

		$description = $wpdb->get_var("SELECT description FROM {$table_name} WHERE post_type = '{$taxonomy}' AND section = '{$section}'");
		$description = $description ? str_replace('%title%', $name, $description) : $name;
	}

	if(isset($segments[1]) && (!isset($segments[2]) || !$segments[2])) {
		if(taxonomy_exists($segments[1]) && $segments[1]!='post_tag') {
			$name = ($segments[1]==='by-year' ? 'Year' : ucfirst($segments[1])).'s';
			$description = $wpdb->get_var("SELECT description FROM {$table_name} WHERE name = 'tax-{$segments[1]}'");
			$description = $description ? str_replace('%title%', $name, $description) : $name;
		}
	}


	$description = $description ? replace_tags($description, $section, $post_type, $post_id) : '';

	wp_reset_postdata();

	return $description;
}