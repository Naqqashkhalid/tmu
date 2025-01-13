<?php

function check_create_table() {
	global $wpdb;

	$tables = [
		$wpdb->prefix.'tmu_movies' => 'CREATE TABLE `'.$wpdb->prefix.'tmu_movies` (
			  `ID` bigint(20) UNSIGNED NOT NULL, PRIMARY KEY (ID), FOREIGN KEY (ID) REFERENCES '.$wpdb->prefix.'posts(ID) ON DELETE CASCADE ON UPDATE NO ACTION,
			  `tmdb_id` bigint(20) DEFAULT NULL,
			  `release_date` text DEFAULT NULL,
			  `release_timestamp` bigint(20) DEFAULT NULL,
			  `original_title` text DEFAULT NULL,
			  `tagline` text DEFAULT NULL,
			  `production_house` text DEFAULT NULL,
			  `streaming_platforms` text DEFAULT NULL,
			  `runtime` bigint(20) DEFAULT NULL,
			  `certification` text DEFAULT NULL,
			  `revenue` bigint(20) DEFAULT NULL,
			  `budget` bigint(20) DEFAULT NULL,
			  `star_cast` text DEFAULT NULL,
			  `credits` longtext DEFAULT NULL,
			  `credits_temp` longtext DEFAULT NULL,
			  `videos` text DEFAULT NULL,
			  `images` text DEFAULT NULL,
			  `average_rating` DECIMAL(10,1) DEFAULT 0,
			  `vote_count` bigint(20) DEFAULT 0,
			  `popularity` DECIMAL(10,1) DEFAULT 0,
			  `total_average_rating` DECIMAL(10,1) DEFAULT 0,
			  `total_vote_count` bigint(20) NOT NULL DEFAULT 0
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci',

		$wpdb->prefix.'tmu_movies_cast' => 'CREATE TABLE `'.$wpdb->prefix.'tmu_movies_cast` (
			  `ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, PRIMARY KEY (ID),
			  `movie` bigint(20) UNSIGNED NOT NULL, FOREIGN KEY (movie) REFERENCES '.$wpdb->prefix.'posts(ID) ON DELETE CASCADE ON UPDATE NO ACTION,
			  `person` bigint(20) UNSIGNED NOT NULL, FOREIGN KEY (person) REFERENCES '.$wpdb->prefix.'posts(ID) ON DELETE CASCADE ON UPDATE NO ACTION,
			  `job` varchar(255) DEFAULT NULL,
			  `release_year` bigint(20) DEFAULT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci',

		$wpdb->prefix.'tmu_movies_crew' => 'CREATE TABLE `'.$wpdb->prefix.'tmu_movies_crew` (
			  `ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, PRIMARY KEY (ID),
			  `movie` bigint(20) UNSIGNED NOT NULL, FOREIGN KEY (movie) REFERENCES '.$wpdb->prefix.'posts(ID) ON DELETE CASCADE ON UPDATE NO ACTION,
			  `person` bigint(20) UNSIGNED NOT NULL, FOREIGN KEY (person) REFERENCES '.$wpdb->prefix.'posts(ID) ON DELETE CASCADE ON UPDATE NO ACTION,
			  `department` varchar(255) DEFAULT NULL,
			  `job` varchar(255) DEFAULT NULL,
			  `release_year` bigint(20) DEFAULT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci',

		$wpdb->prefix.'tmu_people' => 'CREATE TABLE `'.$wpdb->prefix.'tmu_people` (
			  `ID` bigint(20) UNSIGNED NOT NULL, PRIMARY KEY (ID), FOREIGN KEY (ID) REFERENCES '.$wpdb->prefix.'posts(ID) ON DELETE CASCADE ON UPDATE NO ACTION,
			  `name` text DEFAULT NULL,
			  `date_of_birth` text DEFAULT NULL,
			  `gender` text DEFAULT NULL,
			  `nick_name` text DEFAULT NULL,
			  `marital_status` text DEFAULT NULL,
			  `basic` text DEFAULT NULL,
			  `videos` text DEFAULT NULL,
			  `photos` text DEFAULT NULL,
			  `profession` text DEFAULT NULL,
			  `net_worth` bigint(20) DEFAULT NULL,
			  `tmdb_id` bigint(20) DEFAULT NULL,
			  `birthplace` text DEFAULT NULL,
			  `dead_on` text DEFAULT NULL,
			  `social_media_account` text DEFAULT NULL,
			  `no_movies` bigint(20) DEFAULT NULL,
			  `no_tv_series` bigint(20) DEFAULT NULL,
			  `no_dramas` bigint(20) DEFAULT NULL,
			  `known_for` text DEFAULT NULL,
			  `popularity` DECIMAL(10,1) DEFAULT 0
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci',

		$wpdb->prefix.'tmu_dramas' => 'CREATE TABLE `'.$wpdb->prefix.'tmu_dramas` (
			  `ID` bigint(20) UNSIGNED NOT NULL, PRIMARY KEY (ID), FOREIGN KEY (ID) REFERENCES '.$wpdb->prefix.'posts(ID) ON DELETE CASCADE ON UPDATE NO ACTION,
			  `tmdb_id` bigint(20) DEFAULT NULL,
			  `release_date` text DEFAULT NULL,
			  `release_timestamp` bigint(20) DEFAULT NULL,
			  `original_title` text DEFAULT NULL,
			  `finished` text DEFAULT NULL,
			  `tagline` text DEFAULT NULL,
			  `seo_genre` BIGINT(20) NULL DEFAULT NULL,
			  `production_house` text DEFAULT NULL,
			  `streaming_platforms` text DEFAULT NULL,
			  `schedule_day` text DEFAULT NULL,
			  `schedule_time` text DEFAULT NULL,
			  `schedule_timestamp` bigint(20) DEFAULT NULL,
			  `runtime` bigint(20) DEFAULT NULL,
			  `certification` text DEFAULT NULL,
			  `star_cast` text DEFAULT NULL,
			  `credits` longtext DEFAULT NULL,
			  `credits_temp` longtext DEFAULT NULL,
			  `videos` text DEFAULT NULL,
			  `images` text DEFAULT NULL,
			  `average_rating` DECIMAL(10,1) DEFAULT 0,
			  `vote_count` bigint(20) DEFAULT 0,
			  `popularity` DECIMAL(10,1) DEFAULT 0,
			  `where_to_watch` text DEFAULT NULL,
			  `total_average_rating` DECIMAL(10,1) DEFAULT 0,
			  `total_vote_count` bigint(20) NOT NULL DEFAULT 0
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci',

		$wpdb->prefix.'tmu_dramas_cast' => 'CREATE TABLE `'.$wpdb->prefix.'tmu_dramas_cast` (
			  `ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, PRIMARY KEY (ID),
			  `dramas` bigint(20) UNSIGNED NOT NULL, FOREIGN KEY (dramas) REFERENCES '.$wpdb->prefix.'posts(ID) ON DELETE CASCADE ON UPDATE NO ACTION,
			  `person` bigint(20) UNSIGNED NOT NULL, FOREIGN KEY (person) REFERENCES '.$wpdb->prefix.'posts(ID) ON DELETE CASCADE ON UPDATE NO ACTION,
			  `job` varchar(255) DEFAULT NULL,
			  `release_year` bigint(20) DEFAULT NULL,
			  `count` bigint(20) DEFAULT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci',

		$wpdb->prefix.'tmu_dramas_crew' => 'CREATE TABLE `'.$wpdb->prefix.'tmu_dramas_crew` (
			  `ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, PRIMARY KEY (ID),
			  `dramas` bigint(20) UNSIGNED NOT NULL, FOREIGN KEY (dramas) REFERENCES '.$wpdb->prefix.'posts(ID) ON DELETE CASCADE ON UPDATE NO ACTION,
			  `person` bigint(20) UNSIGNED NOT NULL, FOREIGN KEY (person) REFERENCES '.$wpdb->prefix.'posts(ID) ON DELETE CASCADE ON UPDATE NO ACTION,
			  `department` varchar(255) DEFAULT NULL,
			  `job` varchar(255) DEFAULT NULL,
			  `release_year` bigint(20) DEFAULT NULL,
			  `count` bigint(20) DEFAULT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci',

		$wpdb->prefix.'tmu_dramas_episodes' => 'CREATE TABLE `'.$wpdb->prefix.'tmu_dramas_episodes` (
			  `ID` bigint(20) UNSIGNED NOT NULL, PRIMARY KEY (ID), FOREIGN KEY (ID) REFERENCES '.$wpdb->prefix.'posts(ID) ON DELETE CASCADE ON UPDATE NO ACTION,
			  `episode_title` text DEFAULT NULL,
			  `air_date` text DEFAULT NULL,
			  `episode_type` text DEFAULT NULL,
			  `runtime` bigint(20) DEFAULT NULL,
			  `overview` text DEFAULT NULL,
			  `credits` text DEFAULT NULL,
			  `episode_no` bigint(20) DEFAULT NULL,
			  `dramas`bigint(20) UNSIGNED NOT NULL, FOREIGN KEY (dramas) REFERENCES '.$wpdb->prefix.'posts(ID) ON DELETE CASCADE ON UPDATE NO ACTION,
			  `average_rating` DECIMAL(10,1) NOT NULL DEFAULT 0,
			  `vote_count` bigint(20) NOT NULL DEFAULT 0,
			  `video` text DEFAULT NULL,
			  `total_average_rating` DECIMAL(10,1) DEFAULT 0,
			  `total_vote_count` bigint(20) NOT NULL DEFAULT 0
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci',

		$wpdb->prefix.'tmu_tv_series' => 'CREATE TABLE `'.$wpdb->prefix.'tmu_tv_series` (
			  `ID` bigint(20) UNSIGNED NOT NULL, PRIMARY KEY (ID), FOREIGN KEY (ID) REFERENCES '.$wpdb->prefix.'posts(ID) ON DELETE CASCADE ON UPDATE NO ACTION,
			  `tmdb_id` bigint(20) DEFAULT NULL,
			  `release_date` text DEFAULT NULL,
			  `release_timestamp` bigint(20) DEFAULT NULL,
			  `original_title` text DEFAULT NULL,
			  `finished` text DEFAULT NULL,
			  `tagline` text DEFAULT NULL,
			  `production_house` text DEFAULT NULL,
			  `streaming_platforms` text DEFAULT NULL,
			  `schedule_time` text DEFAULT NULL,
			  `runtime` bigint(20) DEFAULT NULL,
			  `certification` text DEFAULT NULL,
			  `revenue` bigint(20) DEFAULT NULL,
			  `budget` bigint(20) DEFAULT NULL,
			  `star_cast` text DEFAULT NULL,
			  `credits` longtext DEFAULT NULL,
			  `credits_temp` longtext DEFAULT NULL,
			  `videos` text DEFAULT NULL,
			  `images` text DEFAULT NULL,
			  `seasons` text DEFAULT NULL,
			  `last_season` bigint(20) DEFAULT NULL,
			  `last_episode` bigint(20) DEFAULT NULL,
			  `average_rating` DECIMAL(10,1) DEFAULT 0,
			  `vote_count` bigint(20) DEFAULT 0,
			  `popularity` DECIMAL(10,1) DEFAULT 0,
			  `where_to_watch` text DEFAULT NULL,
			  `total_average_rating` DECIMAL(10,1) DEFAULT 0,
			  `total_vote_count` bigint(20) NOT NULL DEFAULT 0
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci',

		$wpdb->prefix.'tmu_tv_series_cast' => 'CREATE TABLE `'.$wpdb->prefix.'tmu_tv_series_cast` (
			  `ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, PRIMARY KEY (ID),
			  `tv_series` bigint(20) UNSIGNED NOT NULL, FOREIGN KEY (tv_series) REFERENCES '.$wpdb->prefix.'posts(ID) ON DELETE CASCADE ON UPDATE NO ACTION,
			  `person` bigint(20) UNSIGNED NOT NULL, FOREIGN KEY (person) REFERENCES '.$wpdb->prefix.'posts(ID) ON DELETE CASCADE ON UPDATE NO ACTION,
			  `job` varchar(255) DEFAULT NULL,
			  `release_year` bigint(20) DEFAULT NULL,
			  `count` bigint(20) DEFAULT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci',

		$wpdb->prefix.'tmu_tv_series_crew' => 'CREATE TABLE `'.$wpdb->prefix.'tmu_tv_series_crew` (
			  `ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, PRIMARY KEY (ID),
			  `tv_series` bigint(20) UNSIGNED NOT NULL, FOREIGN KEY (tv_series) REFERENCES '.$wpdb->prefix.'posts(ID) ON DELETE CASCADE ON UPDATE NO ACTION,
			  `person` bigint(20) UNSIGNED NOT NULL, FOREIGN KEY (person) REFERENCES '.$wpdb->prefix.'posts(ID) ON DELETE CASCADE ON UPDATE NO ACTION,
			  `department` varchar(255) DEFAULT NULL,
			  `job` varchar(255) DEFAULT NULL,
			  `release_year` bigint(20) DEFAULT NULL,
			  `count` bigint(20) DEFAULT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci',

		$wpdb->prefix.'tmu_tv_series_episodes' => 'CREATE TABLE `'.$wpdb->prefix.'tmu_tv_series_episodes` (
			  `ID` bigint(20) UNSIGNED NOT NULL, PRIMARY KEY (ID), FOREIGN KEY (ID) REFERENCES '.$wpdb->prefix.'posts(ID) ON DELETE CASCADE ON UPDATE NO ACTION,
			  `tv_series` bigint(20) UNSIGNED NOT NULL, FOREIGN KEY (tv_series) REFERENCES '.$wpdb->prefix.'posts(ID) ON DELETE CASCADE ON UPDATE NO ACTION,
			  `season_no` bigint(20) DEFAULT NULL,
			  `episode_no` bigint(20) DEFAULT NULL,
			  `episode_title` text DEFAULT NULL,
			  `air_date` text DEFAULT NULL,
			  `air_date_timestamp` bigint(20) DEFAULT NULL,
			  `episode_type` text DEFAULT NULL,
			  `runtime` bigint(20) DEFAULT NULL,
			  `overview` text DEFAULT NULL,
			  `credits` text DEFAULT NULL,
			  `average_rating` DECIMAL(10,1) NOT NULL DEFAULT 0,
			  `vote_count` bigint(20) NOT NULL DEFAULT 0,
			  `season_id` bigint(20) UNSIGNED NOT NULL, FOREIGN KEY (season_id) REFERENCES '.$wpdb->prefix.'posts(ID) ON DELETE CASCADE ON UPDATE NO ACTION,
			  `total_average_rating` DECIMAL(10,1) DEFAULT 0,
			  `total_vote_count` bigint(20) NOT NULL DEFAULT 0
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci',

		$wpdb->prefix.'tmu_tv_series_seasons' => 'CREATE TABLE `'.$wpdb->prefix.'tmu_tv_series_seasons` (
			  `ID` bigint(20) UNSIGNED NOT NULL, PRIMARY KEY (ID), FOREIGN KEY (ID) REFERENCES '.$wpdb->prefix.'posts(ID) ON DELETE CASCADE ON UPDATE NO ACTION,
			  `season_no` bigint(20) DEFAULT NULL,
			  `season_name` text DEFAULT NULL,
			  `tv_series` bigint(20) UNSIGNED NOT NULL, FOREIGN KEY (tv_series) REFERENCES '.$wpdb->prefix.'posts(ID) ON DELETE CASCADE ON UPDATE NO ACTION,
			  `air_date` text DEFAULT NULL,
			  `air_date_timestamp` bigint(20) DEFAULT NULL,
			  `total_episodes` bigint(20) DEFAULT NULL,
			  `average_rating` DECIMAL(10,1) NOT NULL DEFAULT 0,
			  `total_average_rating` DECIMAL(10,1) DEFAULT 0,
			  `total_vote_count` bigint(20) NOT NULL DEFAULT 0
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci',

		$wpdb->prefix.'tmu_dramas_seasons' => 'CREATE TABLE `'.$wpdb->prefix.'tmu_dramas_seasons` (
			  `ID` bigint(20) UNSIGNED NOT NULL, PRIMARY KEY (ID), FOREIGN KEY (ID) REFERENCES '.$wpdb->prefix.'posts(ID) ON DELETE CASCADE ON UPDATE NO ACTION,
			  `season_no` bigint(20) DEFAULT NULL,
			  `season_name` text DEFAULT NULL,
			  `dramas` bigint(20) UNSIGNED NOT NULL, FOREIGN KEY (dramas) REFERENCES '.$wpdb->prefix.'posts(ID) ON DELETE CASCADE ON UPDATE NO ACTION,
			  `air_date` text DEFAULT NULL,
			  `air_date_timestamp` bigint(20) DEFAULT NULL,
			  `total_episodes` bigint(20) DEFAULT NULL,
			  `average_rating` DECIMAL(10,1) NOT NULL DEFAULT 0,
			  `total_average_rating` DECIMAL(10,1) DEFAULT 0,
			  `total_vote_count` bigint(20) NOT NULL DEFAULT 0
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci',

		$wpdb->prefix.'tmu_videos' => 'CREATE TABLE `'.$wpdb->prefix.'tmu_videos` (
			  `ID` bigint(20) UNSIGNED NOT NULL, PRIMARY KEY (ID), FOREIGN KEY (ID) REFERENCES '.$wpdb->prefix.'posts(ID) ON DELETE CASCADE ON UPDATE NO ACTION,
			  `video_data` text DEFAULT NULL,
			  `post_id` bigint(20) UNSIGNED NOT NULL, FOREIGN KEY (post_id) REFERENCES '.$wpdb->prefix.'posts(ID) ON DELETE CASCADE ON UPDATE NO ACTION
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci',

		$wpdb->prefix.'tmu_seo_options' => 'CREATE TABLE `'.$wpdb->prefix.'tmu_seo_options` (
			  `ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, PRIMARY KEY (ID),
			  `name` text DEFAULT NULL,
			  `title` text DEFAULT NULL,
			  `description` text DEFAULT NULL,
			  `keywords` text DEFAULT NULL,
			  `robots` text DEFAULT NULL,
			  `post_type` text DEFAULT NULL,
			  `section` text DEFAULT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;'
	];


    foreach ($tables as $table_name => $table_query) {
    	 // Check if table exists
    	$result = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'");
    	// Create table if it doesn't exist
    	if (!$result) $wpdb->query($table_query);
    }

    $table_name = $wpdb->prefix . 'comments';
    // Check if column exists
    $result = $wpdb->get_var("SHOW COLUMNS FROM $table_name LIKE 'comment_rating'");
    // Create column if it doesn't exist
    if (!$result) { $wpdb->query("ALTER TABLE $table_name ADD comment_rating INT(11) NOT NULL DEFAULT 0"); }
    $result = $wpdb->get_var("SHOW COLUMNS FROM $table_name LIKE 'parent_post_id'");
    if (!$result) { $wpdb->query("ALTER TABLE $table_name ADD parent_post_id INT(11) DEFAULT NULL"); }

    ////  Post Types SEO Title column
    $result = $wpdb->get_var("SHOW COLUMNS FROM {$wpdb->prefix}posts LIKE 'seo_title'");
    if (!$result) { $wpdb->query("ALTER TABLE {$wpdb->prefix}posts ADD seo_title TEXT NULL DEFAULT NULL"); }
    ////  Post Types SEO Description column
    $result = $wpdb->get_var("SHOW COLUMNS FROM {$wpdb->prefix}posts LIKE 'seo_description'");
    if (!$result) { $wpdb->query("ALTER TABLE {$wpdb->prefix}posts ADD seo_description TEXT NULL DEFAULT NULL"); }
    //// Post Types Seo Meta Keywords
    $result = $wpdb->get_var("SHOW COLUMNS FROM {$wpdb->prefix}posts LIKE 'meta_keywords'");
    if (!$result) { $wpdb->query("ALTER TABLE {$wpdb->prefix}posts ADD meta_keywords TEXT NULL DEFAULT NULL"); }

    ////  Term SEO Title column
    // $result = $wpdb->get_var("SHOW COLUMNS FROM {$wpdb->prefix}terms LIKE 'seo_title'");
    // if (!$result) { $wpdb->query("ALTER TABLE {$wpdb->prefix}terms ADD seo_title TEXT NULL DEFAULT NULL"); }
    ////  Term SEO Description column
    // $result = $wpdb->get_var("SHOW COLUMNS FROM {$wpdb->prefix}terms LIKE 'seo_description'");
    // if (!$result) { $wpdb->query("ALTER TABLE {$wpdb->prefix}terms ADD seo_description TEXT NULL DEFAULT NULL"); }

    $redirect = false;
    $options = get_options( ['tmu_movies', 'tmu_tv_series', 'tmu_dramas', 'tmu_email'] );
    if(!$options['tmu_movies']) { add_option( 'tmu_movies', 'off', '', 'off' ); $redirect = true; }
	if(!$options['tmu_tv_series']) { add_option( 'tmu_tv_series', 'off', '', 'off' ); $redirect = true; }
	if(!$options['tmu_dramas']) { add_option( 'tmu_dramas', 'off', '', 'off' ); $redirect = true; }
	if (!$options['tmu_email'] && $options['tmu_email'] !== '') { add_option( 'tmu_email', '', '', 'off' ); $redirect = true; };
	flush_rewrite_rules();
	if ($redirect) { activate_plugin( 'tmu/tmu.php' ); wp_redirect(admin_url('admin.php?page=tmu-settings')); exit; }
	return;
}
