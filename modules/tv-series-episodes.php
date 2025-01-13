<?php
/**
 * Latest and Upcoming Episodes
 *
 * @package green_entertainment
 */

function latest_episodes($type, $count){  // $type = 'upcoming' or 'latest'
	global $wpdb;
	$table_name = $wpdb->prefix.'tmu_tv_series_episodes';
	if ($type === 'upcoming') {
		$results = $wpdb->get_results("SELECT $table_name.* FROM $table_name JOIN {$wpdb->prefix}posts AS posts ON ({$table_name}.ID = posts.ID) WHERE unix_timestamp(`air_date`)>unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) AND posts.post_status = 'publish' ORDER BY `air_date` ASC LIMIT 50");
		$results = array_slice(combine_tv_series_episodes($results), 0, 12, true);
	} else {
		$results = $wpdb->get_results("SELECT $table_name.* FROM $table_name JOIN {$wpdb->prefix}posts AS posts ON ({$table_name}.ID = posts.ID) WHERE unix_timestamp(`air_date`)<=unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) AND posts.post_status = 'publish' ORDER BY `air_date` DESC LIMIT 50");
		$results = array_slice(combine_tv_series_episodes($results), 0, 12, true);
	}

	$schema = ''; $data = '';
	if ($results) {
		$data .= '<div class="latest_episodes">';
			$data .= '<div class="heading"><h2>'.ucfirst($type).' TV Series Episodes</h2></div>';
			$data .= '<div class="episode-flex">';
			foreach ($results as $episode) {
				if ($episode && get_post_status($episode['ID']) == 'publish') {
					$permalink = get_permalink($episode['ID']);
					$release_date = ''; $release = $episode['air_date']; $release_date = $release ? new DateTime($release) : '';
					$poster_url = has_post_thumbnail( $episode['ID'] ) ? get_the_post_thumbnail_url($episode['ID'],'full') : plugin_dir_url( __DIR__ ).'src/images/no-image.webp';
					// $episode['runtime'] = (int)$episode['runtime']; $runtime_hours = $episode['runtime']/60; $runtime_hours = $runtime_hours > 1 ? round($runtime_hours) : 0;
					// $episode['runtime'] = $episode['runtime'] ? ($runtime_hours==0 ? '' : $runtime_hours.' hour ').($episode['runtime']%60).' minutes' : '';

					$title = get_the_title($episode['ID']);
		            		$schema .= ($count !== 0 ? ',' : '' ).'{"@type": "ListItem","position": "'.(++$count).'","url": "'.$permalink.'"}';

		            		$data .= '<div class="episode_container">';
		            			$data .= '<a class="episode-image episode-single" href="'.$permalink.'">';
		            				$data .= '<img '.($poster_url ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-image.webp" data-src="'.$poster_url.'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-image.webp"') ).' alt="'.$title.'" width="100%" height="100%">';
		            			$data .= '</a>';
		            			$data .= '<a class="episode-details" href="'.$permalink.'">';
		            				$data .= '<h3 class="series-title"><span class="series-name">'.get_the_title($episode['tv_series']).'</span> - S'.$episode['season_no'].' E'.$episode['episode_no'].'</h3>';
			  				$data .= '<p class="ep-release">'.($release_date ? $release_date->format('j F Y') : '').'</p>';
			  			$data .= '</a>';
		            		$data .= '</div>';
			  	}
		  	}
			$data .= '</div>';
		$data .= '</div>';
	}
	return ['data' => $data, 'schema' => $schema];
}


function latest_drama_episodes($type, $count){  // $type = 'upcoming' or 'latest'
	global $wpdb;
	$table_name = $wpdb->prefix.'tmu_dramas_episodes';
	if ($type === 'upcoming') {
		$results = $wpdb->get_results("SELECT $table_name.* FROM $table_name JOIN {$wpdb->prefix}posts AS posts ON ({$table_name}.ID = posts.ID) WHERE unix_timestamp(`air_date`)>unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) AND posts.post_status = 'publish' ORDER BY `air_date` DESC LIMIT 50");
		$results = array_slice(combine_dramas_episodes($results), 0, 12, true);
	} else {
		$results = $wpdb->get_results("SELECT $table_name.* FROM $table_name JOIN {$wpdb->prefix}posts AS posts ON ({$table_name}.ID = posts.ID) WHERE unix_timestamp(`air_date`)<=unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) AND posts.post_status = 'publish' ORDER BY `air_date` DESC LIMIT 50");
		$results = array_slice(combine_dramas_episodes($results), 0, 12, true);
	}

	$schema = ''; $data = '';
	if ($results) {
		$data .= '<div class="latest_episodes">';
			$data .= '<div class="heading"><h2>'.ucfirst($type).' Drama Episodes</h2></div>';
			$data .= '<div class="episode-flex">';
			foreach ($results as $episode) {
				if ($episode && get_post_status($episode['ID']) == 'publish') {
					$permalink = get_permalink($episode['ID']);
					$release_date = ''; $release = $episode['air_date']; $release_date = $release ? new DateTime($release) : '';
					$poster_url = has_post_thumbnail( $episode['ID'] ) ? get_the_post_thumbnail_url($episode['ID'],'full') : plugin_dir_url( __DIR__ ).'src/images/no-image.webp';
					// $episode['runtime'] = (int)$episode['runtime']; $runtime_hours = $episode['runtime']/60; $runtime_hours = $runtime_hours > 1 ? round($runtime_hours) : 0;
					// $episode['runtime'] = $episode['runtime'] ? ($runtime_hours==0 ? '' : $runtime_hours.' hour ').($episode['runtime']%60).' minutes' : '';

					$title = get_the_title($episode['ID']);
		            		$schema .= ($count !== 0 ? ',' : '' ).'{"@type": "ListItem","position": "'.(++$count).'","url": "'.$permalink.'"}';

		            		$data .= '<div class="episode_container">';
		            			$data .= '<a class="episode-image episode-single" href="'.$permalink.'">';
		            				$data .= '<img '.($poster_url ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-image.webp" data-src="'.$poster_url.'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-image.webp"') ).' alt="'.$title.'" width="100%" height="100%">';
		            			$data .= '</a>';
		            			$data .= '<a class="episode-details" href="'.$permalink.'">';
		            				$data .= '<h3 class="series-title"><span class="series-name">'.get_the_title($episode['tv_series']).'</span> - S'.$episode['season_no'].' E'.$episode['episode_no'].'</h3>';
			  				$data .= '<p class="ep-release">'.($release_date ? $release_date->format('j F Y') : '').'</p>';
			  			$data .= '</a>';
		            		$data .= '</div>';
			  	}
		  	}
			$data .= '</div>';
		$data .= '</div>';
	}
	return ['data' => $data, 'schema' => $schema];
}

