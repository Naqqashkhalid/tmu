<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

$post_type = get_post_type();
$site_url = get_site_url();
$permalink = $site_url.$_SERVER['REQUEST_URI'];

$options = get_options( ['tmu_movies', 'tmu_tv_series', 'tmu_dramas'] );
$trailers = latest_trailers($options);
$schema = default_schema(ucfirst($post_type), $permalink).$trailers['schema'];

add_action('wp_head', function() use ($schema) { echo $schema; });
get_header();
?>
  <link rel="stylesheet" href="<?= plugin_dir_url( __DIR__ ) ?>src/css/archive-movies.css">
  <div <?php generate_do_attr( 'content' ); ?>>
    <main <?php generate_do_attr( 'main' ); ?>>
      <?php do_action( 'generate_before_main_content' ); ?>
      <section>
      	<div class="archive-header">
      		<ul class="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
      			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a itemprop="item" href="<?= $site_url ?>" title="Home"><span itemprop="name">Home</span></a><meta itemprop="position" content="1" /></li>
      			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"> / <meta itemprop="item" content="<?= $permalink ?>" /><span itemprop="name"><?= ucfirst($post_type) ?></span><meta itemprop="position" content="2" /></li>
      		</ul>

      		<div class="trailer">
      			<div class="heading"><h1><?= $options['tmu_movies']==='on' ? ($options['tmu_tv_series']==='on' ? 'Watch New Movies & TV Show Trailers' : 'Watch New Movies Trailers') : ($options['tmu_tv_series']==='on' ? 'Watch New TV Show Trailers' : 'Latest Pakistani Drama Trailers & OST') ?></h1></div>
      			<div class="trailer-flex">
      				<?= $trailers['data'] ?>
      			</div>
      		</div>

      	</div>
      </section>
    </main>
  </div>

  <?php
  do_action( 'generate_after_primary_content_area' );

  generate_construct_sidebars();

  get_footer();


  function recent_trailers(){
  	global $wpdb;
  	$table_name = $wpdb->prefix.'tmu_videos';
  	$query = 'a:2:{s:6:"source";s:11:"%";s:12:"content_type";s:7:"Trailer";}';
  	$results = $wpdb->get_results("SELECT v.ID,v.post_id FROM `wp_videos` as v JOIN {$wpdb->prefix}posts AS posts ON (v.ID = posts.ID) WHERE `video_data` LIKE '{$query}' AND v.`post_id` IS NOT NULL AND posts.post_status = 'publish' GROUP BY v.`post_id` LIMIT 20");
  	foreach ($results as $result) {
			$videoURL = has_post_thumbnail($result->post_id) ? get_the_post_thumbnail_url($result->post_id, 'full') : '';
  		$video_permalink = get_permalink($result->ID);
  		$title = get_the_title($result->ID);
  		$post_title = get_the_title($result->post_id);
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
  			<p class="title-trailer"><?= $title ?></p>
  			<a class="series-title" href="<?= get_permalink($result->post_id) ?>" title="<?= $post_title ?>"><h3><?= $post_title ?></h3></a>
  		</div>
  	<?php
		}
  }


  function latest_trailers($options){
  	global $wpdb;
		$movies_table = $wpdb->prefix.'tmu_movies';
		$tv_table = $wpdb->prefix.'tmu_tv_series';
		$drama_table = $wpdb->prefix.'tmu_dramas';
		// $movies = $wpdb->get_results("SELECT `ID`,`videos`,`release_date` FROM $movies_table WHERE `videos` IS NOT NULL AND `videos` != 'a:0:{}' ORDER BY `release_date` DESC LIMIT 20");
		// $tv_table = $wpdb->get_results("SELECT `ID`,`videos`,`release_date` FROM $tv_table WHERE `videos` IS NOT NULL AND `videos` != 'a:0:{}' ORDER BY `release_date` DESC LIMIT 20");

		$filter = " `videos` IS NOT NULL AND `videos` != 'a:0:{}' AND release_timestamp<=unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) AND posts.post_status = 'publish' ";

		// Join query with filtering and ordering
		$sql = "SELECT m.ID AS ID, m.videos AS videos, m.release_timestamp AS release_timestamp, 'movie' AS media_type
        FROM $movies_table AS m
        JOIN {$wpdb->prefix}posts AS posts ON (m.ID = posts.ID)
        WHERE $filter
        UNION ALL
        SELECT tv.ID AS ID, tv.videos AS videos, tv.release_timestamp AS release_timestamp, 'tv_show' AS media_type
        FROM $tv_table AS tv
        JOIN {$wpdb->prefix}posts AS posts ON (tv.ID = posts.ID)
        WHERE $filter
        UNION ALL
        SELECT drama.ID AS ID, drama.videos AS videos, drama.release_timestamp AS release_timestamp, 'drama' AS media_type
        FROM $drama_table AS drama
        JOIN {$wpdb->prefix}posts AS posts ON (drama.ID = posts.ID)
        WHERE $filter
        ORDER BY release_timestamp DESC
        LIMIT 40";

		$results = $wpdb->get_results($sql);
		$count_schema = 0;

		$schema = '<script type="application/ld+json"> { "@context": "https://schema.org", "@type": "ItemList", "itemListElement": [';
		$data = '';
  	
  	foreach ($results as $result) {
  		$videos = $result->videos ? unserialize($result->videos) : '';
  		$video_id = ''; $video_meta = ''; $count = 0;

  		if (is_array($videos)) {
  			foreach ($videos as $video_id) {
  				if ($count++ == 1) break;
	  			$video_meta = rwmb_meta( 'video_data', '', $video_id );
	  			$videoURL = has_post_thumbnail($result->ID) ? get_the_post_thumbnail_url($result->ID, 'full') : '';
		  		$video_permalink = get_permalink($video_id);
		  		$title = get_the_title($video_id);
		  		$post_title = get_the_title($result->ID);

		  		$data .= '<div class="trailer-box">';
		  			$data .= '<a class="video-image video-single" href="'.$video_permalink.'" title="'.$title.'">';
		  				$data .= '<img '.('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-image.webp" data-src="'.($videoURL ? $videoURL : plugin_dir_url( __DIR__ ) . 'src/images/no-image.webp').'" class="lazyload"').' alt="'.$title.'" width="100%" height="100%">';
							$data .= '<span class="play-icon absolute bottom-0 left-0 z-30 flex items-center gap-x-6 p-10 text-white">';
								$data .= '<svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" data-testid="PlayCircleOutlinedIcon" class="svg-icon icon-md" height="1em" width="1em">';
									$data .= '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-2.5-3.5 7-4.5-7-4.5v9z"></path>';
								$data .= '</svg>';
								$data .= '<span class="capitalize">'.$video_meta['content_type'].'</span>';
							$data .= '</span>';
							$data .= '<div class="blend"></div>';
		  			$data .= '</a>';
		  			$data .= '<p class="title-trailer">'.$title.'</p>';
		  			$data .= '<a class="series-title" href="'.get_permalink($result->ID).'" title="'.$post_title.'"><h3>'.$post_title.'</h3></a>';
		  		$data .= '</div>';

		  	$schema .= ($count_schema !== 0 ? ',' : '' ).'{ "@type": "ListItem", "position": "'.($count_schema+1).'", "url": "'.$video_permalink.'" }';
        $count_schema++;
	  		}
  		}
		}

		$schema .= '] } </script>';

    return ['data' => $data, 'schema' => $schema];
  }