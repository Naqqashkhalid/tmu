<?php

function get_default_title($post_id=''){
	return is_tax() ? get_queried_object()->name : ($post_id ? get_the_title($post_id) : get_the_title());
}


function remove_title_tag() { remove_action( 'wp_head', '_wp_render_title_tag', 1 ); }
add_action( 'init', 'remove_title_tag' );