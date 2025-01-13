<?php

add_filter( 'the_content', 'single_movie' );
function single_movie($content){
  if ( !(get_post_type() == 'movie' && is_singular()) ) return $content;

  $post_id = get_the_ID();
  $title = get_the_title();
  global $wpdb;
  $movie = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}tmu_movies WHERE `ID` = %d", $post_id), ARRAY_A);

  $movie['star_cast'] = $star_cast = $movie['star_cast'] ? unserialize($movie['star_cast']) : [];
  // $movie['credits'] = $movie['credits'] ? unserialize($movie['credits']) : ['cast' => [], 'crew' => [] ];
  $movie['runtime'] = $movie['runtime'] ? $movie['runtime'].' minutes' : '';
  // $runtime_hours = round($movie['runtime']/60);
  // $movie['runtime'] = $movie['runtime'] ? ($runtime_hours==0 ? '' : $runtime_hours.'h ').($movie['runtime']%60).'m' : '';
  $movie['images'] = rwmb_meta( 'images', [ 'size' => 'thumbnail' ] );
  $movie['videos'] = $movie['videos'] ? unserialize($movie['videos']) : [];

  // $tmdb_rating['average'] = $movie['average_rating'];
  // $tmdb_rating['count'] = $movie['vote_count'];

  // $comments = get_comments(array('post_id' => $post_id, 'status' => 'approve'));
  // $average_ratings = get_average_ratings($comments, $tmdb_rating);

  $average_ratings = ['average' => $movie['total_average_rating'], 'count' => $movie['total_vote_count']];

  global $wpdb;
	$cast_table = $wpdb->prefix . 'tmu_movies_cast';
	$cast = $wpdb->get_results($wpdb->prepare("SELECT * FROM $cast_table WHERE movie = %d", $post_id));

	$crew_table = $wpdb->prefix . 'tmu_movies_crew';
	$crew = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$crew_table} WHERE movie = %d", $post_id));

	$director_array = get_movies_credit_by_job($crew, 'Director');
	$director_alt = $director_array ? NULL : $wpdb->get_row($wpdb->prepare("SELECT person,job FROM $crew_table WHERE movie = %d AND (job LIKE '%Co-Director%' OR job LIKE '%Assistant Director%') ORDER BY CASE WHEN job LIKE '%Co-Director%' THEN 1 ELSE 2 END", $post_id));
	$director_array = $director_alt ? ['permalink' => get_permalink($director_alt->person), 'name' => get_the_title($director_alt->person)] : $director_array;

	$producer_array = get_movies_credit_by_job($crew, 'Producer');
	$producer_alt = $producer_array ? NULL : $wpdb->get_row($wpdb->prepare("SELECT person,job FROM $crew_table WHERE movie = %d AND (job LIKE '%Co-Producer%' OR job LIKE '%Executive Producer%' OR job LIKE '%Associate Producer%') ORDER BY CASE WHEN job LIKE '%Co-Producer%' THEN 1 ELSE 2 END", $post_id));
	$producer_array = $producer_alt ? ['permalink' => get_permalink($producer_alt->person), 'name' => get_the_title($producer_alt->person)] : $producer_array;

	$writer_array = get_movies_credit_by_job($crew, 'Writer');
	$musician_array = get_movies_credit_by_job($crew, 'Musician');

	$director = $director_array ? '<a href="'.$director_array['permalink'].'" class="credits-member" title="'.$director_array['name'].' Movies">'.$director_array['name'].'</a>' : '';
	$director_job = $director_alt ? $director_alt->job : 'Director';
	$producer = $producer_array ? '<a href="'.$producer_array['permalink'].'" class="credits-member" title="'.$producer_array['name'].' Movies">'.$producer_array['name'].'</a>' : '';
	$producer_job = $producer_alt ? $producer_alt->job : 'Producer';
	$writer = $writer_array ? '<a href="'.$writer_array['permalink'].'" class="credits-member" title="'.$writer_array['name'].' Movies">'.$writer_array['name'].'</a>' : '';
	$musician = $musician_array ? '<a href="'.$musician_array['permalink'].'" class="credits-member" title="'.$musician_array['name'].' Movies">'.$musician_array['name'].'</a>' : '';
	$related_news = get_related_news($title, 4);
	// echo default_schema($title, $_SERVER['REQUEST_URI']);
  $data = '';
	  $data .= '<div class="movie_sec">';
		$data .= breadcrumb();
		$data .= '<div class="grid_movie_sec">';
			$data .= movie_header($movie, $director, $director_job, $producer, $producer_job, $average_ratings);
			
			if ($movie['star_cast']) { 
				$data .= '<div class="star_casts">';
					$data .= '<div class="short-heading font-size-22 weight-700">';
						$data .= '<h2 class="images-title weight-700 font-size-22">Star Cast</h2>';
					$data .= '</div>';
					$data .= '<p>Meet the talented star cast of '.$title.' Movie.</p>';
					$data .= the_star_cast($movie['star_cast']); //display_credits_with_image($movie['star_cast']) 
				$data .= '</div>';
			}
		$data .= '</div>';

		if($content):
		$data .= '<div class="movie-story">';
			$data .= '<div class="heading">';
				$data .= '<h2 class="weight-700 font-size-22">'.$title.' Movie Plot:</h2>';
			$data .= '</div>';
			$data .= '<div>'.$content.'</div>';
		$data .= '</div>';
		endif;

		$data .= $movie['videos'] ? display_videos($movie['videos']) : '';
		$data .= $movie['images'] ? display_images($movie['images']) : '';

		$data .= '<div class="cast_crew" id="cast-and-crew">';
			$data .= '<div class="short-heading font-size-22 weight-700">';
				$data .= '<h2 class="images-title weight-700 font-size-22">'.$title.' Cast & Crew</h2>';
			$data .= '</div>';
			$data .= '<p>Check out the '.$title.' Cast & Crew, showcasing the talented cast and the crew that worked tirelessly to bring the '.$title.' movie to the screen.</p>';
			$data .= '<ul class="nav_tabs">';
				$data .= '<li class="tab_item active">Cast</li>';
				$data .= '<li class="tab_item">Crew</li>';
			$data .= '</ul>';
			$data .= '<div class="full_cast" id="cast">'.movies_credits($cast, 'cast').'</div>';
			$data .= '<div class="full_crew" id="crew" style="display: none;">'.movies_credits($crew, 'crew').'</div>';
		$data .= '</div>';

		$data .= more_like_this($post_id);
		
		$data .= movie_details_table($movie, $director, $director_job, $producer, $producer_job, $writer, $musician);

		if ($related_news):
			$data .= '<div class="related-news">';
				$data .= '<h2 class="short-heading weight-700 font-size-36 news-title">'.$title.' Movie News – Latest Updates and Stories</h2>';
				$data .= '<p>Stay informed with all the '.$title.' Movie related news. From exclusive insights to breaking updates, plot twists, cast news and more so don’t miss any important story about '.$title.'.</p>';
				$data .= $related_news;
			$data .= '</div>';
		endif;
		
		if($keywords = get_linked_terms('keyword', '')):
		$data .= '<div class="keywords">';
			$data .= '<div class="heading-2">Keywords:</div>';
			$data .= '<div class="tags">'.$keywords.'</div>';
		$data .= '</div>';
		endif;

	$data .= '</div>';
  $data .= cast_crew_tabs_script();
  return $data;
}

function movie_header($movie, $director, $director_job, $producer, $producer_job, $average_ratings){
	$genres = get_linked_terms('genre');
	$last = count($movie['videos'])-1;
	$title = get_the_title();
  $data = '';
  $data .= '<section class="movie-trailer">';
  $data .= '<div class="feature-image"><img '. (has_post_thumbnail() ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url(get_the_ID(), 'full').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ).' alt="'.$title.'" width="100%" height="100%"></div>';
  $data .= '<div class="movie_details">';
  $data .= '<h1 class="font-size-36">'.$title.'</h1>';
	$data .= '<div class="rating-box">'.get_post_rating($average_ratings).'<div class="rate-this">'.rate_this().'</div></div>';
	$data .= '<div class="release">'.($movie['release_timestamp'] ? date( 'Y', $movie['release_timestamp'] ).' <span class="sep">|</span> ' : '').($movie['certification'] ? $movie['certification'].' <span class="sep">|</span> ' : '').($movie['runtime'] ? $movie['runtime'].' <span class="sep">|</span> ' : '').($genres ? '<span class="genres">'.$genres.'</span>' : '').'</div>';
	$data .= '<div class="credits_main">';
		if ($director) { $data .= '<div'.(!$producer ? ' class="width-100"' : '').'>'.$director.'<div class="font-size-13">'.$director_job.'</div></div>'; } 
		if ($producer) { $data .= '<div'.(!$director ? ' class="width-100"' : '').'>'.$producer.'<div class="font-size-13">'.$producer_job.'</div></div>'; } 
	$data .= '</div>';

	$data .= '<div class="watch-buttons">';
		if($movie['streaming_platforms']):
			$data .= '<a href="https://www.eonline.live/redirect/'.urlencode($movie['streaming_platforms']).'" title="WHERE TO WATCH">WHERE TO WATCH</a>';
		endif;
		if (isset($movie['videos'][$last])):
			$data .= '<a href="'.get_permalink($movie['videos'][$last]).'" title="WATCH TRAILER">WATCH TRAILER</a>';
		endif;
	$data .= '</div>';
	$data .= '</div>';
  $data .= '</section>';
  return $data;
}

function movie_details_table($movie, $director, $director_job, $producer, $producer_job, $writer, $musician){
	$title = get_the_title();
	$release = $movie['release_date'] ? date( 'd F Y', $movie['release_timestamp'] ) : '';
	$starcast = implode(', ', array_map(function ($cast) { if(isset($cast['person'])) return get_linked_post($cast['person']); }, $movie['star_cast']));
  $data = '';
	$data .= '<div>';
		$data .= '<h2 class="details-section-heading heading-2">'.$title.' Movie - Release Date, Cast, Trailer, Review and Other Details</h2>';
		$data .= '<p class="movie_intro">'.($release ? $title.' '.(strtotime('today GMT') > $movie[ 'release_timestamp' ] ? 'was released in' : 'is all set to hit').' theaters on '.$release.'.' : '').' '.$title.($director ? ' directed by '.$director.($starcast ? ' and ' : '') : ' ').($starcast ? $starcast.' played the primary leads.':'') .'</p>';
		$data .= '<table class="movie-details-section">';
			$data .= '<tr class="detail-item"><th class="item-title">Status</th><td class="item-value">'.$movie['release_timestamp'] > time() ? 'Upcoming' : 'Released' .'</td></tr>';
			if($movie[ 'release_timestamp' ]) { $data .= '<tr class="detail-item"><th class="item-title">Release Date</th><td class="item-value">'.$release.'</td></tr>'; } 
			if($movie[ 'original_title' ]) { $data .= '<tr class="detail-item"><th class="item-title">Original Title</th><td class="item-value">'.$movie[ 'original_title' ].'</td></tr>'; } 
			if($movie[ 'tagline' ]) { $data .= '<tr class="detail-item"><th class="item-title">Tagline</th><td class="item-value">'.$movie[ 'tagline' ].'</td></tr>'; } 
			if(get_the_terms(get_the_ID(), 'language')) { $data .= '<tr class="detail-item"><th class="item-title">Language</th><td class="item-value">'.get_linked_terms('language').'</td></tr>'; } 
			if(get_the_terms(get_the_ID(), 'country')) { $data .= '<tr class="detail-item"><th class="item-title">Country</th><td class="item-value">'.get_linked_terms('country').'</td></tr>'; } 
			if(get_the_terms(get_the_ID(), 'genre')) { $data .= '<tr class="detail-item"><th class="item-title">Genre</th><td class="item-value">'.get_linked_terms('genre').'</td></tr>'; } 
			if($director) { $data .= '<tr class="detail-item"><th class="item-title">'.$director_job.'</th><td class="item-value">'.$director.'</td></tr>'; } 
			if($writer) { $data .= '<tr class="detail-item"><th class="item-title">Writer</th><td class="item-value">'.$writer.'</td></tr>'; } 
			if($producer) { $data .= '<tr class="detail-item"><th class="item-title">'.$producer_job.'</th><td class="item-value">'.$producer.'</td></tr>'; } 
			if($movie[ 'runtime' ]) { $data .= '<tr class="detail-item"><th class="item-title">Duration</th><td class="item-value">'.$movie[ 'runtime' ].'</td></tr>'; } 
			if($movie[ 'production_house' ]) { $data .= '<tr class="detail-item"><th class="item-title">Production House</th><td class="item-value">'.$movie[ 'production_house' ].'</td></tr>'; } 
			if($musician) { $data .= '<tr class="detail-item"><th class="item-title">Music</th><td class="item-value">'.$musician.'</td></tr>'; } 
			if($movie[ 'budget' ]) { $data .= '<tr class="detail-item"><th class="item-title">Budget ($)</th><td class="item-value">$'.number_format($movie[ 'budget' ], 2).'</td></tr>'; } 
			if($movie[ 'revenue' ]) { $data .= '<tr class="detail-item"><th class="item-title">Revenue ($)</th><td class="item-value">$'.number_format($movie[ 'revenue' ], 2).'</td></tr>'; } 
			if($movie[ 'certification' ]) { $data .= '<tr class="detail-item"><th class="item-title">Certificate</th><td class="item-value">'.$movie[ 'certification' ].'</td></tr>'; } 
		$data .= '</table>';
	$data .= '</div>';
  return $data;
}

function cast_crew_tabs_script(){ 
  $data = '';
  $data .= '<script>';
  $data .= 'function scrollRelease(button) {';
  $data .= '  const scrollContainer = button.closest(\'.scrollable-section\');';
  $data .= '  const scrollTarget = scrollContainer.dataset.scrollTarget;';
  $data .= '  const direction = button.dataset.direction ? parseInt(button.dataset.direction) : 1;';
  $data .= '';
  $data .= '  const scrollElement = document.querySelector(scrollTarget);';
  $data .= '';
  $data .= '  if (scrollElement) {';
  $data .= '    scrollElement.scrollLeft += direction * 1000;';
  $data .= '  } else {';
  $data .= '    console.warn(\'Scroll element not found with selector:\', scrollTarget);';
  $data .= '  }';
  $data .= '}';
  $data .= '</script>';
  $data .= '<script type="text/javascript">';
	$data .= '	const tabItems = document.querySelectorAll(\'.tab_item\');';
	$data .= '	const castSection = document.getElementById(\'cast\');';
	$data .= '	const crewSection = document.getElementById(\'crew\');';
  $data .= '';
	$data .= '	tabItems.forEach(tabItem => {';
	$data .= '	  tabItem.addEventListener(\'click\', function() {';
	$data .= '	    tabItems.forEach(item => item.classList.remove(\'active\'));';
	$data .= '	    this.classList.add(\'active\');';
  $data .= '';
	$data .= '	    if (this.textContent === \'Cast\') {';
	$data .= '	      castSection.style.display = \'block\';';
	$data .= '	      crewSection.style.display = \'none\';';
	$data .= '	    } else {';
	$data .= '	      castSection.style.display = \'none\';';
	$data .= '	      crewSection.style.display = \'block\';';
	$data .= '	    }';
	$data .= '	  });';
	$data .= '	});';
  $data .= '';
	$data .= '	const showFullCreditsButtons = document.querySelectorAll(\'.show_full_credits\');';
  $data .= '';
	$data .= '	showFullCreditsButtons.forEach(button => {';
	$data .= '	  button.addEventListener(\'click\', function() {';
	$data .= '	    this.style.display = \'none\';';
  $data .= '';
	$data .= '	    const nextSibling = this.nextElementSibling;';
  $data .= '	    nextSibling.style.display = \'block\';';
	$data .= '	  });';
	$data .= '	});';
  $data .= '';
  $data .= '</script>';
  return $data;
}

function get_movies_credit_by_job($credits, $job){
	if ($credits) {
		foreach ($credits as $credit) {
			if ($credit->job === $job) {
				return ['permalink' => get_permalink($credit->person), 'name' => get_the_title($credit->person)];
			}
		}
	}
}

function movies_credits($credits, $type){
	$count = 0;
  $data = '';
	foreach ( $credits as $credit ) {
		  if($credit->person):
		  	$title = get_the_title($credit->person);
		  	$count++;
		  	$data .= '<div class="credit-block">';
					$data .= '<div class="block1">';
						$data .= '<div class="credit-img"><img '. (has_post_thumbnail($credit->person) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/icons/no-image.svg" data-src="'.get_the_post_thumbnail_url($credit->person, 'medium').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/icons/no-image.svg"') ).'></div>';
						$data .= '<div class="credit-name"><a href="'.get_permalink($credit->person).'" class="cast-name" title="'.($title ? $title : 'No name') .'">'.($title ? $title : 'No name') .'</a></div>';
					$data .= '</div>';
					$data .= '<div class="credit-character">'.$credit->job && $credit->job!== '' ? $credit->job : 'TBA' .'</div>';
				$data .= '</div>';
		  	if ($count === 5) $data .= '<div class="show_full_credits">View All</div><div class="wrap_full_credits" style="display:none">';
		  endif;	
		}
		if ($count >= 5) $data .= '</div>';
		if ($count === 0)  $data .= "We don't have any ".$type." added to this ".get_the_title().". For more information about this TV Show stay tune.";
    return $data;
}

