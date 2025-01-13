<?php

function replace_tags($string, $section, $post_type, $post_id='') {
    $pattern = '/%([^%]+)%/';

    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	$segments = explode('/', $uri);

    // Replace keywords with corresponding variables
    return preg_replace_callback($pattern, function($match) use ($section, $post_type, $post_id) {
    	if ($match[1]) {
    		switch ($match[1]) {
			  case 'title':
			    return get_default_title($post_id);
			    break;
			  case 'excerpt':
			  	$excerpt = wp_trim_words(wp_strip_all_tags(get_the_content()), 60);
			    return $excerpt ?? '';
			    break;
			  case 'current_month':
			    return date("F");
			    break;
			  case 'current_year':
			    return date("Y");
			    break;
			  case 'sitename':
			    return get_bloginfo('name');
			    break;
			  case 'movie_or_drama_list':
			    return post_type_exists( 'drama' ) ? 'Drama list' : ( post_type_exists( 'movie' ) ? (post_type_exists( 'tv' ) ? 'Movies & TV Shows list' : 'Movies list') : 'TV Shows list');
			    break;
			}

			if ($section === 'single' && $post_id) {
				global $wpdb;
				$db_table_type = ($post_type === 'drama' || $post_type === 'drama-episode') ? 'tmu_dramas' : ($post_type === 'tv' || $post_type === 'episode' || $post_type === 'season' ? 'tmu_tv_series' : ($post_type === 'movie' ? 'tmu_movies' : $post_type));
				$table_name = $wpdb->prefix.$db_table_type;
				
				$taxonomies = get_object_taxonomies( $post_type );
				if ($post_type === 'movie' || $post_type === 'tv' || $post_type === 'drama') {
					$db_section = $post_type === 'drama' ? 'dramas' : ($post_type === 'tv' ? 'tv_series' : $post_type);
					switch ($match[1]) {
					  case 'release_date':
					  	$release_date = $wpdb->get_var("SELECT release_timestamp FROM {$table_name} WHERE ID = $post_id");
					    return $release_date ? date("F d, Y", $release_date) : '';
					    break;
					  case 'release_month':
					  	$release_date = $wpdb->get_var("SELECT release_timestamp FROM {$table_name} WHERE ID = $post_id");
					    return $release_date ? date("F", $release_date) : '';
					    break;
					  case 'star_cast':
					  	$star_cast = $wpdb->get_var("SELECT star_cast FROM {$table_name} WHERE ID = $post_id");
					    return $star_cast ? implode(', ', array_map(function($cast) { return get_the_title($cast['person']); }, unserialize($star_cast))) : '';
					    break;
					  case 'runtime':
					  	$runtime = $wpdb->get_var("SELECT runtime FROM {$table_name} WHERE ID = $post_id");
					    return $runtime ? $runtime.' minutes' : '';
					    break;
					  case 'tagline':
					  	$tagline = $wpdb->get_var("SELECT tagline FROM {$table_name} WHERE ID = $post_id");
					    return $tagline ?? '';
					    break;
					  case 'producer':
					  	$producer = $wpdb->get_var("SELECT person FROM {$table_name}_crew WHERE $db_section=$post_id AND (job='Producer' OR job LIKE '%Co-Producer%' OR job LIKE '%Executive Producer%' OR job LIKE '%Associate Producer%')");
					    return $producer ? get_the_title($producer) : '';
					    break;
					  case 'director':
					    $director = $wpdb->get_var("SELECT person FROM {$table_name}_crew WHERE $db_section=$post_id AND (job='Director' OR job LIKE '%Co-Director%' OR job LIKE '%Executive Director%' OR job LIKE '%Associate Director%')");
					    return $director ? get_the_title($director) : '';
					    break;
					  case 'writer':
					    $writer = $wpdb->get_var("SELECT person FROM {$table_name}_crew WHERE $db_section=$post_id AND job='Writer'");
					    return $writer ? get_the_title($writer) : '';
					    break;
					  case 'production_house':
					  	$production_house = $wpdb->get_var("SELECT production_house FROM {$table_name} WHERE ID = $post_id");
					    return $production_house ?? '';
					    break;
					}
				}

				if ($post_type === 'tv' && 'total_seasons' === $match[1]) {
					$total_seasons = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}_seasons WHERE $db_section = $post_id");
					return $total_seasons ?? '';
				}

				if ($post_type === 'drama' && 'drama_total_episodes' === $match[1]) {
					$total_episodes = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}_episodes WHERE $db_section = $post_id");
					return $total_episodes ?? '';
				}

				if ($post_type === 'season') {
					switch ($match[1]) {
					  case 'tv_series':
					  	$tv_series = $wpdb->get_var("SELECT tv_series FROM {$table_name}_seasons WHERE ID = $post_id");
					    return $tv_series ? get_the_title($tv_series) : '';
					    break;
					  case 'season_no':
					  	$season_no = $wpdb->get_var("SELECT season_no FROM {$table_name}_seasons WHERE ID = $post_id");
					    return $season_no ?? 0;
					    break;
					  case 'total_season_episodes':
					  	$total_season_episodes = $wpdb->get_var("SELECT total_episodes FROM {$table_name}_seasons WHERE ID = $post_id");
					    return $total_season_episodes ?? 0;
					    break;
					  case 'season_release_month':
					  	$season_release_month = $wpdb->get_var("SELECT air_date FROM {$table_name}_seasons WHERE ID = $post_id");
					    return $season_release_month ? date("F", strtotime($season_release_month)) : '';
					    break;
					  case 'season_release_year':
					  	$season_release_year = $wpdb->get_var("SELECT air_date FROM {$table_name}_seasons WHERE ID = $post_id");
					    return $season_release_year ? date("Y", strtotime($season_release_year)) : '';
					    break;
					  case 'episode_release_date':
					  	$season_release_year = $wpdb->get_var("SELECT air_date FROM {$table_name}_seasons WHERE ID = $post_id");
					    return $season_release_year ? date("F d, Y", strtotime($season_release_year)) : '';
					    break;
					}
				}

				if ($post_type === 'episode') {
					switch ($match[1]) {
					  case 'tv_series':
					  	$tv_series = $wpdb->get_var("SELECT tv_series FROM {$table_name}_episodes WHERE ID = $post_id");
					    return $tv_series ? get_the_title($tv_series) : '';
					    break;
					  case 'episode_no':
					    $episode_no = $wpdb->get_var("SELECT episode_no FROM {$table_name}_episodes WHERE ID = $post_id");
					    return $episode_no ?? 0;
					    break;
					  case 'season_no':
					    $season_no = $wpdb->get_var("SELECT season_no FROM {$table_name}_episodes WHERE ID = $post_id");
					    return $season_no ?? 0;
					    break;
					  case 'tv_series_month':
					  	$tv_series_month = $wpdb->get_var("SELECT air_date FROM {$table_name}_episodes WHERE ID = $post_id");
					    return $tv_series_month ? date("F", strtotime($tv_series_month)) : '';
					    break;
					  case 'tv_series_year':
					    $tv_series_year = $wpdb->get_var("SELECT air_date FROM {$table_name}_episodes WHERE ID = $post_id");
					    return $tv_series_year ? date("Y", strtotime($tv_series_year)) : '';
					    break;
					  case 'episode_release_date':
					    $episode_release_date = $wpdb->get_var("SELECT air_date FROM {$table_name}_episodes WHERE ID = $post_id");
					    return $episode_release_date ? date("Y", strtotime($episode_release_date)) : '';
					    break;
					}
				}

				if ($post_type === 'drama-episode') {
					switch ($match[1]) {
					  case 'drama':
					  	$drama = $wpdb->get_var("SELECT dramas FROM {$table_name}_episodes WHERE ID = $post_id");
					    return $drama ? get_the_title($drama) : '';
					    break;
					  case 'drama_episode_no':
					    $episode_no = $wpdb->get_var("SELECT episode_no FROM {$table_name}_episodes WHERE ID = $post_id");
					    return $episode_no ?? 0;
					    break;
					  case 'drama_month':
					  	$drama_month = $wpdb->get_var("SELECT air_date FROM {$table_name}_episodes WHERE ID = $post_id");
					    return $drama_month ? date("F", strtotime($drama_month)) : '';
					    break;
					  case 'drama_year':
					    $drama_year = $wpdb->get_var("SELECT air_date FROM {$table_name}_episodes WHERE ID = $post_id");
					    return $drama_year ? date("Y", strtotime($drama_year)) : '';
					    break;
					  case 'drama_episode_release_date':
					    $release_date = $wpdb->get_var("SELECT air_date FROM {$table_name}_episodes WHERE ID = $post_id");
					    return $release_date ? date("d F Y", strtotime($release_date)) : '';
					    break;
					  case 'drama_channel':
					  	$drama = $wpdb->get_var("SELECT dramas FROM {$table_name}_episodes WHERE ID = $post_id");
					  	$terms = get_the_terms( $drama, 'channel' );
					  	$term = $terms ? array_pop($terms) : '';
						return $term ? $term->name : '';
					    break;
					}
				}

				if ($post_type === 'people') {
					if ($match[1] === 'all_jobs') {
						$result = $wpdb->get_row("SELECT gender,profession FROM {$wpdb->prefix}tmu_people WHERE `ID` = $post_id");
						if ($result->profession === 'Acting') {
							return $result->gender === 'Male' ? 'Actor' : 'Actress';
						} else {
							$jobs = $wpdb->get_results("SELECT DISTINCT job FROM ( SELECT job FROM {$wpdb->prefix}tmu_tv_series_crew WHERE person = $post_id UNION ALL SELECT job FROM {$wpdb->prefix}tmu_dramas_crew WHERE person = $post_id UNION ALL SELECT job FROM {$wpdb->prefix}tmu_movies_crew WHERE person = $post_id ) AS combined_jobs");
							return $jobs ? implode(', ', array_map(function($result){ return $result->job; }, $jobs)) : '';
						}
					}
					if ($match[1] === 'He/She') {
						$gender = $wpdb->get_var("SELECT gender FROM {$wpdb->prefix}tmu_people WHERE `ID` = $post_id");
						return $gender==='Male' ? 'He' : 'She';
					}
					if ($match[1] === 'His/Her') {
						$gender = $wpdb->get_var("SELECT gender FROM {$wpdb->prefix}tmu_people WHERE `ID` = $post_id");
						return $gender==='Male' ? 'His' : 'Her';
					}
					if ($match[1] === 'known_for') {
						$gender = $wpdb->get_var("SELECT gender FROM {$wpdb->prefix}tmu_people WHERE `ID` = $post_id");
						$his = $gender==='Male' ? 'His' : 'Her';
						$known_for = $wpdb->get_var("SELECT known_for FROM {$wpdb->prefix}tmu_people WHERE `ID` = $post_id");
						$known_for = $known_for ? unserialize($known_for) : '';
						return $known_for ? 'Some of '.$his.' work includes '.implode(', ', array_map(function($item){ return get_the_title($item); }, $known_for)).'.' : '';
					}
				}

				if ($post_type === 'video') {
					$parent_id = $wpdb->get_var("SELECT post_id FROM {$wpdb->prefix}tmu_videos WHERE `ID` = $post_id");
					if ($match[1] === 'parent_title') return get_the_title($parent_id);
					if ($match[1] === 'parent_description') {
						$parent_content = $wpdb->get_var("SELECT post_content FROM {$wpdb->prefix}posts WHERE `ID` = $parent_id");
						$parent_content = $parent_content ? wp_trim_words(stripslashes(trim(preg_replace('/\s+/', ' ', str_replace('"', ' ', wp_strip_all_tags($parent_content))))), 60) : '';
						return $parent_content;
					}
				}

				foreach ($taxonomies as $taxonomy) {
					if ($taxonomy === $match[1]) {
						$terms = get_the_terms( $post_id, $taxonomy );
						$term = $terms ? array_pop($terms) : '';
						return $term ? $term->name : '';
						break;
					}
				}
				
			}

			// if ($section === 'taxonomy') {
	    	// 	// code...
	    	// }
    	}

        return $match[0];
    }, $string);
}