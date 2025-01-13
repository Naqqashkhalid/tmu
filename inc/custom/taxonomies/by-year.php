<?php
add_action( 'init', 'year_register_taxonomy' );
function year_register_taxonomy() {
	$labels = [
		'name'                       => esc_html__( 'Years', 'your-textdomain' ),
		'singular_name'              => esc_html__( 'Year', 'your-textdomain' ),
		'menu_name'                  => esc_html__( 'Years', 'your-textdomain' ),
		'search_items'               => esc_html__( 'Search Years', 'your-textdomain' ),
		'popular_items'              => esc_html__( 'Popular Years', 'your-textdomain' ),
		'all_items'                  => esc_html__( 'All Years', 'your-textdomain' ),
		'parent_item'                => esc_html__( 'Parent Year', 'your-textdomain' ),
		'parent_item_colon'          => esc_html__( 'Parent Year:', 'your-textdomain' ),
		'edit_item'                  => esc_html__( 'Edit Year', 'your-textdomain' ),
		'view_item'                  => esc_html__( 'View Year', 'your-textdomain' ),
		'update_item'                => esc_html__( 'Update Year', 'your-textdomain' ),
		'add_new_item'               => esc_html__( 'Add New Year', 'your-textdomain' ),
		'new_item_name'              => esc_html__( 'New Year Name', 'your-textdomain' ),
		'separate_items_with_commas' => esc_html__( 'Separate years with commas', 'your-textdomain' ),
		'add_or_remove_items'        => esc_html__( 'Add or remove years', 'your-textdomain' ),
		'choose_from_most_used'      => esc_html__( 'Choose most used years', 'your-textdomain' ),
		'not_found'                  => esc_html__( 'No years found.', 'your-textdomain' ),
		'no_terms'                   => esc_html__( 'No years', 'your-textdomain' ),
		'filter_by_item'             => esc_html__( 'Filter by year', 'your-textdomain' ),
		'items_list_navigation'      => esc_html__( 'Years list pagination', 'your-textdomain' ),
		'items_list'                 => esc_html__( 'Years list', 'your-textdomain' ),
		'most_used'                  => esc_html__( 'Most Used', 'your-textdomain' ),
		'back_to_items'              => esc_html__( '&larr; Go to Years', 'your-textdomain' ),
		'text_domain'                => esc_html__( 'your-textdomain', 'your-textdomain' ),
	];
	$args = [
		'label'              => esc_html__( 'Years', 'your-textdomain' ),
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
	register_taxonomy( 'by-year', ['movie', 'tv', 'drama'], $args );
}