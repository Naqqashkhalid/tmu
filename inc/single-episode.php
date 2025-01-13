<?php

add_filter( 'the_content', 'single_episode' );
function single_episode($content){
  if ( !(get_post_type() == 'episode' && is_singular()) ) return $content;
  $post_id = get_the_ID();

  global $wpdb;
  $episode = $wpdb->get_row($wpdb->prepare("SELECT episode.* FROM {$wpdb->prefix}tmu_tv_series_episodes as episode JOIN {$wpdb->prefix}posts AS posts ON (episode.ID = posts.ID) WHERE episode.`ID` = %d AND posts.post_status = 'publish'", $post_id), ARRAY_A);
  $episode['runtime'] = $episode['runtime'] ?? $wpdb->get_var("SELECT runtime FROM {$wpdb->prefix}tmu_tv_series WHERE `ID` = ".$episode['tv_series']);
  $episode['runtime'] = (int)$episode['runtime'];
  $episode['runtime'] = $episode['runtime'] ? $episode['runtime'].'m' : '';
  $episode['credits'] = $episode['credits'] ? @unserialize($episode['credits']) : ['cast' => [], 'crew' => [] ];
  $episode['season_permalink'] = get_permalink($episode['season_id']);
  $tv_series_title = get_the_title($episode['tv_series']);

  $permalink = get_permalink($post_id);

  // $tmdb_rating['average'] = $episode['average_rating'];
  // $tmdb_rating['count'] = $episode['vote_count'];

  // $comments = get_comments(array('post_id' => $post_id, 'status' => 'approve'));
  // $average_ratings = get_average_ratings($comments, $tmdb_rating);

  $average_ratings = ['average' => $episode['total_average_rating'], 'count' => $episode['total_vote_count']];

  $director_id = get_credits_ids_by_profession($episode['credits'][ 'crew' ], 'directing', 'Director', 1);
  $producer_id = get_credits_ids_by_profession($episode['credits']['crew'], 'production', 'Producer', 1);

  $director_id = $director_id ? (is_array($director_id) ? $director_id[0] : $director_id) : $wpdb->get_var($wpdb->prepare("SELECT crew.person FROM {$wpdb->prefix}tmu_tv_series_crew as crew JOIN {$wpdb->prefix}posts AS posts ON (crew.ID = posts.ID) WHERE crew.tv_series = %d AND crew.job='Director' AND posts.post_status = 'publish'", $episode['tv_series']));
  $producer_id = $producer_id ? (is_array($producer_id) ? $producer_id[0] : $producer_id) : $wpdb->get_var($wpdb->prepare("SELECT crew.person FROM {$wpdb->prefix}tmu_tv_series_crew as crew JOIN {$wpdb->prefix}posts AS posts ON (crew.ID = posts.ID) WHERE crew.tv_series = %d AND crew.job='Producer' AND posts.post_status = 'publish'", $episode['tv_series']));

  // if ('publish' !== get_post_status( $director_id )) {	$wpdb->delete( $wpdb->prefix . 'tmu_tv_series_crew', ['ID' => $director_id], ['%d'] ); }
  // if ('publish' !== get_post_status( $producer_id )) {	$wpdb->delete( $wpdb->prefix . 'tmu_tv_series_crew', ['ID' => $producer_id], ['%d'] ); }

  $director = $director_id ? '<a href="'.get_permalink($director_id).'" class="credits-member" title="'.get_the_title($director_id).'">'.get_the_title($director_id).'</a>' : '';
  $producer = $producer_id ? '<a href="'.get_permalink($producer_id).'" class="credits-member" title="'.get_the_title($producer_id).'">'.get_the_title($producer_id).'</a>' : '';

  $next_episode = get_episode_no($episode['tv_series'], $episode['season_no'], $episode['episode_no']+1);
  $prev_episode = get_episode_no($episode['tv_series'], $episode['season_no'], $episode['episode_no']-1);

  $genres = json_encode(wp_get_object_terms( $episode['tv_series'], 'genre', array('fields' => 'names') ));
  $casts = episode_credit($episode['credits']['cast']);

  $episode['overview'] = $episode['overview'] ?? get_episode_seo_desc($post_id);
  $episode['overview'] = $episode['overview'] ? $episode['overview'] : ($episode['air_date_timestamp'] && $episode['air_date_timestamp'] < time() ? $tv_series_title." {$episode['episode_title']} Release on ".date( 'd M Y', $episode['air_date_timestamp'] )." with Eng Sub is now live! ".get_bloginfo('name')." bring you the drama as soon as it’s Aired, so don’t miss out. Bookmark this site for instant access to future episodes. Enjoy the $tv_series_title with free streaming and download options available online." : '');
  $new_content = '
	<div class="episode_sec">

		<ul class="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a itemprop="item" href="'.get_site_url().'" title="Home"><span itemprop="name">Home</span></a><meta itemprop="position" content="1" /></li>
			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"> / <a itemprop="item" href="'.get_post_type_archive_link( 'tv' ).'" title="TV"><span itemprop="name">TV</span></a><meta itemprop="position" content="2" /></li>
			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"> / <a itemprop="item" href="'.get_permalink($episode['tv_series']).'" title="Home"><span itemprop="'.$tv_series_title.'">'.$tv_series_title.'</span></a><meta itemprop="position" content="3" /></li>
			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"> / <a itemprop="item" href="'.$episode['season_permalink'].'" title="Season '.$episode['season_no'].'"><span itemprop="name">Season '.$episode['season_no'].'</span></a><meta itemprop="position" content="4" /></li>
			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"> / <span itemprop="name">Episode '.$episode['episode_no'].'<meta itemprop="item" content="'.$permalink.'" /></span><meta itemprop="position" content="5" /></li>
		</ul>

		<div class="grid_episode_sec">
			'.episode_header($episode, $director, $producer, $average_ratings).'

				<div class="next-prev">
					<div class="prev-episode">'.($prev_episode ? '<a href="'.get_permalink($prev_episode).'" title="Previous Episode"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left-square" viewBox="0 0 16 16">
	  <path fill-rule="evenodd" d="M15 2a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1zM0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm11.5 5.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5z"/>
	</svg> Previous Episode</a>' : '').'</div>
					<div class="next-episode">'.($next_episode ? '<a href="'.get_permalink($next_episode).'" title="Next Episode">Next Episode  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right-square" viewBox="0 0 16 16">
	  <path fill-rule="evenodd" d="M15 2a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1zM0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm4.5 5.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5z"/>
	</svg></a>' : '').'</div>
				</div>

			'.($episode['overview'] ? '<div class="short-heading font-size-36 weight-700">
				<h2 class="images-title weight-700 font-size-36">Episode Plot</h2>
				<svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" data-testid="KeyboardArrowRightOutlinedIcon" class="svg-icon icon-lg" height="1em" width="1em">
					<path d="M8.59 16.59 13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"></path>
				</svg>
			</div>
			<div>'.$episode['overview'].'</div>' : '').'

			'.((isset($episode['credits']['crew'][0]['person']) && $episode['credits']['crew'][0]['person']) || (isset($episode['credits']['cast'][0]['person']) && $episode['credits']['cast'][0]['person']) ? '
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
				<div class="full_crew" id="crew" style="display: none;">'.episode_credit($episode['credits']['crew'])['credits'].'</div>
			</div>
				' : '' ).'

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

function episode_header($episode, $director, $producer, $average_ratings){
	$where_to_watch = rwmb_meta( 'streaming_platforms', '', $episode['tv_series'] );
  
  return '<section class="episode-section">
		<div class="feature-image">'.(has_post_thumbnail() ? get_the_post_thumbnail() : '<img src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp">').'</div>
		<div class="episode_details">
			
			<h1 class="font-size-36">'.$episode['episode_title'].'</h1>
			
			<div class="rating-box">'.get_post_rating($average_ratings).'<div class="rate-this">'.rate_this_episode().'</div></div>
			
			<div class="release">Aired: '.($episode['air_date'] ? date( 'd M Y', $episode['air_date_timestamp'] ).' <span class="sep">|</span> ' : '').($episode['runtime'] ? $episode['runtime'] : '').'  <span class="sep">|</span> <a href="'.get_permalink($episode['tv_series']).'" ><span "'.get_the_title($episode['tv_series']).'">'.get_the_title($episode['tv_series']).'</span></a></div>
			
			<div class="credits_main">
				'.($director ? '<div'.(!$producer ? ' class="width-100"' : '').'>'.$director.'<div class="font-size-13">Director</div></div>' : '').
				($producer ? '<div'.(!$director ? ' class="width-100"' : '').'>'.$producer.'<div class="font-size-13">Producer</div></div>' : '').'
			</div>
			<div class="watch-buttons">
					'.($where_to_watch ? '<a href="' . get_site_url() . '/redirect/'.$where_to_watch.'" target="_blank" title="WHERE TO WATCH">WHERE TO WATCH</a>' : '').'
			</div>
		</div>
	</section>';
}

function episode_credit($credits, $job=''){
	$credits = $credits && is_array($credits) ? $credits : [$credits];
	$count = 0;
	$schema = '';
	if($credits && is_array($credits)):
		$data = '';
		foreach ( $credits as $credit ) {
		  if(isset($credit[ 'person' ]) && $credit[ 'person' ]):
		  	$count++;
		  	$permalink = get_permalink($credit[ 'person' ]);
		  	$title = get_the_title($credit[ 'person' ]);
			$data .= '<div class="credit-block">
				<div class="block1">
					<div class="credit-img"><img '.(has_post_thumbnail($credit[ 'person' ]) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/icons/no-image.svg" data-src="'.get_the_post_thumbnail_url($credit[ 'person' ], 'medium').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/icons/no-image.svg"') ) .' alt="'.$title.'"></div>
					<div class="credit-name"><a href="'.get_permalink($credit[ 'person' ]).'" class="cast-name" title="'.$title.'">'.$title.'</a></div>
				</div>
				<div class="credit-character">'.($job ? (stripslashes( $credit[ $job ] ) ?? 'TBA') : $credit[ clean_job_string($credit['department']).'_job' ]).'</div>
			</div>';
			if ($credit['department'] == 'Acting' && $count < 5) {
				$schema .= ($count === 1 ? '' : ',').'{"@type":"Person","name":"'.$title.'","sameAs":"'.$permalink.'"'.(has_post_thumbnail($credit[ 'person' ]) ? ',"image":"'.get_the_post_thumbnail_url($credit[ 'person' ], 'full').'"' : '').'}';
			}
			if ($count === 5) $data .= '<div class="show_full_credits">View All</div><div class="wrap_full_credits" style="display:none">';
		  endif;
		}
		if ($count >= 5) $data .= '</div>';
	endif;

	if ($count == 0) { $data .= "We don't have any credits added to this Episode."; }

	return ['credits' => $data, 'schema' => $schema];
}


function get_episode_no($tv_series, $season, $episode){
	global $wpdb;
	$table_name = $wpdb->prefix.'tmu_tv_series_episodes';
	$episode = $wpdb->get_var("SELECT episode.ID FROM $table_name as episode JOIN {$wpdb->prefix}posts AS posts ON (episode.ID = posts.ID) WHERE episode.tv_series=$tv_series AND episode.season_no=$season AND episode.episode_no=$episode AND posts.post_status = 'publish'");
	if ($episode) {
		return $episode;
	}
}


function rate_this_episode(){
	$post_id = get_the_ID();
    $title = get_the_title();
    $user_ip = get_client_ip();
    $post_type = get_post_type($post_id);

    global $wpdb;
    $table_name = $wpdb->prefix.'comments';
    $comment_rating = $wpdb->get_var("SELECT $table_name.comment_rating FROM $table_name JOIN {$wpdb->prefix}posts AS posts ON ({$table_name}.comment_post_ID = posts.ID) WHERE $table_name.comment_post_ID=$post_id AND $table_name.comment_author_IP='$user_ip' AND posts.post_status = 'publish'");
	
	$data = '<button class="rate-this-button">
		'.($comment_rating ? 'Your Rating '.$comment_rating.'/10' : '<svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" data-testid="StarBorderOutlinedIcon" class="svg-icon m-auto -ml-4 mr-8 icon-sm" height="1em" width="1em"><path d="m22 9.24-7.19-.62L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21 12 17.27 18.18 21l-1.63-7.03L22 9.24zM12 15.4l-3.76 2.27 1-4.28-3.32-2.88 4.38-.38L12 6.1l1.71 4.04 4.38.38-3.32 2.88 1 4.28L12 15.4z"></path></svg> Rate this').'
	</button>

    <div class="rate-this-pop-modal" style="display: none;">
        <div class="rate-container">
            <div class="rate-top">
                <div class="text-top">Rate this '.($post_type==='tv' ? 'TV Show' : ($post_type==='episode' ? 'Episode' : 'Movie')).'</div><button class="close-pop-modal"><svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" data-testid="CloseOutlinedIcon" class="svg-icon icon-sm" height="1em" width="1em"><path d="M19 6.41 17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"></path></svg></button>
            </div>
            <div class="rate-details">
                <div class="rate-item">
                    <div class="rate-item-poster"><img src="'.(has_post_thumbnail($post_id) ? get_the_post_thumbnail_url($post_id, 'medium') : plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp').'" alt="Poster for '.$title.'"></div>
                    <div class="rate-item-text">
                        <div>';
                        	if ($post_type==='episode') {
                        		$series_id = rwmb_meta( 'tv_series' );
                        		$series_title = get_the_title($series_id);
                        		$data .= '<a href="'.get_permalink($series_id).'" style="color: black; font-weight: 600; font-size: 20px; text-decoration: underline !important;" title="'.$series_title.'">'.get_the_title($series_id).'</a>';
                        	}
                        	$data .= '
                        </div>
                        <div>'.($post_type==='episode' ? rwmb_meta( 'episode_title', '', $post_id ) : $title).'</div>
                        <div>'.($post_type!=='episode' ? implode(', ', wp_get_object_terms( $post_id, 'by-year', array('fields' => 'names') )) : '' ).'</div>
                    </div>
                </div>

                <div class="select-stars-rating">
                    <input type="radio" name="select-stars-rating" value="10">
                    <input type="radio" name="select-stars-rating" value="9">
                    <input type="radio" name="select-stars-rating" value="8">
                    <input type="radio" name="select-stars-rating" value="7">
                    <input type="radio" name="select-stars-rating" value="6">
                    <input type="radio" name="select-stars-rating" value="5">
                    <input type="radio" name="select-stars-rating" value="4">
                    <input type="radio" name="select-stars-rating" value="3">
                    <input type="radio" name="select-stars-rating" value="2">
                    <input type="radio" name="select-stars-rating" value="1">
                </div>

                <button class="rate-submit-button" data-post-id="'.$post_id.'">Rate</button>

            </div>
        </div>
    </div>';

    return $data;
}

function get_episode_seo_desc($post_id){
	global $wpdb;
	$description = $wpdb->get_var("SELECT description FROM {$wpdb->prefix}tmu_seo_options WHERE post_type = 'episode'  AND section = 'single'");
  	return $description ? replace_tags($description, 'single', 'episode', $post_id) : '';
}

