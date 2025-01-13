<?php
add_filter( 'rwmb_meta_boxes', 'tv_series_season_meta' );

function tv_series_season_meta( $meta_boxes ) {
    $prefix = '';
    global $wpdb;

    $meta_boxes[] = [
        'title'        => __( 'TV Series Season', 'tv-series-season' ),
        'id'           => 'tv-series-season',
        'post_types'   => ['season'],
        'storage_type' => 'custom_table',
        'table'        => $wpdb->prefix.'tmu_tv_series_seasons',
        'fields'       => [
            [
                'name'    => __( 'Season No', 'tv-series-season' ),
                'id'      => $prefix . 'season_no',
                'type'    => 'number',
                'columns' => 2,
            ],
            [
                'name'    => __( 'Season Name', 'tv-series-season' ),
                'id'      => $prefix . 'season_name',
                'type'    => 'text',
                'columns' => 4,
            ],
            [
                'name'       => __( 'TV Series', 'tv-series-season' ),
                'id'         => $prefix . 'tv_series',
                'type'       => 'post',
                'post_type'  => ['tv'],
                'field_type' => 'select_advanced',
                'required'   => true,
                'columns'    => 3,
            ],
            [
                'name'    => __( 'Air Date', 'tv-series-season' ),
                'id'      => $prefix . 'air_date',
                'type'    => 'date',
                'columns' => 3,
            ]
        ],
    ];

    return $meta_boxes;
}