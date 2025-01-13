<?php
add_action( 'init', 'language_register_taxonomy' );
function language_register_taxonomy() {
	$labels = [
		'name'                       => esc_html__( 'Languages', 'your-textdomain' ),
		'singular_name'              => esc_html__( 'Language', 'your-textdomain' ),
		'menu_name'                  => esc_html__( 'Languages', 'your-textdomain' ),
		'search_items'               => esc_html__( 'Search Languages', 'your-textdomain' ),
		'popular_items'              => esc_html__( 'Popular Languages', 'your-textdomain' ),
		'all_items'                  => esc_html__( 'All Languages', 'your-textdomain' ),
		'parent_item'                => esc_html__( 'Parent Language', 'your-textdomain' ),
		'parent_item_colon'          => esc_html__( 'Parent Language:', 'your-textdomain' ),
		'edit_item'                  => esc_html__( 'Edit Language', 'your-textdomain' ),
		'view_item'                  => esc_html__( 'View Language', 'your-textdomain' ),
		'update_item'                => esc_html__( 'Update Language', 'your-textdomain' ),
		'add_new_item'               => esc_html__( 'Add New Language', 'your-textdomain' ),
		'new_item_name'              => esc_html__( 'New Language Name', 'your-textdomain' ),
		'separate_items_with_commas' => esc_html__( 'Separate languages with commas', 'your-textdomain' ),
		'add_or_remove_items'        => esc_html__( 'Add or remove languages', 'your-textdomain' ),
		'choose_from_most_used'      => esc_html__( 'Choose most used languages', 'your-textdomain' ),
		'not_found'                  => esc_html__( 'No languages found.', 'your-textdomain' ),
		'no_terms'                   => esc_html__( 'No languages', 'your-textdomain' ),
		'filter_by_item'             => esc_html__( 'Filter by language', 'your-textdomain' ),
		'items_list_navigation'      => esc_html__( 'Languages list pagination', 'your-textdomain' ),
		'items_list'                 => esc_html__( 'Languages list', 'your-textdomain' ),
		'most_used'                  => esc_html__( 'Most Used', 'your-textdomain' ),
		'back_to_items'              => esc_html__( '&larr; Go to Languages', 'your-textdomain' ),
		'text_domain'                => esc_html__( 'your-textdomain', 'your-textdomain' ),
	];
	$args = [
		'label'              => esc_html__( 'Languages', 'your-textdomain' ),
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
	register_taxonomy( 'language', ['tv', 'movie', 'drama'], $args );
}