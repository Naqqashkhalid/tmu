<?php

function top_trending_dramas(){
	$trending = get_option( 'trending-dramas' );

	if ($trending) {
		$data = trending_styles();
		foreach ($trending as $trend => $items) {
			$count = 0;
			$data .= '<div class="trending">';
				if ($trend === 'trending_youtube') { $data .=  '<h2>Trending TOP 10 Pakistani Dramas on YouTube</h2>'; }
				if ($trend === 'trending_tv') { $data .=  '<h2>Trending TOP 10 on TV</h2>'; }
				if ($trend === 'trending_our_recommendation') { $data .=  '<h2>Trending Our Recommendation</h2>'; }
				$data .= '<div class="trending-items">';
					foreach ($items as $item) {
						if (get_post_status($item) === 'publish') {
							$title = get_the_title($item);
							$data .= '<a class="trending-item" href="'.get_permalink($item).'" title="'.$title.'">
					  			<div class="trending-poster">
					  				<img '.(has_post_thumbnail($item) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url($item, 'full').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ).' alt="'.$title.'" width="100%" height="100%"><div class="trending-no">Rank '.++$count.'</div>
					  			</div>
					  			<h3 class="trending-title">'.$title.'</h3>
					  		</a>';
						}
					}
				$data .= '</div>';
			$data .= '</div>';
		}
		return $data;
	}
}


function trending_styles(){
	return '
<style>
	.trending { margin: 20px 0; border-top: 2px solid #000; padding-top: 30px; }
	.trending h2 { margin-bottom: 20px; text-align: center; }
	.trending-items {
		display: flex;
    	column-gap: 1.25%;
	    flex-wrap: wrap;
	    row-gap: 20px;
	}
	.trending-item {
		display: block;
    	width: 19%;
    	text-align: center;
    	box-shadow: 0 4px 8px #0000001a;
	    transition: transform .3s ease-in-out;
	    transform-style: preserve-3d;
	    padding-bottom: 10px;
	    border-radius: 10px;
	    border: 1px solid #ddd;
    	overflow: hidden;
	}
	.trending-item:hover {
		transform: translateY(-5px) translateZ(20px) rotateX(5deg) rotateY(5deg);
	    box-shadow: 0 10px 20px #0003, 0 0 15px #0000001a;
	    border-radius: 10px;
	}
	.trending-poster { position: relative; padding-bottom: 140%; }
	.trending-no {
		position: absolute;
	    top: 10px;
	    left: 10px;
	    background: #000000c4;
	    color: #ffffff;
	    z-index: 2 !important;
	    border-radius: 5px;
	    padding: 5px 10px;
	    font-size: 18px;
	    font-weight: 700;
	}
	.trending-title { text-decoration: unset!important; }
	@media (max-width: 768px) {
		.trending-items { column-gap: 2%; }
		.trending-item { width: 49%; }
	}
</style>';
}