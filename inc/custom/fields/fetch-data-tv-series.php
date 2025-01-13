<?php
add_filter( 'rwmb_meta_boxes', 'fetch_data_tv_series' );

function fetch_data_tv_series( $meta_boxes ) {
    $prefix = '';

    $meta_boxes[] = [
        'title'      => __( 'Fetch Data Only TV Series', 'fetch-data-tv-series' ),
        'id'         => 'fetch-data-only-2',
        'post_types' => ['tv'],
        'fields'     => [
            [
                'name'       => __( 'Images', 'fetch-data-tv-series' ),
                'id'         => $prefix . 'get_images',
                'type'       => 'checkbox',
                'columns'    => 2,
                'save_field' => false,
            ],
            [
                'name'       => __( 'Videos', 'fetch-data-tv-series' ),
                'id'         => $prefix . 'get_videos',
                'type'       => 'checkbox',
                'columns'    => 2,
                'save_field' => false,
            ],
            [
                'name'       => __( 'Credits', 'fetch-data-tv-series' ),
                'id'         => $prefix . 'get_credits',
                'type'       => 'checkbox',
                'columns'    => 2,
                'save_field' => false,
            ],
            [
                'name'       => __( 'All Seasons', 'fetch-data-tv-series' ),
                'id'         => $prefix . 'get_seasons',
                'type'       => 'checkbox',
                'columns'    => 2,
                'save_field' => false,
            ],
            [
                'name'        => __( 'Season No', 'fetch-data-tv-series' ),
                'id'          => $prefix . 'get_season_no',
                'type'        => 'number',
                'placeholder' => __( 'Enter Season No.', 'fetch-data-tv-series' ),
                'size'        => 100,
                'columns'     => 2,
                'save_field'  => false,
            ],
        ],
    ];

    return $meta_boxes;
}