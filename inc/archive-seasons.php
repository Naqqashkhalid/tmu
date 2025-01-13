<?php

function season_archive($slug){

if ( $series = get_page_by_path( $slug, OBJECT, 'tv' ) ):
	global $post; $post = $series; setup_postdata( $post );
	$post_title = $series->post_title;

	$series_id = get_the_ID();
	global $wpdb;

	$seasons = $wpdb->get_results($wpdb->prepare("SELECT s.* FROM {$wpdb->prefix}tmu_tv_series_seasons s JOIN {$wpdb->prefix}posts AS posts ON (s.ID = posts.ID) WHERE s.`tv_series` = %s AND posts.post_status = 'publish' ORDER BY s.air_date", $series_id), ARRAY_A);
	$release_years = []; $seasons_data = ''; $count = 0;
	$schema = default_schema($post_title.' Seasons List', $_SERVER['REQUEST_URI']).'<script type="application/ld+json">{ "@context": "https://schema.org", "@type": "TVSeries", "name": "'.$post_title.'", "containsSeason": [';
	if($seasons) foreach ($seasons as $season) {
		$post = season_wrapper($season);
		$schema .= ($count != 0 ? ',' : '').$post['schema']; $count++;
		$release_years[] = $season['air_date'] ? date('Y', strtotime($season['air_date'])) : '';
		$seasons_data .= $post['data'];
	}
	$schema .= ']}</script>';
	$total_years = $release_years ? count($release_years)-1 : 0;
	$year = $release_years ? (' ('.($total_years >= 1 ? $release_years[0].' - '.$release_years[$total_years] : $release_years[0]).')') : '';

	remove_action( 'wp_head', '_wp_render_title_tag', 1 );
	add_action('wp_head', function () use($post_title, $year, $schema) { meta_inf_seasons_archive($post_title, $year); echo $schema; }, 1);

	get_header();
	?>

	<div <?php generate_do_attr( 'content' ); ?> style="width: 100%;">
		<main <?php generate_do_attr( 'main' ); ?>>
			<?php
				do_action( 'generate_before_main_content' );
				series_header($series_id, $post_title, $year); ?>
				<div class="seasons-list"><?= $seasons_data ?></div>
				<?php do_action( 'generate_after_main_content' ); ?>
		</main>
	</div>

	<?php

	do_action( 'generate_after_primary_content_area' );
	get_footer();
	wp_reset_postdata();

endif;

}

function meta_inf_seasons_archive($title, $year){ ?>
		<title><?= $title.$year ?> Seasons List</title>
		<meta name="description" content="<?= $title.$year ?> Seasons List" />
        <meta property="og:url"           content="<?= get_permalink() ?>seasons/" />
        <meta property="og:type"          content="website" />
        <meta property="og:title"         content="<?= $title.$year ?> Seasons List" />
        <meta property="og:description"   content="<?= $title.$year ?> Seasons List" />
        <meta name="robots" content="follow, index, max-snippet:-1, max-image-preview:-1, max-image-preview:large"/>
		<link rel="canonical" href="<?= get_permalink() ?>" />
		<link rel="stylesheet" href="<?= plugin_dir_url( __DIR__ ) ?>src/css/seasons-archive.css">
<?php }

function series_header($series_id, $title, $year){
$current_page = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$series_permalink = get_permalink($series_id);
?>
	<section class="series-header">
		<div class="series-column">
			<a class="series-poster" href="<?= $series_permalink ?>" title="<?= $title ?>">
				<img <?= (has_post_thumbnail() ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url(get_the_ID(), 'medium').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ) ?> alt="<?= $title ?>" width="100%" height="100%">
			</a>
			<div class="series">
				<div class="title-column">
					<h1 class="series-title">
						<a href="<?= $series_permalink ?>" title="<?= $title ?>"><?= $title ?></a>
						<span class="series-release-year"><?= $year ?></span>
					</h1>
					<a href="<?= $series_permalink ?>" class="parent-post" title="Back to main">Back to main</a>
				</div>
				<?php social_sharing_button_seasons($current_page, $title.' Season List'); ?>
			</div>
		</div>
	</section>
<?php
}

function season_wrapper($season){
	$season_id = (int)$season['ID'];
	$name = $season['season_name'];
	$season_no = $season['season_no'];
	$series_id = $season['tv_series'];
	$release_date = $season['air_date'];
	$poster_url = has_post_thumbnail($season_id) ? get_the_post_thumbnail_url($season_id, 'medium') : plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"';
	$permalink = get_permalink($season_id);
	global $wpdb;
	$total_episodes = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}tmu_tv_series_episodes e JOIN {$wpdb->prefix}posts AS posts ON (e.ID = posts.ID) WHERE e.`season_id` = $season_id AND posts.post_status = 'publish'");

	$data = '<div class="season_wrapper">';
		$data .= '<div class="season">';
			$data .= '<a href="'.$permalink.'" class="season_poster" title="'.$name.'">';
				$data .= '<img '.($poster_url ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.$poster_url.'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ).' alt="'.$name.'" width="100%" height="100%">';
			$data .= '</a>';

      $data .= '<div class="content">';
        $data .= '<h2><a href="'.$permalink.'" title="'.$name.'">'.$name.'</a></h2>';
        $data .= '<div class="total-episodes">'.date('Y', strtotime($release_date)).' • '.$total_episodes.' Episodes</div>';
        $data .= '<div class="season_overview">Season '.$season_no.' of '.get_the_title().' premiered on '.$release_date.'.</div>';
      $data .= '</div>';
    $data .= '</div>';
  $data .= '</div>';

	return ['data' => $data, 'schema' => '{ "@type": "TVSeason", "datePublished": "'.$release_date.'", "name": "Season '.$season_no.'", "numberOfEpisodes": "'.$total_episodes.'" }'];

}

function social_sharing_button_seasons($current_page, $title){
?>
	<div class="social-sharing-icons">
		<a class="social-icon facebook" href="https://www.facebook.com/sharer/sharer.php?u=<?= $current_page ?>" title="Share on Facebook">
			<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="24" height="24" viewBox="0,0,256,256">
				<g transform="translate(-16.64,-16.64) scale(1.13,1.13)"><g fill="#0073c2" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none" font-size="none" text-anchor="none" style="mix-blend-mode: normal"><g transform="scale(5.12,5.12)"><path d="M42,3h-34c-2.8,0 -5,2.2 -5,5v34c0,2.8 2.2,5 5,5h34c2.8,0 5,-2.2 5,-5v-34c0,-2.8 -2.2,-5 -5,-5zM37,19h-2c-2.1,0 -3,0.5 -3,2v3h5l-1,5h-4v16h-5v-16h-4v-5h4v-3c0,-4 2,-7 6,-7c2.9,0 4,1 4,1z"></path></g></g></g>
			</svg>
		</a>
		<a class="social-icon twitter" href="https://twitter.com/intent/tweet?text=<?= $title ?>&url=<?= $current_page ?>" title="Share on Twitter">
			<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="24" height="24" viewBox="0,0,256,256">
				<g transform="translate(-24.32,-24.32) scale(1.19,1.19)"><g fill="#000000" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none" font-size="none" text-anchor="none" style="mix-blend-mode: normal"><g transform="scale(5.12,5.12)"><path d="M11,4c-3.866,0 -7,3.134 -7,7v28c0,3.866 3.134,7 7,7h28c3.866,0 7,-3.134 7,-7v-28c0,-3.866 -3.134,-7 -7,-7zM13.08594,13h7.9375l5.63672,8.00977l6.83984,-8.00977h2.5l-8.21094,9.61328l10.125,14.38672h-7.93555l-6.54102,-9.29297l-7.9375,9.29297h-2.5l9.30859,-10.89648zM16.91406,15l14.10742,20h3.06445l-14.10742,-20z"></path></g></g></g>
			</svg>
		</a>
		<a class="social-icon whatsapp" href="https://api.whatsapp.com/send?text=<?= $title ?>: <?= $current_page ?>" title="Share on Whatsapp">
			<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="24" height="24" viewBox="0,0,256,256">
				<g transform="translate(-29.44,-29.44) scale(1.23,1.23)"><g fill="#45d354" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none" font-size="none" text-anchor="none" style="mix-blend-mode: normal"><g transform="scale(8.53333,8.53333)"><path d="M15,3c-6.627,0 -12,5.373 -12,12c0,2.25121 0.63234,4.35007 1.71094,6.15039l-1.60352,5.84961l5.97461,-1.56836c1.74732,0.99342 3.76446,1.56836 5.91797,1.56836c6.627,0 12,-5.373 12,-12c0,-6.627 -5.373,-12 -12,-12zM10.89258,9.40234c0.195,0 0.39536,-0.00119 0.56836,0.00781c0.214,0.005 0.44692,0.02067 0.66992,0.51367c0.265,0.586 0.84202,2.05608 0.91602,2.20508c0.074,0.149 0.12644,0.32453 0.02344,0.51953c-0.098,0.2 -0.14897,0.32105 -0.29297,0.49805c-0.149,0.172 -0.31227,0.38563 -0.44727,0.51563c-0.149,0.149 -0.30286,0.31238 -0.13086,0.60938c0.172,0.297 0.76934,1.27064 1.65234,2.05664c1.135,1.014 2.09263,1.32561 2.39063,1.47461c0.298,0.149 0.47058,0.12578 0.64258,-0.07422c0.177,-0.195 0.74336,-0.86411 0.94336,-1.16211c0.195,-0.298 0.39406,-0.24644 0.66406,-0.14844c0.274,0.098 1.7352,0.8178 2.0332,0.9668c0.298,0.149 0.49336,0.22275 0.56836,0.34375c0.077,0.125 0.07708,0.72006 -0.16992,1.41406c-0.247,0.693 -1.45991,1.36316 -2.00391,1.41016c-0.549,0.051 -1.06136,0.24677 -3.56836,-0.74023c-3.024,-1.191 -4.93108,-4.28828 -5.08008,-4.48828c-0.149,-0.195 -1.21094,-1.61031 -1.21094,-3.07031c0,-1.465 0.76811,-2.18247 1.03711,-2.48047c0.274,-0.298 0.59492,-0.37109 0.79492,-0.37109z"></path></g></g></g>
			</svg>
		</a>
		<a class="social-icon telegram" href="https://t.me/share/url?url=<?= $current_page ?>&text=<?= $title ?>&to=" title="Share on Telegram">
			<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="24" height="24" viewBox="0,0,256,256">
				<g transform="translate(-10.24,-10.24) scale(1.08,1.08)"><g fill="#02c8f0" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none" font-size="none" text-anchor="none" style="mix-blend-mode: normal"><g transform="scale(5.12,5.12)"><path d="M25,2c12.703,0 23,10.297 23,23c0,12.703 -10.297,23 -23,23c-12.703,0 -23,-10.297 -23,-23c0,-12.703 10.297,-23 23,-23zM32.934,34.375c0.423,-1.298 2.405,-14.234 2.65,-16.783c0.074,-0.772 -0.17,-1.285 -0.648,-1.514c-0.578,-0.278 -1.434,-0.139 -2.427,0.219c-1.362,0.491 -18.774,7.884 -19.78,8.312c-0.954,0.405 -1.856,0.847 -1.856,1.487c0,0.45 0.267,0.703 1.003,0.966c0.766,0.273 2.695,0.858 3.834,1.172c1.097,0.303 2.346,0.04 3.046,-0.395c0.742,-0.461 9.305,-6.191 9.92,-6.693c0.614,-0.502 1.104,0.141 0.602,0.644c-0.502,0.502 -6.38,6.207 -7.155,6.997c-0.941,0.959 -0.273,1.953 0.358,2.351c0.721,0.454 5.906,3.932 6.687,4.49c0.781,0.558 1.573,0.811 2.298,0.811c0.725,0 1.107,-0.955 1.468,-2.064z"></path></g></g></g>
			</svg>
		</a>
	</div>
<?php
}