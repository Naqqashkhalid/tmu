<?php
add_filter( 'rwmb_meta_boxes', 'fetch_data_movie' );

function fetch_data_movie( $meta_boxes ) {
    $prefix = '';

    $meta_boxes[] = [
        'title'      => __( 'Fetch Data Only Movie', 'fetch-data-movie' ),
        'id'         => 'fetch-data-only',
        'post_types' => ['movie'],
        'fields'     => [
            [
                'name'       => __( 'Images', 'fetch-data-movie' ),
                'id'         => $prefix . 'get_images',
                'type'       => 'checkbox',
                'columns'    => 2,
                'save_field' => false,
            ],
            [
                'name'       => __( 'Videos', 'fetch-data-movie' ),
                'id'         => $prefix . 'get_videos',
                'type'       => 'checkbox',
                'columns'    => 2,
                'save_field' => false,
            ],
            [
                'name'       => __( 'Credits', 'fetch-data-movie' ),
                'id'         => $prefix . 'get_credits',
                'type'       => 'checkbox',
                'columns'    => 2,
                'save_field' => false,
            ],
        ],
    ];

    return $meta_boxes;
}