<?php

add_filter( 'the_content', 'single_person' );
function single_person($content){
  if ( !(get_post_type() === 'people' && is_singular()) ) return $content;
  $data = '';
  $data .= person_extra_css();

  $post_id = get_the_ID();

  global $wpdb;
  $person = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}tmu_people WHERE `ID` = %d", $post_id), ARRAY_A);

  $person['name'] = get_the_title();
  $person['description'] = $content;
  $person['basic'] = $person['basic'] ? unserialize($person['basic']) : [];
  $person[ 'height' ] = isset($person['basic'][ 'height' ]) && $person['basic'][ 'height' ] ? $person['basic'][ 'height' ] : 'will update soon';
  $person[ 'weight' ] = isset($person['basic'][ 'weight' ]) && $person['basic'][ 'weight' ] ? $person['basic'][ 'weight' ] : 'will update soon';
  $person[ 'spouse' ] = isset($person['basic'][ 'spouse' ]) && $person['basic'][ 'spouse' ] ? $person['basic'][ 'spouse' ] : '';
  $person[ 'parents' ] = ['father' => (isset($person['basic'][ 'parents' ]['father']) && $person['basic'][ 'parents' ]['father'] ? $person['basic'][ 'parents' ]['father'] : 'will update soon'), 'mother' => (isset($person['basic'][ 'parents' ]['mother']) && $person['basic'][ 'parents' ]['mother'] ? $person['basic'][ 'parents' ]['mother'] : 'will update soon')];
  $person[ 'siblings' ] = isset($person['basic'][ 'siblings' ]) && $person['basic'][ 'siblings' ] ? $person['basic'][ 'siblings' ] : 'will update soon';
  $person['social_media_account'] = $person['social_media_account'] ? unserialize($person['social_media_account']) : [];
  $person['videos'] = unserialize($person['videos']);
  $person['age'] = calculate_age($person['date_of_birth'], $person['dead_on']);
  $person['dead_on'] = $person['dead_on'] ? date( 'd F Y', strtotime($person['dead_on']) ) : '';
  $person['refer'] = $person['gender'] === 'Male' ? 'He' : 'She';
  $person['net_worth'] = $person['net_worth'] ? '$'.nice_numbers($person['net_worth']) : 'will update soon';
  $person['basic'] = '';
  $photos = rwmb_meta( 'photos', [ 'size' => 'thumbnail' ] );
  $nationality = implode(', ', wp_get_object_terms( get_the_ID(), 'nationality', array('fields' => 'names') ));
  $person['nationality'] = $nationality ? $nationality : '';
  $related_news = get_related_news($person['name'], 4);

  if ($content) {
  	$paragraphs = explode("</p>", $content);
  	$tp = count($paragraphs);
	  $firstparagraphs = array_shift($paragraphs);
	  $remainingContent = implode("</p>", $paragraphs);
  }
  
  $data .= '<div class="movie_sec">';
  $data .= breadcrumb();
  $data .= '<div class="grid_movie_sec">';
  $data .= person_header($post_id, $person);
  $data .= '<div style="text-align: end; font-weight: 600;">Last Updated: '.date( 'd F Y',get_the_modified_time( 'U' )).'</div>';
			
  $data .= '<div class="text-container">';
  $data .= $content ? ($tp > 2 ? '<p>' . $firstparagraphs . ' <a id="showMoreLink" title="read more">read more...</a></p><div id="additional-text" style="display: none">' . $remainingContent . '</div>' : $content) : 'We don\'t have a biography for '.$person['name'].'.' ;
  $data .= '</div>';
  $data .= known_for();
  $data .= $person['videos'] ? display_videos($person['videos']) : '';
  $data .= $photos ? display_images($photos) : '';
  $data .= person_details_table($person);
  $data .= '</div>';
  $data .= get_related_movies_by_id(get_the_ID(), $person['nationality']);
  if ($related_news){
  	$data .= '<div class="related-news">';
  	$data .= '<h2 class="short-heading weight-700 font-size-36 news-title">'.$person['name'].' News – Latest Updates and Stories</h2>';
  	$data .= '<p class="heading-des">Stay informed with all the '.$person['name'].' related news. From exclusive insights to breaking updates, plot twists, cast news and more so don’t miss any important story about '.$person['name'].'.</p>';
  	$data .= $related_news;
  	$data .= '</div>';
  }
  $data .= '</div>';
  $data .= '<script> document.getElementById("showMoreLink").addEventListener("click", function() { document.getElementById("additional-text").style.display = "block"; this.remove(); }); </script>';
  return $data;
}

function person_header($post_id, $person){
	$genres = get_linked_terms('genre');
	$latest_movie = person_latest($post_id);
	$upcoming_movie = person_upcoming($post_id);
  $data = '';
  $data .= '<section class="movie-trailer">';
  $data .= '<div class="feature-image"><img '. (has_post_thumbnail() ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url(get_the_ID(), 'full').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ).' alt="'.$person['name'].'" width="100%" height="100%">';
  $social = $person['social_media_account'] ? socialmedia($person['social_media_account']) : '';
  $data .= $social ? '<div class="socialmedia desktop">'.$social.'</div>' : '';
  $data .= '</div>';
  $data .= '<div class="movie_details">';
  $data .= '<h1 class="font-size-36">'.$person['name'].'</h1>';
  $data .= $person['profession'] ? '<p class="profession">Known For '.$person['profession'].'</p>' : '';
  $data .= '<div class="born">';
  $data .= $person['date_of_birth'] ? '<p><span>Birth Day: </span>'.date( 'd F Y', strtotime($person['date_of_birth']) ).' </p>' : '';
  $data .= $person[ 'birthplace' ] ? '<p><span>Birth Place: </span>'.$person[ 'birthplace' ].'</p>' : '';
  $data .= $person['dead_on'] ? '<p><span>Died On: </span>'.$person['dead_on'].'</p>' : ''; 
  $data .= '</div>';
  $data .= $person['net_worth'] ? '<p class="net-worth">Net Worth: '.$person['net_worth'].'</p>' : '';
  $data .= '<div class="recent_working">';
  if($latest_movie){
  	$data .= '<div class="item">';
  	$data .= '<div class="item-block">';
  	$data .= '<div class="item-img"><img '. (has_post_thumbnail($latest_movie) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url($latest_movie, 'medium').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ).' alt="'.get_the_title($latest_movie).'" width="100%" height="100%"></div>';
  	$data .= '<div class="item-title recent-title">';
  	$data .= get_linked_post($latest_movie);
  	$data .= '<div class="job">Latest '. (get_post_type($latest_movie)==='movie' ? 'Movie' : 'TV Show').'</div>';
  	$data .= '</div>';
  	$data .= '</div>';
  	$data .= '<div class="item-extra">'.implode(', ', wp_get_object_terms( $latest_movie, 'by-year', array('fields' => 'names') )).'</div>';
  	$data .= '</div>';
  }
  if (isset($upcoming_movie)){
  	$data .= '<div class="item">';
  	$data .= '<div class="item-block">';
  	$data .= '<div class="item-img"><img '. (has_post_thumbnail($upcoming_movie) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url($upcoming_movie, 'medium').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ).' alt="'.get_the_title($upcoming_movie).'" width="100%" height="100%"></div>';
  	$data .= '<div class="item-title recent-title">';
  	$data .= get_linked_post($upcoming_movie);
  	$data .= '<div class="job">Upcoming '. (get_post_type($upcoming_movie)==='movie' ? 'Movie' : 'TV Show').'</div>';
  	$data .= '</div>';
  	$data .= '</div>';
  	$data .= '<div class="item-extra">'.implode(', ', wp_get_object_terms( $upcoming_movie, 'by-year', array('fields' => 'names') )).'</div>';
  	$data .= '</div>';
  }
  $data .= '</div>';
  $social = $person['social_media_account'] ? socialmedia($person['social_media_account']) : '';
  $data .= $social ? '<div class="socialmedia mobile">'.$social.'</div>' : '';
  $data .= '</div>';
  $data .= '</section>';
  return $data;
}

function person_details_table($person){
	$dob_formated = $person['date_of_birth'] ? date( 'd F Y', strtotime($person['date_of_birth']) ) : '';
	$data = '';
	$data .= '<div>';
	$data .= '<h2 class="short-heading weight-700 font-size-36 credit-heading">'.(($person || $person['nick_name'] || $person['date_of_birth'] || $person['gender']) ? $person['name'].' Info' : ''). '</h2>';
	$data .= '<p class="heading-des">'.($person['name'] ?? '').' '.($person['profession'] ? 'is known for '.$person['profession'] : ''). ($dob_formated ? ', born on '.$dob_formated.' and currently '.$person['age'].' years old as of '.date('Y') : ''). '. '.$person['name'] .'\'s ' . ($person[ 'height' ] ? 'height is '.$person[ 'height' ].', and ' : '') . ($person[ 'weight' ] ? 'weight is '.$person[ 'weight' ].', ' : '') .'making a notable presence in the industry.'. ($person['net_worth'] ? ' With an estimated net worth of '.$person['net_worth'].'.' : ''). '</p>';
	$data .= '<table class="movie-details-section">';
	if($person['nick_name']) $data .= '<tr class="detail-item"><th class="item-title">Nick Name</th><td class="item-value">'.$person['nick_name'].'</td></tr>';
	if($person['gender']) $data .= '<tr class="detail-item"><th class="item-title">Gender</th><td class="item-value">'.$person['gender'].'</td></tr>';
	if($person['date_of_birth']) $data .= '<tr class="detail-item"><th class="item-title">Date Of Birth</th><td class="item-value">'.$dob_formated.'</td></tr>';
	if($person['age']) $data .= '<tr class="detail-item"><th class="item-title">Age</th><td class="item-value">'.$person['age'].' Years Old</td></tr>';
	if($person[ 'birthplace' ]) $data .= '<tr class="detail-item"><th class="item-title">Birthplace</th><td class="item-value">'.$person[ 'birthplace' ].'</td></tr>';
	if($person['profession']) $data .= '<tr class="detail-item"><th class="item-title">Profession</th><td class="item-value">'.$person['profession'].'</td></tr>';
	if($person['dead_on']) $data .= '<tr class="detail-item"><th class="item-title">Dead On</th><td class="item-value">'.$person['dead_on'].'</td></tr>';
	if($person['marital_status']) $data .= '<tr class="detail-item"><th class="item-title">Marital Status</th><td class="item-value">'.$person['marital_status'].'</td></tr>';
	if($person[ 'spouse' ]) $data .= '<tr class="detail-item"><th class="item-title">Spouse</th><td class="item-value">'.get_linked_post($person[ 'spouse' ]).'</td></tr>';
	if($person[ 'parents' ]['father'] || $person[ 'parents' ]['mother']) $data .= '<tr class="detail-item"><th class="item-title">Parents</th><td class="item-value">'.($person[ 'parents' ]['father'] ? 'Father: '.($person[ 'parents' ]['father']) : '').($person[ 'parents' ]['mother'] ? (($person[ 'parents' ]['father'] ? '<br>':'').'Mother: '.$person[ 'parents' ]['mother']) : '').'</td></tr>';
	if($person[ 'siblings' ]) $data .= '<tr class="detail-item"><th class="item-title">Siblings</th><td class="item-value">'.$person[ 'siblings' ].'</td></tr>';
	if($person[ 'height' ]) $data .= '<tr class="detail-item"><th class="item-title">Height</th><td class="item-value">'.$person[ 'height' ].'</td></tr>';
	if($person[ 'weight' ]) $data .= '<tr class="detail-item"><th class="item-title">Weight</th><td class="item-value">'.$person[ 'weight' ].'</td></tr>';
	if($person['net_worth']) $data .= '<tr class="detail-item"><th class="item-title">Net Worth</th><td class="item-value">'.$person['net_worth'].'</td></tr>';
	$data .= '</table>';
	$data .= '</div>';
	return $data;
}

function socialmedia($links){
	$dir = plugin_dir_url( __DIR__ ).'/src/icons/';
	$data = '';
	$data .= $links ? implode('', array_map(function($link) use ($dir){
		return ($link && isset($link['url'])) ? ('<a href="'.$link['url'].'" rel="nofollow"><img src="'.($link['platform'] == 'Facebook' ? $dir.'fb-icon.svg' : '').($link['platform'] == 'X' ? $dir.'twitter-icon.svg' : '').($link['platform'] == 'Instagram' ? $dir.'ig-icon.svg' : '').($link['platform'] == 'YouTube' ? $dir.'youtube-icon.svg' : '').'" alt="'.$link['platform'].'" title="'.$link['platform'].'"></a>') : '';
	},$links)) : '';
	return $data;
}

function person_extra_css(){
  return '<style>
	.text-container p {
		margin-top: 20px;
	}
	.item-title .job { font-weight:400; }
	.socialmedia {
		display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    padding: 5px 0;
	}
	.profession {
		margin-top: 5px;
		color: #ddd;
		font-size: 14px;
	}
	.born {
		font-size: 14px;
		font-weight: 700;
		margin-bottom: 5px;
	}
	.born span { font-weight: 400; }
	.born p { margin-bottom: 5px; }
	.net-worth {
		font: 600 17px/1.35 Gilroy;
		background: #2e2e5d;
		letter-spacing: 0.26px;
		text-align: center!important;
		text-transform: uppercase;
		color: #f5f5fa;
		padding: 5px 0;
		width: 100%;
		margin-top: 20px;
	}
	.videos { margin-top: 30px; }
	.opacity-5 { opacity: .5 }
	.recent-title a { font-size: 18px; }
	.recent-title a:active,.recent-title a:focus, .recent-title a:active { color:white!important }
	.recent-title .job { font-size: 12px; }
	.socialmedia.mobile {display: none}

	@media screen and (max-width: 768px) {
		.inside-article { padding: 0!important; }
		.socialmedia.desktop { display: none }
		.socialmedia.mobile { display: flex; }
	}
  </style>';
}


function get_related_movies_by_id($post_id, $nationality){
	global $wpdb;
	$options = get_options(['tmu_movies', 'tmu_tv_series', 'tmu_dramas']);
	$count_acting = 0; $count_production = 0;
	$data = '';
	$credits_acting_movie = $credits_acting_tv = $credits_production_tv = $credits_production_movie = [];
	if($options['tmu_movies'] === 'on' || $options['tmu_tv_series'] === 'on') {
		if ($options['tmu_movies'] === 'on') {
			$movies_cast_table = $wpdb->prefix.'tmu_movies_cast';
			$movies_crew_table = $wpdb->prefix.'tmu_movies_crew';
			$credits_acting_movie = $wpdb->get_results($wpdb->prepare("SELECT cast.* FROM $movies_cast_table as cast JOIN {$wpdb->prefix}posts AS posts ON (cast.movie = posts.ID) WHERE cast.`person`=%d AND posts.post_status = 'publish' ORDER BY cast.`release_year` DESC", $post_id));
			$credits_production_movie = $wpdb->get_results($wpdb->prepare("SELECT crew.person, crew.movie, release_year, GROUP_CONCAT(crew.job SEPARATOR ', ') AS job FROM $movies_crew_table crew JOIN {$wpdb->prefix}posts AS posts ON (crew.movie = posts.ID) WHERE crew.`person` = %d GROUP BY crew.person, crew.movie AND posts.post_status = 'publish' ORDER BY crew.`release_year` DESC", $post_id));
			$acting_movies_count = count($credits_acting_movie);
			$count_acting += $acting_movies_count;
			$credits_production_movie = combine_production($credits_production_movie);
			$production_movies_count = count($credits_production_movie);
			$count_production += $production_movies_count;
		} 
		if ($options['tmu_tv_series'] === 'on') {
			$tv_series_cast_table = $wpdb->prefix.'tmu_tv_series_cast';
			$tv_series_crew_table = $wpdb->prefix.'tmu_tv_series_crew';
			$credits_acting_tv = $wpdb->get_results($wpdb->prepare("SELECT cast.* FROM $tv_series_cast_table cast JOIN {$wpdb->prefix}posts AS posts ON (cast.tv_series = posts.ID) WHERE cast.`person`=%d AND posts.post_status = 'publish' ORDER BY cast.`release_year` DESC", $post_id));
			$credits_production_tv = $wpdb->get_results($wpdb->prepare("SELECT crew.person, crew.tv_series, crew.release_year, GROUP_CONCAT(crew.job SEPARATOR ', ') AS job FROM $tv_series_crew_table crew JOIN {$wpdb->prefix}posts AS posts ON (crew.tv_series = posts.ID) WHERE crew.`person`=%d AND posts.post_status = 'publish' GROUP BY crew.person, crew.tv_series ORDER BY crew.`release_year` DESC", $post_id));
			$acting_tv_count = count($credits_acting_tv);
			$count_acting += $acting_tv_count;
			$credits_production_tv = combine_production($credits_production_tv);
			$production_tv_count = count($credits_production_tv);
			$count_production += $production_tv_count;
		}
	} 
	if ($options['tmu_dramas'] === 'on') {
		$dramas_cast_table = $wpdb->prefix.'tmu_dramas_cast';
		$dramas_crew_table = $wpdb->prefix.'tmu_dramas_crew';
		$credits_acting_drama = $wpdb->get_results($wpdb->prepare("SELECT cast.* FROM $dramas_cast_table cast JOIN {$wpdb->prefix}posts AS posts ON (cast.dramas = posts.ID) WHERE cast.`person`=%d AND posts.post_status = 'publish' ORDER BY cast.`release_year` DESC", $post_id));
		$credits_production_drama = $wpdb->get_results($wpdb->prepare("SELECT crew.person, crew.dramas, crew.release_year, GROUP_CONCAT(crew.job SEPARATOR ', ') AS job FROM $dramas_crew_table crew JOIN {$wpdb->prefix}posts AS posts ON (crew.dramas = posts.ID) WHERE crew.`person`=%d AND posts.post_status = 'publish' GROUP BY crew.person, crew.dramas ORDER BY crew.`release_year` DESC", $post_id));
		$acting_drama_count = count($credits_acting_drama);
		$count_acting += $acting_drama_count;
		$production_drama_count = count($credits_production_drama);
		$count_production += $production_drama_count;
	}
	$data .= '<div class="person-movies">';
	$data .= '<h2 class="short-heading weight-700 font-size-36 credit-heading">'.($options['tmu_dramas'] === 'on' ? get_the_title($post_id).' Drama list' : 'Credits').'</h2>';
	$data .= '<p>Explore a complete list of '.get_the_title($post_id).'\'s '.$nationality.' '.($options['tmu_dramas'] === 'on' ? 'dramas' : 'credits').', from latest to most iconic roles.</p>';
		
	$data .= '<button class="subtitle" data-target="content-1">';
	$data .= '<h3 class="sub-title-block"><div class="sub-title">Acting</div><div class="credits-count">( '.$count_acting.' credits)</div></h3>';
	$data .= '<div class="icon"><svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" data-testid="ArrowDropDownOutlinedIcon" class="svg-icon rotate-180" height="1em" width="1em"><path d="m7 10 5 5 5-5H7z"></path></svg></div>';
	$data .= '</button>';
	$data .= '<div class="items related content-1">';
	if ($options['tmu_dramas'] === 'on') { 
		$data .= '<div class="acting_dramas" id="dramas">';
		$count = 0;
		foreach($credits_acting_drama as $post){
			$credit_post_id = $post->dramas;
			$release_year = $post->release_year ? date('Y', $post->release_year) : 'TBA';

			if($credit_post_id): $count++; 
				$data .= '<div class="item">';
				$data .= '<div class="item-block">';
				$data .= '<div class="item-img"><img '. (has_post_thumbnail($credit_post_id) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url($credit_post_id, 'medium').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ).' alt="'.get_the_title($credit_post_id).'" width="100%" height="100%"></div>';
				$data .= '<div class="item-title">';
				$data .= get_linked_post($credit_post_id);
				$data .= '<div class="job">'.stripslashes($post->job).'</div>';
				$data .= '</div>';
				$data .= '</div>';
				$data .= '<div class="item-extra">'.$release_year.'</div>';
				$data .= '</div>';
			endif;

			if ($count === 15) $data .= '<div class="show_full_credits show_all">View All</div><div class="wrap_full_credits" style="display:none">';
		}
		if ($count >= 15) $data .= '</div>';
		$data .= '</div>';
	} else {
		if ($options['tmu_tv_series'] === 'on' && $options['tmu_movies'] === 'on') { 
			$data .= '<ul class="nav_tabs">';
			$data .= '<li class="tab_item'.($credits_acting_movie ? ' active' : '').'" id="movie_btn">Movies <span class="opacity-5">('.($acting_movies_count).')</span></li>';
			$data .= '<li class="tab_item'.(!$credits_acting_movie && $credits_acting_tv ? ' active' : '').'">TV Series <span class="opacity-5">('.($acting_tv_count).')</span></li>';
			$data .= '</ul>';
		}
		if (isset($credits_acting_movie) && $credits_acting_movie) { 
			$data .= '<div class="acting_movies" id="movies">';
			$count = 0;
			foreach($credits_acting_movie as $post){
				$credit_post_id = $post->movie;
				$release_year = $post->release_year ? date('Y', $post->release_year) : 'TBA';

				if($credit_post_id): $count++; 
					$data .= '<div class="item">';
					$data .= '<div class="item-block">';
					$data .= '<div class="item-img"><img '. (has_post_thumbnail($credit_post_id) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url($credit_post_id, 'medium').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ).' alt="'.get_the_title($credit_post_id).'" width="100%" height="100%"></div>';
					$data .= '<div class="item-title">';
					$data .= get_linked_post($credit_post_id);
					$data .= '<div class="job">'.stripslashes($post->job).'</div>';
					$data .= '</div>';
					$data .= '</div>';
					$data .= '<div class="item-extra">'.$release_year.'</div>';
					$data .= '</div>';
				endif;

				if ($count === 15) $data .= '<div class="show_full_credits show_all">View All</div><div class="wrap_full_credits" style="display:none">';
			}
			if ($count >= 15) $data .= '</div>';
			$data .= '</div>';
		} 

		if(isset($credits_acting_tv) && $credits_acting_tv){ 
			$data .= '<div class="acting_tv_series" id="tv_series">';
			$count = 0;
			foreach($credits_acting_tv as $post){
				$credit_post_id = $post->tv_series;
				$release_year = $post->release_year ? date('Y', $post->release_year) : 'TBA';

				if($credit_post_id): $count++; 
					$data .= '<div class="item">';
					$data .= '<div class="item-block">';
					$data .= '<div class="item-img"><img '. (has_post_thumbnail($credit_post_id) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url($credit_post_id, 'medium').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ).' alt="'.get_the_title($credit_post_id).'" width="100%" height="100%"></div>';
					$data .= '<div class="item-title">';
					$data .= get_linked_post($credit_post_id);
					$data .= '<div class="job">'.stripslashes($post->job).'</div>';
					$data .= '</div>';
					$data .= '</div>';
					$data .= '<div class="item-extra">'.$release_year.'</div>';
					$data .= '</div>';
				endif;

				if ($count === 15) $data .= '<div class="show_full_credits show_all">View All</div><div class="wrap_full_credits" style="display:none">';
			}
			if ($count >= 15) $data .= '</div>';
			$data .= '</div>';
		} 
	}
	$data .= '</div>';
	if($count_production){
		$data .= '<button class="subtitle" data-target="content-2">';
		$data .= '<h3 class="sub-title-block"><div class="sub-title">Production</div><div class="credits-count">( '.$count_production.' credits)</div></h3>';
		$data .= '<div class="icon"><svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" data-testid="ArrowDropDownOutlinedIcon" class="svg-icon" height="1em" width="1em"><path d="m7 10 5 5 5-5H7z"></path></svg></div>';
		$data .= '</button>';
		$data .= '<div class="items related'.($options['tmu_dramas'] === 'on' ? (!$credits_acting_drama ? '' : ' hidden') : (!$credits_acting_movie && !$credits_acting_tv && $credits_production_movie ? '' : ' hidden')).' content-2">';
		if ($options['tmu_dramas'] === 'on') { 
			$data .= '<div class="production_dramas">';
			foreach($credits_production_drama as $post){
				$credit_post_id = $post->dramas;
				$release_year = $post->release_year ? date('Y', $post->release_year) : 'TBA';
				if($credit_post_id): 
					$data .= '<div class="item">';
					$data .= '<div class="item-block">';
					$data .= '<div class="item-img"><img '. (has_post_thumbnail($credit_post_id) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url($credit_post_id, 'medium').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ).' alt="'.get_the_title($credit_post_id).'" width="100%" height="100%"></div>';
					$data .= '<div class="item-title">';
					$data .= get_linked_post($credit_post_id);
					$data .= '<div class="job">'.stripslashes($post->job).'</div>';
					$data .= '</div>';
					$data .= '</div>';
					$data .= '<div class="item-extra">'.$release_year.'</div>';
					$data .= '</div>';
				endif;
				if ($count === 15) $data .= '<div class="show_full_credits show_all">View All</div><div class="wrap_full_credits" style="display:none">';
			}
			if ($count >= 15) $data .= '</div>';
			$data .= '</div>';
		} else {
			$count = 0; 
			if (isset($credits_production_movie) && $credits_production_movie) { 
				$data .= '<div class="production_movies">';
				foreach($credits_production_movie as $post){
					$credit_post_id = $post->movie;
					$release_year = $post->release_year ? date('Y', $post->release_year) : 'TBA';
					if($credit_post_id): $count++; 
						$data .= '<div class="item">';
						$data .= '<div class="item-block">';
						$data .= '<div class="item-img"><img '. (has_post_thumbnail($credit_post_id) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url($credit_post_id, 'medium').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ).' alt="'.get_the_title($credit_post_id).'" width="100%" height="100%"></div>';
						$data .= '<div class="item-title">';
						$data .= get_linked_post($credit_post_id);
						$data .= '<div class="job">'.stripslashes($post->job).'</div>';
						$data .= '</div>';
						$data .= '</div>';
						$data .= '<div class="item-extra">'.$release_year.'</div>';
						$data .= '</div>';
					endif;
					if ($count === 15) $data .= '<div class="show_full_credits show_all">View All</div><div class="wrap_full_credits" style="display:none">';
				}
				$data .= '</div>';
			} 

			if (isset($credits_production_tv) && $credits_production_tv) { 
				$data .= '<div class="production_tv_series">';
				foreach($credits_production_tv as $post){
					$credit_post_id = $post->tv_series;
					$release_year = $post->release_year ? date('Y', $post->release_year) : 'TBA';
					if($credit_post_id): 
						$data .= '<div class="item">';
						$data .= '<div class="item-block">';
						$data .= '<div class="item-img"><img '. (has_post_thumbnail($credit_post_id) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url($credit_post_id, 'medium').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ).' alt="'.get_the_title($credit_post_id).'" width="100%" height="100%"></div>';
						$data .= '<div class="item-title">';
						$data .= get_linked_post($credit_post_id);
						$data .= '<div class="job">'.stripslashes($post->job).'</div>';
						$data .= '</div>';
						$data .= '</div>';
						$data .= '<div class="item-extra">'.$release_year.'</div>';
						$data .= '</div>';
					endif;
					if ($count === 15) $data .= '<div class="show_full_credits show_all">View All</div><div class="wrap_full_credits" style="display:none">';
				}
				if ($count >= 15) $data .= '</div>';
				$data .= '</div>';
			}
		}
		$data .= '</div>';
	}
	$data .= '</div>';
	$data .= '<script>
function toggleContent(button) {
  const targetId = button.dataset.target; 
  const contentDiv = document.querySelector(`.${targetId}`); 

  button.querySelector(".icon").classList.toggle("rotate-180");
  contentDiv.classList.toggle("hidden");
}

const buttons = document.querySelectorAll(".subtitle");
buttons.forEach(button => {
  button.addEventListener("click", () => toggleContent(button)); 
});

const tabItems = document.querySelectorAll(".tab_item");
const movies_section = document.getElementById("movies");
const tv_series_section = document.getElementById("tv_series");

const movie = document.getElementById("movie_btn");
if (movie) {
	if (movie.classList.contains("active")) {
	  movies_section.style.display = "block";
	  tv_series_section.style.display = "none";
	} else {
	  movies_section.style.display = "none";
	  tv_series_section.style.display = "block";
	}
}

tabItems.forEach(tabItem => {
  tabItem.addEventListener("click", function() {
    // Remove active class from all tabs
    tabItems.forEach(item => item.classList.remove("active"));
    // Add active class to the clicked tab
    this.classList.add("active");

    // Toggle visibility based on clicked tab
    if (movies_section) {
    	if (this.id == "movie_btn") {
	      movies_section.style.display = "block";
	      tv_series_section.style.display = "none";
	    } else {
	      movies_section.style.display = "none";
	      tv_series_section.style.display = "block";
	    }
    }
  });
});

const showFullCreditsButtons = document.querySelectorAll(".show_all");

showFullCreditsButtons.forEach(button => {
  button.addEventListener("click", function() {
    // Hide the clicked button
    this.style.display = "none";

    // Get the next sibling element (wrap_full_credits)
    const nextSibling = this.nextElementSibling;

    // Show the next sibling element
    nextSibling.style.display = "block";
  });
});

</script>';
	return $data;
}

function combine_production($credits){
	$combinedCredits = [];

	foreach ($credits as $credit) {
	  $credit_id = isset($credit->movie) ? $credit->movie : (isset($credit->tv_series) ? $credit->tv_series : '');
	  
	  if ($credit_id) {
	  	if (!isset($combinedCredits[$credit_id])) {
		    $combinedCredits[$credit_id] = $credit;
		  } else {
		    $combinedCredits[$credit_id]->job .= "<br>" . $credit->job;
		  }
	  }
	  
	}

	return $combinedCredits;
}

function person_latest($post_id){
	global $wpdb;
	$options = get_options(['tmu_movies', 'tmu_tv_series', 'tmu_dramas']);
	if ($options['tmu_movies'] === 'on' || $options['tmu_tv_series'] === 'on') {
		$person_movie = $wpdb->get_var("SELECT cast.movie FROM {$wpdb->prefix}tmu_movies_cast cast JOIN {$wpdb->prefix}posts AS posts ON (cast.movie = posts.ID) WHERE cast.`person` = $post_id AND cast.release_year<=unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) AND posts.post_status = 'publish' ORDER BY cast.`release_year` DESC LIMIT 1");
	  $person_movie = $person_movie ?? $wpdb->get_var("SELECT cast.tv_series FROM {$wpdb->prefix}tmu_tv_series_cast cast JOIN {$wpdb->prefix}posts AS posts ON (cast.tv_series = posts.ID) WHERE cast.`person` = $post_id AND release_year<=unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) AND posts.post_status = 'publish' ORDER BY cast.`release_year` DESC LIMIT 1");
	  $person_movie = $person_movie ?? $wpdb->get_var("SELECT crew.movie FROM {$wpdb->prefix}tmu_movies_crew crew JOIN {$wpdb->prefix}posts AS posts ON (crew.movie = posts.ID) WHERE crew.`person` = $post_id AND crew.release_year<=unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) AND posts.post_status = 'publish' ORDER BY crew.`release_year` DESC LIMIT 1");
	  $person_movie = $person_movie ?? $wpdb->get_var("SELECT crew.tv_series FROM {$wpdb->prefix}tmu_tv_series_crew crew JOIN {$wpdb->prefix}posts AS posts ON (crew.tv_series = posts.ID) WHERE crew.`person` = $post_id AND crew.release_year<=unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) AND posts.post_status = 'publish' ORDER BY crew.`release_year` DESC LIMIT 1");
	} elseif ($options['tmu_dramas'] === 'on') {
		$person_movie = $wpdb->get_var("SELECT cast.dramas FROM {$wpdb->prefix}tmu_dramas_cast cast JOIN {$wpdb->prefix}posts AS posts ON (cast.dramas = posts.ID) WHERE cast.`person` = $post_id AND release_year<=unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) AND posts.post_status = 'publish' ORDER BY cast.`release_year` DESC LIMIT 1");
		$person_movie = $person_movie ?? $wpdb->get_var("SELECT crew.dramas FROM {$wpdb->prefix}tmu_dramas_crew crew JOIN {$wpdb->prefix}posts AS posts ON (crew.dramas = posts.ID) WHERE crew.`person` = $post_id AND crew.release_year<=unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) AND posts.post_status = 'publish' ORDER BY crew.`release_year` DESC LIMIT 1");
	}

  return $person_movie;
}

function person_upcoming($post_id){
	global $wpdb;
	$options = get_options(['tmu_movies', 'tmu_tv_series', 'tmu_dramas']);
	if ($options['tmu_movies'] === 'on' || $options['tmu_tv_series'] === 'on') {
		$person_movie = $wpdb->get_var("SELECT cast.movie FROM {$wpdb->prefix}tmu_movies_cast cast JOIN {$wpdb->prefix}posts AS posts ON (cast.movie = posts.ID) WHERE cast.`person` = $post_id AND release_year>unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) AND posts.post_status = 'publish' ORDER BY cast.`release_year` ASC LIMIT 1");
	  $person_movie = $person_movie ?? $wpdb->get_var("SELECT cast.tv_series FROM {$wpdb->prefix}tmu_tv_series_cast cast JOIN {$wpdb->prefix}posts AS posts ON (cast.tv_series = posts.ID) WHERE cast.`person` = $post_id AND release_year>unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) AND posts.post_status = 'publish' ORDER BY cast.`release_year` ASC LIMIT 1");
	  $person_movie = $person_movie ?? $wpdb->get_var("SELECT crew.movie FROM {$wpdb->prefix}tmu_movies_crew crew JOIN {$wpdb->prefix}posts AS posts ON (crew.movie = posts.ID) WHERE crew.`person` = $post_id AND crew.release_year>unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) AND posts.post_status = 'publish' ORDER BY crew.`release_year` ASC LIMIT 1");
	  $person_movie = $person_movie ?? $wpdb->get_var("SELECT crew.tv_series FROM {$wpdb->prefix}tmu_tv_series_crew crew JOIN {$wpdb->prefix}posts AS posts ON (crew.tv_series = posts.ID) WHERE crew.`person` = $post_id AND crew.release_year>unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) AND posts.post_status = 'publish' ORDER BY crew.`release_year` ASC LIMIT 1");
	} elseif ($options['tmu_dramas'] === 'on') {
		$person_movie = $wpdb->get_var("SELECT cast.dramas FROM {$wpdb->prefix}tmu_dramas_cast cast JOIN {$wpdb->prefix}posts AS posts ON (cast.dramas = posts.ID) WHERE cast.`person` = $post_id AND release_year>unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) AND posts.post_status = 'publish' ORDER BY cast.`release_year` ASC LIMIT 1");
  	$person_movie = $person_movie ?? $wpdb->get_var("SELECT crew.dramas FROM {$wpdb->prefix}tmu_dramas_crew crew JOIN {$wpdb->prefix}posts AS posts ON (crew.dramas = posts.ID) WHERE crew.`person` = $post_id AND crew.release_year>unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) AND posts.post_status = 'publish' ORDER BY crew.`release_year` ASC LIMIT 1");
	}

  return $person_movie;
}

function known_for(){
	$post_ids = rwmb_meta( 'known_for' );
	$data = '';
	if($post_ids){
		$data .= '<style type="text/css">
.heading {
  display: flex;
  justify-content: space-between;
  margin-top: 10px;
  margin-bottom: 15px;
}

.heading h2 { font-size: 22px; font-weight: 700; }

.known-for-details .known-item-title {
	text-decoration: underline;
  font-size: 15px;
  font-weight: bold;
  line-height: 1.25;
  color: #0c0c0f;
  margin-bottom: 0 !important;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  padding: 10px 0;
  text-align: center;
}

.scroll-btns { display:flex; gap:10px; }

.scroll-btn {
	padding: 0;
    margin: 0;
    background: unset;
    height: 30px;
    width: 30px;
}
.scroll-btn path { fill:white }
.known-for {
	margin-bottom: 30px;
}

.known-for-flex {
	display: flex;
  -webkit-overflow-scrolling: touch;
  gap: 8px;
  overflow-x: auto;	
  scroll-behavior: smooth;
	transition: scroll-left 5s ease-in-out;
}

.known-for-box {
	display: block;
  width: 160px;
  margin-bottom: 20px;
  text-align: center;
  box-shadow: 0 4px 8px #0000001a;
  transition: transform .3s ease-in-out;
  transform-style: preserve-3d;
  border-radius: 10px;
  border: 1px solid #ddd;
}

.known-for-box:hover {
    transform: translateY(-5px) translateZ(20px) rotateX(5deg) rotateY(5deg);
    box-shadow: 0 10px 20px #0003, 0 0 15px #0000001a;
    border-radius: 10px;
}

.known-for-poster {
	width: 160px;
	height: 220px;
	overflow: hidden;
}
.known-for-poster img {
	width: 160px;
  height: 220px;
	object-fit: cover;
}
#showMoreLink { cursor: pointer; }
@media screen and (max-width: 768px) {
	.scroll-btns { display:none }
}
</style>
<div class="known-for scrollable-section" data-scroll-target="#known-for">
	<div class="heading">
		<h2>Known For</h2>'.(count($post_ids) > 5 ? '<div class="scroll-btns">'.button_left_person().button_right_person().'</div>' : '').'
	</div>
	<div class="known-for-flex scrollable-content" id="known-for">';
		foreach ($post_ids as $result) {
			if (get_post_status($result) === 'publish') {
				global $wpdb;
				$title = get_the_title($result);
	  		$data .= '<a class="known-for-box" href="'.get_permalink($result).'" title="'.$title.'">';
	  		$data .= '<div class="known-for-poster">';
	  		$data .= '<img '. (has_post_thumbnail($result) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url($result, 'full').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ).' alt="'.$title.'" width="100%" height="100%">';
	  		$data .= '</div>';
	  		$data .= '<div class="known-for-details">';
	  		$data .= '<p class="known-item-title">'.$title.'</p>';
	  		$data .= '</div>';
	  		$data .= '</a>';
	  	}
	  }
		$data .= '</div>';
		$data .= '</div>';
		$data .= '<script>
function scrollRelease(button) {
  const scrollContainer = button.closest(".scrollable-section");
  const scrollTarget = scrollContainer.dataset.scrollTarget;
  const direction = button.dataset.direction ? parseInt(button.dataset.direction) : 1; // Default right scroll

  const scrollElement = document.querySelector(scrollTarget);

  if (scrollElement) {
    scrollElement.scrollLeft += direction * 1000; // Adjust scroll distance as needed
  } else {
    console.warn("Scroll element not found with selector:", scrollTarget);
  }
}
</script>';
	}
	return $data;
}

function button_left_person(){
  return '
  <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 30 30" class="btn-left">
	  <rect id="Rectangle_154" data-name="Rectangle 154" width="30" height="30"/>
	  <path id="Icon_material-keyboard-arrow-right" data-name="Icon material-keyboard-arrow-right" class="cls-12" d="M22.885,22.916,16.7,16.722l6.181-6.194-1.9-1.9-8.1,8.1,8.1,8.1Z" transform="translate(-4.084 -1.625)"/>
	</svg>';
}

function button_right_person(){
  return '
  <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 30 30" class="btn-right">
	  <g id="Group_276" data-name="Group 276" transform="translate(-1290 -476)">
	    <rect id="Rectangle_154" data-name="Rectangle 154" width="30" height="30" transform="translate(1290 476)"/>
	    <path id="Icon_material-keyboard-arrow-right" data-name="Icon material-keyboard-arrow-right" class="cls-12" d="M12.885,22.916l6.181-6.194-6.181-6.194,1.9-1.9,8.1,8.1-8.1,8.1Z" transform="translate(1288.314 474.375)"/>
	  </g>
	</svg>';
}

