<?php
add_filter( 'rwmb_meta_boxes', 'fetch_data_drama' );

function fetch_data_drama( $meta_boxes ) {
    $prefix = '';

    $meta_boxes[] = [
        'title'      => __( 'Fetch Data Only Drama', 'fetch-data-drama' ),
        'id'         => 'fetch-data-only',
        'post_types' => ['drama'],
        'fields'     => [
            [
                'name'       => __( 'Images', 'fetch-data-drama' ),
                'id'         => $prefix . 'get_images',
                'type'       => 'checkbox',
                'columns'    => 2,
                'save_field' => false,
            ],
            [
                'name'       => __( 'Videos', 'fetch-data-drama' ),
                'id'         => $prefix . 'get_videos',
                'type'       => 'checkbox',
                'columns'    => 2,
                'save_field' => false,
            ],
            [
                'name'       => __( 'Credits', 'fetch-data-drama' ),
                'id'         => $prefix . 'get_credits',
                'type'       => 'checkbox',
                'columns'    => 2,
                'save_field' => false,
            ],
            [
                'name'       => __( 'Episodes', 'fetch-data-drama' ),
                'id'         => $prefix . 'get_episodes',
                'type'       => 'checkbox',
                'columns'    => 2,
                'save_field' => false,
            ],
        ],
    ];

    return $meta_boxes;
}