<?php

// more like this 

// drama
// same title without number on last,
// same channel & same genre
// same channel
// same genre
// actor known for

// tv series/movie
// similar title without number on last
// similar, genre
// country
// actor known for

// array_unique(array_merge($arr, $arr2));

function more_like_this($post_id){
	global $wpdb;
	$title = get_the_title($post_id);
	$post_type = get_post_type($post_id);
	$title_array = explode(' ', $title);
	$total_words = count($title_array);
	$search_title = $total_words > 2 ? $title_array[0].' '. $title_array[1] : $title_array[0];
	$genres = join(', ', wp_list_pluck(get_the_terms( $post_id, 'genre' ), 'term_id'));

	$results = $wpdb->get_col("SELECT ID FROM $wpdb->posts WHERE post_title LIKE '%{$search_title}%' AND (post_type = 'movie' OR post_type = 'drama' OR post_type = 'tv') AND post_status = 'publish' AND ID != $post_id ORDER BY CASE WHEN post_title LIKE '{$search_title}%' = 1 THEN 1 ELSE 0 END DESC LIMIT 10");
	$results = $results ?? [];

	if ($post_type === 'drama') {
		$channels = join(', ', wp_list_pluck(get_the_terms( $post_id, 'channel' ), 'term_id'));
		
		$results1 = $genres && $channels ? $wpdb->get_col("SELECT t.object_id FROM wp_term_relationships t JOIN wp_posts AS posts ON (t.object_id = posts.ID) WHERE t.term_taxonomy_id IN ($channels) AND t.term_taxonomy_id IN ($genres) AND t.object_id != $post_id AND posts.post_status = 'publish' LIMIT 10") : '';
		$results2 = $channels ? $wpdb->get_col("SELECT t.object_id FROM wp_term_relationships t JOIN wp_posts AS posts ON (t.object_id = posts.ID) WHERE t.term_taxonomy_id IN ($channels) AND t.object_id != $post_id AND posts.post_status = 'publish' LIMIT 10") : '';
		$results = $results1 ? array_unique(array_merge($results, $results1)) : $results;
		$results = $results2 ? array_unique(array_merge($results, $results2)) : $results;
	}

	$results3 = $genres ? $wpdb->get_col("SELECT t.object_id FROM wp_term_relationships t JOIN wp_posts AS posts ON (t.object_id = posts.ID) WHERE t.term_taxonomy_id IN ($genres) AND t.object_id != $post_id AND posts.post_status = 'publish' LIMIT 10") : '';
	$results = $results3 ? array_unique(array_merge($results, $results3)) : $results;

	if ($post_type === 'tv' || $post_type === 'movie') {
		$country = join(', ', wp_list_pluck(get_the_terms( $post_id, 'country' ), 'term_id'));
		$results4 = $country ? $wpdb->get_col("SELECT t.object_id FROM wp_term_relationships t JOIN wp_posts AS posts ON (t.object_id = posts.ID) WHERE t.term_taxonomy_id IN ($country) AND t.object_id != $post_id AND posts.post_status = 'publish' LIMIT 10") : '';
		$results = $results4 ? array_unique(array_merge($results, $results4)) : $results;
	}

	if (!$results) return;

	if (count($results) < 10) {
		$table_name = $wpdb->prefix.'tmu_'.($post_type === 'tv' ? 'tv_series' : ($post_type === 'movie' ? 'movies' : 'dramas'));
		$star_casts = $wpdb->get_var("SELECT star_cast FROM $table_name WHERE `ID` = $post_id");
		$casts = $star_casts ? unserialize($star_casts) : [];
		if (!empty($casts)) {
			foreach ($casts as $cast) {
				if (isset($cast['person']) && $cast['person']) {
					$known_for = $wpdb->get_row("SELECT known_for FROM {$wpdb->prefix}tmu_people WHERE `ID` = $post_id");
					$known_for = $known_for ? unserialize($known_for) : [];
					$results = array_unique(array_merge($results, $known_for));
				}
			}
		}
	}

	foreach (array_keys($results, $post_id) as $key) unset($results[$key]);
	array_splice($results, 10);

	$typeText = $post_type === 'drama' ? 'Dramas' : ($post_type === 'tv' ? 'TV Shows' : ($post_type === 'movie' ? 'Movies' : ''));
	$data = '<div class="more-like-this scrollable-section" data-scroll-target="#more-like-this">
      			<div class="heading">
      				<h2 class="weight-700 font-size-22">More '.$typeText.' Like '.$title.' You’ll Love</h2><div class="scroll-btns"><button class="scroll-btn scroll-more-like-this-left" data-direction="-1" onclick="scrollRelease(this)">'.item_left_arrow().'</button><button class="scroll-btn scroll-more-like-this-right" data-direction="1" onclick="scrollRelease(this)">'.item_right_arrow().'</button></div>
      			</div>
      			<p class="heading-des">
      			If you loved '.$title.', don\'t miss these popular '.($post_type === 'drama' ? 'Pakistani ' : '').$typeText.' similar to '.$title.'.
      			</p>
      			<div class="more-like-this-flex scrollable-content" id="more-like-this">';
	foreach ($results as $result) $data .= more_like_template($result);
	return $data.'</div></div>';
}

function more_like_template($result){
	$permalink = get_permalink($result);
	$title = get_the_title($result);
	return '<a class="item-box" href="'.get_permalink($result).'">
			<div class="item-poster">
				<img '.(has_post_thumbnail($result) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url($result, 'full').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ).' alt="'.$title.'" width="100%" height="100%">
			</div>
			<div class="item-details" href="'.$permalink.'">
				<h3>'.$title.'</h3>
				<div class="genres">'.join(', ', wp_list_pluck(get_the_terms( $result, 'genre' ), 'name')).'</div>
			</div>
		</a>';
}

function item_left_arrow(){
  return '<svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 30 30" class="btn-left">
	  <defs><style> .cls-1 { fill: #fff; } </style> </defs>
	  <rect id="Rectangle_154" data-name="Rectangle 154" width="30" height="30"/>
	  <path id="Icon_material-keyboard-arrow-right" data-name="Icon material-keyboard-arrow-right" class="cls-1" d="M22.885,22.916,16.7,16.722l6.181-6.194-1.9-1.9-8.1,8.1,8.1,8.1Z" transform="translate(-4.084 -1.625)"/>
	</svg>';
}

function item_right_arrow(){
  return '<svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 30 30" class="btn-right">
	  <defs><style>.cls-1 {fill: #fff;}</style></defs>
	  <g id="Group_276" data-name="Group 276" transform="translate(-1290 -476)">
	    <rect id="Rectangle_154" data-name="Rectangle 154" width="30" height="30" transform="translate(1290 476)"/>
	    <path id="Icon_material-keyboard-arrow-right" data-name="Icon material-keyboard-arrow-right" class="cls-1" d="M12.885,22.916l6.181-6.194-6.181-6.194,1.9-1.9,8.1,8.1-8.1,8.1Z" transform="translate(1288.314 474.375)"/>
	  </g>
	</svg>';
}