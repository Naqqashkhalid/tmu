<?php
add_action( 'init', 'network_register_taxonomy' );
function network_register_taxonomy() {
	$labels = [
		'name'                       => esc_html__( 'Networks', 'network_tax' ),
		'singular_name'              => esc_html__( 'Network', 'network_tax' ),
		'menu_name'                  => esc_html__( 'Networks', 'network_tax' ),
		'search_items'               => esc_html__( 'Search Networks', 'network_tax' ),
		'popular_items'              => esc_html__( 'Popular Networks', 'network_tax' ),
		'all_items'                  => esc_html__( 'All Networks', 'network_tax' ),
		'parent_item'                => esc_html__( 'Parent Network', 'network_tax' ),
		'parent_item_colon'          => esc_html__( 'Parent Network:', 'network_tax' ),
		'edit_item'                  => esc_html__( 'Edit Network', 'network_tax' ),
		'view_item'                  => esc_html__( 'View Network', 'network_tax' ),
		'update_item'                => esc_html__( 'Update Network', 'network_tax' ),
		'add_new_item'               => esc_html__( 'Add New Network', 'network_tax' ),
		'new_item_name'              => esc_html__( 'New Network Name', 'network_tax' ),
		'separate_items_with_commas' => esc_html__( 'Separate Networks with commas', 'network_tax' ),
		'add_or_remove_items'        => esc_html__( 'Add or remove Networks', 'network_tax' ),
		'choose_from_most_used'      => esc_html__( 'Choose most used Networks', 'network_tax' ),
		'not_found'                  => esc_html__( 'No Networks found.', 'network_tax' ),
		'no_terms'                   => esc_html__( 'No Networks', 'network_tax' ),
		'filter_by_item'             => esc_html__( 'Filter by Network', 'network_tax' ),
		'items_list_navigation'      => esc_html__( 'Networks list pagination', 'network_tax' ),
		'items_list'                 => esc_html__( 'Networks list', 'network_tax' ),
		'most_used'                  => esc_html__( 'Most Used', 'network_tax' ),
		'back_to_items'              => esc_html__( '&larr; Go to Networks', 'network_tax' ),
		'text_domain'                => esc_html__( 'network_tax', 'network_tax' ),
	];
	$args = [
		'label'              => esc_html__( 'Networks', 'network_tax' ),
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
	
	if(get_option( 'tmu_tv_series' ) === 'on') register_taxonomy( 'network', ['tv'], $args );
}