<?php

add_action('parse_request', 'custom_url_all_seasons');
add_action('init', 'add_custom_redirects');

function custom_url_all_seasons() {
	$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	$segments = explode('/', $uri);

	// Check ONLY for drama episodes feed pattern and return 404
	if(isset($segments[1]) && isset($segments[2]) && isset($segments[3]) && isset($segments[4]) && 
	   $segments[1] === 'drama' && 
	   $segments[3] === 'episodes' && 
	   $segments[4] === 'feed') {
		
		// Set up 404 status
		global $wp_query;
		$wp_query->is_404 = true;
		$wp_query->is_feed = false;
		status_header(404);
		
		// Only remove feed-related actions for this specific URL
		remove_action('wp_head', 'feed_links', 2);
		remove_action('wp_head', 'feed_links_extra', 3);
		
		// Add our custom meta tags
		add_action('wp_head', function() {
			?>
			<meta name="robots" content="noindex,nofollow">
			<title>404 Not Found - <?php echo get_bloginfo('name'); ?></title>
			<meta name="description" content="The page you are looking for could not be found.">
			<meta property="og:type" content="website">
			<meta property="og:title" content="404 Not Found - <?php echo get_bloginfo('name'); ?>">
			<meta property="og:description" content="The page you are looking for could not be found.">
			<meta property="og:url" content="<?php echo esc_url(home_url($_SERVER['REQUEST_URI'])); ?>">
			<meta property="og:site_name" content="<?php echo get_bloginfo('name'); ?>">
			<meta name="twitter:card" content="summary">
			<meta name="twitter:title" content="404 Not Found - <?php echo get_bloginfo('name'); ?>">
			<meta name="twitter:description" content="The page you are looking for could not be found.">
			<?php
		}, 0);
		
		get_header();
		
		// Output 404 content
		?>
		<div id="primary" class="content-area">
			<main id="main" class="site-main">
				<section class="error-404 not-found">
					<header class="page-header">
						<h1 class="page-title">404 - Page Not Found</h1>
					</header>
					<div class="page-content">
						<p>The page you are looking for could not be found.</p>
						<?php get_search_form(); ?>
					</div>
				</section>
			</main>
		</div>
		<?php
		
		get_footer();
		exit();
	}

	// Regular seasons archive
	if(isset($segments[3]) && ($segments[3] == 'seasons')):
		require_once __DIR__ .'/archive-seasons.php';
		season_archive($segments[2]);
		exit;
	endif;

	// Regular episodes archive
	if(isset($segments[1]) && isset($segments[3]) && $segments[1] === 'drama' && ($segments[3] === 'episodes') && (!isset($segments[4]) || $segments[4] == '')):
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

function add_custom_redirects() {
	// Drama feed rules
	add_rewrite_rule(
		'drama/feed/?$',
		'index.php?post_type=drama&feed=feed',
		'top'
	);
	
	// Keep existing feed rules
	add_rewrite_rule('drama/([^/]+)/feed/?$', 'index.php?drama=$matches[1]&feed=rss2', 'top');
	add_rewrite_rule('drama/([^/]+)/([^/]+)/feed/?$', 'index.php?drama-episode=$matches[1]-$matches[2]&feed=rss2', 'top');
	add_rewrite_rule('tv/([^/]+)/([^/]+)/feed/?$', 'index.php?season=$matches[1]-$matches[2]&feed=rss2', 'top');
	add_rewrite_rule('tv/([^/]+)/feed/?$', 'index.php?tv=$matches[1]&feed=rss2', 'top');
	add_rewrite_rule('movie/([^/]+)/feed/?$', 'index.php?movie=$matches[1]&feed=rss2', 'top');
	add_rewrite_rule('people/([^/]+)/feed/?$', 'index.php?people=$matches[1]&feed=rss2', 'top');
	add_rewrite_rule('video/([^/]+)/feed/?$', 'index.php?video=$matches[1]&feed=rss2', 'top');
	add_rewrite_rule('tv/([^/]+)/([^/]+)/([^/]+)/feed/?$', 'index.php?episode=$matches[1]-$matches[2]-$matches[3]&feed=rss2', 'top');
	
	// Episode post type redirect rules
	add_rewrite_rule('tv/([^/]+)/([^/]+)/([^/]+)/?$', 'index.php?episode=$matches[1]-$matches[2]-$matches[3]', 'top');
	add_rewrite_rule('drama/([^/]+)/([^/]+)/?$', 'index.php?drama-episode=$matches[1]-$matches[2]', 'top');
}

// Add this new function to register drama feed
function register_drama_feed() {
	add_feed('drama', function() {
		get_template_part('feed', 'drama');
	});
}
add_action('init', 'register_drama_feed');

// After making these changes, you'll need to flush rewrite rules
function flush_rewrite_rules_once() {
	if (get_option('drama_feed_rules_flushed') != true) {
		flush_rewrite_rules();
		update_option('drama_feed_rules_flushed', true);
	}
}
add_action('init', 'flush_rewrite_rules_once');

// Remove existing template_redirect action
remove_action('template_redirect', 'custom_url_all_seasons_template_redirect');

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

