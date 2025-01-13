<?php

add_shortcode('upcoming', 'upcoming_calendar_posts');
function upcoming_calendar_posts($atts=[]){
	global $wpdb;
	$post_type = isset($atts['post_type']) && $atts['post_type'] ? $atts['post_type'] : 'movie';
	$current_time = current_time( 'timestamp' );
	$end_time = $current_time * 365 * 24 * 60 * 60;
	$data = ''; $count = 0; $schema = '';

	$column = $post_type === 'tv' ? 'tv_series' : ($post_type === 'drama' ? 'dramas' : ($post_type === 'movie' ? $post_type : 'post'));
	$table_name = $wpdb->prefix.'tmu_'.($post_type === 'movie' ? 'movies' : ($post_type === 'drama' ? 'dramas' : ($post_type === 'tv' ? 'tv_series' : 'posts')));
	$results = $column !== 'post' ? $wpdb->get_results("SELECT `ID`,`release_date`,`release_timestamp`,`star_cast`,`average_rating`,`vote_count` FROM $table_name WHERE release_timestamp BETWEEN $current_time AND $end_time ORDER BY `release_timestamp` ASC") : '';

	if ($results) {
		$results_by_day = array();
		foreach ($results as $result) {
			if (!isset($results_by_day[$result->release_date])) $results_by_day[$result->release_date] = array();
		    $results_by_day[$result->release_date][] = $result;
		}
		$schema .= '<script type="application/ld+json">{ "@context": "https://schema.org", "@type": "ItemList", "itemListElement": [';
		$data .= '<link rel="stylesheet" href="'.plugin_dir_url( __DIR__ ) . 'src/css/upcoming-posts.css"><div class="upcoming-container">';
		foreach ($results_by_day as $day => $results) {
			$data .= '<div class="day-column">';
		    	$data .= '<h3>'.date('M d, Y', strtotime($day)).'</h3>';
		    	$data .= '<div class="day-posts-block">';
			    foreach ($results as $result) {
			    	$schema .= ($count !== 0 ? ', ' : '').listitem_schema($result, $count, $post_type); $count++;
			    	$title = get_the_title($result->ID);
			    	$genres = implode(', ', array_map(fn($term) => $term->name, has_term('', 'genre', $result->ID) ? get_the_terms($result->ID, 'genre') : []));
			    	$casts = $result->star_cast ? implode(', ', array_map(fn($cast) => isset($cast['person']) ? get_the_title($cast['person']) : '', unserialize($result->star_cast))) : '';
			    	$data .= '<a href="'.get_permalink($result->ID).'" class="single-result">';
			    		$data .= '<div class="image-container"><div class="result-image"><img src="'.(has_post_thumbnail($result->ID) ? get_the_post_thumbnail_url($result->ID, 'medium') : plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp').'" alt="'.$title.'"></div></div>';
			    		$data .= '<div class="result-details">';
	                    	$data .= '<div class="result-title">'.$title.'</div>';
	                    	$data .= '<div class="result-meta">'.$genres.'</div>';
	                    	$data .= '<div class="result-meta">'.$casts.'</div>';
	                    $data .= '</div>';
			        $data .= '</a>';
			    }
			    $data .= '</div>';
			$data .= '</div>';
		}
		$data .= '</div>';
		$schema .= ']}</script>';
	}
	return $data.$schema;
}



add_shortcode('latest', 'latest_calendar_posts');
function latest_calendar_posts($atts=[]){
	global $wpdb;
	$post_type = isset($atts['post_type']) && $atts['post_type'] ? $atts['post_type'] : 'movie';
	$current_time = current_time( 'timestamp' );
	$data = ''; $count = 0; $schema = '';

	$column = $post_type === 'tv' ? 'tv_series' : ($post_type === 'drama' ? 'dramas' : ($post_type === 'movie' ? $post_type : 'post'));
	$table_name = $wpdb->prefix.'tmu_'.($post_type === 'movie' ? 'movies' : ($post_type === 'drama' ? 'dramas' : ($post_type === 'tv' ? 'tv_series' : 'posts')));
	$results = $column !== 'post' ? $wpdb->get_results("SELECT `ID`,`release_date`,`release_timestamp`,`star_cast`,`average_rating`,`vote_count` FROM $table_name WHERE release_timestamp < $current_time ORDER BY `release_timestamp` DESC") : '';

	if ($results) {
		$results_by_day = array();
		foreach ($results as $result) {
			if (!isset($results_by_day[$result->release_date])) $results_by_day[$result->release_date] = array();
		    $results_by_day[$result->release_date][] = $result;
		}
		$schema .= '<script type="application/ld+json">{ "@context": "https://schema.org", "@type": "ItemList", "itemListElement": [';
		$data .= '<link rel="stylesheet" href="'.plugin_dir_url( __DIR__ ) . 'src/css/upcoming-posts.css"><div class="upcoming-container">';
		foreach ($results_by_day as $day => $results) {
			$data .= '<div class="day-column">';
		    	$data .= '<h3>'.date('M d, Y', strtotime($day)).'</h3>';
		    	$data .= '<div class="day-posts-block">';
			    foreach ($results as $result) {
			    	$schema .= ($count !== 0 ? ', ' : '').listitem_schema($result, $count, $post_type); $count++;
			    	$title = get_the_title($result->ID);
			    	$genres = implode(', ', array_map(fn($term) => $term->name, has_term('', 'genre', $result->ID) ? get_the_terms($result->ID, 'genre') : []));
			    	$casts = $result->star_cast ? implode(', ', array_map(fn($cast) => isset($cast['person']) ? get_the_title($cast['person']) : '', unserialize($result->star_cast))) : '';
			    	$data .= '<a href="'.get_permalink($result->ID).'" class="single-result">';
			    		$data .= '<div class="image-container"><div class="result-image"><img src="'.(has_post_thumbnail($result->ID) ? get_the_post_thumbnail_url($result->ID, 'medium') : plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp').'" alt="'.$title.'"></div></div>';
			    		$data .= '<div class="result-details">';
	                    	$data .= '<div class="result-title">'.$title.'</div>';
	                    	$data .= '<div class="result-meta">'.$genres.'</div>';
	                    	$data .= '<div class="result-meta">'.$casts.'</div>';
	                    $data .= '</div>';
			        $data .= '</a>';
			    }
			    $data .= '</div>';
			$data .= '</div>';
		}
		$data .= '</div>';
		$schema .= ']}</script>';
	}
	return $data.$schema;
}