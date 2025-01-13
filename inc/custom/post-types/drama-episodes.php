<?php
add_action( 'init', 'drama_episodes' );
function drama_episodes() {
	$labels = [
		'name'                     => esc_html__( 'Episodes', 'drama-episodes' ),
		'singular_name'            => esc_html__( 'Episode', 'drama-episodes' ),
		'add_new'                  => esc_html__( 'Add New Episode', 'drama-episodes' ),
		'add_new_item'             => esc_html__( 'Add New Episode', 'drama-episodes' ),
		'edit_item'                => esc_html__( 'Edit Episode', 'drama-episodes' ),
		'new_item'                 => esc_html__( 'New Episode', 'drama-episodes' ),
		'view_item'                => esc_html__( 'View Episode', 'drama-episodes' ),
		'view_items'               => esc_html__( 'View Episodes', 'drama-episodes' ),
		'search_items'             => esc_html__( 'Search Episodes', 'drama-episodes' ),
		'not_found'                => esc_html__( 'No episodes found.', 'drama-episodes' ),
		'not_found_in_trash'       => esc_html__( 'No episodes found in Trash.', 'drama-episodes' ),
		'parent_item_colon'        => esc_html__( 'Parent Episode:', 'drama-episodes' ),
		'all_items'                => esc_html__( 'All Episodes', 'drama-episodes' ),
		'archives'                 => esc_html__( 'Episode Archives', 'drama-episodes' ),
		'attributes'               => esc_html__( 'Episode Attributes', 'drama-episodes' ),
		'insert_into_item'         => esc_html__( 'Insert into episode', 'drama-episodes' ),
		'uploaded_to_this_item'    => esc_html__( 'Uploaded to this episode', 'drama-episodes' ),
		'featured_image'           => esc_html__( 'Featured image', 'drama-episodes' ),
		'set_featured_image'       => esc_html__( 'Set featured image', 'drama-episodes' ),
		'remove_featured_image'    => esc_html__( 'Remove featured image', 'drama-episodes' ),
		'use_featured_image'       => esc_html__( 'Use as featured image', 'drama-episodes' ),
		'menu_name'                => esc_html__( 'Episodes', 'drama-episodes' ),
		'filter_items_list'        => esc_html__( 'Filter episodes list', 'drama-episodes' ),
		'filter_by_date'           => esc_html__( '', 'drama-episodes' ),
		'items_list_navigation'    => esc_html__( 'Episodes list navigation', 'drama-episodes' ),
		'items_list'               => esc_html__( 'Episodes list', 'drama-episodes' ),
		'item_published'           => esc_html__( 'Episode published.', 'drama-episodes' ),
		'item_published_privately' => esc_html__( 'Episode published privately.', 'drama-episodes' ),
		'item_reverted_to_draft'   => esc_html__( 'Episode reverted to draft.', 'drama-episodes' ),
		'item_scheduled'           => esc_html__( 'Episode scheduled.', 'drama-episodes' ),
		'item_updated'             => esc_html__( 'Episode updated.', 'drama-episodes' ),
		'text_domain'              => esc_html__( 'drama-episodes', 'drama-episodes' ),
	];
	$args = [
		'label'               => esc_html__( 'Episodes', 'drama-episodes' ),
		'labels'              => $labels,
		'description'         => '',
		'public'              => true,
		'hierarchical'        => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'show_ui'             => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'show_in_rest'        => false,
		'query_var'           => true,
		'can_export'          => true,
		'delete_with_user'    => true,
		'has_archive'         => true,
		'rest_base'           => '',
		'show_in_menu'        => false,
		'menu_icon'           => 'dashicons-feedback',
		'capability_type'     => 'post',
		'supports'            => ['title', 'thumbnail', 'comments'],
		'rewrite'             => [
			'with_front' => false,
		],
	];

	if(get_option( 'tmu_dramas' ) === 'on') register_post_type( 'drama-episode', $args );
}

function add_episode_submenu() {
    if(get_option( 'tmu_dramas' ) === 'on') {
    	add_submenu_page( 'edit.php?post_type=drama', 'All Episodes', 'All Episodes', 'manage_options', 'edit.php?post_type=drama-episode' );
    	add_submenu_page( 'edit.php?post_type=drama', 'Add New Episode', 'Add New Episode', 'manage_options', 'post-new.php?post_type=drama-episode', null, 2 );
    }
}
add_action( 'admin_menu', 'add_episode_submenu' );

