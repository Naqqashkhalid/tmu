<?php
add_filter( 'rwmb_meta_boxes', 'blog_posts_list2' );

function blog_posts_list2( $meta_boxes ) {
    $prefix = ''; $fields = '';

    if(get_option( 'tmu_dramas' ) === 'on') {
        $fields = [
            [
                'name'       => __( 'Drama', 'blog-posts-list2' ),
                'id'         => $prefix . 'drama',
                'type'       => 'post',
                'post_type'  => ['drama'],
                'field_type' => 'select_advanced',
                'multiple'   => true,
                'columns'    => 12,
            ]
        ];
    } else {
        $fields = [
                    [
                        'name'       => __( 'Movie', 'blog-posts-list2' ),
                        'id'         => $prefix . 'movie',
                        'type'       => 'post',
                        'post_type'  => ['movie'],
                        'field_type' => 'select_advanced',
                        'multiple'   => true,
                        'columns'    => 6,
                    ],
                    [
                        'name'       => __( 'TV Show', 'blog-posts-list2' ),
                        'id'         => $prefix . 'tv_show',
                        'type'       => 'post',
                        'post_type'  => ['tv'],
                        'field_type' => 'select_advanced',
                        'multiple'   => true,
                        'columns'    => 6,
                    ]
                ];
    }

    $fields[] = [ 'name'    => __( 'Content', 'blog-posts-list2' ), 'id'      => $prefix . 'content', 'type'    => 'wysiwyg', 'columns' => 11, ];

    $meta_boxes[] = [
        'title'  => __( 'Post Listing 1', 'blog-posts-list2' ),
        'id'     => 'post-listing-1',
        'fields' => [
            [
                'name'       => __( 'List item', 'blog-posts-list2' ),
                'id'         => $prefix . 'list_item',
                'type'       => 'group',
                'clone'      => true,
                'sort_clone' => true,
                'fields'     => $fields
            ],
        ],
    ];

    return $meta_boxes;
}