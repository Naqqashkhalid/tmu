<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

?>
<title>Find - <?php bloginfo('name'); ?></title>
<meta name="description" content="<?php bloginfo('name'); ?> Find Results Page" />
<?php

get_header();

$options = get_options( ['tmu_movies', 'tmu_tv_series', 'tmu_dramas'] );
?>
	<div <?php generate_do_attr( 'content' ); ?>>
		<main class="site-main inside-article" id="main">
			<?php $query = get_search_query(); $results = search_results( $query ); ?>
			<h1 id="query" data-query='<?= json_encode($results['query_words']) ?>'>Search "<?= $query ?>"</h1>
			<div class="search-results-block">
				<?php
					if ($results['movie'] && $options['tmu_movies'] === 'on') { ?>
						<div class="heading"><h2>Movies</h2></div>
						<div class="movie-block results-block">
							<?php foreach ($results['movie'] as $result) {
								$star_casts = rwmb_meta( 'star_cast', '', $result->ID );
								$casts = $star_casts ? array_slice($star_casts,0,2) : []; ?>
								<a href="<?= get_permalink($result->ID) ?>" class="single-result" title="<?= $result->post_title ?>">
									<div class="image-container"><div class="result-image"><img <?= (has_post_thumbnail($result->ID) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url($result->ID, 'medium').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ) ?> alt="<?= $result->post_title ?>" width="100%" height="100%"></div></div>
									<div class="result-details">
										<div class="result-title"><?= $result->post_title ?></div>
										<div class="extra-info"><span class="release_year"><?= implode(', ', wp_get_object_terms( $result->ID, 'by-year', array('fields' => 'names') )) ?></span> <span class="post_type">Movie</span></div>
										<div class="main_casts"><?= implode(', ', array_map(function ($cast){ return get_the_title($cast['person']); },$casts)) ?></div>
									</div>
								</a>
							<?php } ?>
						</div>
						<?php if ($results['total_movies'] > 5) { ?> <div class="load_more_box load-more-movies" data-type="movie" data-page="1" data-total="<?= ceil($results['total_movies']/5) ?>">More popular matches</div> <?php }
					}

					if ($results['tv'] && $options['tmu_tv_series'] === 'on') { ?>
						<div class="heading"><h2>TV Series</h2></div>
						<div class="tv-block results-block">
							<?php foreach ($results['tv'] as $result) {
								$star_casts = rwmb_meta( 'star_cast', '', $result->ID );
								$casts = $star_casts ? array_slice($star_casts,0,2) : []; ?>
								<a href="<?= get_permalink($result->ID) ?>" class="single-result" title="<?= $result->post_title ?>">
									<div class="image-container"><div class="result-image"><img <?= (has_post_thumbnail($result->ID) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url($result->ID, 'medium').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ) ?> alt="<?= $result->post_title ?>" width="100%" height="100%"></div></div>
									<div class="result-details">
										<div class="result-title"><?= $result->post_title ?></div>
										<div class="extra-info"><span class="release_year"><?= implode(', ', wp_get_object_terms( $result->ID, 'by-year', array('fields' => 'names') )) ?></span> <span class="post_type">TV Series</span></div>
										<div class="main_casts"><?= implode(', ', array_map(function ($cast){ return get_the_title($cast['person']); },$casts)) ?></div>
									</div>
								</a>
							<?php } ?>
						</div>
						<?php if ($results['total_tv'] > 5) { ?> <div class="load_more_box load-more-tv" data-type="tv" data-page="1" data-total="<?= ceil($results['total_tv']/5) ?>">More popular matches</div> <?php }
					}

					if ($results['drama'] && $options['tmu_dramas'] === 'on') { ?>
						<div class="heading"><h2>Drama Series</h2></div>
						<div class="drama-block results-block">
							<?php foreach ($results['drama'] as $result) {
								$star_casts = rwmb_meta( 'star_cast', '', $result->ID );
								$casts = $star_casts ? array_slice($star_casts,0,2) : []; ?>
								<a href="<?= get_permalink($result->ID) ?>" class="single-result" title="<?= $result->post_title ?>">
									<div class="image-container"><div class="result-image"><img <?= (has_post_thumbnail($result->ID) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url($result->ID, 'medium').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ) ?> alt="<?= $result->post_title ?>" width="100%" height="100%"></div></div>
									<div class="result-details">
										<div class="result-title"><?= $result->post_title ?></div>
										<div class="extra-info"><span class="release_year"><?= implode(', ', wp_get_object_terms( $result->ID, 'by-year', array('fields' => 'names') )) ?></span> <span class="post_type">Drama Series</span></div>
										<div class="main_casts"><?= implode(', ', array_map(function ($cast){ return get_the_title($cast['person']); },$casts)) ?></div>
									</div>
								</a>
							<?php } ?>
						</div>
						<?php if ($results['total_drama'] > 5) { ?> <div class="load_more_box load-more-drama" data-type="drama" data-page="1" data-total="<?= ceil($results['total_drama']/5) ?>">More popular matches</div> <?php }
					}

					if ($results['people']) { ?>
						<div class="heading"><h2>People</h2></div>
						<div class="people-block results-block">
							<?php foreach ($results['people'] as $result) { 
								global $wpdb;
								$movies_cast_table = $wpdb->prefix.'tmu_movies_cast';
								$movies_crew_table = $wpdb->prefix.'tmu_movies_crew';
								$tv_series_cast_table = $wpdb->prefix.'tmu_tv_series_cast';
								$tv_series_crew_table = $wpdb->prefix.'tmu_tv_series_crew';
								$dramas_cast_table = $wpdb->prefix.'tmu_dramas_cast';
								$dramas_crew_table = $wpdb->prefix.'tmu_dramas_crew';

								$tv_movie = $wpdb->get_var($wpdb->prepare("SELECT t.movie FROM $movies_cast_table t JOIN {$wpdb->prefix}posts AS posts ON (t.ID = posts.ID) WHERE t.`person`=%d AND posts.post_status = 'publish' ORDER BY `release_year` DESC", $result->ID));
								$tv_movie = $tv_movie ? $tv_movie : $wpdb->get_var($wpdb->prepare("SELECT t.tv_series FROM $tv_series_cast_table t JOIN {$wpdb->prefix}posts AS posts ON (t.ID = posts.ID) WHERE t.`person`=%d AND posts.post_status = 'publish' ORDER BY `release_year` DESC", $result->ID));
								$tv_movie = $tv_movie ? $tv_movie : $wpdb->get_var($wpdb->prepare("SELECT t.dramas FROM $dramas_cast_table t JOIN {$wpdb->prefix}posts AS posts ON (t.ID = posts.ID) WHERE t.`person`=%d AND posts.post_status = 'publish' ORDER BY `release_year` DESC", $result->ID));
								$tv_movie = $tv_movie ? $tv_movie : $wpdb->get_var($wpdb->prepare("SELECT t.movie FROM $movies_crew_table t JOIN {$wpdb->prefix}posts AS posts ON (t.ID = posts.ID) WHERE t.`person`=%d AND posts.post_status = 'publish' ORDER BY `release_year` DESC", $result->ID));
								$tv_movie = $tv_movie ? $tv_movie : $wpdb->get_var($wpdb->prepare("SELECT t.tv_series FROM $tv_series_crew_table t JOIN {$wpdb->prefix}posts AS posts ON (t.ID = posts.ID) WHERE t.`person`=%d AND posts.post_status = 'publish' ORDER BY `release_year` DESC", $result->ID));
								$tv_movie = $tv_movie ? $tv_movie : $wpdb->get_var($wpdb->prepare("SELECT t.dramas FROM $dramas_crew_table t JOIN {$wpdb->prefix}posts AS posts ON (t.ID = posts.ID) WHERE t.`person`=%d AND posts.post_status = 'publish' ORDER BY `release_year` DESC", $result->ID));
								$year = $tv_movie ? implode(', ', wp_get_object_terms( $tv_movie, 'by-year', array('fields' => 'names') )) : ''; ?>
								<a href="<?= get_permalink($result->ID) ?>" class="single-result" title="<?= $result->post_title ?>">
									<div class="image-container"><div class="result-image"><img <?= (has_post_thumbnail($result->ID) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url($result->ID, 'medium').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ) ?> alt="<?= $result->post_title ?>" width="100%" height="100%"></div></div>
									<div class="result-details">
										<div class="result-title"><?= $result->post_title ?></div>
										<div class="profession"><?php rwmb_the_value( 'profession', '', $result->ID ); ?></div>
										<div class="movie"><?= $tv_movie ? get_the_title($tv_movie).($year ? '('.$year.')' : '') : '' ?></div>
									</div>
								</a>
							<?php } ?>
						</div>
						<?php if ($results['total_people'] > 5) { ?> <div class="load_more_box load-more-people" data-type="people" data-page="1" data-total="<?= ceil($results['total_people']/5) ?>"><span class="button-text">More popular matches</span><span class="arrow-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" class="ipc-icon ipc-icon--expand-more ipc-btn__icon ipc-btn__icon--post" viewBox="0 0 24 24" fill="currentColor" role="presentation"><path opacity=".87" fill="none" d="M24 24H0V0h24v24z"></path><path d="M15.88 9.29L12 13.17 8.12 9.29a.996.996 0 1 0-1.41 1.41l4.59 4.59c.39.39 1.02.39 1.41 0l4.59-4.59a.996.996 0 0 0 0-1.41c-.39-.38-1.03-.39-1.42 0z"></path></svg></span></div> <?php }
					}

					if ($results['post']) { ?>
						<div class="heading"><h2>News</h2></div>
						<div class="post-block results-block">
							<?php foreach ($results['post'] as $result) { ?>
								<a href="<?= get_permalink($result->ID) ?>" class="single-result" title="<?= $result->post_title ?>">
									<div class="result-details">
										<div class="result-title"><?= $result->post_title ?></div>
										<div class="extra-info"><span class="release_year"><?= get_the_date('F d, Y', $result->ID) ?></span> <span class="post_type">News</span></div>
									</div>
								</a>
							<?php } ?>
						</div>
						<?php if ($results['total_posts'] > 5) { ?> <div class="load_more_box load-more-post" data-type="post" data-page="1" data-total="<?= ceil($results['total_posts']/5) ?>">More popular matches</div> <?php }
					}

					if (!$results['movie'] && !$results['drama'] && !$results['tv'] && !$results['people'] && !$results['post']) {
						?><p style="text-align: center; font-size: 26px; margin-top: 43px;">Your Search result is not available try with right word!</p>
						<style>.search-links {display: flex; gap: 10px; justify-content: center;} .search-link { font-weight: 700; font-size: 20px; padding: 10px; background: #323846; color: #24baef; border-radius: 6px; min-width: 75px;} .search-link:hover { background-color:#24baef; color:#323846 }</style>
						<center class="search-links">
							<a href="<?= site_url(); ?>" class="search-link" title="Home">Home</a>
							<?php if ($options['tmu_movies'] === 'on') { ?><a href="<?= site_url( '/movie/' ); ?>" class="search-link" title="Movie">Movie</a><?php } ?>
							<?php if ($options['tmu_tv_series'] === 'on') { ?><a href="<?= site_url( '/tv/' ); ?>" class="search-link" title="TV">TV</a><?php } ?>
							<?php if ($options['tmu_dramas'] === 'on') { ?><a href="<?= site_url( '/drama/' ); ?>" class="search-link" title="Drama">Drama</a><?php } ?>
						</center>
						<?php
					}
				?>
			</div>
		</main>
	</div>

	<?php

	generate_construct_sidebars();

	get_footer();