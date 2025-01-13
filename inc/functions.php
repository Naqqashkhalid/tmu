<?php

require_once __DIR__ . '/custom/includes.php';
require_once __DIR__ . '/archive.php';
require_once __DIR__ . '/images.php';
require_once __DIR__ . '/single-movie.php';
require_once __DIR__ . '/single-tv.php';
require_once __DIR__ . '/single-season.php';
require_once __DIR__ . '/single-episode.php';
require_once __DIR__ . '/single-drama.php';
require_once __DIR__ . '/single-drama-episode.php';
require_once __DIR__ . '/videos-page.php';
require_once __DIR__ . '/single-person.php';
require_once __DIR__ . '/single-video.php';
require_once __DIR__ . '/url-structure.php';
require_once __DIR__ . '/schema.php';
require_once __DIR__ . '/shortcodes.php';
require_once __DIR__ . '/query-search.php';
require_once __DIR__ . '/single-blog-post.php';
require_once __DIR__ . '/footer.php';
require_once __DIR__ . '/sidebar.php';
if ( is_plugin_active( 'seo-by-rank-math/rank-math.php' ) ) require_once __DIR__ . '/rank-math-seo.php';

function breadcrumb(){
  $post_type = get_post_type();
  $category = ($post_type == 'post') ? get_the_category()[0] : '';
  $home = get_site_url();
  $title = get_the_title();
  $permalink = get_permalink();
  $item_name = $category ? $category->name : (($post_type == 'movie' || $post_type == 'tv' || $post_type == 'people' || $post_type == 'drama') ? ($post_type == 'tv' ? strtoupper($post_type) : ucfirst($post_type)) : '');
  $item_link = $category ? get_category_link( $category ) : (($post_type == 'movie' || $post_type == 'tv' || $post_type == 'people' || $post_type == 'drama') ? get_post_type_archive_link( get_post_type() ) : '');
  
  $data = '<ul class="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">';
	  $data .= '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a itemprop="item" href="'.$home.'" title="Home"><span itemprop="name">Home</span></a><meta itemprop="position" content="1" /></li>';
  	if($item_name) $data .= '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"> / <a itemprop="item" href="'.$item_link.'" title="'.$item_name.'"><span itemprop="name">'.$item_name.'</span></a><meta itemprop="position" content="2" /></li>';
	  $data .= '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"> / <meta itemprop="item" content="'.$permalink.'" /><span itemprop="name">'.$title.'</span><meta itemprop="position" content="3" /></li>';
	  $data .= '</ul>';
  
  // echo '<script type="application/ld+json" class="green-entertainment-breadcrumb">{"@context":"https://schema.org","@graph":[{"@type":"BreadcrumbList","@id":"'.$permalink.'#breadcrumb","itemListElement":[{"@type":"ListItem","position":"1","item":{"@id":"'.$home.'","name":"Home"}},{"@type":"ListItem","position":"2","item":{"@id":"'.($item_link ? $item_link : $permalink).'","name":"'.($item_name ? $item_name : $title).'"}}'.($item_name ? (',{"@type":"ListItem","position":"3","item":{"@id":"'.$permalink.'","name":"'.$title.'"}}') : '').']}]}</script>';
}

function get_term_names($tax, $separator = ' | ') {
    $terms = array_map(function ($term) {
        return $term->name;
    }, get_the_terms(get_the_ID(), $tax));

    return implode($separator, $terms);
}

function get_linked_terms($tax, $separator = ', ', $post_id=NULL) {
	$post_id = $post_id ?? get_the_ID();
	$terms = get_the_terms($post_id, $tax);
	$post_type = get_post_type($post_id);
	$typeText = $post_type === 'tv' ? 'TV Shows' : ($post_type === 'movie' ? 'Movies' : ($post_type === 'drama' ? 'Dramas' : ''));
    return $terms && is_array($terms) ? implode($separator, array_map(function ($term) use ($tax, $typeText) {
        return '<a href="' . get_term_link($term->slug, $tax) . '" title="'.$term->name.' '.$typeText.'">' . $term->name . '</a>';
    }, $terms)) : '';
}

function job_exist($credits, $department, $job) {
    return array_reduce($credits, function ($result, $person) use ($department, $job) {
        if (isset($person[clean_job_string($department) . '_job']) && ($person[clean_job_string($department) . '_job'] === $job)) {
            return true;
        }
        return $result;  // Continue if person doesn't match
    }, '');  // Initialize result as empty string
}


function get_linked_credits_by_profession($credits, $department, $job, $limit=0) {
	if(!is_array($credits)) $credits = [$credits];
	 $filtered_credits = array_filter($credits, function ($person) use ($department, $job) {
	   return isset($person[clean_job_string($department) . '_job']) && $person[clean_job_string($department) . '_job'] === $job;
	 });

	 if (empty($filtered_credits)) {
	   return ''; // No matching credits members
	 }

	 $filtered_credits = $limit ? array_slice($filtered_credits, 0, $limit) : $filtered_credits;

	 return implode(', ', array_map(function ($person) {
	 	$title = get_the_title($person['person']);
	   return isset($person['person']) ? '<a href="' . get_permalink($person['person']) . '" class="credits-member" title="'.$title.'">' . $title . '</a>' : '';
	 }, $filtered_credits));
}

function get_credits_ids_by_profession($credits, $department, $job, $limit=0) {
	if(!is_array($credits)) $credits = [$credits];
	 $filtered_credits = array_filter($credits, function ($person) use ($department, $job) {
	   return isset($person[clean_job_string($department) . '_job']) && $person[clean_job_string($department) . '_job'] === $job;
	 });

	 if (empty($filtered_credits)) {
	   return ''; // No matching credits members
	 }

	 $filtered_credits = $limit ? array_slice($filtered_credits, 0, $limit) : $filtered_credits;

	 return array_map(function ($person) {
	   return isset($person['person']) ? $person['person'] : '';
	 }, $filtered_credits);
}

function display_credits_with_image($credits, $job=''){
	$credits = $credits && is_array($credits) ? $credits : [$credits];
	$count = 0;
	$data = '';
	if($credits && is_array($credits)):
		foreach ( $credits as $credit ) {
		  if(isset($credit[ 'person' ]) && $credit[ 'person' ]):
		  	$name = get_the_title($credit[ 'person' ]);
		  	$count++;
		  	$data .= '<div class="credit-block">
					<div class="block1">
						<div class="credit-img">'.(has_post_thumbnail($credit[ 'person' ]) ? '<img src="'.plugin_dir_url( __DIR__ ) . 'src/icons/no-image.svg" data-src="'.get_the_post_thumbnail_url($credit[ 'person' ], 'medium').'" alt="no-image" class="lazyload">' : '<img src="" data-src="'.plugin_dir_url( __DIR__ ) . 'src/icons/no-image.svg" alt="no-image" class="lazyload">').'</div>
						<div class="credit-name"><a href="'.get_permalink($credit[ 'person' ]).'" class="cast-name" title="'.$name.'">'.$name.'</a></div>
					</div>
					<div class="credit-character">'.(isset($credit['department']) ? ($job ? (stripslashes( $credit[ $job ] ) ?? 'TBA') : (isset($credit[ clean_job_string($credit['department']).'_job' ]) ? stripslashes($credit[ clean_job_string($credit['department']).'_job' ]) : 'TBA' )) : (isset($credit['character']) && $credit['character'] ? stripslashes($credit['character']) : 'TBA')).'</div>
				</div>';
		  if ($count === 5) $data .= '<div class="show_full_credits">View All</div><div class="wrap_full_credits" style="display:none">';
		endif;	
		}
		if ($count >= 5) $data .= '</div>';
	endif;

	if ($count == 0) { $data .= "We don't have any credits added to this ".(get_post_type()==='episode' ? 'Episode.' : (get_post_type()==='drama' ? 'Drama.' : (get_post_type()==='tv' ? 'TV Show.' : "Movie."))); }
	
	return $data;
}

function the_star_cast($credits){
	$data = '';
	if($credits && is_array($credits)):
		$data .= '<div class="star-cast-container">';
		foreach ( $credits as $credit ):
			if(isset($credit[ 'person' ]) && $credit[ 'person' ]):
				$title = get_the_title($credit[ 'person' ]);
				$permalink = get_permalink($credit[ 'person' ]);
				$data .= '<div class="single-cast-item">';
					
					$data .= '<a class="credit-img" href="'.$permalink.'" title="'.$title.'">';
						$data .= has_post_thumbnail($credit[ 'person' ]) ? '<img src="'.plugin_dir_url( __DIR__ ) . 'src/icons/no-image.svg" data-src="'.get_the_post_thumbnail_url($credit[ 'person' ], 'medium').'" alt="'.$title.'" class="lazyload">' : '<img src="" data-src="'.plugin_dir_url( __DIR__ ) . 'src/icons/no-image.svg" alt="no-image" class="lazyload">';
					$data .= '</a>';
					
					$data .= '<div class="credit-details">';
						$data .= '<div class="credit-name"><a href="'.$permalink.'" class="cast-name" title="'.$title.'">'.$title.'</a></div>';
						$data .= '<div class="credit-character">'.(isset($credit['department']) ? ($job ? (stripslashes( $credit[ $job ] ) ?? 'TBA') : (isset($credit[ clean_job_string($credit['department']).'_job' ]) ? stripslashes($credit[ clean_job_string($credit['department']).'_job' ]) : 'TBA' )) : (isset($credit['character']) && $credit['character'] ? stripslashes($credit['character']) : 'TBA')).'</div>';
					$data .= '</div>';

				$data .= '</div>';
			endif;
		endforeach;
		$data .= '</div>';
	endif;
	return $data;
}

function get_linked_post($post_id) {
	$title = get_the_title($post_id);
  return '<a href="'.get_permalink($post_id).'" class="post post-'.$post_id.'" title="'.$title.'">'.$title.'</a>';
}

function display_videos($videos, $limit=-1){
	if($videos):
		$post_type = get_post_type();
		$title = get_the_title();
		$typeText = $post_type === 'tv' ? 'TV Show' : ($post_type === 'movie' ? 'Movie' : ($post_type === 'drama' ? 'Drama' : ''));
		$data = '';
		$data .= '<div class="videos scrollable-section" data-scroll-target="#videos">';
		  $data .= '<div class="short-heading font-size-36 weight-700 heading">';
				$data .= '<h2 class="videos-title weight-700 font-size-36">Watch '.$title.' '.$typeText.' Latest Videos</h2>';
				if(count($videos) > 4):
					$data .= '<div class="scroll-btns"><button class="scroll-btn scroll-new-release-left" data-direction="-1" onclick="scrollRelease(this)">'.button_left_f().'</button><button class="scroll-btn scroll-new-release-right" data-direction="1" onclick="scrollRelease(this)">'.button_right_f().'</button></div>';
				endif;
		  $data .= '</div>';
		  $data .= '<p class="heading-des">Watch the latest '.$title.' '.$typeText.' '.($post_type === 'drama' ? ' teasers, OSTs, and' : '').' trailers. Stay up to date with the latest '.$title.' clips'.($post_type === 'tv' || $post_type === 'drama' ? ' and get excited for upcoming episodes' : 'exciting videos').'.</p>';
		  $data .= '<div class="videos-container"><div class="videos-gallery scrollable-content" id="videos">'.(get_videos($videos, $limit)).'</div></div>';
		$data .= '</div>';
	endif;

	return $data;
}

function get_videos($videos, $limit) {
	$data = '';
	foreach($videos as $video_id):
		if($limit === 0) { wp_reset_postdata(); return; } --$limit;
		global $post;
		$post = get_post( $video_id, OBJECT );
		setup_postdata( $post );
		$video = rwmb_meta( 'video_data' );
		$title = get_the_title();
		$data .= '<a href="'.get_permalink().'" title="'.$title.'">
			<div class="video-single">
				'.(has_post_thumbnail($video_id) ? get_the_post_thumbnail($video_id) : '').'
				<span class="play-icon absolute bottom-0 left-0 z-30 flex items-center gap-x-6 p-10 text-white">
					<svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" data-testid="PlayCircleOutlinedIcon" class="svg-icon icon-md" height="1em" width="1em">
						<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-2.5-3.5 7-4.5-7-4.5v9z"></path>
					</svg>
					<span class="capitalize">'.$video['content_type'].'</span>
				</span>
				<div class="blend"></div>
			</div>
			<div class="single-video-title font-size-15">'.$title.'</div>
		</a>';
	endforeach;
	wp_reset_postdata();
	return $data;
}

function display_images($images){
	$post_type = get_post_type();
	$options = get_options(['tmu_movies', 'tmu_tv_series', 'tmu_dramas']);
	$typeText = $options['tmu_dramas'] === 'on' ? 'Dramas' : ($options['tmu_tv_series'] === 'on' ? ($options['tmu_movies'] === 'on' ? 'TV shows' : 'TV shows, Movies') : ($options['tmu_movies'] === 'on' ? : 'movies'));
	$data = '';
	if($images):
		$data .= '<div class="images scrollable-section" data-scroll-target="#images">
		  <div class="short-heading font-size-36 weight-700 heading">
				<h2 class="images-title weight-700 font-size-36">'.get_the_title().' Images</h2>';
				if(count($images) > 5):
					$data .= '<div class="scroll-btns"><button class="scroll-btn scroll-new-release-left" data-direction="-1" onclick="scrollRelease(this)">'.button_left_f().'</button><button class="scroll-btn scroll-new-release-right" data-direction="1" onclick="scrollRelease(this)">'.button_right_f().'</button></div>';
				endif;
		  $data .= '</div>';
		  $data .= '<p class="heading-des">Browse the latest '.get_the_title().' photos from '.$typeText.', events, and social media highlights, all in one place.</p>
		  <div class="images-container"><div class="images-gallery scrollable-content pics" id="images">'.get_images($images).'</div></div>
		</div>';
	endif;

	return $data;
}

function calculate_age($birth_date_str, $dean_on='') {
  // Create DateTime objects from the birth date and today's date
  $birth_date = new DateTime($birth_date_str);
  $today = $dean_on ? new DateTime($dean_on) : new DateTime('today');

  // Calculate the difference (interval) between the dates
  $diff = $birth_date->diff($today);

  // Extract the age in years from the interval
  return $diff->y;
}

function get_posts_and_count_by_term_name($taxonomy, $term_name, $post_type = 'tv') {
  $args = array(
    'tax_query' => array(
      array(
        'taxonomy' => $taxonomy,
        'field' => 'name', // Use 'name' to search by term name
        'terms' => array( $term_name ),
      )
    ),
    'post_type' => $post_type,
    'posts_per_page' => -1, // Get all posts (adjust if needed)
  );

  $query = new WP_Query( $args );

  $posts = array();
  if ( $query->have_posts() ) {
    while ( $query->have_posts() ) {
      $query->the_post();
      $title = get_the_title();
      $posts[] = $title ? str_replace("&#8211;", "-", get_the_title()) : '';
    }
    wp_reset_postdata();
  }

  $count = $query->found_posts; // Get the total number of posts

  return array(
    'count' => $count,
    'title' => $posts,
  );
}

function get_episode_count_by_season_id($season_id) {
  $args = array(
    'tax_query' => array(
      array(
        'taxonomy' => 'season',
        'field' => 'id', // Use 'id' to search by term ID
        'terms' => array( $season_id ),
      )
    ),
    'post_type' => 'episode', // Adjust 'post' to your desired post type
    'fields' => 'count', // Only retrieve the total count
  );

  $query = new WP_Query( $args );

  return $query->found_posts;
}

function get_images($images) {
	$imgNo = 0;
	$total = count($images);
	$data = '';
    $images = array_reverse($images);
	foreach($images as $image):
		++$imgNo;
		$image_attributes = wp_get_attachment_image_src( $image['ID'],'full' );
		$image_thumb = wp_get_attachment_image_src( $image,'medium' );
		$alt = get_post_meta($image , '_wp_attachment_image_alt');
		$alt = $alt ?? '';

		$data .= '<div class="single-image" id="img-'.$imgNo.'" data-url="'.$image_attributes[0].'" data-no="'.$imgNo.'" onClick="myFunction(this)">
				<div id="#image-'.$imgNo.'">
					<img src="'.plugin_dir_url( __DIR__ ) . 'src/icons/silver-background.svg'.'" data-src="'.$image['url'].'" title="'.($alt ? ($alt[0].'" alt="'.$alt[0]) : '').'" class="lazyload" alt="'.($alt ? ($alt[0].'" alt="'.$alt[0]) : '').'" width="150" height="150">
				</div>
			</div>';
		if($imgNo==$total) break;
	endforeach;
	
	$data .= '<div id="myModal" class="modal-content">
		<div id="main-modal">
			<div id="image-box"></div>
		</div>

		<button title="Previous (Left arrow key)" type="button" class="mfp-arrow mfp-arrow-left mfp-prevent-close" id="leftbutton"></button>
		<button title="Next (Right arrow key)" type="button" class="mfp-arrow mfp-arrow-right mfp-prevent-close" id="rightbutton"></button>

		<button type="button" id="close-button" aria-label="Close" class="Box-button">
			<svg width="30" height="30">
				<g stroke="rgb(160,160,160)" stroke-width="4">
					<line x1="5" y1="5" x2="25" y2="25"></line>
					<line x1="5" y1="25" x2="25" y2="5"></line>
				</g>
			</svg>
		</button>
	</div>

	<script>
		var modal = document.getElementById("myModal");
		var closebtn = document.getElementById("close-button");

		var leftbutton = document.getElementById("leftbutton");
		var rightbutton = document.getElementById("rightbutton");

		closebtn.onclick = function() {
			modal.style.display = "none";
		}

		function myFunction(e) {

			const clickedImage = e.querySelector(\'img\');
			document.getElementById("image-box").innerHTML = \'<img src="\'+e.dataset.url+\'" width="100%" height="100%" alt="\'+clickedImage.alt+\'" data-no="\'+e.dataset.no+\'"><figcaption>\'+clickedImage.alt+\'</figcaption>\';
			window.location.hash = "image";
			document.getElementById("myModal").style.display = "block";
			updateImageDimensions();

			var page = e.dataset.no;

			leftbutton.onclick = function() {
				page = --page;
				page = page==0 || page==-1 ? '.$total.' : page;
				const previmg = document.getElementById("img-"+page);
				const prevImage = previmg.querySelector("img");
				document.getElementById("image-box").innerHTML = \'<img src="\'+previmg.dataset.url+\'" width="100%" height="100%" alt="\'+prevImage.alt+\'" data-no="\'+page+\'"><figcaption>\'+prevImage.alt+\'</figcaption>\';
				updateImageDimensions();
			};

			document.getElementById("image-box").onclick = function() {
				page = --page;
				page = page==0 || page==-1 ? '.$total.' : page;
				const previmg = document.getElementById("img-"+page);
				const prevImage = previmg.querySelector("img");
				document.getElementById("image-box").innerHTML = \'<img src="\'+previmg.dataset.url+\'" width="100%" height="100%" alt="\'+prevImage.alt+\'" data-no="\'+page+\'"><figcaption>\'+prevImage.alt+\'</figcaption>\';
				updateImageDimensions();
			};

			rightbutton.onclick = function() {
				page = ++page;
				page = (page >= '.$total.') ? 1 : page;
				var nextimg = document.getElementById("img-"+page);
				nextImage = nextimg.querySelector("img");
				document.getElementById("image-box").innerHTML = \'<img src="\'+nextimg.dataset.url+\'" width="100%" height="100%" alt="\'+nextImage.alt+\'" data-no="\'+page+\'"><figcaption>\'+nextImage.alt+\'</figcaption>\';
				updateImageDimensions();
			};

		}

		document.addEventListener("mouseup", function(e) {
			var container = document.getElementById("image-box");
			if (!container.contains(e.target) && !leftbutton.contains(e.target) && !rightbutton.contains(e.target)) {
				modal.style.display = "none";
			}
		});

	</script>

	<script>
		window.onpopstate = function(event) {
			if(modal.style.display = "block") modal.style.display = "none";
		}

	</script>

	<script>
		function updateImageDimensions() {
		  // Get the viewport height and width
		  const viewportHeight = window.innerHeight;
		  const viewportWidth = window.innerWidth;

		  // Get all img elements within the image-box
		  const images = document.getElementById("image-box").querySelectorAll("img");

		  // Iterate over each image and update its max-height and max-width
		  images.forEach(image => {
		    image.style.maxHeight = `${viewportHeight}px`;
		    image.style.maxWidth = `${viewportWidth}px`;
		  });
		}

		// Add an event listener to update dimensions on window resize
		window.addEventListener("resize", updateImageDimensions);
	</script>';
	
	return $data;
}

function upload_images_from_urls($urls, $post_id='', $title='') {
	$isArray = true;
	if (!is_array($urls)) { $urls = [$urls]; $isArray = false;}
  $attachment_ids = array();
	$filename = $title ?? ($post_id ? get_the_title($post_id) : ''); $count=0; $upload_file = '';
    foreach ($urls as $url) {
    	if($url):
    		$image = wp_remote_get($url);
    		$output_filename = ($filename ? $filename : basename($url)).($isArray ? '-'.++$count : '').'-'.generateRandomString().'.webp';
    		if (is_array( $image ) && ! is_wp_error( $image )) {
    			$gd_image = imagecreatefromstring($image['body']);
    			if ($gd_image) {
		        ob_start(); // Start output buffering

		        // Convert to WebP with quality 50 and maximum compression (lossy)
		        imagewebp($gd_image, null, 50);

		        $webp_data = ob_get_contents(); // Get WebP data from output buffer
		        ob_end_clean(); // Clear output buffer

		        imagedestroy($gd_image);
		      }
    			$upload_file = $webp_data ? wp_upload_bits($output_filename, null, $webp_data) : '';
    		}


        if ($upload_file && !$upload_file['error']) {
            $wp_filetype = wp_check_filetype(basename($url), null );
            $attachment = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
                'post_content' => '',
                'post_status' => 'inherit',
								'post_parent' => $post_id
            );

            $attachment_id = wp_insert_attachment($attachment, $upload_file['file']);
            if (!is_wp_error($attachment_id)) {
                require_once(ABSPATH . "wp-admin" . '/includes/image.php');
                $attachment_data = wp_generate_attachment_metadata($attachment_id, $upload_file['file']);
                wp_update_attachment_metadata($attachment_id,  $attachment_data);
								update_post_meta($attachment_id, '_wp_attachment_image_alt', $filename );

                $attachment_ids[] = $attachment_id;
            }
        }
       endif;
    }

    return $isArray ? serialize($attachment_ids) : (isset($attachment_ids[0]) ? $attachment_ids[0] : $attachment_ids) ;
}

function get_average_ratings($comments, $tmdb_rating=['average' => 0, 'count' => 0], $new_rate_ajax=0){
	$count = 0;
	$all_ratings = 0;
	$tmdb_rating['average'] = $tmdb_rating['average'] ? $tmdb_rating['average'] : 0;
	$tmdb_rating['count'] = $tmdb_rating['count'] ? $tmdb_rating['count'] : 0;
	foreach($comments as $comment):
		$all_ratings += isset($comment->comment_rating) && $comment->comment_rating ? (int)$comment->comment_rating : 0;
		$count++;
	endforeach;
	$all_ratings = ($all_ratings+$new_rate_ajax)+($tmdb_rating['average']*$tmdb_rating['count']);
	$count += $tmdb_rating['count'];
	$average = $count ? number_format(($all_ratings/$count), 1) : 5;

	return ['average' => $average, 'count' => $count];
}

function get_rating($post_id){
	global $wpdb;
	$post_type = get_post_type($post_id);
	$col = $post_type === 'drama' ? 'dramas' : ($post_type === 'tv' ? 'tv_series' : ($post_type === 'episode' ? 'tv_series_episodes' : ($post_type === 'drama-episode' ? 'dramas_episodes' : ($post_type === 'movie' ? 'movies' : ''))));
	$table_name = $col ? $wpdb->prefix.'tmu_'.$col : '';
	if ($table_name) {
		$rating = $wpdb->get_row("SELECT total_average_rating,total_vote_count FROM $table_name WHERE `ID` = $post_id");
	}
	return ['average' => $rating->total_average_rating, 'count' => $rating->total_vote_count];
}

function get_post_rating($rating){

	return '
<style>
  .ratebox {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    padding: 20px 0;
    font-weight: bold;
  }
  .singlerate {
    display: flex;
    flex-flow: column;
    align-items: center;
  }
  
  .singlerate .rate-detail {
    margin: 6px 0 0 0;
    font-size: 1.5rem;
	display: flex;
    flex-direction: column;
	line-height: 1.2;
    color: #F97316;
  }
  
.singlerate .rate-detail:hover{
	text-decoration: none!important;
}

  .total-votes {
    font-size: 0.75rem;
    font-weight: 400;
    color: #8f8f8f;
  }

  .rate-star {
    display: flex;
    color: #F97316;
	
  }

</style>
<div class="ratebox">
    <a class="singlerate" href="#commentform" title="Rating">
      <div class="eo-rating rate-star">
        <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" class="ipc-icon ipc-icon--star sc-7ab21ed2-4 gtyXiL" id="iconContext-star" viewBox="0 0 24 24" fill="currentColor" role="presentation"><path d="M12 17.27l4.15 2.51c.76.46 1.69-.22 1.49-1.08l-1.1-4.72 3.67-3.18c.67-.58.31-1.68-.57-1.75l-4.83-.41-1.89-4.46c-.34-.81-1.5-.81-1.84 0L9.19 8.63l-4.83.41c-.88.07-1.24 1.17-.57 1.75l3.67 3.18-1.1 4.72c-.2.86.73 1.54 1.49 1.08l4.15-2.5z"></path></svg>
        <div class="rate-detail"><span id="avg-rating">'.$rating['average'].'</span><span class="total-votes" id="ttl-votes">'.$rating['count'].' Votes</span></div>
      </div>
    </a>
  </div>';
}

function add_additional_scripts() {
wp_enqueue_style( 'main_css', plugin_dir_url( __DIR__ ) . 'src/css/main.css', array(), '1.1', 'all' );
wp_enqueue_script( 'lazyload_credits', plugin_dir_url( __DIR__ ) . 'src/js/lazyload.min.js', array( 'jquery' ), 1.1 );
// wp_enqueue_script( 'ajax', plugin_dir_url( __DIR__ ) . 'src/js/ajax.js', array( 'jquery' ), 1.1, true ); 
//  wp_enqueue_script( 'script', get_template_directory_uri() . '/js/script.js', array( 'jquery' ), 1.1, true );

  if ( is_singular() && get_post_type() == 'post' ) {
   wp_enqueue_style( 'blog_css', plugin_dir_url( __DIR__ ) . 'src/css/single-post.css', array(), '1.1', 'all' );
 }
 if ( (get_post_type() == 'movie' || get_post_type() == 'tv' || get_post_type() == 'drama' || get_post_type() == 'people') && is_singular() ) {
   wp_enqueue_style( 'movie_css', plugin_dir_url( __DIR__ ) . 'src/css/single-movie.css', array(), '1.1', 'all' );
 }
 if (get_post_type() == 'episode' || get_post_type() == 'drama-episode') {
   wp_enqueue_style( 'episode_css', plugin_dir_url( __DIR__ ) . 'src/css/single-episode.css', array(), '1.1', 'all' );
 }
}
add_action( 'wp_enqueue_scripts', 'add_additional_scripts' );
add_action( 'admin_enqueue_scripts', 'my_admin_styles' );
add_action('admin_footer', 'admin_footer_script');

function my_admin_styles() {
    wp_enqueue_style( 'my-admin-styles', plugin_dir_url( __DIR__ ) . 'src/css/admin.css', array(), '1.0.0' );
}
function admin_footer_script() { ?>
<script>
const childElements = document.querySelectorAll('.credits > .rwmb-input > .rwmb-group-clone > .rwmb-row > .rwmb-column > .rwmb-text-wrapper[data-visible="visible"]');

for (const child of childElements) {
    const grandparent = child.parentNode.parentNode;
    grandparent.style.width = '30%';
}
</script>
<?php }

function clean_job_string($text) {
  if ($text) {
  	// 1. Remove "&" symbols
	  $text = str_replace("&", "", $text);

	  // 2. Replace multiple spaces with a single space
	  $text = preg_replace("/\s+/", " ", $text);

	  // 3. Convert to lowercase
	  $text = strtolower($text);

	  // 4. Replace spaces with underscores, preserving word boundaries
	  $text = preg_replace("/\b\s+\b/", "_", $text);

	  // 5. Trim any leading or trailing underscores
	  $text = trim($text, '_');

	  $text = trim($text, '\\');
  }

  return $text;
}

add_filter( 'generate_sidebar_layout','custom_sidebar' );
function custom_sidebar( $layout )
{
 	if ( is_archive('season') || is_404() ) return 'no-sidebar';
 	return $layout;

 }

 function generateRandomString($length = 5) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}


add_action('delete_post', 'delete_post_images');
function delete_post_images($post_id) {
    // Get the featured image ID
    $featured_image_id = get_post_thumbnail_id($post_id);
    if ($featured_image_id) {
    	$file_path = get_attached_file( $featured_image_id );
    	wp_delete_attachment($featured_image_id);
    	unlink( $file_path );
    }

    // Get all attached images
    $attached_images = get_attached_media('image', $post_id);
    foreach ($attached_images as $image) {
    	$file_path = get_attached_file( $image->ID );
    	wp_delete_attachment($image->ID);
    	unlink( $file_path );
    }
}


add_filter( 'rank_math/json_ld', function( $data, $jsonld ) { return []; }, 99, 2 );

function button_left_f(){
  return '<svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 30 30" class="btn-left">
	  <defs><style> .cls-1 { fill: #fff; } </style> </defs>
	  <rect id="Rectangle_154" data-name="Rectangle 154" width="30" height="30"/>
	  <path id="Icon_material-keyboard-arrow-right" data-name="Icon material-keyboard-arrow-right" class="cls-1" d="M22.885,22.916,16.7,16.722l6.181-6.194-1.9-1.9-8.1,8.1,8.1,8.1Z" transform="translate(-4.084 -1.625)"/>
	</svg>';
}

function button_right_f(){
  return '<svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 30 30" class="btn-right">
	  <defs><style>.cls-1 {fill: #fff;}</style></defs>
	  <g id="Group_276" data-name="Group 276" transform="translate(-1290 -476)">
	    <rect id="Rectangle_154" data-name="Rectangle 154" width="30" height="30" transform="translate(1290 476)"/>
	    <path id="Icon_material-keyboard-arrow-right" data-name="Icon material-keyboard-arrow-right" class="cls-1" d="M12.885,22.916l6.181-6.194-6.181-6.194,1.9-1.9,8.1,8.1-8.1,8.1Z" transform="translate(1288.314 474.375)"/>
	  </g>
	</svg>';
}

/**
 * Build our the_title() parameters.
 *
 * @since 3.0.0
 */
function tmu_get_the_title_parameters() {
	$params = array(
		'before' => sprintf(
			'<h1 class="entry-title"%s>',
			'microdata' === generate_get_schema_type() ? ' itemprop="headline"' : ''
		),
		'after' => '</h1>',
	);

	if ( ! is_singular() ) {
		$params = array(
			'before' => sprintf(
				'<h2 class="entry-title"%2$s><a href="%1$s" rel="bookmark" title="%3$s">',
				esc_url( get_permalink() ),
				('microdata' === generate_get_schema_type() ? ' itemprop="headline"' : ''),
				get_the_title()
			),
			'after' => '</a></h2>',
		);
	}

	if ( 'link' === get_post_format() ) {
		$params = array(
			'before' => sprintf(
				'<h2 class="entry-title"%2$s><a href="%1$s" rel="bookmark" title="%3$s">',
				esc_url( generate_get_link_url() ),
				('microdata' === generate_get_schema_type() ? ' itemprop="headline"' : ''),
				get_the_title()
			),
			'after' => '</a></h2>',
		);
	}

	return apply_filters( 'generate_get_the_title_parameters', $params );
}

function tmu_do_template_part( $template ) {
	/**
	 * generate_before_do_template_part hook.
	 *
	 * @since 3.0.0
	 * @param string $template The template.
	 */
	do_action( 'generate_before_do_template_part', $template );

	if ( apply_filters( 'tmu_do_template_part', true, $template ) ) {
		if ( 'archive' === $template || 'index' === $template ) {
			get_template_part( 'content', get_post_format() );
		} elseif ( 'none' === $template ) {
			get_template_part( 'no-results' );
		} else {
			if (file_exists( $single_content = plugin_dir_path( __FILE__ ).'content-single.php')) {
		    load_template( $single_content );
		    exit;
			} else { get_template_part( 'content', $template ); }
		}
	}

	/**
	 * generate_after_do_template_parts hook.
	 *
	 * @since 3.0.0
	 * @param string $template The template.
	 */
	do_action( 'generate_after_do_template_part', $template );
}

remove_action( 'wp_head', 'feed_links_extra', 3 ); // Display the links to the extra feeds such as category feeds
remove_action( 'wp_head', 'feed_links', 2 ); // Display the links to the general feeds: Post and Comment Feed
remove_action( 'wp_head', 'rsd_link' ); // Display the link to the Really Simple Discovery service endpoint, EditURI link
remove_action( 'wp_head', 'wlwmanifest_link' ); // Display the link to the Windows Live Writer manifest file.
remove_action( 'wp_head', 'index_rel_link' ); // index link
remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 ); // prev link
remove_action( 'wp_head', 'start_post_rel_link', 10, 0 ); // start link
remove_action( 'wp_head', 'adjacent_posts_rel_link', 10, 0 ); // Display relational links for the posts adjacent to the current post.
remove_action( 'wp_head', 'wp_generator' ); // Display the XHTML generator that is generated on the wp_head hook, WP version








// convert uploaded images in webp

function create_webp($file) {
    if (in_array($file['type'], ['image/png', 'image/jpeg'])) {
        $webp_file = str_replace('.' . pathinfo($file['file'], PATHINFO_EXTENSION), '-'.generateRandomString().'.webp', $file['file']);

        // Use GD or Imagick library to convert image to WebP
        if (function_exists('imagewebp')) {
            $image = imagecreatefromstring(file_get_contents($file['file']));
            imagewebp($image, $webp_file, 60); // Adjust quality as needed
            imagedestroy($image);
        } elseif (class_exists('Imagick')) {
            $image = new Imagick($file['file']);
            $image->setImageFormat('webp');
            $image->writeImages($webp_file, true);
            $image->destroy();
        }

        // Create a new WordPress attachment for the WebP image
        $wp_upload_dir = wp_upload_dir();
        unlink($file['file']);
        return ['url' => $wp_upload_dir['url'] . '/' . basename($webp_file).'-'.generateRandomString(), 'file' => $webp_file, 'type' => 'image/webp'];
    }

    return $file;
}
add_filter('wp_handle_upload', 'create_webp', 10, 1);

function get_related_news($tag_name, $no_of_news){
	$news = get_posts(['post_type' => 'post', 'posts_per_page' => $no_of_news, 'tax_query' => [['taxonomy' => 'post_tag', 'field' => 'name', 'terms' => [$tag_name]]]]);
	$data = '';
	if ($news) {
		$data = '<div class="news-container">';
		foreach ($news as $post) {
			$title = get_the_title($post->ID);
			$data .= '<a href="'.get_permalink($post->ID).'" class="news-single" title="'.$title.'">';
				$data .= '<div class="news-poster">'.(has_post_thumbnail($post->ID) ? '<img src="'.plugin_dir_url( __DIR__ ) . 'src/icons/silver-back-land.svg" data-src="'.get_the_post_thumbnail_url($post->ID, 'full').'" alt="no-image" class="lazyload">' : '<img src="" data-src="'.plugin_dir_url( __DIR__ ) . 'src/icons/silver-back-land.svg" alt="no-image" class="lazyload">').'</div>';
				$data .= '<div class="news-details">';
					$data .= '<h3>'.$title.'</h3>';
					$data .= '<p>'.wp_trim_words(wp_strip_all_tags(get_the_content('', false, $post->ID)), 20).'</p>';
				$data .= '</div>';
			$data .= '</a>';
		}
		$data .= '</div>';
		$data .= '<a class="btn-primary" href="'.get_term_link(sanitize_title($tag_name), 'post_tag').'" title="View All">View All</a>';
	}
	return $data;
}

function listitem_schema($result, $count, $post_type){
  $tmdb_rating['average'] = $result->average_rating;
  $tmdb_rating['count'] = $result->vote_count;
  $comments = get_comments(array('post_id' => $result->ID, 'status' => 'approve'));
  $average_ratings = get_average_ratings($comments, $tmdb_rating);
	return '{
        "@type": "ListItem",
        "position": "'.($count+1).'",
        "item": {
          "@type": "'.($post_type === 'movie' ? 'Movie' : 'TVSeries').'",
          "url": "'.get_permalink($result->ID).'",
          "name": "'.get_the_title($result->ID).'",
          '.(has_post_thumbnail($result->ID) ? '"image": "'.get_the_post_thumbnail_url($result->ID).'",' : '').'
          "dateCreated": "'.get_the_date( 'Y-m-d', $result->ID ).'",
          "aggregateRating": {
            "@type": "AggregateRating",
            "bestRating": "10",
            "worstRating": "1",
            "ratingValue": "'.$average_ratings['average'].'",
            "ratingCount": "'.($average_ratings['count'] ? $average_ratings['count'] : 1).'"
          }
        }
      }';
}

function social_sharing_button($current_page, $title){
?>
	<div class="social-sharing-icons">
		<a class="social-icon facebook" href="https://www.facebook.com/sharer/sharer.php?u=<?= $current_page ?>" title="Share on facebook">
			<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="24" height="24" viewBox="0,0,256,256">
				<g transform="translate(-16.64,-16.64) scale(1.13,1.13)"><g fill="#0073c2" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none" font-size="none" text-anchor="none" style="mix-blend-mode: normal"><g transform="scale(5.12,5.12)"><path d="M42,3h-34c-2.8,0 -5,2.2 -5,5v34c0,2.8 2.2,5 5,5h34c2.8,0 5,-2.2 5,-5v-34c0,-2.8 -2.2,-5 -5,-5zM37,19h-2c-2.1,0 -3,0.5 -3,2v3h5l-1,5h-4v16h-5v-16h-4v-5h4v-3c0,-4 2,-7 6,-7c2.9,0 4,1 4,1z"></path></g></g></g>
			</svg>
		</a>
		<a class="social-icon twitter" href="https://twitter.com/intent/tweet?text=<?= $title ?>&url=<?= $current_page ?>" title="Share on twitter">
			<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="24" height="24" viewBox="0,0,256,256">
				<g transform="translate(-24.32,-24.32) scale(1.19,1.19)"><g fill="#000000" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none" font-size="none" text-anchor="none" style="mix-blend-mode: normal"><g transform="scale(5.12,5.12)"><path d="M11,4c-3.866,0 -7,3.134 -7,7v28c0,3.866 3.134,7 7,7h28c3.866,0 7,-3.134 7,-7v-28c0,-3.866 -3.134,-7 -7,-7zM13.08594,13h7.9375l5.63672,8.00977l6.83984,-8.00977h2.5l-8.21094,9.61328l10.125,14.38672h-7.93555l-6.54102,-9.29297l-7.9375,9.29297h-2.5l9.30859,-10.89648zM16.91406,15l14.10742,20h3.06445l-14.10742,-20z"></path></g></g></g>
			</svg>
		</a>
		<a class="social-icon whatsapp" href="https://api.whatsapp.com/send?text=<?= $title ?>: <?= $current_page ?>" title="Share on whatsapp">
			<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="24" height="24" viewBox="0,0,256,256">
				<g transform="translate(-29.44,-29.44) scale(1.23,1.23)"><g fill="#45d354" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none" font-size="none" text-anchor="none" style="mix-blend-mode: normal"><g transform="scale(8.53333,8.53333)"><path d="M15,3c-6.627,0 -12,5.373 -12,12c0,2.25121 0.63234,4.35007 1.71094,6.15039l-1.60352,5.84961l5.97461,-1.56836c1.74732,0.99342 3.76446,1.56836 5.91797,1.56836c6.627,0 12,-5.373 12,-12c0,-6.627 -5.373,-12 -12,-12zM10.89258,9.40234c0.195,0 0.39536,-0.00119 0.56836,0.00781c0.214,0.005 0.44692,0.02067 0.66992,0.51367c0.265,0.586 0.84202,2.05608 0.91602,2.20508c0.074,0.149 0.12644,0.32453 0.02344,0.51953c-0.098,0.2 -0.14897,0.32105 -0.29297,0.49805c-0.149,0.172 -0.31227,0.38563 -0.44727,0.51563c-0.149,0.149 -0.30286,0.31238 -0.13086,0.60938c0.172,0.297 0.76934,1.27064 1.65234,2.05664c1.135,1.014 2.09263,1.32561 2.39063,1.47461c0.298,0.149 0.47058,0.12578 0.64258,-0.07422c0.177,-0.195 0.74336,-0.86411 0.94336,-1.16211c0.195,-0.298 0.39406,-0.24644 0.66406,-0.14844c0.274,0.098 1.7352,0.8178 2.0332,0.9668c0.298,0.149 0.49336,0.22275 0.56836,0.34375c0.077,0.125 0.07708,0.72006 -0.16992,1.41406c-0.247,0.693 -1.45991,1.36316 -2.00391,1.41016c-0.549,0.051 -1.06136,0.24677 -3.56836,-0.74023c-3.024,-1.191 -4.93108,-4.28828 -5.08008,-4.48828c-0.149,-0.195 -1.21094,-1.61031 -1.21094,-3.07031c0,-1.465 0.76811,-2.18247 1.03711,-2.48047c0.274,-0.298 0.59492,-0.37109 0.79492,-0.37109z"></path></g></g></g>
			</svg>
		</a>
		<a class="social-icon telegram" href="https://t.me/share/url?url=<?= $current_page ?>&text=<?= $title ?>&to=" title="Share on telegram">
			<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="24" height="24" viewBox="0,0,256,256">
				<g transform="translate(-10.24,-10.24) scale(1.08,1.08)"><g fill="#02c8f0" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none" font-size="none" text-anchor="none" style="mix-blend-mode: normal"><g transform="scale(5.12,5.12)"><path d="M25,2c12.703,0 23,10.297 23,23c0,12.703 -10.297,23 -23,23c-12.703,0 -23,-10.297 -23,-23c0,-12.703 10.297,-23 23,-23zM32.934,34.375c0.423,-1.298 2.405,-14.234 2.65,-16.783c0.074,-0.772 -0.17,-1.285 -0.648,-1.514c-0.578,-0.278 -1.434,-0.139 -2.427,0.219c-1.362,0.491 -18.774,7.884 -19.78,8.312c-0.954,0.405 -1.856,0.847 -1.856,1.487c0,0.45 0.267,0.703 1.003,0.966c0.766,0.273 2.695,0.858 3.834,1.172c1.097,0.303 2.346,0.04 3.046,-0.395c0.742,-0.461 9.305,-6.191 9.92,-6.693c0.614,-0.502 1.104,0.141 0.602,0.644c-0.502,0.502 -6.38,6.207 -7.155,6.997c-0.941,0.959 -0.273,1.953 0.358,2.351c0.721,0.454 5.906,3.932 6.687,4.49c0.781,0.558 1.573,0.811 2.298,0.811c0.725,0 1.107,-0.955 1.468,-2.064z"></path></g></g></g>
			</svg>
		</a>
	</div>
<?php
}