<?php

function footer_content() {
	$site_title = '<a href="'.get_site_url().'" title="'.get_bloginfo( 'name' ).'">'.get_bloginfo( 'name' ).'</a>';
	?>
	<footer class="footer-box">
		<div class="footer-container">
			<div class="ft-top">
				<div class="pages">
					<a href="<?= site_url( '/privacy-policy/' ); ?>" title="Privacy Policy">Privacy Policy</a>
					<a href="<?= site_url( '/terms-of-service/' ); ?>" title="Terms of Service">Terms of Service</a>
					<a href="<?= site_url( '/contact-us/' ); ?>" title="Contact US">Contact US</a>
				</div>
				<div class="social-media">
					<a class="social-icon" href="#" rel="nofollow" title="Share on Facebook"><svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" data-testid="Icon" class="svg-icon icon-sm" height="1em" width="1em"><path d="M19 3H5a2 2 0 00-2 2v14a2 2 0 002 2h7.621v-6.961h-2.343v-2.725h2.343V9.309c0-2.324 1.421-3.591 3.495-3.591.699-.002 1.397.034 2.092.105v2.43H16.78c-1.13 0-1.35.534-1.35 1.322v1.735h2.7l-.351 2.725h-2.365V21H19a2 2 0 002-2V5a2 2 0 00-2-2z"></path></svg></a>

					<a class="social-icon" href="#" rel="nofollow" title="Share on Twitter"><svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" data-testid="Icon" class="svg-icon icon-sm" height="1em" width="1em"><path d="M24 4.3c-.898.4-1.8.7-2.8.802 1-.602 1.8-1.602 2.198-2.704-1 .602-2 1-3.097 1.204C19.3 2.602 18 2 16.6 2a4.907 4.907 0 00-4.9 4.898c0 .403 0 .801.102 1.102C7.7 7.8 4.102 5.898 1.7 2.898c-.5.704-.7 1.602-.7 2.5 0 1.704.898 3.204 2.2 4.102-.802-.102-1.598-.3-2.2-.602V9c0 2.398 1.7 4.398 3.898 4.8-.398.098-.796.2-1.296.2-.301 0-.602 0-.903-.102.602 2 2.403 3.403 4.602 3.403-1.7 1.3-3.801 2.097-6.102 2.097-.398 0-.8 0-1.199-.097C2.2 20.699 4.8 21.5 7.5 21.5c9.102 0 14-7.5 14-14v-.602c1-.699 1.8-1.597 2.5-2.597"></path></svg></a>
					
					<a class="social-icon" href="#" rel="nofollow" title="Share on Instagram"><svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" data-testid="Icon" class="svg-icon icon-sm" height="1em" width="1em"><path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913a5.885 5.885 0 001.384 2.126A5.868 5.868 0 004.14 23.37c.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558a5.898 5.898 0 002.126-1.384 5.86 5.86 0 001.384-2.126c.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913a5.89 5.89 0 00-1.384-2.126A5.847 5.847 0 0019.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227a3.81 3.81 0 01-.899 1.382 3.744 3.744 0 01-1.38.896c-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421a3.716 3.716 0 01-1.379-.899 3.644 3.644 0 01-.9-1.38c-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678a6.162 6.162 0 100 12.324 6.162 6.162 0 100-12.324zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405a1.441 1.441 0 01-2.88 0 1.44 1.44 0 012.88 0z"></path></svg></a>

					<a class="social-icon" href="#" rel="nofollow" title="Share on YouTube"><svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" data-testid="Icon" class="svg-icon icon-sm" height="1em" width="1em"><path d="M5.68 2l1.478 5.344v2.562H8.44V7.344L9.937 2h-1.29l-.538 2.432a27.21 27.21 0 00-.29 1.515h-.04c-.063-.42-.159-.93-.29-1.525L6.97 2H5.68zm5.752 2.018c-.434 0-.784.084-1.051.257-.267.172-.464.448-.59.825-.125.377-.187.876-.187 1.498v.84c0 .615.054 1.107.164 1.478.11.371.295.644.556.82.261.176.62.264 1.078.264.446 0 .8-.087 1.06-.26.26-.173.45-.444.565-.818.116-.374.174-.869.174-1.485v-.84c0-.62-.059-1.118-.178-1.492-.119-.373-.308-.648-.566-.824-.258-.176-.598-.263-1.025-.263zm2.447.113v4.314c0 .534.09.927.271 1.178.182.251.465.377.848.377.552 0 .968-.267 1.244-.8h.028l.113.706H17.4V4.131h-1.298v4.588a.635.635 0 01-.23.263.569.569 0 01-.325.104c-.132 0-.226-.054-.283-.164-.057-.11-.086-.295-.086-.553V4.131h-1.3zm-2.477.781c.182 0 .311.095.383.287.072.191.108.495.108.91v1.8c0 .426-.036.735-.108.923-.072.188-.2.282-.38.283-.183 0-.309-.095-.378-.283-.07-.188-.103-.497-.103-.924V6.11c0-.414.035-.718.107-.91.072-.19.195-.287.371-.287zM5 11c-1.1 0-2 .9-2 2v7c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-7c0-1.1-.9-2-2-2H5zm7.049 2h1.056v2.568h.008c.095-.186.232-.335.407-.449.175-.114.364-.17.566-.17.26 0 .463.07.611.207.148.138.257.361.323.668.066.308.097.735.097 1.281v.772h.002c0 .727-.089 1.26-.264 1.602-.175.342-.447.513-.818.513-.207 0-.394-.047-.564-.142a.93.93 0 01-.383-.391h-.024l-.11.46h-.907V13zm-6.563.246h3.252v.885h-1.09v5.789H6.576v-5.79h-1.09v-.884zm11.612 1.705c.376 0 .665.07.867.207.2.138.343.354.426.645.082.292.123.695.123 1.209v.836h-1.836v.248c0 .313.008.547.027.703.02.156.057.27.115.342.058.072.148.107.27.107.164 0 .277-.064.338-.191.06-.127.094-.338.1-.635l.947.055a1.6 1.6 0 01.007.175c0 .451-.124.788-.37 1.01-.248.223-.595.334-1.046.334-.54 0-.92-.17-1.138-.51-.218-.339-.326-.863-.326-1.574v-.851c0-.732.112-1.267.337-1.604.225-.337.613-.506 1.159-.506zm-8.688.094h1.1v3.58c0 .217.024.373.072.465.048.093.126.139.238.139a.486.486 0 00.276-.088.538.538 0 00.193-.223v-3.873h1.1v4.875h-.862l-.093-.598h-.026c-.234.452-.584.678-1.05.678-.325 0-.561-.106-.715-.318-.154-.212-.233-.544-.233-.994v-3.643zm8.664.648c-.117 0-.204.036-.26.104-.056.069-.093.182-.11.338a6.504 6.504 0 00-.028.71v.35h.803v-.35c0-.312-.01-.548-.032-.71-.02-.162-.059-.276-.115-.342-.056-.066-.14-.1-.258-.1zm-3.482.036a.418.418 0 00-.293.126.699.699 0 00-.192.327v2.767a.487.487 0 00.438.256.337.337 0 00.277-.127c.07-.085.12-.228.149-.43.029-.2.043-.48.043-.835v-.627c0-.382-.011-.676-.035-.883-.024-.207-.067-.357-.127-.444a.299.299 0 00-.26-.13z"></path></svg></a>
				</div>
			</div>
			<div class="ft-bottom">
				<div class="copyright">Copyright © <?= date( 'Y' ) ?>, All Rights Reserved</div>
				<div class="site-title"><?= $site_title ?></div>
			</div>
		</div>
	</footer>
	<?php
}

function term_footer_links($current_term, $current_tax, $current_links=true){
	$terms = $current_links ? get_terms( $current_tax, array( 'orderby' => 'count', 'order' => 'DESC', 'hide_empty' => 0, 'exclude' => array($current_term), ) ) : '';
	$data = $channels_data = $genres_data = $networks_data = '';
	$options = get_options(["tmu_dramas","tmu_movies","tmu_tv_series"]);
	$dramas = $options['tmu_dramas'] === 'on' && $options['tmu_tv_series'] === 'off' && $options['tmu_movies'] === 'off';
	$movie = $options['tmu_dramas'] === 'off' && $options['tmu_tv_series'] === 'off' && $options['tmu_movies'] === 'on';
	$tv = $options['tmu_dramas'] === 'off' && $options['tmu_tv_series'] === 'on' && $options['tmu_movies'] === 'off';
	$typeText = $dramas ? ' Dramas' : ($movie ? ' Movies' : ($tv ? ' TV Shows' : ''));
	$svg = '<svg xmlns="http://www.w3.org/2000/svg" width="21" height="15" viewBox="0 0 21 15" class="explore_more_arrow"><path id="Icon_ionic-md-arrow-forward" data-name="Icon ionic-md-arrow-forward" d="M-.023,14.414H17.367l-5.25,5.25,1.36,1.312,7.5-7.5-7.5-7.5L12.164,7.289l5.2,5.25H-.023Z" transform="translate(0.023 -5.977)"/></svg>';


	if ($current_links && $terms) {
		$data .= '<main class="exp--more--secv2">';
			$data .= '<div class="heading"><h3>Browse'.$typeText.' By '.ucfirst($current_tax).'</h3></div>';
			$data .= '<div class="grid--expbox--v2">';
				foreach ($terms as $term) {
					$data .= '<div class="flex--exp--boxv2">';
					$data .= '<a href="'.esc_url( get_term_link( $term ) ).'" title="'.$term->name.'"><h4 class="exp--v2--fonts">'.$term->name.'</h4>'.$svg.'</a>';
					$data .= '</div>';
				}
			$data .= '</div>';
		$data .= '</main>';
	}

	// if ($current_tax !== 'genre') {
	// 	$genres = get_terms( 'genre', array( 'orderby' => 'count', 'order' => 'DESC', 'hide_empty' => 0, ) );
	// 	if ($genres) {
	// 		$genres_data .= '<div class="footer-links-container">';
	// 			$genres_data .= '<h3>Browse'.$typeText.' By Genres</h3>';
	// 			$genres_data .= '<div class="footer-links">';
	// 			foreach ($genres as $term) {
	// 				$genres_data .= '<a class="footer-link" href="'.esc_url( get_term_link( $term ) ).'">'.$term->name.'</a>';
	// 			}
	// 			$genres_data .= '</div>';
	// 		$genres_data .= '</div>';
	// 	}
	// }

	if (taxonomy_exists( 'genre' ) && $current_tax !== 'genre') {
		$genres = get_terms( 'genre', array( 'orderby' => 'count', 'order' => 'DESC', 'hide_empty' => 0, ) );
		if ($genres) {
			$genres_data .= '<main class="exp--more--secv2">';
				$genres_data .= '<div class="heading"><h3>Browse'.$typeText.' By Genres</h3></div>';
				$genres_data .= '<div class="grid--expbox--v2">';
					foreach ($genres as $term) {
						$genres_data .= '<div class="flex--exp--boxv2">';
						$genres_data .= '<a href="'.esc_url( get_term_link( $term ) ).'" title="'.$term->name.'"><h4 class="exp--v2--fonts">'.$term->name.'</h4>'.$svg.'</a>';
						$genres_data .= '</div>';
					}
				$genres_data .= '</div>';
			$genres_data .= '</main>';
		}
	}

	if (taxonomy_exists( 'channel' ) && $current_tax !== 'channel') {
		$channels = get_terms( 'channel', array( 'orderby' => 'count', 'order' => 'DESC', 'hide_empty' => 0, ) );
		if ($channels) {
			$channels_data .= '<main class="exp--more--secv2">';
				$channels_data .= '<div class="heading"><h3>Browse'.$typeText.' By Channel</h3></div>';
				$channels_data .= '<div class="grid--expbox--v2">';
					foreach ($channels as $term) {
						$channels_data .= '<div class="flex--exp--boxv2">';
						$channels_data .= '<a href="'.esc_url( get_term_link( $term ) ).'" title="'.$term->name.'"><h4 class="exp--v2--fonts">'.$term->name.'</h4>'.$svg.'</a>';
						$channels_data .= '</div>';
					}
				$channels_data .= '</div>';
			$channels_data .= '</main>';
		}
	}

	if (taxonomy_exists( 'network' ) && $current_tax !== 'network') {
		$networks = get_terms( 'network', array( 'orderby' => 'count', 'order' => 'DESC', 'hide_empty' => 0, ) );
		if ($networks) {
			$networks_data .= '<main class="exp--more--secv2">';
				$networks_data .= '<div class="heading"><h3>Browse'.$typeText.' By Network</h3></div>';
				$networks_data .= '<div class="grid--expbox--v2">';
					foreach ($networks as $term) {
						$networks_data .= '<div class="flex--exp--boxv2">';
						$networks_data .= '<a href="'.esc_url( get_term_link( $term ) ).'" title="'.$term->name.'"><h4 class="exp--v2--fonts">'.$term->name.'</h4>'.$svg.'</a>';
						$networks_data .= '</div>';
					}
				$networks_data .= '</div>';
			$networks_data .= '</main>';
		}
	}

	return $channels_data.$genres_data.$networks_data.$data;
}