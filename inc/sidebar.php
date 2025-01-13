<?php

// sidebar
function term_sidebar(){
	$recent_posts = wp_get_recent_posts(array( 'numberposts' => 35, 'post_status' => 'publish' ));
	$data = '<div class="sidebar-container">';
		$data .= '<div class="term-sidebar">';
			$data .= '<div class="sidebar-title">Latest News</div>';
			$data .= '<ul id="slider-id" class="slider-class">';
			foreach( $recent_posts as $post_item ) :
				$data .= '<li>';
					$data .= '<a href="'.get_permalink($post_item['ID']).'" title="'.$post_item['post_title'].'">';
						// $data .= '<img src="" alt="">';
						$data .= '<p class="slider-caption-class">'.$post_item['post_title'].'</p>';
					$data .= '</a>';
				$data .= '</li>';
			endforeach;
			$data .= '</ul>';
		$data .= '</div>';
	$data .= '</div>';
	return $data;
}
