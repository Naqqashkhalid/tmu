<?php
add_action( 'init', 'country_register_taxonomy' );
function country_register_taxonomy() {
	$labels = [
		'name'                       => esc_html__( 'Countries', 'your-textdomain' ),
		'singular_name'              => esc_html__( 'Country', 'your-textdomain' ),
		'menu_name'                  => esc_html__( 'Countries', 'your-textdomain' ),
		'search_items'               => esc_html__( 'Search Countries', 'your-textdomain' ),
		'popular_items'              => esc_html__( 'Popular Countries', 'your-textdomain' ),
		'all_items'                  => esc_html__( 'All Countries', 'your-textdomain' ),
		'parent_item'                => esc_html__( 'Parent Country', 'your-textdomain' ),
		'parent_item_colon'          => esc_html__( 'Parent Country:', 'your-textdomain' ),
		'edit_item'                  => esc_html__( 'Edit Country', 'your-textdomain' ),
		'view_item'                  => esc_html__( 'View Country', 'your-textdomain' ),
		'update_item'                => esc_html__( 'Update Country', 'your-textdomain' ),
		'add_new_item'               => esc_html__( 'Add New Country', 'your-textdomain' ),
		'new_item_name'              => esc_html__( 'New Country Name', 'your-textdomain' ),
		'separate_items_with_commas' => esc_html__( 'Separate countries with commas', 'your-textdomain' ),
		'add_or_remove_items'        => esc_html__( 'Add or remove countries', 'your-textdomain' ),
		'choose_from_most_used'      => esc_html__( 'Choose most used countries', 'your-textdomain' ),
		'not_found'                  => esc_html__( 'No countries found.', 'your-textdomain' ),
		'no_terms'                   => esc_html__( 'No countries', 'your-textdomain' ),
		'filter_by_item'             => esc_html__( 'Filter by country', 'your-textdomain' ),
		'items_list_navigation'      => esc_html__( 'Countries list pagination', 'your-textdomain' ),
		'items_list'                 => esc_html__( 'Countries list', 'your-textdomain' ),
		'most_used'                  => esc_html__( 'Most Used', 'your-textdomain' ),
		'back_to_items'              => esc_html__( '&larr; Go to Countries', 'your-textdomain' ),
		'text_domain'                => esc_html__( 'your-textdomain', 'your-textdomain' ),
	];
	$args = [
		'label'              => esc_html__( 'Countries', 'your-textdomain' ),
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
	register_taxonomy( 'country', ['tv', 'movie', 'drama'], $args );
}