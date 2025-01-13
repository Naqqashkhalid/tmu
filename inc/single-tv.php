<?php

add_filter( 'the_content', 'single_tv' );
function single_tv($content){
  if ( !(get_post_type() == 'tv' && is_singular()) ) return $content;
  $data = '';
  $content = $content ? $content : get_the_content();
  $post_id = get_the_ID();
 
  global $wpdb;
  $tv = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}tmu_tv_series WHERE `ID` = %d", $post_id), ARRAY_A);

  $title = get_the_title();

  $tv['star_cast'] = $star_cast = $tv['star_cast'] ? @unserialize($tv['star_cast']) : [];
  $tv['runtime'] = $tv['runtime'] ? $tv['runtime'].' minutes' : '';
  $tv['images'] = rwmb_meta( 'images', [ 'size' => 'thumbnail' ] );
  $tv['videos'] = $tv['videos'] ? @unserialize($tv['videos']) : [];
  $tv['seasons'] = $tv['seasons'] ? @unserialize($tv['seasons']) : [];

  // $tmdb_rating['average'] = $tv['average_rating'];
  // $tmdb_rating['count'] = $tv['vote_count'];

  // if ($post_type === 'tv' || $post_type === 'drama') {
// 		$table_col = $post_type === 'tv' ? 'tv_series' : 'dramas';
  //   $eps = $wpdb->get_results("SELECT ID,average_rating,vote_count FROM {$wpdb->prefix}tmu_{$table_col}_episodes WHERE $table_col = $post_id");
  //   if ($eps) {
  //       $ep_rating = $tmdb_rating['average']*$tmdb_rating['count'];
  //       foreach ($eps as $ep) {
  //           $ep_comments = get_comments(array('post_id' => $ep->ID, 'status' => 'approve'));
  //           foreach($ep_comments as $comment):
  //               $ep_rating += isset($comment->comment_rating) && $comment->comment_rating ? (int)$comment->comment_rating : 0;
  //               $tmdb_rating['count']++;
  //           endforeach;
  //           $ep_rating = $ep_rating+($ep->average_rating*$ep->vote_count);
  //           $tmdb_rating['count'] += $ep->vote_count;
  //       }
  //       $tmdb_rating['average'] = $tmdb_rating['count'] ? number_format(($ep_rating/$tmdb_rating['count']), 1) : 5;
  //   }
  // }

  // $comments = get_comments(array('post_id' => $post_id, 'status' => 'approve'));
  // $average_ratings = get_average_ratings($comments, $tmdb_rating);

  $average_ratings = ['average' => $tv['total_average_rating'], 'count' => $tv['total_vote_count']];

  global $wpdb;
	$cast_table = $wpdb->prefix . 'tmu_tv_series_cast';
	$cast = $wpdb->get_results($wpdb->prepare("SELECT cast.* FROM $cast_table as cast JOIN {$wpdb->prefix}posts AS posts ON (cast.tv_series = posts.ID) WHERE cast.tv_series = %d AND posts.post_status = 'publish'", $post_id));

	$crew_table = $wpdb->prefix . 'tmu_tv_series_crew';
	$crew = $wpdb->get_results($wpdb->prepare("SELECT crew.* FROM {$crew_table} as crew JOIN {$wpdb->prefix}posts AS posts ON (crew.tv_series = posts.ID) WHERE crew.tv_series = %d AND posts.post_status = 'publish'", $post_id));

	$director_array = get_tv_series_credit_by_job($crew, 'Director');
	$director_alt = $director_array ? NULL : $wpdb->get_row($wpdb->prepare("SELECT crew.person,crew.job FROM $crew_table as crew JOIN {$wpdb->prefix}posts AS posts ON (crew.tv_series = posts.ID) WHERE crew.tv_series = %d AND (crew.job LIKE '%Co-Director%' OR crew.job LIKE '%Assistant Director%') AND posts.post_status = 'publish' ORDER BY CASE WHEN crew.job LIKE '%Co-Director%' THEN 1 ELSE 2 END", $post_id));
	$director_array = $director_alt ? ['permalink' => get_permalink($director_alt->person), 'name' => get_the_title($director_alt->person)] : $director_array;

	$producer_array = get_tv_series_credit_by_job($crew, 'Producer');
	$producer_alt = $producer_array ? NULL : $wpdb->get_row($wpdb->prepare("SELECT crew.person,crew.job FROM $crew_table as crew JOIN {$wpdb->prefix}posts AS posts ON (crew.tv_series = posts.ID) WHERE crew.tv_series = %d AND (crew.job LIKE '%Co-Producer%' OR crew.job LIKE '%Executive Producer%' OR crew.job LIKE '%Associate Producer%') AND posts.post_status = 'publish' ORDER BY CASE WHEN crew.job LIKE '%Co-Producer%' THEN 1 ELSE 2 END", $post_id));
	$producer_array = $producer_alt ? ['permalink' => get_permalink($producer_alt->person), 'name' => get_the_title($producer_alt->person)] : $producer_array;
	
	$writer_array = get_tv_series_credit_by_job($crew, 'Writer');
	$musician_array = get_tv_series_credit_by_job($crew, 'Musician');

	$director = $director_array ? '<a href="'.$director_array['permalink'].'" class="credits-member" title="'.$director_array['name'].' TV Shows">'.$director_array['name'].'</a>' : '';
	$director_job = $director_alt ? $director_alt->job : 'Director';

	$producer = $producer_array ? '<a href="'.$producer_array['permalink'].'" class="credits-member" title="'.$producer_array['name'].' TV Shows">'.$producer_array['name'].'</a>' : '';
	$producer_job = $producer_alt ? $producer_alt->job : 'Producer';

	$writer = $writer_array ? '<a href="'.$writer_array['permalink'].'" class="credits-member" title="'.$writer_array['name'].' TV Shows">'.$writer_array['name'].'</a>' : '';
	$musician = $musician_array ? '<a href="'.$musician_array['permalink'].'" class="credits-member" title="'.$musician_array['name'].' TV Shows">'.$musician_array['name'].'</a>' : '';

	$related_news = get_related_news($title, 4);

	// echo default_schema($title, $_SERVER['REQUEST_URI']);
  $data .= '<link rel="stylesheet" href="' . plugin_dir_url( __DIR__ ) . 'src/css/single-tv-series.css">';
	  $data .= '<div class="movie_sec">';
		$data .= breadcrumb();
		$data .= '<div class="grid_movie_sec">';
			$data .= tv_header($tv, $director, $director_job, $producer, $producer_job, $writer, $musician, $average_ratings);

			if ($tv['star_cast']) { 
				$data .= '<div class="star_casts">';
					$data .= '<div class="short-heading font-size-36 weight-700">';
						$data .= '<h2 class="images-title weight-700 font-size-36">'.$title.' Star Cast</h2><svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" class="svg-icon icon-lg" viewBox="0 0 24 24" fill="currentColor" role="presentation"><path d="M5.622.631A2.153 2.153 0 0 0 5 2.147c0 .568.224 1.113.622 1.515l8.249 8.34-8.25 8.34a2.16 2.16 0 0 0-.548 2.07c.196.74.768 1.317 1.499 1.515a2.104 2.104 0 0 0 2.048-.555l9.758-9.866a2.153 2.153 0 0 0 0-3.03L8.62.61C7.812-.207 6.45-.207 5.622.63z"></path></svg>';
					$data .= '</div>';
					$data .= '<p class="heading-des">Meet the talented star cast of ' . $title . ' TV Show.</p>';
					$data .= the_star_cast($tv['star_cast']); //display_credits_with_image($tv['star_cast']) 
				$data .= '</div>';
			}

			if ($tv['seasons']) {
			$data .= '<div class="short-heading font-size-36 weight-700">';
				$data .= '<h2 class="images-title weight-700 font-size-36">'.$title.' ' . ($tv['finished']=='1' ? 'Last' : 'Current') . ' Season</h2><svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" class="svg-icon icon-lg" viewBox="0 0 24 24" fill="currentColor" role="presentation"><path d="M5.622.631A2.153 2.153 0 0 0 5 2.147c0 .568.224 1.113.622 1.515l8.249 8.34-8.25 8.34a2.16 2.16 0 0 0-.548 2.07c.196.74.768 1.317 1.499 1.515a2.104 2.104 0 0 0 2.048-.555l9.758-9.866a2.153 2.153 0 0 0 0-3.03L8.62.61C7.812-.207 6.45-.207 5.622.63z"></path></svg>';
			$data .= '</div>';
			}

			remove_filter( 'the_content', 'single_tv' );
			$data .= current_season($post_id, ($tv['last_season'] ? $tv['last_season'] : (isset($tv['seasons'][0]) ? rwmb_meta( 'season_no', '', $tv['seasons'][0] ) : '')), $tv['last_episode']);
			add_filter( 'the_content', 'single_tv' ); 

			if($content):
			$data .= '<div class="movie-story">';
				$data .= '<div class="short-heading font-size-36 weight-700">';
					$data .= '<h2 class="images-title weight-700 font-size-36">' . $title . ' TV Show Story</h2><svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" class="svg-icon icon-lg" viewBox="0 0 24 24" fill="currentColor" role="presentation"><path d="M5.622.631A2.153 2.153 0 0 0 5 2.147c0 .568.224 1.113.622 1.515l8.249 8.34-8.25 8.34a2.16 2.16 0 0 0-.548 2.07c.196.74.768 1.317 1.499 1.515a2.104 2.104 0 0 0 2.048-.555l9.758-9.866a2.153 2.153 0 0 0 0-3.03L8.62.61C7.812-.207 6.45-.207 5.622.63z"></path></svg>';
				$data .= '</div>';
				$data .= '<div>' . $content . '</div>';
			$data .= '</div>';
			endif;

			$data .= tv_where_to_watch($tv);

			$data .= $tv['videos'] ? display_videos($tv['videos']) : '';
			$data .= $tv['images'] ? display_images($tv['images']) : '';
		$data .= '</div>';

		$data .= '<div class="cast_crew" id="cast-and-crew">';

			$data .= '<div class="short-heading font-size-36 weight-700">';
				$data .= '<h2 class="images-title weight-700 font-size-36">' . $title . ' Cast & Crew</h2><svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" class="svg-icon icon-lg" viewBox="0 0 24 24" fill="currentColor" role="presentation"><path d="M5.622.631A2.153 2.153 0 0 0 5 2.147c0 .568.224 1.113.622 1.515l8.249 8.34-8.25 8.34a2.16 2.16 0 0 0-.548 2.07c.196.74.768 1.317 1.499 1.515a2.104 2.104 0 0 0 2.048-.555l9.758-9.866a2.153 2.153 0 0 0 0-3.03L8.62.61C7.812-.207 6.45-.207 5.622.63z"></path></svg>';
			$data .= '</div>';

			$data .= '<p class="heading-des">Check out the ' . $title . ' Cast & Crew, showcasing the talented cast and the crew that worked tirelessly to bring the ' . $title . ' tv show to the screen.</p>';

			$data .= '<ul class="nav_tabs">';
				$data .= '<li class="tab_item active">Cast</li>';
				$data .= '<li class="tab_item">Crew</li>';
			$data .= '</ul>';
			$data .= '<div class="full_cast" id="cast">' . tv_series_credits($cast, 'cast') . '</div>';
			$data .= '<div class="full_crew" id="crew" style="display: none;">' . tv_series_credits($crew, 'crew') . '</div>';
		$data .= '</div>';

		$data .= more_like_this($post_id);

		$data .= tv_details_table($tv, $director, $director_job, $producer, $producer_job, $writer, $musician);

		if ($related_news):
			$data .= '<div class="short-heading font-size-36 weight-700">';
				$data .= '<h2 class="images-title weight-700 font-size-36">' . $title . ' TV Show News – Latest Updates and Stories</h2>';
				$data .= '</div>';
				$data .= '<p class="heading-des">Stay informed with all the ' . $title . ' TV Show related news. From exclusive insights to breaking updates, plot twists, cast news and more so don’t miss any important story about ' . $title . '.</p>';
				$data .= $related_news;
					endif;
		
		$keywords = get_linked_terms('keyword', '');
		if($keywords): 
			$data .= '<div class="keywords">';
				$data .= '<div class="heading-2">Keywords:</div>';
				$data .= '<div class="tags">' . $keywords . '</div>';
			$data .= '</div>';
		endif;
	$data .= '</div>';
  $data .= cast_crew_tabs_script();
  return $data;
}

function tv_header($tv, $director, $director_job, $producer, $producer_job, $writer, $musician, $average_ratings){
	$data = '';
	$genres = get_linked_terms('genre');
	$last = count($tv['videos'])-1;
  $data .= '<section class="movie-trailer">';
  			$data .= '<div class="feature-image"><img '.(has_post_thumbnail() ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url(get_the_ID(), 'full').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ).' alt="' . get_the_title() . '" width="100%" height="100%"></div>';
  			$data .= '<div class="movie_details">';
  				$data .= '<h1 class="font-size-36">' . get_the_title() . '</h1>';
					$data .= '<div class="rating-box">' . get_post_rating($average_ratings) . '<div class="rate-this">' . rate_this() . '</div></div>';
  				$data .= '<div class="release">' . ($tv['release_timestamp'] ? date( 'Y', $tv['release_timestamp'] ).' <span class="sep">|</span> ' : '').($tv['certification'] ? $tv['certification'].' <span class="sep">|</span> ' : '').($tv['runtime'] ? $tv['runtime'].' <span class="sep">|</span> ' : '').($genres ? '<span class="genres">'.$genres.'</span>' : '') . '</div>';
					$data .= '<div class="credits_main">';
						if ($director) { $data .= '<div' . (!$producer ? ' class="width-100"' : '') . '>' . $director . '<div class="font-size-13">' . $director_job . '</div></div>'; } 
						if ($producer) { $data .= '<div' . (!$director ? ' class="width-100"' : '') . '>' . $producer . '<div class="font-size-13">' . $producer_job . '</div></div>'; } 
					$data .= '</div>';

					$data .= '<div class="watch-buttons">';
						if($tv['streaming_platforms']):
							$data .= '<a href="' . get_site_url() . '/redirect/' . urlencode( $tv['streaming_platforms'] ) . '" title="Where to watch">WHERE TO WATCH</a>';
						endif;
						if (isset($tv['videos'][$last])):
							$data .= '<a href="' . get_permalink($tv['videos'][$last]) . '" title="Watch TV Show trailer">WATCH TRAILER</a>';
						endif;
					$data .= '</div>';

  			$data .= '</div>';
  		$data .= '</section>';
  return $data;
}

function tv_details_table($tv, $director, $director_job, $producer, $producer_job, $writer, $musician){
	$data = '';
	$title = get_the_title();
	$release = $tv['release_date'] ? date( 'd F Y', $tv['release_timestamp'] ) : '';
	$starcast = implode(', ', array_map(function ($cast) { return isset($cast['person']) ? get_linked_post($cast['person']) : ''; }, $tv['star_cast']));
	$network = get_linked_terms('network');
	$data .= '<div>';
	$data .= '<h2 class="heading-2">' . get_the_title() . ' TV Show - Release Date, Cast, Trailer, Review and Other Details</h2>';
	$data .= '<p class="heading-des">';
		if ($release && $director && $starcast) {
		$data .= $title.( (strtotime('today GMT') > $tv[ 'release_timestamp' ]) ? ' was released on ' : ' is all set to air on ').($network ? $network.' on ' : '').$release.'. '.$title.' directed by '.$director.' and '.$starcast.' played the primary leads.';
	} $data .= '</p>';
	$data .= '<table class="movie-details-section">';
		$data .= '<tr class="detail-item"><th class="item-title">Status</th><td class="item-value">' . ($tv['release_timestamp'] > time() ? 'Upcoming' : ($tv['finished'] ? 'Ended' : 'Ongoing')) . '</td></tr>';
		if($tv[ 'release_date' ]) { $data .= '<tr class="detail-item"><th class="item-title">Release Date</th><td class="item-value">' . date( 'd F Y', $tv['release_timestamp'] ) . '</td></tr>'; } 
		if($tv[ 'original_title' ]) { $data .= '<tr class="detail-item"><th class="item-title">Original Title</th><td class="item-value">' . $tv[ 'original_title' ] . '</td></tr>'; } 
		if($tv[ 'tagline' ]) { $data .= '<tr class="detail-item"><th class="item-title">Tagline</th><td class="item-value">' . $tv[ 'tagline' ] . '</div></div>'; } 
		if(get_the_terms(get_the_ID(), 'language')) { $data .= '<tr class="detail-item"><th class="item-title">Language</th><td class="item-value">' . get_linked_terms('language') . '</td></tr>'; } 
		if(get_the_terms(get_the_ID(), 'country')) { $data .= '<tr class="detail-item"><th class="item-title">Country</th><td class="item-value">' . get_linked_terms('country') . '</td></tr>'; } 
		if(get_the_terms(get_the_ID(), 'genre')) { $data .= '<tr class="detail-item"><th class="item-title">Genre</th><td class="item-value">' . get_linked_terms('genre') . '</td></tr>'; } 
		if($network) { $data .= '<tr class="detail-item"><th class="item-title">Networks</th><td class="item-value">' . $network . '</td></tr>'; } 
		if($director) { $data .= '<tr class="detail-item"><th class="item-title">' . $director_job . '</th><td class="item-value">' . $director . '</td></tr>'; } 
		if($writer) { $data .= '<tr class="detail-item"><th class="item-title">Writer</th><td class="item-value">' . $writer . '</td></tr>'; } 
		if($producer) { $data .= '<tr class="detail-item"><th class="item-title">' . $producer_job . '</th><td class="item-value">' . $producer . '</td></tr>'; } 
		if($tv[ 'runtime' ]) { $data .= '<tr class="detail-item"><th class="item-title">Duration</th><td class="item-value">' . $tv[ 'runtime' ] . '</td></tr>'; } 
		if($tv[ 'schedule_time' ]) { $data .= '<tr class="detail-item"><th class="item-title">Schedule</th><td class="item-value">' . $tv[ 'schedule_time' ] . '</div></div>'; } 
		if($tv[ 'production_house' ]) { $data .= '<tr class="detail-item"><th class="item-title">Production House</th><td class="item-value">' . $tv[ 'production_house' ] . '</td></tr>'; } 
		if($musician) { $data .= '<tr class="detail-item"><th class="item-title">Music</th><td class="item-value">' . $musician . '</td></tr>'; } 
		if($tv[ 'budget' ]) { $data .= '<tr class="detail-item"><th class="item-title">Budget ($)</th><td class="item-value">' . $tv[ 'budget' ] . '</td></tr>'; } 
		if($tv[ 'revenue' ]) { $data .= '<tr class="detail-item"><th class="item-title">Revenue ($)</th><td class="item-value">' . $tv[ 'revenue' ] . '</td></tr>'; } 
		if($tv[ 'certification' ]) { $data .= '<tr class="detail-item"><th class="item-title">Certificate</th><td class="item-value">' . $tv[ 'certification' ] . '</td></tr>'; } 
	$data .= '</table>';
$data .= '</div>';
return $data;
}

function tv_series_credits($credits, $type){
	$data = '';
	$count = 0;
	foreach ( $credits as $credit ) {
			$title = get_the_title($credit->person);
		  if($credit->person):
		  	$count++;
		  	$data .= '<div class="credit-block">
					<div class="block1">
						<div class="credit-img"><img '.(has_post_thumbnail($credit->person) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/icons/no-image.svg" data-src="'.get_the_post_thumbnail_url($credit->person, 'medium').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/icons/no-image.svg"') ).' alt="'.$title.'"></div>
						<div class="credit-name"><a href="'.get_permalink($credit->person).'" class="cast-name" title="'.($title ? $title : 'No name').'">'.($title ? $title : 'No name').'</a></div>
					</div>
					<div class="credit-character">'.($credit->job && $credit->job!== '' ? $credit->job : 'TBA').'</div>
				</div>';
		  if ($count === 5) $data .= '<div class="show_full_credits">View All '.ucfirst($type).'</div><div class="wrap_full_credits" style="display:none">';
		endif;	
		}
		if ($count >= 5) $data .= '</div>';
		if ($count === 0)  $data .= "We don't have any ".$type." added to this ".get_the_title().". For more information about this TV Show stay tune.";
		return $data;
}

function current_season($series, $season_no, $episode_no, $schema=false){
	$data = '';
	global $wpdb;

  $season = $wpdb->get_row($wpdb->prepare("SELECT season.* FROM {$wpdb->prefix}tmu_tv_series_seasons as season JOIN {$wpdb->prefix}posts AS posts ON (season.ID = posts.ID) WHERE season.`tv_series` = %d AND season.`season_no` = %d AND posts.post_status = 'publish'", $series, $season_no), ARRAY_A);
  
  $next_episode = $wpdb->get_row($wpdb->prepare("SELECT episode.ID,episode.episode_title,episode.air_date,episode.episode_type,episode.episode_no FROM {$wpdb->prefix}tmu_tv_series_episodes as episode JOIN {$wpdb->prefix}posts AS posts ON (episode.ID = posts.ID) WHERE episode.`tv_series` = %d AND episode.`season_no` = %d AND episode.`episode_no` = %d AND posts.post_status = 'publish'", $series, $season_no, ((int)$episode_no)), ARRAY_A);

  $episode = $next_episode ? $next_episode : $wpdb->get_row($wpdb->prepare("SELECT episode.ID,episode.episode_title,episode.air_date,episode.episode_type,episode.episode_no FROM {$wpdb->prefix}tmu_tv_series_episodes as episode JOIN {$wpdb->prefix}posts AS posts ON (episode.ID = posts.ID) WHERE episode.`tv_series` = %d AND episode.`season_no` = %d AND episode.`episode_no` = %d AND posts.post_status = 'publish'", $series, $season_no, $episode_no), ARRAY_A);

  if ($season) {
  	$season_id = (int)$season['ID'];
  	$permalink = get_permalink($season_id);
  	$total_episodes = $season['total_episodes'] ?? 0;
		$poster_url= has_post_thumbnail($season_id) ? get_the_post_thumbnail_url($season_id) : '';
		$season_air_date = $season['air_date_timestamp'] ?? '';

		if (!$schema) {
			$data .= '<div class="season-card">';
				$data .=	'<a class="season-poster" href="'.$permalink.'" title="'.$season['season_name'].'">';
					$data .=	'<img '.($poster_url ? 'src="'.plugin_dir_url( __DIR__ ).'src/images/preloader.gif" data-src="'.$poster_url.'" class="lazyload"' : 'src="'.plugin_dir_url( __DIR__ ).'src/images/no-poster.webp"').' alt="'.$season['season_name'].'">';
				$data .=	'</a>';
				$data .=	'<div class="season-content">';
					$data .=	'<div>';
						$data .=	'<div class="season-head">';
							$data .=	'<div class="season-title"><a href="'.$permalink.'" title="'.$season['season_name'].'">'.$season['season_name'].'</a></div>';
							$data .=	'<p>'.($season_air_date ? date("Y",$season_air_date).' • ' : '').$total_episodes.' Episodes</p>';
						$data .=	'</div>';

						$data .=	$season_air_date ? '<p class="season-text">'.$season['season_name'].' of '.get_the_title($series).' premiered on '.date( 'd F Y', $season_air_date ).'</p>' : '';

						if ($episode) {
						$data .=	'<div class="last_episode">';
							$data .=	'<div style="width:1.3em;height:1.3em;margin-right:4px;">';
								$data .=	'<svg id="glyphicons-basic" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32">
									  <path id="calendar" d="M15,18.5v1a.5.5,0,0,1-.5.5h-1a.5.5,0,0,1-.5-.5v-1a.5.5,0,0,1,.5-.5h1A.5.5,0,0,1,15,18.5ZM14.5,22h-1a.5.5,0,0,0-.5.5v1a.5.5,0,0,0,.5.5h1a.5.5,0,0,0,.5-.5v-1A.5.5,0,0,0,14.5,22Zm0-8h-1a.5.5,0,0,0-.5.5v1a.5.5,0,0,0,.5.5h1a.5.5,0,0,0,.5-.5v-1A.5.5,0,0,0,14.5,14Zm4,8h-1a.5.5,0,0,0-.5.5v1a.5.5,0,0,0,.5.5h1a.5.5,0,0,0,.5-.5v-1A.5.5,0,0,0,18.5,22Zm-8-4h-1a.5.5,0,0,0-.5.5v1a.5.5,0,0,0,.5.5h1a.5.5,0,0,0,.5-.5v-1A.5.5,0,0,0,10.5,18Zm0,4h-1a.5.5,0,0,0-.5.5v1a.5.5,0,0,0,.5.5h1a.5.5,0,0,0,.5-.5v-1A.5.5,0,0,0,10.5,22Zm12-8h-1a.5.5,0,0,0-.5.5v1a.5.5,0,0,0,.5.5h1a.5.5,0,0,0,.5-.5v-1A.5.5,0,0,0,22.5,14Zm0,4h-1a.5.5,0,0,0-.5.5v1a.5.5,0,0,0,.5.5h1a.5.5,0,0,0,.5-.5v-1A.5.5,0,0,0,22.5,18Zm-4,0h-1a.5.5,0,0,0-.5.5v1a.5.5,0,0,0,.5.5h1a.5.5,0,0,0,.5-.5v-1A.5.5,0,0,0,18.5,18Zm0-4h-1a.5.5,0,0,0-.5.5v1a.5.5,0,0,0,.5.5h1a.5.5,0,0,0,.5-.5v-1A.5.5,0,0,0,18.5,14ZM27,7V26a2.00006,2.00006,0,0,1-2,2H7a2.00006,2.00006,0,0,1-2-2V7A2.002,2.002,0,0,1,7,5V6.5a.5.5,0,0,0,.5.5h1A.5.5,0,0,0,9,6.5v-1A.5.5,0,0,1,9.5,5h1a.5.5,0,0,1,.5.5v1a.5.5,0,0,0,.5.5h1a.5.5,0,0,0,.5-.5v-1a.5.5,0,0,1,.5-.5h1a.5.5,0,0,1,.5.5v1a.5.5,0,0,0,.5.5h1a.5.5,0,0,0,.5-.5v-1a.5.5,0,0,1,.5-.5h1a.5.5,0,0,1,.5.5v1a.5.5,0,0,0,.5.5h1a.5.5,0,0,0,.5-.5V5A2.002,2.002,0,0,1,27,7ZM25.00146,26l-.00109-14H7V26Z"/>';
								$data .=	'</svg>';
							$data .=	'</div>';
							$data .=	'<div><a href="'.get_permalink($episode['ID']).'" title="'.$episode['episode_title'].'">'.$episode['episode_title'].'</a> ('.($season_no.'x'.$episode['episode_no'].($episode['air_date'] ? ', '.date( 'F d, Y', strtotime($episode['air_date']) ) : '')).')'.($episode['episode_type']==='finale' ? '<span class="episode_type">Season Finale</span>' : '').'</div>';
						$data .=	'</div>';
						}
					$data .=	'</div>';
				$data .=	'</div>';
			$data .=	'</div>';
		}
		
		return $schema ? ['season_name' => $season['season_name'], 'air_date' => $season['air_date'], 'total_episodes' => $total_episodes, 'episode_no' => ($episode ? $episode['episode_no'] : ''), 'episode_name' => ($episode ? $episode['episode_title'] : '')] : $data;
  }
}

function get_tv_series_credit_by_job($credits, $job){
	if ($credits) {
		foreach ($credits as $credit) {
			if ($credit->job === $job) {
				return ['permalink' => get_permalink($credit->person), 'name' => get_the_title($credit->person)];
			}
		}
	}
}

function tv_where_to_watch($tv){
	$data = '';
	if ($tv['where_to_watch']) {
		$tv['where_to_watch'] = is_array($tv['where_to_watch']) ? $tv['where_to_watch'] : unserialize($tv['where_to_watch']);
		if (isset($tv['where_to_watch'][0]['channel']) && $tv['where_to_watch'][0]['channel']) {
			$data .= '<div class="short-heading font-size-36 weight-700"><h2 class="images-title weight-700 font-size-36">Where to Watch ' . get_the_title() . '</h2></div><div class="watch-list-container">';
			foreach ($tv['where_to_watch'] as $item) {
				$title = get_term_by('id', (int) $item['channel'], 'network')->name;
				$logo = wp_get_attachment_image_url(get_term_meta( $item['channel'], 'logo', true ), 'full'); 
				$data .= '<a href="' . $item['url'] . '" class="watch-item" rel="nofollow" title="' . $title . '">';
					$data .= '<div class="watch-icon"><img ' . ($logo ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/icons/silver-background.svg" data-src="'.$logo.'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/icons/silver-background.svg"') ) . ' alt="' . get_the_title() . '" width="100%" height="100%"></div>';
					$data .= '<div class="watch-title">' . $title . '</div>';
				$data .= '</a>';
			}
			$data .= '</div>';
		}
	}	
	return $data;
}

