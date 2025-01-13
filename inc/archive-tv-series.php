<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

$post_type = get_post_type();
$site_url = get_site_url();
$permalink = $site_url.$_SERVER['REQUEST_URI'];

$posts = module_1('tv');
$schema = default_schema(ucfirst($post_type), $permalink).$posts['schema'];
add_action('wp_head', function() use ($schema) { echo $schema; });
get_header();
?>
  <link rel="stylesheet" href="<?= plugin_dir_url( __DIR__ ) ?>src/css/archive-tv-series.css">
  <div <?php generate_do_attr( 'content' ); ?>>
    <main <?php generate_do_attr( 'main' ); ?>>
      <?php do_action( 'generate_before_main_content' ); ?>
      <section>
      	<div class="archive-header">
      		<ul class="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
      			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a itemprop="item" href="<?= $site_url ?>" title="Home"><span itemprop="name">Home</span></a><meta itemprop="position" content="1" /></li>
      			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"> / <meta itemprop="item" content="<?= $permalink ?>" /><span itemprop="name"><?= ucfirst($post_type) ?></span><meta itemprop="position" content="2" /></li>
      		</ul>

      		<h1>Best TV Shows (<?= date('F Y') ?>)</h1>

      		<div class="block-seperate">
	      		<!-- <div class="heading"><h2>TV Series</h2></div> -->
	      		<?= $posts['data'] ?>
	      	</div>

      		<!-- TV Series By Recent Episode Release -->
      		<?php tv_series_with_recent_episode_release(); ?>

      		<div class="block-seperate">
	      		<div class="new-releases scrollable-section" data-scroll-target="#new-release">
	      			<div class="heading">
	      				<h2>New Releases</h2><div class="scroll-btns"><button class="scroll-btn scroll-new-release-left" data-direction="-1" onclick="scrollRelease(this)"><?php button_left() ?></button><button class="scroll-btn scroll-new-release-right" data-direction="1" onclick="scrollRelease(this)"><?php button_right() ?></button></div>
	      			</div>
	      			<div class="new-release-flex scrollable-content" id="new-release">
	      				<?php new_releases($post_type); ?>
	      			</div>
	      		</div>

	      		<div class="upcoming-tv-series scrollable-section" data-scroll-target="#upcoming-tv-series">
	      			<div class="heading">
	      				<h2>Upcoming TV Series</h2><div class="scroll-btns"><button class="scroll-btn scroll-upcoming-tv-series-left" data-direction="-1" onclick="scrollRelease(this)"><?php button_left() ?></button><button class="scroll-btn scroll-upcoming-tv-series-right" data-direction="1" onclick="scrollRelease(this)"><?php button_right() ?></button></div>
	      			</div>
	      			<div class="upcoming-tv-series-flex scrollable-content" id="upcoming-tv-series">
	      				<?php upcoming_tv_series($post_type); ?>
	      			</div>
	      		</div>
	      	</div>

      		<div class="trailer">
      			<div class="heading"><h2>TV Series Trailer</h2></div>
      			<div class="trailer-flex">
      				<?php tv_series_trailer($post_type); ?>
      			</div>
      		</div>

      		<main class="exp--more--secv2">
	      		<div class="heading"><h2>Explore More</h2></div>
	      		<?php explore_more(); ?>
      		</main>

      		<!-- Upcoming TV Series List -->
      		<?php module_2('tv'); ?>

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
  	$section = $post_type == 'movie' ? 'movies' : ($post_type == 'tv' ? 'tv_series' : '');
		$table_name = $section ? $wpdb->prefix.'tmu_'.$section : '';
		if ($table_name) {
			$results = $wpdb->get_results("SELECT t.`ID`,t.`release_date`,t.`original_title` FROM $table_name t JOIN {$wpdb->prefix}posts AS posts ON (t.ID = posts.ID) WHERE unix_timestamp(t.`release_date`)<unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) AND posts.post_status = 'publish' ORDER BY t.`release_date` DESC LIMIT 10");

	  	foreach ($results as $result) {
	  		$title = get_the_title($result->ID);
	  		?>
	  			<a class="new-release-box" href="<?= get_permalink($result->ID) ?>" title="<?= $title ?>">
		  			<div class="new-release-poster">
		  				<img <?= (has_post_thumbnail($result->ID) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url($result->ID, 'full').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ) ?> alt="<?= $title ?>" width="100%" height="100%">
		  			</div>
		  			<div class="new-release-details">
		  				<h3><?= $title ?></h3>
		  				<p class="release-date">RELEASED ON: <?= date( 'd M Y', strtotime($result->release_date) ) ?></p>
		  			</div>
		  		</a>
	  		<?php
	  	}
		}
  }

  function upcoming_tv_series($post_type){
  	global $wpdb;
  	$section = $post_type == 'movie' ? 'movies' : ($post_type == 'tv' ? 'tv_series' : '');
		$table_name = $section ? $wpdb->prefix.'tmu_'.$section : '';
		if ($table_name) {
			if($post_type == 'tv') {
				$query = "SELECT t1.`ID`, t1.`original_title`, t1.`release_date` FROM $table_name t1 JOIN {$wpdb->prefix}posts AS posts ON (t1.ID = posts.ID) LEFT JOIN `{$table_name}_seasons` t2 ON t1.`ID` = t2.`tv_series` WHERE t2.air_date_timestamp > unix_timestamp(DATE_ADD(NOW(), INTERVAL 3 HOUR)) AND posts.post_status = 'publish' ORDER BY t2.air_date_timestamp ASC LIMIT 10";
			} else {
				$query = "SELECT t.`ID`,t.`original_title` FROM $table_name t JOIN {$wpdb->prefix}posts AS posts ON (t.ID = posts.ID) WHERE unix_timestamp(`release_date`)>unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) AND posts.post_status = 'publish' ORDER BY `release_date` ASC LIMIT 10";
			}
			$results = $wpdb->get_results($query);

	  	foreach ($results as $result) {
	  		$title = get_the_title($result->ID);
	  		?>
	  			<a class="upcoming-tv-series-box" href="<?= get_permalink($result->ID) ?>" title="<?= $title ?>">
		  			<div class="upcoming-tv-series-poster">
		  				<img <?= (has_post_thumbnail($result->ID) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url($result->ID, 'full').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ) ?> alt="<?= $title ?>" width="100%" height="100%">
		  			</div>
		  			<div class="upcoming-tv-series-details">
		  				<h3><?= $title ?></h3>
		  				<p class="release-date">RELEASED ON: <?= date( 'd M Y', strtotime($result->release_date) ) ?></p>
		  			</div>
		  		</a>
	  		<?php
	  	}
		}
  }

  function tv_series_trailer($post_type){
  	global $wpdb;
  	$section = $post_type == 'movie' ? 'movies' : ($post_type == 'tv' ? 'tv_series' : '');
		$table_name = $section ? $wpdb->prefix.'tmu_'.$section : ''; $tmp_count = 0;
		if ($table_name) {
			$results = $wpdb->get_results("SELECT t.`ID`,t.`videos`,t.`release_date`,t.`original_title` FROM $table_name t JOIN {$wpdb->prefix}posts AS posts ON (t.ID = posts.ID) WHERE unix_timestamp(`release_date`)>unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) AND posts.post_status = 'publish' ORDER BY `release_date` DESC LIMIT 100");

		  	foreach ($results as $result) {

	  		$genres = implode(', ', wp_get_object_terms( $result->ID, 'genre', array('fields' => 'names') ));

	  		$videos = $result->videos ? unserialize($result->videos) : '';
	  		$video_id = ''; $video_meta = ''; $total = $videos ? count($videos) : 0;

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
			  				<img <?= ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-image.webp" data-src="'.($videoURL ? $videoURL : plugin_dir_url( __DIR__ ) . 'src/images/no-image.webp').'" class="lazyload"') ?> alt="<?= $title ?>" width="100%" height="100%">
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
			  	if ($tmp_count===4) return;
	  		}
	  	}
		}
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