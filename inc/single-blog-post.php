<?php

add_filter( 'the_content', 'single_blog_post' );
function single_blog_post($content){

	if ( !(get_post_type() == 'post' && is_singular()) ) return $content;

	$post_id = get_the_ID();
	$content = $content ?? get_the_content($post_id);

	$lists = rwmb_meta( 'list_item' );
	$movies = rwmb_meta( 'movies' );
	$tv_series = rwmb_meta( 'tv_show' );
	$dramas = rwmb_meta( 'dramas' );

	$list_schema = '<script type="application/ld+json">['; $schema_count = 0;

	if ($lists) {
		$count = 1;
		foreach ($lists as $list) {
			$content .= '<div class="items-container">';
				if (isset($list['movie']) && $list['movie']) { foreach ($list['movie'] as $item) { $movie = movie_list2_item($item, $count); $content .= $movie['content']; $count++; $schema_count++; $list_schema .= ($schema_count != 1 ? ',
' : '').$movie['schema']; } }
				if (isset($list['tv_show']) && $list['tv_show']) { foreach ($list['tv_show'] as $item) { $tv_show = tv_list2_item($item, $count); $content .= $tv_show['content']; $count++; $schema_count++; $list_schema .= ($schema_count != 1 ? ',
' : '').$tv_show['schema']; } }
				if (isset($list['drama']) && $list['drama']) { foreach ($list['drama'] as $item) { $drama = drama_list2_item($item, $count); $content .= $drama['content']; $count++; $schema_count++; $list_schema .= ($schema_count != 1 ? ',
' : '').$drama['schema']; } }
				$new_content = $list[ 'content' ] ?? '';
				$content .= $new_content ? '<div class="mt-10">'.do_shortcode( wpautop( $new_content ) ).'</div>' : '';
			$content .= '</div>';
		}
	}

	if ($movies) {
		$count = 1;
		$content .= '<div class="items-container">';
			foreach ($movies as $item) { $movie = movie_item_template($item, $count); $content .= $movie['content']; $count++; $schema_count++; $list_schema .= ($schema_count != 1 ? ',
' : '').$movie['schema']; }
		$content .= '</div>';
	}

	if ($tv_series) {
		$count = 1;
		$content .= '<div class="items-container">';
			foreach ($tv_series as $item) { $tv_show = tv_item_template($item, $count); $content .= $tv_show['content']; $count++; $schema_count++; $list_schema .= ($schema_count != 1 ? ',
' : '').$tv_show['schema']; }
		$content .= '</div>';
	}

	if ($dramas) {
		$count = 1;
		$content .= '<div class="items-container">';
			foreach ($dramas as $item) { $drama = drama_item_template($item, $count); $content .= $drama['content']; $count++; $schema_count++; $list_schema .= ($schema_count != 1 ? ',
' : '').$drama['schema']; }
		$content .= '</div>';
	}

	$content .= '<div class="article-bottom-options">';
		$tags = get_the_tags($post_id);
		if ($tags) {
			$content .= '<div class="tags">';
			foreach ($tags as $tag) { $content .= '<a href="'.get_tag_link($tag).'" title="'.$tag->name.'">'.$tag->name.'</a>'; }
			$content .= '</div>';
		}
		$content .= share_icons_blog_post_bottom();
	$content .= '</div>';

	$list_schema .= ']</script>';

	return blog_post_header().$list_schema.$content."<script>
		const textContainers = document.querySelectorAll('.text-container');

		textContainers.forEach(container => {
		  const clampedText = container.querySelector('.clamped-text');
		  const readMoreButton = container.querySelector('.read-more');

		  if (clampedText.scrollHeight > clampedText.offsetHeight) {
		    readMoreButton.style.display = 'block';
		  }

		  readMoreButton.addEventListener('click', () => {
		    clampedText.style.display = 'block';
		    readMoreButton.style.display = 'none';
		  });
		});
	</script>";
}


function movie_item_template($item, $count) {
	$post_id = get_the_ID();
	$title = get_the_title($item);
	$permalink = get_permalink($item);
	// remove_filter( 'the_content', 'single_blog_post' );
	$story = get_the_content(null, false, $item);
	// add_filter( 'the_content', 'single_blog_post' );
	$image_attributes = has_post_thumbnail($item) ? wp_get_attachment_image_src( get_post_thumbnail_id($post_id), 'full' ) : '';
	$image = '<a href="'.$permalink.'" class="feature-image" title="'.$title.'">' . (has_post_thumbnail($item) ? get_the_post_thumbnail($item) : '<img src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp" alt="'.get_the_title($title).'" width="100%" height="100%">' ) . '</a>';
	$star_casts = rwmb_meta( 'star_cast', '', $item );
	$casts = $star_casts ? implode(', ', array_map(function($cast){ return get_linked_post($cast['person']); }, $star_casts)) : '';
	$year = implode(', ', wp_get_object_terms( $item, 'by-year', array('fields' => 'names') ));

	global $wpdb;
	$director1 = $wpdb->get_var("SELECT crew.person FROM {$wpdb->prefix}tmu_movies_crew crew JOIN {$wpdb->prefix}posts AS posts ON (crew.ID = posts.ID) WHERE crew.movie = $item AND crew.job = 'Director' AND posts.post_status = 'publish'");
	$director = $director1 ? get_linked_post($director1) : '';

	$result = $wpdb->get_row("SELECT movie.runtime,movie.certification,movie.average_rating,movie.vote_count FROM {$wpdb->prefix}tmu_movies movie JOIN {$wpdb->prefix}posts AS posts ON (movie.ID = posts.ID) WHERE movie.ID = $item AND posts.post_status = 'publish'");
	$tmdb_rating = [];
	$tmdb_rating['average'] = $result->average_rating;
	$tmdb_rating['count'] = $result->vote_count;
	$comments = get_comments(array('post_id' => $item, 'status' => 'approve'));
	$average_ratings = get_average_ratings($comments, $tmdb_rating);

	$content = '<div class="list1 numbers_of_items"><span class="item-number">'.$count.'</span></div>';
	$content .= '<div class="single-item">';
		$content .= $image;
		$content .= '<div class="singleitem-details">';
			$content .= '<div class="item-head">';
				$content .= '<div class="title-container">';
					$content .= '<h3 class="item-title"><a href="'.$permalink.'" title="'.$title.'">'.$title.'</a>'.($year ? ' <span>('.$year.')</span>' : '').'</h3>';
					$content .= ($average_ratings ? '<div class="item-rating"><svg id="glyphicons-basic" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><path fill="#ffffff" id="star" d="M27.34766,14.17944l-6.39209,4.64307,2.43744,7.506a.65414.65414,0,0,1-.62238.85632.643.643,0,0,1-.38086-.12744l-6.38568-4.6383-6.38574,4.6383a.643.643,0,0,1-.38086.12744.65419.65419,0,0,1-.62238-.85632l2.43744-7.506L4.66046,14.17944A.65194.65194,0,0,1,5.04358,13h7.89978L15.384,5.48438a.652.652,0,0,1,1.24018,0L19.06476,13h7.89978A.652.652,0,0,1,27.34766,14.17944Z"/></svg> <span>'.$average_ratings['average'].'</span></div>' : '');
				$content .= '</div>';
				$content .= '<div class="numbers">#'.$count.'</div>';
			$content .= '</div>';
		$content .= '<p class="text-container"><span class="clamped-text">'.$story.'</span><span class="read-more">Read More</span></p>';
		$content .= $casts ? '<p class="mb-5"><strong>Starring: </strong><span>'.$casts.'</span></p>' : '';
		$content .= $director ? '<p class="mb-5"><strong>Directed By: </strong><span>'.$director.'</span></p>' : '';
		$content .= '</div>';
	$content .='</div>';

	$cast_schema = $star_casts ? implode(', ', array_map(function($cast){ return '
		{
			"@type": "Person",
			"name": "'.get_the_title($cast['person']).'",
			"url": "'.get_permalink($cast['person']).'"
		}'; }, $star_casts)) : '';

	$schema = '
{
	"@type": "Movie",
	"name": "'.$title.'",
	'.($image_attributes ? '"image": {
			"@type": "ImageObject",
			"url": "'.$image_attributes[0].'",
	        "height": "'.$image_attributes[2].'",
	        "width": "'.$image_attributes[1].'"
		},' : '').'
	"description": "'.str_replace('"', '', wp_strip_all_tags($story)).'",
	'.($result->certification ? '"contentRating": "'.$result->certification.'",' : '').'
	'.($result->runtime ? '"duration": "PT'.$result->runtime.'M",' : '').'
	'.($director1 ? '"director": [
		{
			"@type": "Person",
			"name": "'.get_the_title($director1).'",
			"url": "'.get_permalink($director1).'"
		}
	],' : '').'
	'.($cast_schema ? '"actor": ['.$cast_schema.'
		],' : '').'
	"@context": "https://schema.org/"
}';

	return ['content' => $content, 'schema' => $schema];
}

function tv_item_template($item, $count){
	$post_id = get_the_ID();
	$title = get_the_title($item);
	$permalink = get_permalink($item);
	$story = get_the_content(null, false, $item);
	$image_attributes = has_post_thumbnail($item) ? wp_get_attachment_image_src( get_post_thumbnail_id($post_id), 'full' ) : '';
	$image = '<a href="'.$permalink.'" class="feature-image" title="'.$title.'">' . (has_post_thumbnail($item) ? get_the_post_thumbnail($item) : '<img src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp" alt="'.get_the_title($title).'" width="100%" height="100%">' ) . '</a>';
	$star_casts = rwmb_meta( 'star_cast', '', $item );
	$casts = $star_casts ? implode(', ', array_map(function($cast){ return get_linked_post($cast['person']); }, $star_casts)) : '';
	$year = implode(', ', wp_get_object_terms( $item, 'by-year', array('fields' => 'names') ));

	global $wpdb;
	$director1 = $wpdb->get_var("SELECT crew.person FROM {$wpdb->prefix}tmu_tv_series_crew crew JOIN {$wpdb->prefix}posts AS posts ON (crew.ID = posts.ID) WHERE crew.tv_series = $item AND crew.job = 'Director' AND posts.post_status = 'publish'");
	$director = $director1 ? get_linked_post($director1) : '';

	$results = $wpdb->get_row("SELECT tv.certification,tv.last_season,tv.total_average_rating,tv.total_vote_count FROM {$wpdb->prefix}tmu_tv_series tv JOIN {$wpdb->prefix}posts AS posts ON (tv.ID = posts.ID) WHERE ID = $item AND posts.post_status = 'publish'");
	$season = $results->last_season ? $wpdb->get_row("SELECT season.ID,season.season_name FROM {$wpdb->prefix}tmu_tv_series_seasons season JOIN {$wpdb->prefix}posts AS posts ON (season.ID = posts.ID) WHERE season.tv_series = $item AND season.season_no = {$results->last_season} AND posts.post_status = 'publish'") : $results->last_season;
	// $tmdb_rating = [];
	// $tmdb_rating['average'] = $results->average_rating;
	// $tmdb_rating['count'] = $results->vote_count;
	// $comments = get_comments(array('post_id' => $item, 'status' => 'approve'));
	// $average_ratings = get_average_ratings($comments, $tmdb_rating);

	$average_ratings = ['average' => $results->total_average_rating, 'count' => $results->total_vote_count];

	$content = '<div class="single-item">';
		$content .= '<div class="numbers-top">#'.$count.'</div>';
		$content .= $image;
		$content .= '<div class="singleitem-details">';
			$content .= '<div class="item-head">';
				$content .= '<div class="title-container">';
					$content .= '<h3 class="item-title"><a href="'.$permalink.'" title="'.$title.'">'.$title.'</a>'.($year ? ' <span>('.$year.')</span>' : '').'</h3>';
					$content .= ($average_ratings ? '<div class="item-rating"><svg id="glyphicons-basic" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><path fill="#ffffff" id="star" d="M27.34766,14.17944l-6.39209,4.64307,2.43744,7.506a.65414.65414,0,0,1-.62238.85632.643.643,0,0,1-.38086-.12744l-6.38568-4.6383-6.38574,4.6383a.643.643,0,0,1-.38086.12744.65419.65419,0,0,1-.62238-.85632l2.43744-7.506L4.66046,14.17944A.65194.65194,0,0,1,5.04358,13h7.89978L15.384,5.48438a.652.652,0,0,1,1.24018,0L19.06476,13h7.89978A.652.652,0,0,1,27.34766,14.17944Z"/></svg> <span>'.$average_ratings['average'].'</span></div>' : '');
				$content .= '</div>';
				$content .= '<div class="numbers">#'.$count.'</div>';
			$content .= '</div>';
		$content .= '<p class="text-container"><span class="clamped-text">'.$story.'</span><span class="read-more">Read More</span></p>';
		$content .= $casts ? '<p class="mb-5"><strong>Starring: </strong><span>'.$casts.'</span></p>' : '';
		$content .= $director ? '<p class="mb-5"><strong>Directed By: </strong><span>'.$director.'</span></p>' : '';
		$content .= $season ? '<p class="mb-5"><strong>Current Season: </strong><a href="'.get_permalink((int)$season->ID).'" title="'.$season->season_name.'">'.$season->season_name.'</a></p>' : '';
		$content .= '</div>';
	$content .='</div>';

	$cast_schema = $star_casts ? implode(', ', array_map(function($cast){ return '
		{
			"@type": "Person",
			"name": "'.get_the_title($cast['person']).'",
			"url": "'.get_permalink($cast['person']).'"
		}'; }, $star_casts)) : '';

	$schema = '
{
	"@type": "TVSeries",
	"name": "'.$title.'",
	'.($image_attributes ? '"image": {
			"@type": "ImageObject",
			"url": "'.$image_attributes[0].'",
	        "height": "'.$image_attributes[2].'",
	        "width": "'.$image_attributes[1].'"
		},' : '').'
	"description": "'.str_replace('"', '', wp_strip_all_tags($story)).'",
	'.($results->certification ? '"contentRating": "'.$results->certification.'",' : '').'
	'.($director1 ? '"director": [
		{
			"@type": "Person",
			"name": "'.get_the_title($director1).'",
			"url": "'.get_permalink($director1).'"
		}
	],' : '').'
	'.($cast_schema ? '"actor": ['.$cast_schema.'
		],' : '').'
	"@context": "https://schema.org/"
}';

	return ['content' => $content, 'schema' => $schema];
}

function drama_item_template($item, $count){
	$post_id = get_the_ID();
	$title = get_the_title($item);
	$permalink = get_permalink($item);
	$story = get_the_content(null, false, $item);
	$image_attributes = has_post_thumbnail($item) ? wp_get_attachment_image_src( get_post_thumbnail_id($post_id), 'full' ) : '';
	$image = '<a href="'.$permalink.'" class="feature-image" title="'.$title.'">' . (has_post_thumbnail($item) ? get_the_post_thumbnail($item) : '<img src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp" alt="'.$title.'" width="100%" height="100%">' ) . '</a>';
	$star_casts = rwmb_meta( 'star_cast', '', $item );
	$casts = $star_casts ? implode(', ', array_map(function($cast){ return get_linked_post($cast['person']); }, $star_casts)) : '';
	$year = implode(', ', wp_get_object_terms( $item, 'by-year', array('fields' => 'names') ));

	global $wpdb;
	$director1 = $wpdb->get_var("SELECT crew.person FROM {$wpdb->prefix}tmu_dramas_crew crew JOIN {$wpdb->prefix}posts AS posts ON (crew.ID = posts.ID) WHERE crew.dramas = $item AND crew.job = 'Director' AND posts.post_status = 'publish'");
	$director = $director1 ? get_linked_post($director1) : '';

	$results = $wpdb->get_row("SELECT drama.certification,drama.average_rating,drama.vote_count FROM {$wpdb->prefix}tmu_dramas drama JOIN {$wpdb->prefix}posts AS posts ON (drama.ID = posts.ID) WHERE drama.ID = $item AND posts.post_status = 'publish'");
	$tmdb_rating = [];
	$tmdb_rating['average'] = $results->average_rating;
	$tmdb_rating['count'] = $results->vote_count;
	$comments = get_comments(array('post_id' => $item, 'status' => 'approve'));
	$average_ratings = get_average_ratings($comments, $tmdb_rating);

	$content = '<div class="single-item">';
		$content .= '<div class="numbers-top">#'.$count.'</div>';
		$content .= $image;
		$content .= '<div class="singleitem-details">';
			$content .= '<div class="item-head">';
				$content .= '<div class="title-container">';
					$content .= '<h3 class="item-title"><a href="'.$permalink.'" title="'.$title.'">'.$title.'</a>'.($year ? ' <span>('.$year.')</span>' : '').'</h3>';
					$content .= ($average_ratings ? '<div class="item-rating"><svg id="glyphicons-basic" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><path fill="#ffffff" id="star" d="M27.34766,14.17944l-6.39209,4.64307,2.43744,7.506a.65414.65414,0,0,1-.62238.85632.643.643,0,0,1-.38086-.12744l-6.38568-4.6383-6.38574,4.6383a.643.643,0,0,1-.38086.12744.65419.65419,0,0,1-.62238-.85632l2.43744-7.506L4.66046,14.17944A.65194.65194,0,0,1,5.04358,13h7.89978L15.384,5.48438a.652.652,0,0,1,1.24018,0L19.06476,13h7.89978A.652.652,0,0,1,27.34766,14.17944Z"/></svg> <span>'.$average_ratings['average'].'</span></div>' : '');
				$content .= '</div>';
				$content .= '<div class="numbers">#'.$count.'</div>';
			$content .= '</div>';
		$content .= '<p class="text-container"><span class="clamped-text">'.$story.'</span><span class="read-more">Read More</span></p>';
		$content .= $casts ? '<p class="mb-5"><strong>Starring: </strong><span>'.$casts.'</span></p>' : '';
		$content .= $director ? '<p class="mb-5"><strong>Directed By: </strong><span>'.$director.'</span></p>' : '';
		$content .= '</div>';
	$content .='</div>';

	$cast_schema = $star_casts ? implode(', ', array_map(function($cast){ return '
		{
			"@type": "Person",
			"name": "'.get_the_title($cast['person']).'",
			"url": "'.get_permalink($cast['person']).'"
		}'; }, $star_casts)) : '';

	$schema = '
{
	"@type": "TVSeries",
	"name": "'.$title.'",
	'.($image_attributes ? '"image": {
			"@type": "ImageObject",
			"url": "'.$image_attributes[0].'",
	        "height": "'.$image_attributes[2].'",
	        "width": "'.$image_attributes[1].'"
		},' : '').'
	"description": "'.str_replace('"', '', wp_strip_all_tags($story)).'",
	'.($results->certification ? '"contentRating": "'.$results->certification.'",' : '').'
	'.($director1 ? '"director": [
		{
			"@type": "Person",
			"name": "'.get_the_title($director1).'",
			"url": "'.get_permalink($director1).'"
		}
	],' : '').'
	'.($cast_schema ? '"actor": ['.$cast_schema.'
		],' : '').'
	"@context": "https://schema.org/"
}';

	return ['content' => $content, 'schema' => $schema];
}

function movie_list2_item($item, $count) {
	$post_id = get_the_ID();
	$title = get_the_title($item);
	$permalink = get_permalink($item);
	// remove_filter( 'the_content', 'single_blog_post' );
	$story = get_the_content(null, false, $item);
	// add_filter( 'the_content', 'single_blog_post' );
	$image_attributes = has_post_thumbnail($item) ? wp_get_attachment_image_src( get_post_thumbnail_id($post_id), 'full' ) : '';
	$image = '<a href="'.$permalink.'" class="feature-image" title="'.$title.'">' . (has_post_thumbnail($item) ? get_the_post_thumbnail($item) : '<img src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp" alt="'.$title.'" width="100%" height="100%">' ) . '</a>';
	$star_casts = rwmb_meta( 'star_cast', '', $item );
	$casts = $star_casts ? implode(', ', array_map(function($cast){ return get_linked_post($cast['person']); }, $star_casts)) : '';

	global $wpdb;
	$director1 = $wpdb->get_var("SELECT crew.person FROM {$wpdb->prefix}tmu_movies_crew crew JOIN {$wpdb->prefix}posts AS posts ON (crew.ID = posts.ID) WHERE crew.movie = $item AND crew.job = 'Director' AND posts.post_status = 'publish'");
	$director = $director1 ? get_linked_post($director1) : '';

	$result = $wpdb->get_row("SELECT movie.certification,movie.videos,movie.tagline,movie.runtime,movie.release_timestamp,movie.average_rating,movie.vote_count FROM {$wpdb->prefix}tmu_movies movie JOIN {$wpdb->prefix}posts AS posts ON (movie.ID = posts.ID) WHERE movie.ID = $item AND posts.post_status = 'publish'");
	$year = $result->release_timestamp ? date('Y', $result->release_timestamp) : implode(', ', wp_get_object_terms( $item, 'by-year', array('fields' => 'names') ));
	$release_date = $result->release_timestamp ? date('F d, Y', $result->release_timestamp) : '';
	$tmdb_rating = [];
	$tmdb_rating['average'] = $result->average_rating;
	$tmdb_rating['count'] = $result->vote_count;
	$comments = get_comments(array('post_id' => $item, 'status' => 'approve'));
	$average_ratings = get_average_ratings($comments, $tmdb_rating);
	$videos = $result->videos ? unserialize($result->videos) : '';
	$trailer = $videos ? $videos[count($videos)-1] : '';

	$content = '<div class="list2">';
		$content .= '<div class="numbers_of_items"><span class="item-number">'.$count.'</span></div>';
		$content .= '<h2 class="mb-5">'.$title.($year ? ' <span>('.$year.')</span>' : '').'</h2>';
		$content .= $result->tagline ? '<p class="center mb-5">'.$result->tagline.'</p>' : '';
		$content .= '<div class="single-item">';
			$content .= $image;
			$content .= '<div class="singleitem-details">';
				
				$content .= '<div class="item-head">';
					$content .= '<div class="title-container">';
						$content .= '<h3 class="item-title"><a href="'.$permalink.'" title="'.$title.'">'.$title.'</a></h3>';
						$content .= $trailer ? '<a href="'.get_permalink($trailer).'" class="watch-btn" title="Watch Trailer">WATCH TRAILER</a>' : '';
						$content .= ($average_ratings ? '<div class="item-rating"><svg id="glyphicons-basic" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><path fill="#ffffff" id="star" d="M27.34766,14.17944l-6.39209,4.64307,2.43744,7.506a.65414.65414,0,0,1-.62238.85632.643.643,0,0,1-.38086-.12744l-6.38568-4.6383-6.38574,4.6383a.643.643,0,0,1-.38086.12744.65419.65419,0,0,1-.62238-.85632l2.43744-7.506L4.66046,14.17944A.65194.65194,0,0,1,5.04358,13h7.89978L15.384,5.48438a.652.652,0,0,1,1.24018,0L19.06476,13h7.89978A.652.652,0,0,1,27.34766,14.17944Z"/></svg> <span>'.$average_ratings['average'].'</span></div>' : '');
					$content .= '</div>';
				$content .= '</div>';
				
				$content .= '<p class="text-container">'.$story.'</p>';
				
				$content .= '<div class="table-of-item">';
					$content .= $casts ? '<p class="mb-5"><strong>Starring: </strong><span>'.$casts.'</span></p>' : '';
					$content .= $director ? '<p class="mb-5 bt-1"><strong>Directed By: </strong><span>'.$director.'</span></p>' : '';
					$content .= $release_date ? '<p class="mb-5 bt-1"><strong>Release Date: </strong><span>'.$release_date.'</span></p>' : '';
					$content .= $result->runtime ? '<p class="mb-5 bt-1"><strong>Runtime: </strong><span>'.$result->runtime.' minutes</span></p>' : '';
				$content .= '</div>';
			
			$content .= '</div>';
		$content .='</div>';
	$content .= '</div>';

	$cast_schema = $star_casts ? implode(', ', array_map(function($cast){ return '
		{
			"@type": "Person",
			"name": "'.get_the_title($cast['person']).'",
			"url": "'.get_permalink($cast['person']).'"
		}'; }, $star_casts)) : '';

	$schema = '
{
	"@type": "Movie",
	"name": "'.$title.'",
	'.($image_attributes ? '"image": {
			"@type": "ImageObject",
			"url": "'.$image_attributes[0].'",
	        "height": "'.$image_attributes[2].'",
	        "width": "'.$image_attributes[1].'"
		},' : '').'
	"description": "'.str_replace('"', '', wp_strip_all_tags($story)).'",
	'.($result->certification ? '"contentRating": "'.$result->certification.'",' : '').'
	'.($result->runtime ? '"duration": "PT'.$result->runtime.'M",' : '').'
	'.($director1 ? '"director": [
		{
			"@type": "Person",
			"name": "'.get_the_title($director1).'",
			"url": "'.get_permalink($director1).'"
		}
	],' : '').'
	'.($cast_schema ? '"actor": ['.$cast_schema.'
		],' : '').'
	"@context": "https://schema.org/"
}';

	return ['content' => $content, 'schema' => $schema];
}

function tv_list2_item($item, $count) {
	$post_id = get_the_ID();
	$title = get_the_title($item);
	$permalink = get_permalink($item);
	// remove_filter( 'the_content', 'single_blog_post' );
	$story = get_the_content(null, false, $item);
	// add_filter( 'the_content', 'single_blog_post' );
	$image_attributes = has_post_thumbnail($item) ? wp_get_attachment_image_src( get_post_thumbnail_id($post_id), 'full' ) : '';
	$image = '<a href="'.$permalink.'" class="feature-image" title="'.$title.'">' . (has_post_thumbnail($item) ? get_the_post_thumbnail($item) : '<img src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp" alt="'.$title.'" width="100%" height="100%">' ) . '</a>';
	$star_casts = rwmb_meta( 'star_cast', '', $item );
	$casts = $star_casts ? implode(', ', array_map(function($cast){ return get_linked_post($cast['person']); }, $star_casts)) : '';

	global $wpdb;
	$director1 = $wpdb->get_var("SELECT crew.person FROM {$wpdb->prefix}tmu_tv_series_crew crew JOIN {$wpdb->prefix}posts AS posts ON (crew.ID = posts.ID) WHERE crew.tv_series = $item AND crew.job = 'Director' AND posts.post_status = 'publish'");
	$director = $director1 ? get_linked_post($director1) : '';

	$result = $wpdb->get_row("SELECT tv.certification,tv.last_season,tv.streaming_platforms,tv.tagline,tv.runtime,tv.release_timestamp,tv.average_rating,tv.vote_count FROM {$wpdb->prefix}tmu_tv_series tv JOIN {$wpdb->prefix}posts AS posts ON (tv.ID = posts.ID) WHERE tv.ID = $item AND posts.post_status = 'publish'");
	$season = $result->last_season ? $wpdb->get_row("SELECT season.ID,season.season_name FROM {$wpdb->prefix}tmu_tv_series_seasons as season JOIN {$wpdb->prefix}posts AS posts ON (season.ID = posts.ID) WHERE season.tv_series = $item AND season.season_no = {$result->last_season} AND posts.post_status = 'publish'") : '';
	$year = $result->release_timestamp ? date('Y', $result->release_timestamp) : implode(', ', wp_get_object_terms( $item, 'by-year', array('fields' => 'names') ));
	$release_date = $result->release_timestamp ? date('F d, Y', $result->release_timestamp) : '';
	$tmdb_rating = [];
	$tmdb_rating['average'] = $result->average_rating;
	$tmdb_rating['count'] = $result->vote_count;
	$comments = get_comments(array('post_id' => $item, 'status' => 'approve'));
	$average_ratings = get_average_ratings($comments, $tmdb_rating);

	$content = '<div class="list2">';
		$content .= '<div class="numbers_of_items"><span class="item-number">'.$count.'</span></div>';
		$content .= '<h2 class="mb-5">'.$title.($year ? ' <span>('.$year.')</span>' : '').'</h2>';
		$content .= $result->tagline ? '<p class="center mb-5">'.$result->tagline.'</p>' : '';
		$content .= '<div class="single-item">';
			$content .= $image;
			$content .= '<div class="singleitem-details">';
				
				$content .= '<div class="item-head">';
					$content .= '<div class="title-container">';
						$content .= '<h3 class="item-title"><a href="'.$permalink.'" title="'.$title.'">'.$title.'</a></h3>';
						$content .= $result->streaming_platforms ? '<a href="'.$result->streaming_platforms.'" class="watch-btn" title="'.$title.'">WHERE TO WATCH</a>' : '';
						$content .= ($average_ratings ? '<div class="item-rating"><svg id="glyphicons-basic" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><path fill="#ffffff" id="star" d="M27.34766,14.17944l-6.39209,4.64307,2.43744,7.506a.65414.65414,0,0,1-.62238.85632.643.643,0,0,1-.38086-.12744l-6.38568-4.6383-6.38574,4.6383a.643.643,0,0,1-.38086.12744.65419.65419,0,0,1-.62238-.85632l2.43744-7.506L4.66046,14.17944A.65194.65194,0,0,1,5.04358,13h7.89978L15.384,5.48438a.652.652,0,0,1,1.24018,0L19.06476,13h7.89978A.652.652,0,0,1,27.34766,14.17944Z"/></svg> <span>'.$average_ratings['average'].'</span></div>' : '');
					$content .= '</div>';
				$content .= '</div>';
				
				$content .= '<p class="text-container">'.$story.'</p>';
				
				$content .= '<div class="table-of-item">';
					$content .= $casts ? '<p class="mb-5"><strong>Starring: </strong><span>'.$casts.'</span></p>' : '';
					$content .= $director ? '<p class="mb-5 bt-1"><strong>Directed By: </strong><span>'.$director.'</span></p>' : '';
					$content .= $release_date ? '<p class="mb-5 bt-1"><strong>Release Date: </strong><span>'.$release_date.'</span></p>' : '';
					$content .= $result->runtime ? '<p class="mb-5 bt-1"><strong>Runtime: </strong><span>'.$result->runtime.' minutes</span></p>' : '';
					$content .= $season ? '<p class="mb-5 bt-1"><strong>Current Season: </strong><a href="'.get_the_title((int)$season->ID).'" title="'.$season->season_name.'">'.$season->season_name.'</a></p>' : '';
				$content .= '</div>';
			
			$content .= '</div>';
		$content .='</div>';
	$content .= '</div>';

	$cast_schema = $star_casts ? implode(', ', array_map(function($cast){ return '
		{
			"@type": "Person",
			"name": "'.get_the_title($cast['person']).'",
			"url": "'.get_permalink($cast['person']).'"
		}'; }, $star_casts)) : '';

	$schema = '
{
	"@type": "TVSeries",
	"name": "'.$title.'",
	'.($image_attributes ? '"image": {
			"@type": "ImageObject",
			"url": "'.$image_attributes[0].'",
	        "height": "'.$image_attributes[2].'",
	        "width": "'.$image_attributes[1].'"
		},' : '').'
	"description": "'.str_replace('"', '', wp_strip_all_tags($story)).'",
	'.($result->certification ? '"contentRating": "'.$result->certification.'",' : '').'
	'.($director1 ? '"director": [
		{
			"@type": "Person",
			"name": "'.get_the_title($director1).'",
			"url": "'.get_permalink($director1).'"
		}
	],' : '').'
	'.($cast_schema ? '"actor": ['.$cast_schema.'
		],' : '').'
	"@context": "https://schema.org/"
}';

	return ['content' => $content, 'schema' => $schema];
}

function drama_list2_item($item, $count) {
	$post_id = get_the_ID();
	$title = get_the_title($item);
	$permalink = get_permalink($item);
	// remove_filter( 'the_content', 'single_blog_post' );
	$story = get_the_content(null, false, $item);
	// add_filter( 'the_content', 'single_blog_post' );
	$image_attributes = has_post_thumbnail($item) ? wp_get_attachment_image_src( get_post_thumbnail_id($item), 'full' ) : '';
	$image = '<a href="'.$permalink.'" class="feature-image" title="'.$title.'">' . (has_post_thumbnail($item) ? get_the_post_thumbnail($item) : '<img src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp" alt="'.$title.'" width="100%" height="100%">' ) . '</a>';
	$star_casts = rwmb_meta( 'star_cast', '', $item );
	$casts = $star_casts ? implode(', ', array_map(function($cast){ return get_linked_post($cast['person']); }, $star_casts)) : '';

	global $wpdb;
	$director1 = $wpdb->get_var("SELECT crew.person FROM {$wpdb->prefix}tmu_dramas_crew crew JOIN {$wpdb->prefix}posts AS posts ON (crew.ID = posts.ID) WHERE crew.dramas = $item AND crew.job = 'Director' AND posts.post_status = 'publish'");
	$director = $director1 ? get_linked_post($director1) : '';

	$result = $wpdb->get_row("SELECT drama.certification,drama.streaming_platforms,drama.tagline,drama.runtime,drama.release_timestamp,drama.average_rating,drama.vote_count FROM {$wpdb->prefix}tmu_dramas drama JOIN {$wpdb->prefix}posts AS posts ON (drama.ID = posts.ID) WHERE drama.ID = $item AND posts.post_status = 'publish'");
	$year = $result->release_timestamp ? date('Y', $result->release_timestamp) : implode(', ', wp_get_object_terms( $item, 'by-year', array('fields' => 'names') ));
	$release_date = $result->release_timestamp ? date('F d, Y', $result->release_timestamp) : '';
	$tmdb_rating = [];
	$tmdb_rating['average'] = $result->average_rating;
	$tmdb_rating['count'] = $result->vote_count;
	$comments = get_comments(array('post_id' => $item, 'status' => 'approve'));
	$average_ratings = get_average_ratings($comments, $tmdb_rating);

	$content = '<div class="list2">';
		$content .= '<div class="numbers_of_items"><span class="item-number">'.$count.'</span></div>';
		$content .= '<h2 class="mb-5">'.$title.($year ? ' <span>('.$year.')</span>' : '').'</h2>';
		$content .= $result->tagline ? '<p class="center mb-5">'.$result->tagline.'</p>' : '';
		$content .= '<div class="single-item">';
			$content .= $image;
			$content .= '<div class="singleitem-details">';
				
				$content .= '<div class="item-head">';
					$content .= '<div class="title-container">';
						$content .= '<h3 class="item-title"><a href="'.$permalink.'" title="'.$title.'">'.$title.'</a></h3>';
						$content .= $result->streaming_platforms ? '<a href="'.$result->streaming_platforms.'" class="watch-btn" title="Where to watch">WHERE TO WATCH</a>' : '';
						$content .= ($average_ratings ? '<div class="item-rating"><svg id="glyphicons-basic" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><path fill="#ffffff" id="star" d="M27.34766,14.17944l-6.39209,4.64307,2.43744,7.506a.65414.65414,0,0,1-.62238.85632.643.643,0,0,1-.38086-.12744l-6.38568-4.6383-6.38574,4.6383a.643.643,0,0,1-.38086.12744.65419.65419,0,0,1-.62238-.85632l2.43744-7.506L4.66046,14.17944A.65194.65194,0,0,1,5.04358,13h7.89978L15.384,5.48438a.652.652,0,0,1,1.24018,0L19.06476,13h7.89978A.652.652,0,0,1,27.34766,14.17944Z"/></svg> <span>'.$average_ratings['average'].'</span></div>' : '');
					$content .= '</div>';
				$content .= '</div>';
				
				$content .= '<p class="text-container">'.$story.'</p>';
				
				$content .= '<div class="table-of-item">';
					$content .= $casts ? '<p class="mb-5"><strong>Starring: </strong><span>'.$casts.'</span></p>' : '';
					$content .= $director ? '<p class="mb-5 bt-1"><strong>Directed By: </strong><span>'.$director.'</span></p>' : '';
					$content .= $release_date ? '<p class="mb-5 bt-1"><strong>Release Date: </strong><span>'.$release_date.'</span></p>' : '';
					$content .= $result->runtime ? '<p class="mb-5 bt-1"><strong>Runtime: </strong><span>'.$result->runtime.' minutes</span></p>' : '';
				$content .= '</div>';
			
			$content .= '</div>';
		$content .='</div>';
	$content .= '</div>';

	$cast_schema = $star_casts ? implode(', ', array_map(function($cast){ return '
		{
			"@type": "Person",
			"name": "'.get_the_title($cast['person']).'",
			"url": "'.get_permalink($cast['person']).'"
		}'; }, $star_casts)) : '';

	$schema = '
{
	"@type": "TVSeries",
	"name": "'.$title.'",
	'.($image_attributes ? '"image": {
			"@type": "ImageObject",
			"url": "'.$image_attributes[0].'",
	        "height": "'.$image_attributes[2].'",
	        "width": "'.$image_attributes[1].'"
		},' : '').'
	"description": "'.str_replace('"', '', wp_strip_all_tags($story)).'",
	'.($result->certification ? '"contentRating": "'.$result->certification.'",' : '').'
	'.($director1 ? '"director": [
		{
			"@type": "Person",
			"name": "'.get_the_title($director1).'",
			"url": "'.get_permalink($director1).'"
		}
	],' : '').'
	'.($cast_schema ? '"actor": ['.$cast_schema.'
		],' : '').'
	"@context": "https://schema.org/"
}';

	return ['content' => $content, 'schema' => $schema];
}

function blog_post_header() {
	$author_id = get_the_author_meta( 'ID' );
	$author_name = get_the_author_meta( 'display_name', $author_id );
	$author_url = get_author_posts_url( $author_id );
	$autho_img = get_avatar_url($author_id, ['size' => '40']);

	$publish_date = get_the_date('F d, Y');
	// $update_date = get_the_modified_date('F d, Y');

	$header = '<div class="blog-post-header">';
		$header .= '<h1>'.get_the_title().'</h1>';
		$header .= '<div class="meta-info-mobile">';
			$header .= '<div class="post-author">';
				$header .= '<div class="author-img"><img src="'.$autho_img.'" alt="'.$author_name.'"></div>';
				$header .= '<div class="author-meta-text">By <a class="author-name" href="'.$author_url.'" title="'.$author_name.'">'.$author_name.'</a></div>';
			$header .= '</div>';
			$header .= '<div class="publish-date">Published '.$publish_date.'</div>';
		$header .= '</div>';
		$header .= has_post_thumbnail() ? '<div class="post-thumb">'.get_the_post_thumbnail().'</div>' : '';
		$header .= '<div class="blog-post-meta">';
			$header .= '<div class="meta-info">';
				$header .= '<div class="post-author">';
					$header .= '<div class="author-img"><img src="'.$autho_img.'" alt="'.$author_name.'"></div>';
					$header .= '<div class="author-meta-text">By <a class="author-name" href="'.$author_url.'" title="'.$author_name.'">'.$author_name.'</a></div>';
				$header .= '</div>';
				$header .= '<div class="publish-date">Published '.$publish_date.'</div>';
			$header .= '</div>';

			$header .= '<div class="option-btns">';
				$header .= '<a href="#comment" class="thread-comments" title="Thread"><span class="icon-thread"><?xml version="1.0" encoding="utf-8"?>
							<svg height="20" width="20" version="1.1" id="_x32_" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" 
							 viewBox="0 0 512 512"  xml:space="preserve"><g><path class="st0" d="M92.574,294.24V124.336H43.277C19.449,124.336,0,144.213,0,168.467v206.44c0,24.254,19.449,44.133,43.277,44.133h62v45.469c0,3.041,1.824,5.777,4.559,6.932c2.736,1.154,5.957,0.486,8.023-1.641l49.844-50.76h106.494c23.828,0,43.279-19.879,43.279-44.133v-0.061H172.262C128.314,374.846,92.574,338.676,92.574,294.24z"/><path class="st0" d="M462.717,40H172.26c-27.105,0-49.283,22.59-49.283,50.197v204.037c0,27.61,22.178,50.199,49.283,50.199 h164.668l75.348,76.033c2.399,2.442,6.004,3.172,9.135,1.852c3.133-1.322,5.176-4.434,5.176-7.887v-69.998h36.131 c27.106,0,49.283-22.59,49.283-50.199V90.197C512,62.59,489.822,40,462.717,40z M369.156,280.115H195.92v-24.316h173.236V280.115z M439.058,204.129H195.92v-24.314h243.138V204.129z M439.058,128.143H195.92v-24.315h243.138V128.143z"/></g>
							</svg></span> Thread</a>';

				$header .= '<div class="share-icons">';
					$header .= '<div class="share-icon"><svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="18" height="18" viewBox="0 0 26 26">
								<path d="M 21 0 C 18.238281 0 16 2.238281 16 5 C 16 5.085938 16.027344 5.164063 16.03125 5.25 L 8.1875 9.1875 C 7.320313 8.457031 6.222656 8 5 8 C 2.238281 8 0 10.238281 0 13 C 0 15.761719 2.238281 18 5 18 C 6.222656 18 7.320313 17.542969 8.1875 16.8125 L 16.03125 20.75 C 16.027344 20.835938 16 20.914063 16 21 C 16 23.761719 18.238281 26 21 26 C 23.761719 26 26 23.761719 26 21 C 26 18.238281 23.761719 16 21 16 C 19.777344 16 18.679688 16.457031 17.8125 17.1875 L 9.96875 13.25 C 9.972656 13.164063 10 13.085938 10 13 C 10 12.914063 9.972656 12.835938 9.96875 12.75 L 17.8125 8.8125 C 18.679688 9.542969 19.777344 10 21 10 C 23.761719 10 26 7.761719 26 5 C 26 2.238281 23.761719 0 21 0 Z"></path>
								</svg><div>Share</div></div>';
					$header .= share_icons_blog_post();
				$header .= '</div>';
			$header .= '</div>';
		$header .= '</div>';
	$header .= '</div>';

	return $header;
}

function share_icons_blog_post(){
	$permalink = get_permalink();
	$title = get_the_title();
	return '<div class="social-sharing-icons">
    <a class="social-icon facebook" href="https://www.facebook.com/sharer/sharer.php?u='.$permalink.'" title="Share on facebook">
      <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="24" height="24" viewBox="0,0,256,256">
        <g transform="translate(-16.64,-16.64) scale(1.13,1.13)"><g fill="#0073c2" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none" font-size="none" text-anchor="none" style="mix-blend-mode: normal"><g transform="scale(5.12,5.12)"><path d="M42,3h-34c-2.8,0 -5,2.2 -5,5v34c0,2.8 2.2,5 5,5h34c2.8,0 5,-2.2 5,-5v-34c0,-2.8 -2.2,-5 -5,-5zM37,19h-2c-2.1,0 -3,0.5 -3,2v3h5l-1,5h-4v16h-5v-16h-4v-5h4v-3c0,-4 2,-7 6,-7c2.9,0 4,1 4,1z"></path></g></g></g>
      </svg> Facebook
    </a>
    <a class="social-icon twitter" href="https://twitter.com/intent/tweet?text='.$title.'&url='.$permalink.'" title="Share on twitter">
      <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="24" height="24" viewBox="0,0,256,256">
        <g transform="translate(-24.32,-24.32) scale(1.19,1.19)"><g fill="#000000" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none" font-size="none" text-anchor="none" style="mix-blend-mode: normal"><g transform="scale(5.12,5.12)"><path d="M11,4c-3.866,0 -7,3.134 -7,7v28c0,3.866 3.134,7 7,7h28c3.866,0 7,-3.134 7,-7v-28c0,-3.866 -3.134,-7 -7,-7zM13.08594,13h7.9375l5.63672,8.00977l6.83984,-8.00977h2.5l-8.21094,9.61328l10.125,14.38672h-7.93555l-6.54102,-9.29297l-7.9375,9.29297h-2.5l9.30859,-10.89648zM16.91406,15l14.10742,20h3.06445l-14.10742,-20z"></path></g></g></g>
      </svg> X
    </a>
    <a class="social-icon whatsapp" href="https://api.whatsapp.com/send?text='.$title.': '.$permalink.'" title="Share on whatsapp">
      <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="24" height="24" viewBox="0,0,256,256">
        <g transform="translate(-29.44,-29.44) scale(1.23,1.23)"><g fill="#45d354" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none" font-size="none" text-anchor="none" style="mix-blend-mode: normal"><g transform="scale(8.53333,8.53333)"><path d="M15,3c-6.627,0 -12,5.373 -12,12c0,2.25121 0.63234,4.35007 1.71094,6.15039l-1.60352,5.84961l5.97461,-1.56836c1.74732,0.99342 3.76446,1.56836 5.91797,1.56836c6.627,0 12,-5.373 12,-12c0,-6.627 -5.373,-12 -12,-12zM10.89258,9.40234c0.195,0 0.39536,-0.00119 0.56836,0.00781c0.214,0.005 0.44692,0.02067 0.66992,0.51367c0.265,0.586 0.84202,2.05608 0.91602,2.20508c0.074,0.149 0.12644,0.32453 0.02344,0.51953c-0.098,0.2 -0.14897,0.32105 -0.29297,0.49805c-0.149,0.172 -0.31227,0.38563 -0.44727,0.51563c-0.149,0.149 -0.30286,0.31238 -0.13086,0.60938c0.172,0.297 0.76934,1.27064 1.65234,2.05664c1.135,1.014 2.09263,1.32561 2.39063,1.47461c0.298,0.149 0.47058,0.12578 0.64258,-0.07422c0.177,-0.195 0.74336,-0.86411 0.94336,-1.16211c0.195,-0.298 0.39406,-0.24644 0.66406,-0.14844c0.274,0.098 1.7352,0.8178 2.0332,0.9668c0.298,0.149 0.49336,0.22275 0.56836,0.34375c0.077,0.125 0.07708,0.72006 -0.16992,1.41406c-0.247,0.693 -1.45991,1.36316 -2.00391,1.41016c-0.549,0.051 -1.06136,0.24677 -3.56836,-0.74023c-3.024,-1.191 -4.93108,-4.28828 -5.08008,-4.48828c-0.149,-0.195 -1.21094,-1.61031 -1.21094,-3.07031c0,-1.465 0.76811,-2.18247 1.03711,-2.48047c0.274,-0.298 0.59492,-0.37109 0.79492,-0.37109z"></path></g></g></g>
      </svg> Whatsapp
    </a>
    <a class="social-icon telegram" href="https://t.me/share/url?url='.$permalink.'&text='.$title.'&to=" title="Share on telegram">
      <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="24" height="24" viewBox="0,0,256,256">
        <g transform="translate(-10.24,-10.24) scale(1.08,1.08)"><g fill="#02c8f0" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none" font-size="none" text-anchor="none" style="mix-blend-mode: normal"><g transform="scale(5.12,5.12)"><path d="M25,2c12.703,0 23,10.297 23,23c0,12.703 -10.297,23 -23,23c-12.703,0 -23,-10.297 -23,-23c0,-12.703 10.297,-23 23,-23zM32.934,34.375c0.423,-1.298 2.405,-14.234 2.65,-16.783c0.074,-0.772 -0.17,-1.285 -0.648,-1.514c-0.578,-0.278 -1.434,-0.139 -2.427,0.219c-1.362,0.491 -18.774,7.884 -19.78,8.312c-0.954,0.405 -1.856,0.847 -1.856,1.487c0,0.45 0.267,0.703 1.003,0.966c0.766,0.273 2.695,0.858 3.834,1.172c1.097,0.303 2.346,0.04 3.046,-0.395c0.742,-0.461 9.305,-6.191 9.92,-6.693c0.614,-0.502 1.104,0.141 0.602,0.644c-0.502,0.502 -6.38,6.207 -7.155,6.997c-0.941,0.959 -0.273,1.953 0.358,2.351c0.721,0.454 5.906,3.932 6.687,4.49c0.781,0.558 1.573,0.811 2.298,0.811c0.725,0 1.107,-0.955 1.468,-2.064z"></path></g></g></g>
      </svg> Telegram
    </a>
  </div>';
}


function share_icons_blog_post_bottom(){
	$permalink = get_permalink();
	$title = get_the_title();
	return '<div class="social-sharing-icons-bottom">
    <a class="social-icon facebook" href="https://www.facebook.com/sharer/sharer.php?u='.$permalink.'">
      <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="30" height="30" viewBox="0,0,256,256">
        <g transform="translate(-16.64,-16.64) scale(1.13,1.13)"><g fill="#0073c2" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none" font-size="none" text-anchor="none" style="mix-blend-mode: normal"><g transform="scale(5.12,5.12)"><path d="M42,3h-34c-2.8,0 -5,2.2 -5,5v34c0,2.8 2.2,5 5,5h34c2.8,0 5,-2.2 5,-5v-34c0,-2.8 -2.2,-5 -5,-5zM37,19h-2c-2.1,0 -3,0.5 -3,2v3h5l-1,5h-4v16h-5v-16h-4v-5h4v-3c0,-4 2,-7 6,-7c2.9,0 4,1 4,1z"></path></g></g></g>
      </svg>
    </a>
    <a class="social-icon twitter" href="https://twitter.com/intent/tweet?text='.$title.'&url='.$permalink.'">
      <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="30" height="30" viewBox="0,0,256,256">
        <g transform="translate(-24.32,-24.32) scale(1.19,1.19)"><g fill="#000000" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none" font-size="none" text-anchor="none" style="mix-blend-mode: normal"><g transform="scale(5.12,5.12)"><path d="M11,4c-3.866,0 -7,3.134 -7,7v28c0,3.866 3.134,7 7,7h28c3.866,0 7,-3.134 7,-7v-28c0,-3.866 -3.134,-7 -7,-7zM13.08594,13h7.9375l5.63672,8.00977l6.83984,-8.00977h2.5l-8.21094,9.61328l10.125,14.38672h-7.93555l-6.54102,-9.29297l-7.9375,9.29297h-2.5l9.30859,-10.89648zM16.91406,15l14.10742,20h3.06445l-14.10742,-20z"></path></g></g></g>
      </svg>
    </a>
    <a class="social-icon whatsapp" href="https://api.whatsapp.com/send?text='.$title.': '.$permalink.'">
      <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="30" height="30" viewBox="0,0,256,256">
        <g transform="translate(-29.44,-29.44) scale(1.23,1.23)"><g fill="#45d354" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none" font-size="none" text-anchor="none" style="mix-blend-mode: normal"><g transform="scale(8.53333,8.53333)"><path d="M15,3c-6.627,0 -12,5.373 -12,12c0,2.25121 0.63234,4.35007 1.71094,6.15039l-1.60352,5.84961l5.97461,-1.56836c1.74732,0.99342 3.76446,1.56836 5.91797,1.56836c6.627,0 12,-5.373 12,-12c0,-6.627 -5.373,-12 -12,-12zM10.89258,9.40234c0.195,0 0.39536,-0.00119 0.56836,0.00781c0.214,0.005 0.44692,0.02067 0.66992,0.51367c0.265,0.586 0.84202,2.05608 0.91602,2.20508c0.074,0.149 0.12644,0.32453 0.02344,0.51953c-0.098,0.2 -0.14897,0.32105 -0.29297,0.49805c-0.149,0.172 -0.31227,0.38563 -0.44727,0.51563c-0.149,0.149 -0.30286,0.31238 -0.13086,0.60938c0.172,0.297 0.76934,1.27064 1.65234,2.05664c1.135,1.014 2.09263,1.32561 2.39063,1.47461c0.298,0.149 0.47058,0.12578 0.64258,-0.07422c0.177,-0.195 0.74336,-0.86411 0.94336,-1.16211c0.195,-0.298 0.39406,-0.24644 0.66406,-0.14844c0.274,0.098 1.7352,0.8178 2.0332,0.9668c0.298,0.149 0.49336,0.22275 0.56836,0.34375c0.077,0.125 0.07708,0.72006 -0.16992,1.41406c-0.247,0.693 -1.45991,1.36316 -2.00391,1.41016c-0.549,0.051 -1.06136,0.24677 -3.56836,-0.74023c-3.024,-1.191 -4.93108,-4.28828 -5.08008,-4.48828c-0.149,-0.195 -1.21094,-1.61031 -1.21094,-3.07031c0,-1.465 0.76811,-2.18247 1.03711,-2.48047c0.274,-0.298 0.59492,-0.37109 0.79492,-0.37109z"></path></g></g></g>
      </svg>
    </a>
    <a class="social-icon telegram" href="https://t.me/share/url?url='.$permalink.'&text='.$title.'&to=">
      <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="30" height="30" viewBox="0,0,256,256">
        <g transform="translate(-10.24,-10.24) scale(1.08,1.08)"><g fill="#02c8f0" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none" font-size="none" text-anchor="none" style="mix-blend-mode: normal"><g transform="scale(5.12,5.12)"><path d="M25,2c12.703,0 23,10.297 23,23c0,12.703 -10.297,23 -23,23c-12.703,0 -23,-10.297 -23,-23c0,-12.703 10.297,-23 23,-23zM32.934,34.375c0.423,-1.298 2.405,-14.234 2.65,-16.783c0.074,-0.772 -0.17,-1.285 -0.648,-1.514c-0.578,-0.278 -1.434,-0.139 -2.427,0.219c-1.362,0.491 -18.774,7.884 -19.78,8.312c-0.954,0.405 -1.856,0.847 -1.856,1.487c0,0.45 0.267,0.703 1.003,0.966c0.766,0.273 2.695,0.858 3.834,1.172c1.097,0.303 2.346,0.04 3.046,-0.395c0.742,-0.461 9.305,-6.191 9.92,-6.693c0.614,-0.502 1.104,0.141 0.602,0.644c-0.502,0.502 -6.38,6.207 -7.155,6.997c-0.941,0.959 -0.273,1.953 0.358,2.351c0.721,0.454 5.906,3.932 6.687,4.49c0.781,0.558 1.573,0.811 2.298,0.811c0.725,0 1.107,-0.955 1.468,-2.064z"></path></g></g></g>
      </svg>
    </a>
  </div>';
}