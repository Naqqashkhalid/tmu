<?php
/**
 * TV Series with Recent Episodes Released
 *
 * @package green_entertainment
 */

function tv_series_with_recent_episode_release(){  // $type = 'upcoming' or 'latest'
	global $wpdb;
	$table_name = $wpdb->prefix.'tmu_tv_series_episodes';
	$results = $wpdb->get_results("SELECT $table_name.* FROM $table_name JOIN {$wpdb->prefix}posts AS posts ON ({$table_name}.ID = posts.ID) WHERE unix_timestamp(`air_date`)<=unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) AND posts.post_status = 'publish' ORDER BY `air_date` DESC LIMIT 100");

	$results = array_slice(combine_tv_series_episodes($results), 0, 20, true);
	$data = ''; $schema = ''; $count = 0;

	if ($results) {
		$data .= '<div class="heading"><h2>Latest Episodes</h2></div>';
		$data .= '<div class="module_flexbox">';
		foreach ($results as $result) {
			$post_id = $result['tv_series'];
			$post_title = get_the_title($post_id);
			$release_date = ''; $release = $result['air_date']; $release_date = $release ? new DateTime($release) : '';
			$permalink = get_permalink($post_id);

			$schema .= ($count !== 0 ? ',' : '' ).'{"@type": "ListItem", "position": "'.(++$count).'", "url": "'.$permalink.'"}';
			$data .= '<div class="latest-posts-module">';
				$data .= '<a class="tv-series-latest" href="'.$permalink.'">';
					$data .= '<div class="poster">';
						$data .= '<img '.(has_post_thumbnail($post_id) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url($post_id, 'full').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ).' alt="'.$post_title.'" width="100%" height="100%">';
					$data .= '</div>';
					
					$data .= '<div class="details">';
						$data .= '<h3>'.$post_title.'</h3>';
						$data .= '<p class="last-episode desktop">'.($release_date ? 'Latest Episode: '.$release_date->format('j F') : '').'</p>';
						$data .= '<p class="last-episode mobile">'.($release_date ? 'Last Ep: '.$release_date->format('j F') : '').'</p>';
					$data .= '</div>';
				$data .= '</a>';
				$data .= '<a href="'.$permalink.'season-'.$result['season_no'].'/" class="watch-list"><span><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 39 38"><path fill-rule="evenodd" d="M19.004.667C8.879.667.67 8.875.67 19c0 10.125 8.208 18.333 18.333 18.333 10.125 0 18.333-8.208 18.333-18.333A18.333 18.333 0 0019.004.667zm0 3.249A15.085 15.085 0 0134.09 19c0 8.33-6.754 15.084-15.085 15.084-8.33 0-15.084-6.753-15.084-15.084 0-8.33 6.753-15.084 15.084-15.084zM27.24 18.96c0-.954-.774-1.728-1.728-1.728h-4.82v-4.82a1.729 1.729 0 10-3.457 0v4.82h-4.819a1.729 1.729 0 100 3.457h4.82v4.82a1.729 1.729 0 003.457 0v-4.82h4.819c.954 0 1.728-.774 1.728-1.729z" clip-rule="evenodd"></path><mask id="a" style="mask-type:luminance" width="38" height="38" x="0" y="0" maskUnits="userSpaceOnUse"><path fill-rule="evenodd" d="M19.004.667C8.879.667.67 8.875.67 19c0 10.125 8.208 18.333 18.333 18.333 10.125 0 18.333-8.208 18.333-18.333A18.333 18.333 0 0019.004.667zm0 3.249A15.085 15.085 0 0134.09 19c0 8.33-6.754 15.084-15.085 15.084-8.33 0-15.084-6.753-15.084-15.084 0-8.33 6.753-15.084 15.084-15.084zM27.24 18.96c0-.954-.774-1.728-1.728-1.728h-4.82v-4.82a1.729 1.729 0 10-3.457 0v4.82h-4.819a1.729 1.729 0 100 3.457h4.82v4.82a1.729 1.729 0 003.457 0v-4.82h4.819c.954 0 1.728-.774 1.728-1.729z" clip-rule="evenodd"></path></mask></svg></span>Watch List</a>';
			$data .= '</div>';
		}
		$data .= '</div>';
	}
	return ['data' => $data, 'schema' => $schema];
}

function dramas_with_recent_episode_release(){  // $type = 'upcoming' or 'latest'
	global $wpdb;
	$table_name = $wpdb->prefix.'tmu_dramas_episodes';
	$results = $wpdb->get_results("SELECT $table_name.* FROM $table_name JOIN {$wpdb->prefix}posts AS posts ON ({$table_name}.ID = posts.ID) WHERE unix_timestamp(`air_date`)<=unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) AND posts.post_status = 'publish' ORDER BY `air_date` DESC LIMIT 100");

	$results = array_slice(combine_dramas_episodes($results), 0, 20, true);
	$data = ''; $schema = ''; $count = 0;

	if ($results) {
		$data .= '<div class="module_flexbox">';
		foreach ($results as $result) {
			$post_id = $result['dramas'];
			$post_title = get_the_title($post_id);
			$release_date = ''; $release = $result['air_date']; $release_date = $release ? new DateTime($release) : '';
			$permalink = get_permalink($post_id);
			
			$schema .= ($count !== 0 ? ',' : '' ).'{"@type": "ListItem", "position": "'.(++$count).'", "url": "'.$permalink.'"}';
			$data .= '<div class="latest-posts-module">';
				$data .= '<a class="tv-series-latest" href="'.$permalink.'">';
					$data .= '<div class="poster">';
						$data .= '<img '.(has_post_thumbnail($post_id) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url($post_id, 'full').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ).' alt="'.$post_title.'" width="100%" height="100%">';
					$data .= '</div>';
					
					$data .= '<div class="details">';
						$data .= '<h3>'.$post_title.'</h3>';
						$data .= '<p class="last-episode desktop">'.($release_date ? 'Latest Episode: '.$release_date->format('j F') : '').'</p>';
						$data .= '<p class="last-episode mobile">'.($release_date ? 'Last Ep: '.$release_date->format('j F') : '').'</p>';
					$data .= '</div>';
				$data .= '</a>';
				$data .= '<a href="'.$permalink.'episodes/" class="watch-list"><span><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 39 38"><path fill-rule="evenodd" d="M19.004.667C8.879.667.67 8.875.67 19c0 10.125 8.208 18.333 18.333 18.333 10.125 0 18.333-8.208 18.333-18.333A18.333 18.333 0 0019.004.667zm0 3.249A15.085 15.085 0 0134.09 19c0 8.33-6.754 15.084-15.085 15.084-8.33 0-15.084-6.753-15.084-15.084 0-8.33 6.753-15.084 15.084-15.084zM27.24 18.96c0-.954-.774-1.728-1.728-1.728h-4.82v-4.82a1.729 1.729 0 10-3.457 0v4.82h-4.819a1.729 1.729 0 100 3.457h4.82v4.82a1.729 1.729 0 003.457 0v-4.82h4.819c.954 0 1.728-.774 1.728-1.729z" clip-rule="evenodd"></path><mask id="a" style="mask-type:luminance" width="38" height="38" x="0" y="0" maskUnits="userSpaceOnUse"><path fill-rule="evenodd" d="M19.004.667C8.879.667.67 8.875.67 19c0 10.125 8.208 18.333 18.333 18.333 10.125 0 18.333-8.208 18.333-18.333A18.333 18.333 0 0019.004.667zm0 3.249A15.085 15.085 0 0134.09 19c0 8.33-6.754 15.084-15.085 15.084-8.33 0-15.084-6.753-15.084-15.084 0-8.33 6.753-15.084 15.084-15.084zM27.24 18.96c0-.954-.774-1.728-1.728-1.728h-4.82v-4.82a1.729 1.729 0 10-3.457 0v4.82h-4.819a1.729 1.729 0 100 3.457h4.82v4.82a1.729 1.729 0 003.457 0v-4.82h4.819c.954 0 1.728-.774 1.728-1.729z" clip-rule="evenodd"></path></mask></svg></span>Watch List</a>';
			$data .= '</div>';
		}
		$data .= '</div>';
	}
	return ['data' => $data, 'schema' => $schema];
}




function combine_tv_series_episodes($data){
	$formattedData = array();

	foreach ($data as $item) {
	  $key = "{$item->tv_series}-{$item->air_date}";
	  if (!isset($formattedData[$key])) {
	    $formattedData[$key] = array(
	      'ID' => $item->ID,	
	      'tv_series' => $item->tv_series,
	      'season_no' => $item->season_no,
	      'episode_no' => $item->episode_no,
	      'air_date' => $item->air_date,
	      'episodes' => array(),
	    );
	  }
	  // Include only the last episode number
	  $formattedData[$key]['episodes'][] = $item->episode_no;
	}

	// Sort episodes by episode number in descending order
	foreach ($formattedData as &$show) {
	  usort($show['episodes'], function($a, $b) {
	    return $b <=> $a;
	  });
	  // Keep only the last episode from the sorted array
	  $show['episodes'] = array_slice($show['episodes'], 0, 1);
	  $show['episodes'] = $show['episodes'][0];
	}

	unset($show);

	return $formattedData;
}

function combine_dramas_episodes($data){
	$formattedData = array();

	foreach ($data as $item) {
	  $key = "{$item->dramas}-{$item->air_date}";
	  if (!isset($formattedData[$key])) {
	    $formattedData[$key] = array(
	      'ID' => $item->ID,	
	      'dramas' => $item->dramas,
	      'episode_no' => $item->episode_no,
	      'air_date' => $item->air_date,
	      'episodes' => array(),
	    );
	  }
	  // Include only the last episode number
	  $formattedData[$key]['episodes'][] = $item->episode_no;
	}

	// Sort episodes by episode number in descending order
	foreach ($formattedData as &$show) {
	  usort($show['episodes'], function($a, $b) {
	    return $b <=> $a;
	  });
	  // Keep only the last episode from the sorted array
	  $show['episodes'] = array_slice($show['episodes'], 0, 1);
	  $show['episodes'] = $show['episodes'][0];
	}

	unset($show);

	return $formattedData;
}