<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once plugin_dir_path( __DIR__ ) . 'modules/celebrities.php';
require_once plugin_dir_path( __DIR__ ) . 'modules/birthday-today.php';

$options = get_options( ['tmu_movies', 'tmu_tv_series', 'tmu_dramas'] );

get_header(); ?>
  <link rel="stylesheet" href="<?= plugin_dir_url( __DIR__ ) ?>src/css/home.css">
  <div <?php generate_do_attr( 'content' ); ?>>
    <main <?php generate_do_attr( 'main' ); ?>>
      <?php do_action( 'generate_before_main_content' ); ?>
      <section>
      	<div class="blog-posts">
      		<section class="blog-section">
      			<?php $featured = featured_posts(wp_get_recent_posts( ['numberposts' => 8, 'post_status' => 'publish'] )); ?>
	      		<div class="featured-primary"><?= $featured['primary'] ?></div>
	      		<div class="featured-second"><?= $featured['second'] ?></div>
	      		<div class="featured-third"><section><?= $featured['third'] ?></section></div>
      		</section>
      	</div>

      	<div class="archive-header">
      		<?php if($options['tmu_movies']==='on' && $options['tmu_tv_series']==='on') { ?>
      			<?php if($popular = popular_tv_movies()): ?>
		      		<div class="new-releases scrollable-section" data-scroll-target="#trending-movies">
		      			<div class="heading">
		      				<h2>Trending Now</h2><div class="scroll-btns"><button class="scroll-btn scroll-new-release-left" data-direction="-1" onclick="scrollRelease(this)"><?php button_left() ?></button><button class="scroll-btn scroll-new-release-right" data-direction="1" onclick="scrollRelease(this)"><?php button_right() ?></button></div>
		      			</div>
		      			<div class="new-release-flex scrollable-content" id="trending-movies">
		      				<?= $popular ?>
		      			</div>
		      		</div>
		      	<?php endif;
		      }

		      if($options['tmu_movies']==='on' && $options['tmu_tv_series']!=='on') { ?>
      			<?php if($popular = popular_movies()): ?>
		      		<div class="new-releases scrollable-section" data-scroll-target="#trending-movies">
		      			<div class="heading">
		      				<h2>Trending Movies</h2><div class="scroll-btns"><button class="scroll-btn scroll-new-release-left" data-direction="-1" onclick="scrollRelease(this)"><?php button_left() ?></button><button class="scroll-btn scroll-new-release-right" data-direction="1" onclick="scrollRelease(this)"><?php button_right() ?></button></div>
		      			</div>
		      			<div class="new-release-flex scrollable-content" id="trending-movies">
		      				<?= $popular ?>
		      			</div>
		      		</div>
		      	<?php endif;
		      }

		      if($options['tmu_movies']!=='on' && $options['tmu_tv_series']==='on') { ?>
      			<?php if($popular = popular_tv_series()): ?>
		      		<div class="new-releases scrollable-section" data-scroll-target="#trending-tv-series">
		      			<div class="heading">
		      				<h2>Trending TV Series</h2><div class="scroll-btns"><button class="scroll-btn scroll-new-release-left" data-direction="-1" onclick="scrollRelease(this)"><?php button_left() ?></button><button class="scroll-btn scroll-new-release-right" data-direction="1" onclick="scrollRelease(this)"><?php button_right() ?></button></div>
		      			</div>
		      			<div class="new-release-flex scrollable-content" id="trending-tv-series">
		      				<?= $popular ?>
		      			</div>
		      		</div>
		      	<?php endif;
		      }


		      if($options['tmu_movies']==='on') { ?>
	      		<div class="new-releases scrollable-section" data-scroll-target="#new-release">
	      			<div class="heading">
	      				<h2>New Movie Releases</h2><div class="scroll-btns"><button class="scroll-btn scroll-new-release-left" data-direction="-1" onclick="scrollRelease(this)"><?php button_left() ?></button><button class="scroll-btn scroll-new-release-right" data-direction="1" onclick="scrollRelease(this)"><?php button_right() ?></button></div>
	      			</div>
	      			<div class="new-release-flex scrollable-content" id="new-release">
	      				<?php new_releases('movie'); ?>
	      			</div>
	      		</div>

	      		<div class="upcoming-movies scrollable-section" data-scroll-target="#upcoming-movies">
	      			<div class="heading">
	      				<h2>Upcoming Movies</h2><div class="scroll-btns"><button class="scroll-btn scroll-upcoming-movies-left" data-direction="-1" onclick="scrollRelease(this)"><?php button_left() ?></button><button class="scroll-btn scroll-upcoming-movies-right" data-direction="1" onclick="scrollRelease(this)"><?php button_right() ?></button></div>
	      			</div>
	      			<div class="upcoming-movies-flex scrollable-content" id="upcoming-movies">
	      				<?php upcoming_posts('movie'); ?>
	      			</div>
	      		</div>
	      	<?php } 

      		if($options['tmu_tv_series']==='on') { ?>
      			<div class="new-releases scrollable-section" data-scroll-target="#new-release-tv-shows">
	      			<div class="heading">
	      				<h2>Latest on TV</h2><a href="<?= get_post_type_archive_link('tv') ?>" class="born-today-view-button">View All</a><!-- <div class="scroll-btns"><button class="scroll-btn scroll-today-birthday-left" data-direction="-1" onclick="scrollRelease(this)"><?php button_left() ?></button><button class="scroll-btn scroll-today-birthday-right" data-direction="1" onclick="scrollRelease(this)"><?php button_right() ?></button></div> -->
	      				
	      			</div>
	      			<div class="new-release-flex scrollable-content" id="new-release-tv-shows">
	      				<?php new_releases('tv'); ?>
	      			</div>
	      		</div>

	      		<div class="upcoming-movies scrollable-section" data-scroll-target="#upcoming-tv-shows">
	      			<div class="heading">
	      				<h2>Upcoming TV Shows</h2><a href="<?= get_permalink(get_page_by_path('upcoming')->ID) ?>" class="born-today-view-button">View All</a><!-- <div class="scroll-btns"><button class="scroll-btn scroll-upcoming-movies-left" data-direction="-1" onclick="scrollRelease(this)"><?php button_left() ?></button><button class="scroll-btn scroll-upcoming-movies-right" data-direction="1" onclick="scrollRelease(this)"><?php button_right() ?></button></div> -->
	      			</div>
	      			<div class="upcoming-movies-flex scrollable-content" id="upcoming-tv-shows">
	      				<?php upcoming_posts('tv'); ?>
	      			</div>
	      		</div>
	      	<?php }

	      	if($options['tmu_movies']==='on') { ?>
	      		<div class="trailer">
	      			<div class="heading"><h2>Movie Trailer</h2></div>
	      			<div class="trailer-flex">
	      				<?php latest_trailers('movie'); ?>
	      				<?php // latest_trailers('tv'); ?>
	      			</div>
	      		</div>

	      	<?php } ?>

	      	<?php if($options['tmu_dramas']==='on') { ?>
	      		<?php if($popular = popular_dramas()): ?>
		      		<div class="new-releases scrollable-section" data-scroll-target="#trending-dramas">
		      			<div class="heading">
		      				<h2>Trending Dramas</h2><div class="scroll-btns"><button class="scroll-btn scroll-new-release-left" data-direction="-1" onclick="scrollRelease(this)"><?php button_left() ?></button><button class="scroll-btn scroll-new-release-right" data-direction="1" onclick="scrollRelease(this)"><?php button_right() ?></button></div>
		      			</div>
		      			<div class="new-release-flex scrollable-content" id="trending-dramas">
		      				<?= $popular ?>
		      			</div>
		      		</div>
		      	<?php endif; ?>

	      		<div style="border: 1px solid #ddd; padding: 10px; border-radius: 10px; background: #fdfdfd;">
			      	<h2 style="text-align: center; margin-top: 20px;">Pakistani Drama Schedule</h2>
			      	<?= dramas_schedule() ?>
		      	</div>

		      	<?= top_trending_dramas() ?>
	      	<?php } ?>
	      	
	      	<?= birthday_today_homepage() ?>

      	</div>
      </section>
    </main>
  </div>

<script>
function scrollRelease(button) {
  const scrollContainer = button.closest('.scrollable-section');
  const scrollTarget = scrollContainer.dataset.scrollTarget;
  const direction = button.dataset.direction ? parseInt(button.dataset.direction) : 1; // Default right scroll

  const scrollElement = document.querySelector(scrollTarget);

  if (scrollElement) {
    scrollElement.scrollLeft += direction * 1000; // Adjust scroll distance as needed
  } else {
    console.warn('Scroll element not found with selector:', scrollTarget);
  }
}
</script>

  <?php
  do_action( 'generate_after_primary_content_area' );

  generate_construct_sidebars();

  get_footer();

  function new_releases($post_type){
  	global $wpdb;
  	$section = $post_type == 'movie' ? 'movies' : ($post_type == 'tv' ? 'tv_series' : 'dramas');
		$table_name = $section ? $wpdb->prefix.'tmu_'.$section : '';
		if ($table_name) {
			$results = $wpdb->get_results("SELECT item.`ID`,item.`original_title` FROM $table_name item JOIN {$wpdb->prefix}posts AS posts ON (item.ID = posts.ID) WHERE unix_timestamp(item.`release_date`)<unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) AND posts.post_status = 'publish' ORDER BY item.`release_date` DESC LIMIT 10");

	  	foreach ($results as $result) {
	  		$title = get_the_title($result->ID);
	  		?>
	  			<a class="new-release-box" href="<?= get_permalink($result->ID) ?>" title="<?= $title ?>">
		  			<div class="new-release-poster">
		  				<img <?= (has_post_thumbnail($result->ID) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url($result->ID, 'full').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ) ?> alt="<?= $result->original_title ?>" width="100%" height="100%">
		  			</div>
		  			<div class="new-release-details" href="<?= get_permalink($result->ID) ?>">
		  				<h3><?= $title ?></h3>
		  			</div>
		  		</a>
	  		<?php
	  	}
		}
  }

  function upcoming_posts($post_type){
  	global $wpdb;
  	$section = $post_type == 'movie' ? 'movies' : ($post_type == 'tv' ? 'tv_series' : 'dramas');
		$table_name = $section ? $wpdb->prefix.'tmu_'.$section : '';
		if ($table_name) {
			if($post_type == 'tv') {
				$query = "SELECT t1.`ID`, t1.`original_title` FROM $table_name t1 JOIN {$wpdb->prefix}posts AS posts ON (t1.ID = posts.ID) LEFT JOIN `{$table_name}_seasons` t2 ON t1.`ID` = t2.`tv_series` WHERE t2.air_date_timestamp > unix_timestamp(DATE_ADD(NOW(), INTERVAL 3 HOUR)) AND posts.post_status = 'publish' ORDER BY t2.air_date_timestamp ASC LIMIT 10";
			} else {
				$query = "SELECT t1.`ID`,t1.`original_title` FROM $table_name t1 JOIN {$wpdb->prefix}posts AS posts ON (t1.ID = posts.ID) WHERE unix_timestamp(t1.`release_date`)>unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) AND posts.post_status = 'publish' ORDER BY `release_date` ASC LIMIT 10";
			}
			$results = $wpdb->get_results($query);

	  	foreach ($results as $result) {
	  		$title = get_the_title($result->ID);
	  		?>
	  			<a class="upcoming-movies-box" href="<?= get_permalink($result->ID) ?>" title="<?= $title ?>">
		  			<div class="upcoming-movies-poster">
		  				<img <?= (has_post_thumbnail($result->ID) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url($result->ID, 'full').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ) ?> alt="<?= $result->original_title ?>" width="100%" height="100%">
		  			</div>
		  			<div class="upcoming-movies-details" href="<?= get_permalink($result->ID) ?>">
		  				<h3><?= $title ?></h3>
		  			</div>
		  		</a>
	  		<?php
	  	}
		}
  }

  function latest_trailers($post_type){
  	global $wpdb;
  	$section = $post_type == 'movie' ? 'movies' : ($post_type == 'tv' ? 'tv_series' : '');
		$table_name = $section ? $wpdb->prefix.'tmu_'.$section : ''; $tmp_count = 0;
		if ($table_name) {
			$results = $wpdb->get_results("SELECT t1.`ID`,t1.`videos`,t1.`release_date`,t1.`original_title` FROM $table_name t1 JOIN {$wpdb->prefix}posts AS posts ON (t1.ID = posts.ID) WHERE t1.release_timestamp>unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) AND posts.post_status = 'publish' ORDER BY release_timestamp ASC LIMIT 100");

	  	foreach ($results as $result) {

	  		$genres = implode(', ', wp_get_object_terms( $result->ID, 'genre', array('fields' => 'names') ));

	  		$videos = $result->videos ? unserialize($result->videos) : '';
	  		$video_id = ''; $video_meta = ''; $total = $videos ? count($videos) : '';



	  		if (is_array($videos) && $total) {
	  			$last_index = $total - 1;
	  			for ($last_index; $last_index >= 0 ; $last_index--) { 
		  			$video_meta = rwmb_meta( 'video_data', '', $videos[$last_index] );
		  			if (isset($video_meta['content_type']) && ($video_meta['content_type'] == 'Trailer' || $video_meta['content_type'] == 'Teaser')) {
		  				$video_id = $videos[$last_index];
		  				break;
		  			}
		  		}
	  		}

		  	if ($video_id) {
		  		++$tmp_count;
		  		$videoURL = has_post_thumbnail($video_id) ? get_the_post_thumbnail_url($video_id, 'full') : get_the_post_thumbnail_url($result->ID, 'full');
		  		$video_permalink = get_permalink($video_id);
		  		$title = get_the_title($result->ID);
			  	?>
			  		<div class="trailer-box">
			  			<a class="video-image video-single" href="<?= $video_permalink ?>" title="<?= $title ?>">
			  				<img <?= ($videoURL ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-image.webp" data-src="'.$videoURL.'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-image.webp"') ) ?> alt="<?= $title ?>" width="100%" height="100%">
								<span class="play-icon absolute bottom-0 left-0 z-30 flex items-center gap-x-6 p-10 text-white">
									<svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" data-testid="PlayCircleOutlinedIcon" class="svg-icon icon-md" height="1em" width="1em">
										<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-2.5-3.5 7-4.5-7-4.5v9z"></path>
									</svg>
									<span class="capitalize">Trailer</span>
								</span>
								<div class="blend"></div>
			  			</a>
			  			<a class="trailer-details" href="<?= $video_permalink ?>" title="<?= $title ?>">
			  				<h3 class="trailer-title"><?= $title ?></h3>
			  				<p class="genres"><?= $genres ?></p>
			  			</a>
			  		</div>
			  	<?php
			  	if ($tmp_count===8) return;
	  		}
	  	}
		}
  }

  function video_wrapper(){
  	?>
  	<div class="video-image video-single">
			<img src="<?= $featured_image ?>" alt="<?= $title ?>">
			<span class="play-icon absolute bottom-0 left-0 z-30 flex items-center gap-x-6 p-10 text-white">
				<svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" data-testid="PlayCircleOutlinedIcon" class="svg-icon icon-md" height="1em" width="1em">
					<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-2.5-3.5 7-4.5-7-4.5v9z"></path>
				</svg>
				<span class="capitalize"><?= $video_meta['content_type'] ?></span>
			</span>
			<div class="blend"></div>
		</div>
  	<?php
  }

function button_left(){
  ?>
  <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 30 30" class="btn-left">
	  <defs><style> .cls-1 { fill: #fff; } </style> </defs>
	  <rect id="Rectangle_154" data-name="Rectangle 154" width="30" height="30"/>
	  <path id="Icon_material-keyboard-arrow-right" data-name="Icon material-keyboard-arrow-right" class="cls-1" d="M22.885,22.916,16.7,16.722l6.181-6.194-1.9-1.9-8.1,8.1,8.1,8.1Z" transform="translate(-4.084 -1.625)"/>
	</svg>
  <?php
}

function button_right(){
  ?>
  <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 30 30" class="btn-right">
	  <defs><style>.cls-1 {fill: #fff;}</style></defs>
	  <g id="Group_276" data-name="Group 276" transform="translate(-1290 -476)">
	    <rect id="Rectangle_154" data-name="Rectangle 154" width="30" height="30" transform="translate(1290 476)"/>
	    <path id="Icon_material-keyboard-arrow-right" data-name="Icon material-keyboard-arrow-right" class="cls-1" d="M12.885,22.916l6.181-6.194-6.181-6.194,1.9-1.9,8.1,8.1-8.1,8.1Z" transform="translate(1288.314 474.375)"/>
	  </g>
	</svg>
  <?php
}

function featured_posts($posts){
	$featured = ['primary' => '', 'second' => '', 'third' => ''];
	if ($posts) {
		foreach ($posts as $index => $post) {
			$permalink = get_permalink($post['ID']);
			$title = get_the_title($post['ID']);
			$image = has_post_thumbnail($post['ID']) ? get_the_post_thumbnail_url($post['ID'], 'full') : '';
			$tags = get_the_tags($post['ID']);
			if (!$index) {
				$featured['primary'] .= '<article>
					<a href="'.$permalink.'" class="post-image" title="'.$title.'"><img src="'.($image ? plugin_dir_url( __DIR__ ) . 'src/icons/silver-back-land.svg" data-src="'.$image.'" class="lazyload' : plugin_dir_url( __DIR__ ) . 'src/icons/silver-back-land.svg').'" alt="'.$title.'" width="100%" height="100%"></a>
					<div class="post-content">
						<div class="post-tags">'.($tags ? '<a href="'.get_tag_link( $tags[0] ).'" class="post-tag" title="'.$tags[0]->name.'">'.$tags[0]->name.'</a>' : '').'</div>
						<h3 class="post-title"><a href="'.$permalink.'" title="'.$title.'">'.$title.'</a></h3>
					</div>
				</article>';
			} elseif ($index > 3) {
				$featured['second'] .= '<article>
					<a href="'.$permalink.'" class="post-image" title="'.$title.'"><img src="'.($image ? plugin_dir_url( __DIR__ ) . 'src/icons/silver-back-land.svg" data-src="'.$image.'" class="lazyload' : plugin_dir_url( __DIR__ ) . 'src/icons/silver-back-land.svg').'" alt="'.$title.'" width="100%" height="100%"></a>
					<div class="post-content">
						<div class="post-tags">'.($tags ? '<a href="'.get_tag_link( $tags[0] ).'" class="post-tag" title="'.$tags[0]->name.'">'.$tags[0]->name.'</a>' : '').'</div>
						<h3 class="post-title"><a href="'.$permalink.'" title="'.$title.'">'.$title.'</a></h3>
					</div>
				</article>';
			} else {
				$featured['third'] .= '<article>
					<a href="'.$permalink.'" class="post-image" title="'.$title.'"><img src="'.($image ? plugin_dir_url( __DIR__ ) . 'src/icons/silver-back-land.svg" data-src="'.$image.'" class="lazyload' : plugin_dir_url( __DIR__ ) . 'src/icons/silver-back-land.svg').'" alt="'.$title.'" width="100%" height="100%"></a>
					<div class="post-content">
						<h3 class="post-title"><a href="'.$permalink.'" title="'.$title.'">'.$title.'</a></h3>
						<div class="post-tags">'.($tags ? '<a href="'.get_tag_link( $tags[0] ).'" class="post-tag" title="'.$tags[0]->name.'">'.$tags[0]->name.'</a>' : '').'</div>
					</div>
				</article>';
			}
		}
	}

	return $featured;
}