<?php
add_action( 'init', 'tvshow_episodes' );
function tvshow_episodes() {
	$labels = [
		'name'                     => esc_html__( 'Episodes', 'tvshow-episodes' ),
		'singular_name'            => esc_html__( 'Episode', 'tvshow-episodes' ),
		'add_new'                  => esc_html__( 'Add New Episode', 'tvshow-episodes' ),
		'add_new_item'             => esc_html__( 'Add New Episode', 'tvshow-episodes' ),
		'edit_item'                => esc_html__( 'Edit Episode', 'tvshow-episodes' ),
		'new_item'                 => esc_html__( 'New Episode', 'tvshow-episodes' ),
		'view_item'                => esc_html__( 'View Episode', 'tvshow-episodes' ),
		'view_items'               => esc_html__( 'View Episodes', 'tvshow-episodes' ),
		'search_items'             => esc_html__( 'Search Episodes', 'tvshow-episodes' ),
		'not_found'                => esc_html__( 'No episodes found.', 'tvshow-episodes' ),
		'not_found_in_trash'       => esc_html__( 'No episodes found in Trash.', 'tvshow-episodes' ),
		'parent_item_colon'        => esc_html__( 'Parent Episode:', 'tvshow-episodes' ),
		'all_items'                => esc_html__( 'All Episodes', 'tvshow-episodes' ),
		'archives'                 => esc_html__( 'Episode Archives', 'tvshow-episodes' ),
		'attributes'               => esc_html__( 'Episode Attributes', 'tvshow-episodes' ),
		'insert_into_item'         => esc_html__( 'Insert into episode', 'tvshow-episodes' ),
		'uploaded_to_this_item'    => esc_html__( 'Uploaded to this episode', 'tvshow-episodes' ),
		'featured_image'           => esc_html__( 'Featured image', 'tvshow-episodes' ),
		'set_featured_image'       => esc_html__( 'Set featured image', 'tvshow-episodes' ),
		'remove_featured_image'    => esc_html__( 'Remove featured image', 'tvshow-episodes' ),
		'use_featured_image'       => esc_html__( 'Use as featured image', 'tvshow-episodes' ),
		'menu_name'                => esc_html__( 'Episodes', 'tvshow-episodes' ),
		'filter_items_list'        => esc_html__( 'Filter episodes list', 'tvshow-episodes' ),
		'filter_by_date'           => esc_html__( '', 'tvshow-episodes' ),
		'items_list_navigation'    => esc_html__( 'Episodes list navigation', 'tvshow-episodes' ),
		'items_list'               => esc_html__( 'Episodes list', 'tvshow-episodes' ),
		'item_published'           => esc_html__( 'Episode published.', 'tvshow-episodes' ),
		'item_published_privately' => esc_html__( 'Episode published privately.', 'tvshow-episodes' ),
		'item_reverted_to_draft'   => esc_html__( 'Episode reverted to draft.', 'tvshow-episodes' ),
		'item_scheduled'           => esc_html__( 'Episode scheduled.', 'tvshow-episodes' ),
		'item_updated'             => esc_html__( 'Episode updated.', 'tvshow-episodes' ),
		'text_domain'              => esc_html__( 'tvshow-episodes', 'tvshow-episodes' ),
	];
	$args = [
		'label'               => esc_html__( 'Episodes', 'tvshow-episodes' ),
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
		'show_in_menu'        => 'edit.php?post_type=tv',
		'menu_icon'           => 'dashicons-feedback',
		'capability_type'     => 'post',
		'supports'            => ['title', 'thumbnail', 'comments'],
		'taxonomies'          => ['season'],
		'rewrite'             => [
			'with_front' => false,
		],
	];

	if(get_option( 'tmu_tv_series' ) === 'on') register_post_type( 'episode', $args );
}


// add_action( 'init', function () {
// 	global $wp_rewrite;
//     add_rewrite_tag( 'seasons', '([^/]+)' );  // Use a generic term placeholder
//     add_filter( 'post_type_link', function ($link, $post) {
//         if (strpos($link, 'seasons')) {
// 		    $tv_post_id = rwmb_meta( 'series_name', '', $post->ID );
// 			$season_no = rwmb_meta( 'season_no', '', $post->ID );
// 		    $new_permalink = get_permalink($tv_post_id).'season'.$season_no.'/';
//             $link = str_replace('seasons', 'test-season', $link);
//         }
//         return $link;

//     var_dump('hello');
//     }, 10, 2);

// } );



