<?php
add_action( 'init', 'channel_register_taxonomy' );
function channel_register_taxonomy() {
	$labels = [
		'name'                       => esc_html__( 'Channels', 'channel_tax' ),
		'singular_name'              => esc_html__( 'Channel', 'channel_tax' ),
		'menu_name'                  => esc_html__( 'Channels', 'channel_tax' ),
		'search_items'               => esc_html__( 'Search Channels', 'channel_tax' ),
		'popular_items'              => esc_html__( 'Popular Channels', 'channel_tax' ),
		'all_items'                  => esc_html__( 'All Channels', 'channel_tax' ),
		'parent_item'                => esc_html__( 'Parent Channel', 'channel_tax' ),
		'parent_item_colon'          => esc_html__( 'Parent Channel:', 'channel_tax' ),
		'edit_item'                  => esc_html__( 'Edit Channel', 'channel_tax' ),
		'view_item'                  => esc_html__( 'View Channel', 'channel_tax' ),
		'update_item'                => esc_html__( 'Update Channel', 'channel_tax' ),
		'add_new_item'               => esc_html__( 'Add New Channel', 'channel_tax' ),
		'new_item_name'              => esc_html__( 'New Channel Name', 'channel_tax' ),
		'separate_items_with_commas' => esc_html__( 'Separate Channels with commas', 'channel_tax' ),
		'add_or_remove_items'        => esc_html__( 'Add or remove Channels', 'channel_tax' ),
		'choose_from_most_used'      => esc_html__( 'Choose most used Channels', 'channel_tax' ),
		'not_found'                  => esc_html__( 'No Channels found.', 'channel_tax' ),
		'no_terms'                   => esc_html__( 'No Channels', 'channel_tax' ),
		'filter_by_item'             => esc_html__( 'Filter by Channel', 'channel_tax' ),
		'items_list_navigation'      => esc_html__( 'Channels list pagination', 'channel_tax' ),
		'items_list'                 => esc_html__( 'Channels list', 'channel_tax' ),
		'most_used'                  => esc_html__( 'Most Used', 'channel_tax' ),
		'back_to_items'              => esc_html__( '&larr; Go to Channels', 'channel_tax' ),
		'text_domain'                => esc_html__( 'channel_tax', 'channel_tax' ),
	];
	$args = [
		'label'              => esc_html__( 'Channels', 'channel_tax' ),
		'labels'             => $labels,
		'description'        => '',
		'public'             => true,
		'publicly_queryable' => true,
		'hierarchical'       => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_nav_menus'  => true,
		'show_in_rest'       => true,
		'show_tagcloud'      => true,
		'show_in_quick_edit' => true,
		'show_admin_column'  => false,
		'query_var'          => true,
		'sort'               => false,
		'meta_box_cb'        => 'post_tags_meta_box',
		'rest_base'          => '',
		'rewrite'            => [
			'with_front'   => false,
			'hierarchical' => false,
		],
	];
	if(get_option( 'tmu_dramas' ) === 'on') register_taxonomy( 'channel', ['drama'], $args );
}