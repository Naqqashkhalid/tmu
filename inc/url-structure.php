<?php

add_action('parse_request', 'custom_url_all_seasons');

function custom_url_all_seasons() {
	$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	$segments = explode('/', $uri);

	if(isset($segments[3]) && ($segments[3] == 'seasons')):
		require_once __DIR__ .'/archive-seasons.php';
		season_archive($segments[2]);
		exit;
	endif;

	if(isset($segments[1]) && isset($segments[3]) && $segments[1] === 'drama' && ($segments[3] === 'episodes')):
		require_once __DIR__ .'/archive-drama-episodes.php';
		drama_episodes_archive($segments[2]);
		exit;
	endif;


	if($segments[1] === 'update-tv-series') {
		require_once __DIR__ .'/custom/crons.php';
		update_tv_series();
		exit;
	}

	if($segments[1] === 'update-tv-series-credits' && $segments[2] === 'page' && $segments[3]) {
		require_once __DIR__ .'/custom/crons.php';
		update_tv_series_credits_cron($segments[3]);
		exit;
	}

	if($segments[1] === 'update-tv-series-seasons' && $segments[2] === 'page' && $segments[3]) {
		require_once __DIR__ .'/custom/crons.php';
		update_tv_series_seasons_cron($segments[3]);
		exit;
	}

	if($segments[1] === 'update-tv-series-media' && $segments[2] === 'page' && $segments[3]) {
		require_once __DIR__ .'/custom/crons.php';
		update_tv_series_media_cron($segments[3]);
		exit;
	}

	if($segments[1] === 'update-released-movies') {
		require_once __DIR__ .'/custom/crons.php';
		update_released_movies();
		exit;
	}

	if($segments[1] === 'total-upcoming-movies') {
		require_once __DIR__ .'/custom/crons.php';
		total_upcoming_movies();
		exit;
	}

	if($segments[1] === 'update-upcoming-movies') {
		require_once __DIR__ .'/custom/crons.php';
		update_upcoming_movies();
		exit;
	}

	if($segments[1] === 'update-upcoming-movies-credits' && $segments[2] === 'page' && $segments[3]) {
		require_once __DIR__ .'/custom/crons.php';
		update_upcoming_movies_credits($segments[3]);
		exit;
	}

	if($segments[1] === 'update-upcoming-movies-images') {
		require_once __DIR__ .'/custom/crons.php';
		update_upcoming_movies_images();
		exit;
	}

	if($segments[1] === 'update-upcoming-movies-videos') {
		require_once __DIR__ .'/custom/crons.php';
		update_upcoming_movies_videos();
		exit;
	}

	if($segments[1] === 'update-credits-year') {
		require_once __DIR__ .'/custom/crons.php';
		update_credits_year();
		exit;
	}

	// if($segments[1] === 'update-people-name') {
	// 	require_once __DIR__ .'/custom/crons.php';
	// 	update_people_name();
	// 	exit;
	// }

	if($segments[1] === 'update-people-movies') {
		require_once __DIR__ .'/custom/crons.php';
		update_people_movies();
		exit;
	}

	if($segments[1] === 'update-people-tv-series') {
		require_once __DIR__ .'/custom/crons.php';
		update_people_tv_series();
		exit;
	}

	if(isset($segments[1]) && (!isset($segments[2]) || !$segments[2])) {

		if(taxonomy_exists($segments[1]) && $segments[1]!='post_tag') {
			require_once __DIR__ .'/archive-taxonomy.php';
			archive_taxonomy($segments[1]);
			exit;
		}
	}

	if($segments[1] === 'redirect' && $segments[2]) {
		require_once __DIR__ .'/redirect.php';
		redirect_url($uri);
		exit;
	}
}

// add_filter( 'term_link', 'custom_taxonomy_season_url', 10, 3 );

// function custom_taxonomy_season_url( $permalink, $term, $taxonomy ) {
//   $new_permalink = $permalink;
//   if ( $taxonomy === 'season' ) {
//     $tv_post_id = rwmb_meta( 'series_name', [ 'object_type' => 'term' ], $term->term_id );
// 		$season_no = rwmb_meta( 'season_no', [ 'object_type' => 'term' ], $term->term_id );
// 		$post_link = get_permalink($tv_post_id);
//     $new_permalink = ($tv_post_id && $season_no && $post_link) ? $post_link.'season-'.$season_no.'/' : $permalink; //(get_query_var( 'paged' ) ? 'page/'.get_query_var( 'paged' ).'/' : '')
//   }

//   return $new_permalink;
// }

add_filter( 'post_type_link', 'custom_post_type_link', 10, 3 );
function custom_post_type_link( $post_link, $post, $leavename ) {

	// if ($post->post_type === 'tv' || $post->post_type === 'drama' || $post->post_type === 'movie' || $post->post_type === 'people') {
  //     $url = rtrim($post_link, '/');
  //     return $url.'-'.$post->ID.'/';
  // }

	if ($post->post_type === 'drama-episode') {
		$drama_id = rwmb_meta( 'dramas', '', $post->ID );
	  $episode_no = rwmb_meta( 'episode_no', '', $post->ID );

	  remove_filter('post_type_link', 'custom_post_type_link');
	  $drama = get_permalink($drama_id);
	  add_filter( 'post_type_link', 'custom_post_type_link', 10, 3 );
	  
	  return ($drama && $episode_no) ? user_trailingslashit( $drama.'episode-'.$episode_no.'/' ) : $post_link;
	}

	if ($post->post_type === 'season') {
		global $wpdb;
		$season = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}tmu_tv_series_seasons WHERE `ID` = {$post->ID}");
		if ($season) {
		  $season_no = $season->season_no ?? 0;

		  remove_filter('post_type_link', 'custom_post_type_link');
		  $series = $season->tv_series ? get_permalink($season->tv_series) : '';
		  add_filter( 'post_type_link', 'custom_post_type_link', 10, 3 );
		  
		  return ($series && $season_no) ? user_trailingslashit( $series.'season-'.$season_no.'/' ) : $post_link;
		}
	}

	if ($post->post_type === 'episode') {
		global $wpdb;
		$episode = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}tmu_tv_series_episodes WHERE `ID` = {$post->ID}");
		if ($episode) {
		  $season_no = $episode->season_no ?? 0;
		  $episode_no = $episode->episode_no ?? 0;

		  remove_filter('post_type_link', 'custom_post_type_link');
		  $series = $episode->tv_series ? get_permalink($episode->tv_series) : '';
		  add_filter( 'post_type_link', 'custom_post_type_link', 10, 3 );
		  
		  return ($series && $episode_no) ? user_trailingslashit( $series.'season-'.$season_no.'/episode-'.$episode_no.'/' ) : $post_link;
		}
	}

	return $post_link;
}


add_action( 'init', 'add_custom_redirects' );

function add_custom_redirects() {
	// feed custom post type for single (feed|rdf|rss|rss2|atom)
	add_rewrite_rule( 'tv/(.+?)/feed/?$', 'index.php?tv=$matches[1]&feed=rss2', 'top' );
	add_rewrite_rule( 'movie/(.+?)/feed/?$', 'index.php?movie=$matches[1]&feed=rss2', 'top' );
	add_rewrite_rule( 'people/(.+?)/feed/?$', 'index.php?people=$matches[1]&feed=rss2', 'top' );
	add_rewrite_rule( 'drama/(.+?)/feed/?$', 'index.php?drama=$matches[1]&feed=rss2', 'top' );
	add_rewrite_rule( 'tv/(.+?)/feed/?$', 'index.php?tv=$matches[1]&feed=rss2', 'top' );
	add_rewrite_rule( 'movie/(.+?)/feed/?$', 'index.php?movie=$matches[1]&feed=rss2', 'top' );
	// add_rewrite_rule( '^people/([^/]+)-([^/]+)/?$', 'index.php?people=$matches[1]&id=$matches[2]', 'top' );
	// add_rewrite_rule( '^drama/([^/]+)-([^/]+)/?$', 'index.php?drama=$matches[1]&id=$matches[2]', 'top' );
	// add_rewrite_rule( '^movie/([^/]+)-([^/]+)/?$', 'index.php?movie=$matches[1]&id=$matches[2]', 'top' );
	// add_rewrite_rule( '^tv/([^/]+)-([^/]+)/?$', 'index.php?tv=$matches[1]&id=$matches[2]', 'top' );
	add_rewrite_rule( 'video/(.+?)/feed/?$', 'index.php?video=$matches[1]&feed=rss2', 'top' );
	add_rewrite_rule( 'tv/(.+?)/(.+?)/(.+?)/feed/?$', 'index.php?episode=$matches[1]-$matches[2]-$matches[3]&feed=rss2', 'top' );
	add_rewrite_rule( 'tv/(.+?)/(.+?)/feed/?$', 'index.php?season=$matches[1]-$matches[2]&feed=rss2', 'top' );
	add_rewrite_rule( 'drama/(.+?)/(.+?)/feed/?$', 'index.php?drama-episode=$matches[1]-$matches[2]&feed=rss2', 'top' );

	// Episode post type redirect rule
	add_rewrite_rule( 'tv/(.+?)/(.+?)/(.+?)/?$', 'index.php?episode=$matches[1]-$matches[2]-$matches[3]', 'top' );
	add_rewrite_rule( 'drama/(.+?)/(.+?)/?$', 'index.php?drama-episode=$matches[1]-$matches[2]', 'top' );
	// add_rewrite_rule( 'drama/([^/]+)-([^/]+)/(.+?)/?$', 'index.php?drama-episode=$matches[1]-$matches[3]', 'top' );
	// add_rewrite_rule( '^drama/(\d+)-([^/]+)/(.+?)/?$', 'index.php?drama-episode=$matches[2]-$matches[3]', 'top' );
	// add_rewrite_rule( '^tv/(\d+)-([^/]+)/(.+?)/(.+?)/?$', 'index.php?episode=$matches[2]-$matches[3]-$matches[4]', 'top' );

	// Season post type redirect rule
	add_rewrite_rule( 'tv/(.+?)/(.+?)/?$', 'index.php?season=$matches[1]-$matches[2]', 'top' );
}

function modify_slug_on_post_save($post_ID) {
    if ( wp_is_post_autosave($post_ID) || wp_is_post_revision($post_ID) ) return;
    $post_type = get_post_type($post_ID);
    $post_title = get_the_title($post_ID);
    if ( in_array($post_type, array('people', 'drama', 'movie', 'tv', 'video')) ) {
      $new_slug = $post_ID . '-' . sanitize_title($post_title);
      remove_action('save_post', 'modify_slug_on_post_save');
      wp_update_post( array( 'ID' => $post_ID, 'post_name' => $new_slug ) );
      add_action('save_post', 'modify_slug_on_post_save');
    }

    if ( in_array($post_type, array('drama-episode', 'episode', 'season')) ) {
    	global $wpdb;
    	if ($post_type === 'drama-episode') $parent_post_id = $wpdb->get_var($wpdb->prepare("SELECT dramas FROM {$wpdb->prefix}tmu_dramas_episodes WHERE `ID` = %s", $post_ID));
    	if ($post_type === 'season') $parent_post_id = $wpdb->get_var($wpdb->prepare("SELECT tv_series FROM {$wpdb->prefix}tmu_tv_series_seasons WHERE `ID` = %s", $post_ID));
    	if ($post_type === 'episode') $parent_post_id = $wpdb->get_var($wpdb->prepare("SELECT tv_series FROM {$wpdb->prefix}tmu_tv_series_episodes WHERE `ID` = %s", $post_ID));
      $new_slug = $parent_post_id . '-' . sanitize_title($post_title);
      remove_action('save_post', 'modify_slug_on_post_save');
      wp_update_post( array( 'ID' => $post_ID, 'post_name' => $new_slug ) );
      add_action('save_post', 'modify_slug_on_post_save');
    }
}
add_action('save_post', 'modify_slug_on_post_save');