<?php

add_action( 'init', 'flush_rewrite_rules' );
add_action( 'init', 'tv_show_seasons' );

function tv_show_seasons() {
	$labels = [
		'name'                       => esc_html__( 'Seasons', 'tvshow-seasons' ),
		'singular_name'              => esc_html__( 'Season', 'tvshow-seasons' ),
		'menu_name'                  => esc_html__( 'TV Show Seasons', 'tvshow-seasons' ),
		'search_items'               => esc_html__( 'Search TV Show Seasons', 'tvshow-seasons' ),
		'popular_items'              => esc_html__( 'Popular TV Show Seasons', 'tvshow-seasons' ),
		'all_items'                  => esc_html__( 'All TV Show Seasons', 'tvshow-seasons' ),
		'parent_item'                => esc_html__( 'Parent TV Show Season', 'tvshow-seasons' ),
		'parent_item_colon'          => esc_html__( 'Parent Season:', 'tvshow-seasons' ),
		'edit_item'                  => esc_html__( 'Edit TV Show Season', 'tvshow-seasons' ),
		'view_item'                  => esc_html__( 'View TV Show Season', 'tvshow-seasons' ),
		'update_item'                => esc_html__( 'Update TV Show Season', 'tvshow-seasons' ),
		'add_new_item'               => esc_html__( 'Add New TV Show Season', 'tvshow-seasons' ),
		'new_item_name'              => esc_html__( 'New Season TV Show Name', 'tvshow-seasons' ),
		'separate_items_with_commas' => esc_html__( 'Separate TV Show seasons with commas', 'tvshow-seasons' ),
		'add_or_remove_items'        => esc_html__( 'Add or remove TV Show seasons', 'tvshow-seasons' ),
		'choose_from_most_used'      => esc_html__( 'Choose most used TV Show seasons', 'tvshow-seasons' ),
		'not_found'                  => esc_html__( 'No TV Show seasons found.', 'tvshow-seasons' ),
		'no_terms'                   => esc_html__( 'No TV Show seasons', 'tvshow-seasons' ),
		'filter_by_item'             => esc_html__( 'Filter by TV Show season', 'tvshow-seasons' ),
		'items_list_navigation'      => esc_html__( 'TV Show Seasons list pagination', 'tvshow-seasons' ),
		'items_list'                 => esc_html__( 'TV Show Seasons list', 'tvshow-seasons' ),
		'most_used'                  => esc_html__( 'Most Used', 'tvshow-seasons' ),
		'back_to_items'              => esc_html__( '&larr; Go to TV Show Seasons', 'tvshow-seasons' ),
		'text_domain'                => esc_html__( 'tvshow-seasons', 'tvshow-seasons' ),
	];
	$args = [
		'label'              => esc_html__( 'Seasons', 'tvshow-seasons' ),
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
		'show_admin_column'  => true,
		'query_var'          => true,
		'sort'               => true,
		'meta_box_cb'        => 'post_tags_meta_box',
		'rest_base'          => '',
		'rewrite'            => [
			'with_front'   => false,
			'hierarchical' => false,
		],
	];
	// if(get_option( 'tmu_tv_series' ) === 'on') register_taxonomy( 'season', ['episode'], $args );
}

