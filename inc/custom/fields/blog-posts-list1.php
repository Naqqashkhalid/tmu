<?php
add_filter( 'rwmb_meta_boxes', 'blog_posts_list1' );

function blog_posts_list1( $meta_boxes ) {
    $prefix = ''; $fields = '';

    if(get_option( 'tmu_dramas' ) === 'on') {
        $fields = [
            [
                'name'       => __( 'Dramas', 'blog-posts-list1' ),
                'id'         => $prefix . 'dramas',
                'type'       => 'post',
                'post_type'  => ['drama'],
                'field_type' => 'select_advanced',
                'multiple'   => true,
            ]
        ];
    } else {
        $fields = [
            [
                'name'       => __( 'Movies', 'blog-posts-list1' ),
                'id'         => $prefix . 'movies',
                'type'       => 'post',
                'post_type'  => ['movie'],
                'field_type' => 'select_advanced',
                'multiple'   => true,
            ],
            [
                'name'       => __( 'TV Show', 'blog-posts-list1' ),
                'id'         => $prefix . 'tv_show',
                'type'       => 'post',
                'post_type'  => ['tv'],
                'field_type' => 'select_advanced',
                'multiple'   => true,
            ],
        ];
    }

    $meta_boxes[] = [
        'title'  => __( 'Post Listing 2', 'blog-posts-list1' ),
        'id'     => 'post-listing-2',
        'fields' => $fields
    ];

    return $meta_boxes;
}