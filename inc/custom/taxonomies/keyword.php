<?php
add_action( 'init', 'keyword_register_taxonomy' );
function keyword_register_taxonomy() {
	$labels = [
		'name'                       => esc_html__( 'Keywords', 'your-textdomain' ),
		'singular_name'              => esc_html__( 'Keyword', 'your-textdomain' ),
		'menu_name'                  => esc_html__( 'Keywords', 'your-textdomain' ),
		'search_items'               => esc_html__( 'Search Keywords', 'your-textdomain' ),
		'popular_items'              => esc_html__( 'Popular Keywords', 'your-textdomain' ),
		'all_items'                  => esc_html__( 'All Keywords', 'your-textdomain' ),
		'parent_item'                => esc_html__( 'Parent Keyword', 'your-textdomain' ),
		'parent_item_colon'          => esc_html__( 'Parent Keyword:', 'your-textdomain' ),
		'edit_item'                  => esc_html__( 'Edit Keyword', 'your-textdomain' ),
		'view_item'                  => esc_html__( 'View Keyword', 'your-textdomain' ),
		'update_item'                => esc_html__( 'Update Keyword', 'your-textdomain' ),
		'add_new_item'               => esc_html__( 'Add New Keyword', 'your-textdomain' ),
		'new_item_name'              => esc_html__( 'New Keyword Name', 'your-textdomain' ),
		'separate_items_with_commas' => esc_html__( 'Separate keywords with commas', 'your-textdomain' ),
		'add_or_remove_items'        => esc_html__( 'Add or remove keywords', 'your-textdomain' ),
		'choose_from_most_used'      => esc_html__( 'Choose most used keywords', 'your-textdomain' ),
		'not_found'                  => esc_html__( 'No keywords found.', 'your-textdomain' ),
		'no_terms'                   => esc_html__( 'No keywords', 'your-textdomain' ),
		'filter_by_item'             => esc_html__( 'Filter by keyword', 'your-textdomain' ),
		'items_list_navigation'      => esc_html__( 'Keywords list pagination', 'your-textdomain' ),
		'items_list'                 => esc_html__( 'Keywords list', 'your-textdomain' ),
		'most_used'                  => esc_html__( 'Most Used', 'your-textdomain' ),
		'back_to_items'              => esc_html__( '&larr; Go to Keywords', 'your-textdomain' ),
		'text_domain'                => esc_html__( 'your-textdomain', 'your-textdomain' ),
	];
	$args = [
		'label'              => esc_html__( 'Keywords', 'your-textdomain' ),
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
	if(get_option( 'tmu_dramas' ) !== 'on') register_taxonomy( 'keyword', ['tv', 'movie'], $args );
}