<?php

add_filter( 'the_content', 'single_drama_episode' );
function single_drama_episode($content){
  if ( !(get_post_type() == 'drama-episode' && is_singular()) ) return $content;
  $post_id = get_the_ID();

  global $wpdb;
  $episode = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}tmu_dramas_episodes WHERE `ID` = %d", $post_id), ARRAY_A);

  $episode['episode_title'] = rwmb_meta( 'episode_title' );
  $episode['air_date'] = rwmb_meta( 'air_date' );
  $episode['episode_type'] = rwmb_meta( 'episode_type' );
  $episode['runtime'] = (int)$episode['runtime']; $runtime_hours = $episode['runtime']/60; $runtime_hours = $runtime_hours > 1 ? round($runtime_hours) : 0;
  $episode['runtime'] = $episode['runtime'] ? ($runtime_hours==0 ? '' : $runtime_hours.'h ').($episode['runtime']%60).'m' : '';
  $episode['credits'] = $episode['credits'] && (isset($episode['credits'][ 'crew' ]) || isset($episode['credits'][ 'cast' ])) ? @unserialize($episode['credits']) : ['cast' => [], 'crew' => [] ];

  $permalink = get_permalink($post_id);
  $title = get_the_title($post_id);

  // $tmdb_rating['average'] = $episode['average_rating'];
  // $tmdb_rating['count'] = $episode['vote_count'];

  // $comments = get_comments(array('post_id' => $post_id, 'status' => 'approve'));
  // $average_ratings = get_average_ratings($comments, $tmdb_rating);
  $average_ratings = ['average' => $episode['total_average_rating'], 'count' => $episode['total_vote_count']];

  $director_id = get_credits_ids_by_profession($episode['credits'][ 'crew' ], 'directing', 'Director', 1);
  $producer_id = get_credits_ids_by_profession($episode['credits']['crew'], 'production', 'Producer', 1);

  $director_id = $director_id ? (is_array($director_id) ? $director_id[0] : $director_id) : $wpdb->get_var($wpdb->prepare("SELECT crew.person FROM {$wpdb->prefix}tmu_dramas_crew crew JOIN {$wpdb->prefix}posts AS posts ON (crew.ID = posts.ID) WHERE crew.dramas = %d AND crew.job='Director' AND posts.post_status = 'publish'", $episode['dramas']));
  $producer_id = $producer_id ? (is_array($producer_id) ? $producer_id[0] : $producer_id) : $wpdb->get_var($wpdb->prepare("SELECT crew.person FROM {$wpdb->prefix}tmu_dramas_crew crew JOIN {$wpdb->prefix}posts AS posts ON (crew.ID = posts.ID) WHERE crew.dramas = %d AND crew.job='Producer' AND posts.post_status = 'publish'", $episode['dramas']));

  // if ('publish' !== get_post_status( $director_id )) {	$wpdb->delete( $wpdb->prefix . 'tmu_dramas_crew', ['ID' => $director_id], ['%d'] ); }
  // if ('publish' !== get_post_status( $producer_id )) {	$wpdb->delete( $wpdb->prefix . 'tmu_dramas_crew', ['ID' => $producer_id], ['%d'] ); }

  $director = $director_id ? '<a href="'.get_permalink($director_id).'" class="credits-member" title="'.get_the_title($director_id).'">'.get_the_title($director_id).'</a>' : '';
  $producer = $producer_id ? '<a href="'.get_permalink($producer_id).'" class="credits-member" title="'.get_the_title($producer_id).'">'.get_the_title($producer_id).'</a>' : '';

  $next_episode = get_drama_episode_no($episode['dramas'], $episode['episode_no']+1);
  $prev_episode = get_drama_episode_no($episode['dramas'], $episode['episode_no']-1);

  $genres = json_encode(wp_get_object_terms( $episode['dramas'], 'genre', array('fields' => 'names') ));

  $casts = $episode['credits']['cast'] ? episode_credit($episode['credits']['cast']) : '';
  $crew = $episode['credits']['crew'] ? episode_credit($episode['credits']['crew']) : '';
  $main_casts = !$casts ? $wpdb->get_results($wpdb->prepare("SELECT cast.* FROM {$wpdb->prefix}tmu_dramas_cast cast JOIN {$wpdb->prefix}posts AS posts ON (cast.ID = posts.ID) WHERE cast.dramas = %d AND posts.post_status = 'publish'", $episode['dramas'])) : '';
  $main_crew = !$crew ? $wpdb->get_results($wpdb->prepare("SELECT crew.* FROM {$wpdb->prefix}tmu_dramas_crew crew JOIN {$wpdb->prefix}posts AS posts ON (crew.ID = posts.ID) WHERE crew.dramas = %d AND posts.post_status = 'publish'", $episode['dramas'])) : '';
  $casts = $casts ? $casts : ($main_casts ? drama_episode_credit($main_casts) : ['credits' => '', 'schema' => '']);
  $crew = $crew ? $crew : ($main_crew ? drama_episode_credit($main_crew) : ['credits' => '', 'schema' => '']);
  $episode['video'] = $episode['video'] ? extractYouTubeId($episode['video']) : '';
  $episode['overview'] = $episode['overview'] ?? get_drama_episode_plot_dessc($post_id);

  $new_content = '
	<div class="episode_sec">

		<ul class="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a itemprop="item" href="'.get_site_url().'" title="Home"><span itemprop="name">Home</span></a><meta itemprop="position" content="1" /></li>
			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"> / <a itemprop="item" href="'.get_post_type_archive_link( 'drama' ).'" title="Drama"><span itemprop="name">Drama</span></a><meta itemprop="position" content="2" /></li>
			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"> / <a itemprop="item" href="'.get_permalink($episode['dramas']).'" title="'.get_the_title($episode['dramas']).'"><span itemprop="name">'.get_the_title($episode['dramas']).'</span></a><meta itemprop="position" content="3" /></li>
			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"> / <span itemprop="name">Episode '.$episode['episode_no'].'<meta itemprop="item" content="'.$permalink.'" /></span><meta itemprop="position" content="4" /></li>
		</ul>

		<div class="grid_episode_sec">
			'.drama_episode_header($episode, $director, $producer, $average_ratings).'

			
			'.('<div class="episode-videos">'.($episode['video'] ? ytplayer($episode['video']) : '').'</div>').'
			
			<div class="next-prev">
				<div class="prev-episode">'.($prev_episode ? '<a href="'.get_permalink($prev_episode).'" title="Previous Episode">Previous Episode</a>' : '').'</div>
				<div class="next-episode">'.($next_episode ? '<a href="'.get_permalink($next_episode).'" title="Next Episode">Next Episode</a>' : '').'</div>
			</div>

			'.($episode['overview'] ? '<div>'.$episode['overview'].'</div>' : '').'

			'.(($casts || $crew) ? '
			<div class="cast_crew">
				<div class="short-heading font-size-36 weight-700">
					<h2 class="images-title weight-700 font-size-36">Cast & Crew</h2>
					<svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" data-testid="KeyboardArrowRightOutlinedIcon" class="svg-icon icon-lg" height="1em" width="1em">
						<path d="M8.59 16.59 13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"></path>
					</svg>
				</div>

				<ul class="nav_tabs">
					<li class="tab_item active">Cast</li>
					<li class="tab_item">Crew</li>
				</ul>
				<div class="full_cast" id="cast">'.$casts['credits'].'</div>
				<div class="full_crew" id="crew" style="display: none;">'.$crew['credits'].'</div>
			</div>' : '' ).'

		</div>
	</div>
  '."
<script type='text/javascript'>
	const tabItems = document.querySelectorAll('.tab_item');
	const castSection = document.getElementById('cast');
	const crewSection = document.getElementById('crew');

	tabItems.forEach(tabItem => {
	  tabItem.addEventListener('click', function() {
	    // Remove active class from all tabs
	    tabItems.forEach(item => item.classList.remove('active'));
	    // Add active class to the clicked tab
	    this.classList.add('active');

	    // Toggle visibility based on clicked tab
	    if (this.textContent === 'Cast') {
	      castSection.style.display = 'block';
	      crewSection.style.display = 'none';
	    } else {
	      castSection.style.display = 'none';
	      crewSection.style.display = 'block';
	    }
	  });
	});

	const showFullCreditsButtons = document.querySelectorAll('.show_full_credits');

	showFullCreditsButtons.forEach(button => {
	  button.addEventListener('click', function() {
	    // Hide the clicked button
	    this.style.display = 'none';

	    // Get the next sibling element (wrap_full_credits)
	    const nextSibling = this.nextElementSibling;

	    // Show the next sibling element
	    nextSibling.style.display = 'block';
	  });
	});

</script>
  ";

  return $new_content;
}

function drama_episode_header($episode, $director, $producer, $average_ratings){
	$where_to_watch = rwmb_meta( 'streaming_platforms', '', $episode['dramas'] );
  
  return '<section class="episode-section">
		<div class="feature-image">'.(has_post_thumbnail() ? get_the_post_thumbnail() : '<img src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp">').'</div>
		<div class="episode_details">
			
			<h1 class="font-size-36">'.get_the_title().'</h1>
			
			<div class="rating-box">'.get_post_rating($average_ratings).'<div class="rate-this">'.rate_this_episode().'</div></div>
			
			<div class="release">'.($episode['air_date'] ? date( 'd M Y', strtotime($episode['air_date']) ).' <span class="sep">|</span> ' : '').($episode['runtime'] ? $episode['runtime'] : '').'</div>
			
			<div class="credits_main">
				'.($director ? '<div'.(!$producer ? ' class="width-100"' : '').'>'.$director.'<div class="font-size-13">Director</div></div>' : '').
				($producer ? '<div'.(!$director ? ' class="width-100"' : '').'>'.$producer.'<div class="font-size-13">Producer</div></div>' : '').'
			</div>
			<div class="watch-buttons">
					'.($where_to_watch ? '<a href="https://www.eonline.live/redirect/'.$where_to_watch.'" target="_blank" title="WHERE TO WATCH">WHERE TO WATCH</a>' : '').'
			</div>
		</div>
	</section>';
}


function get_drama_episode_no($dramas, $episode){
	global $wpdb;
	$table_name = $wpdb->prefix.'tmu_dramas_episodes';
	$episode = $wpdb->get_var("SELECT episode.ID FROM $table_name episode JOIN {$wpdb->prefix}posts AS posts ON (episode.ID = posts.ID) WHERE episode.dramas=$dramas AND episode.episode_no=$episode AND posts.post_status = 'publish'");
	if ($episode) {
		return $episode;
	}
}

function drama_episode_credit($credits, $job=''){
	$credits = $credits && is_array($credits) ? $credits : [$credits];
	$count = 0;
	$schema = '';
	if($credits && is_array($credits)):
		$data = '';
		foreach ( $credits as $credit ) {
		  if($credit->person):
		  	$count++;
		  	$permalink = get_permalink($credit->person);
		  	$title = get_the_title($credit->person);
			$data .= '<div class="credit-block">
				<div class="block1">
					<div class="credit-img"><img '.(has_post_thumbnail($credit->person) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/icons/no-image.svg" data-src="'.get_the_post_thumbnail_url($credit->person, 'medium').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/icons/no-image.svg"') ) .' alt="'.$title.'"></div>
					<div class="credit-name"><a href="'.get_permalink($credit->person).'" class="cast-name" title="'.$title.'">'.$title.'</a></div>
				</div>
				<div class="credit-character">'.$credit->job.'</div>
			</div>';
			if (!isset($credit->department) && $count < 5) {
				$schema .= ($count === 1 ? '' : ',').'{"@type":"Person","name":"'.$title.'","sameAs":"'.$permalink.'"'.(has_post_thumbnail($credit->person) ? ',"image":"'.get_the_post_thumbnail_url($credit->person, 'full').'"' : '').'}';
			}
			if ($count === 5) $data .= '<div class="show_full_credits">View All '.(!isset($credit->department) ? 'Cast' : 'Crew').'</div><div class="wrap_full_credits" style="display:none">';
		  endif;
		}
		if ($count >= 5) $data .= '</div>';
	endif;

	if ($count == 0) { $data .= "We don't have any credits added to this Episode."; }

	return ['credits' => $data, 'schema' => $schema];
}