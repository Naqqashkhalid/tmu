<?php
add_filter( 'rwmb_meta_boxes', 'channel_image' );

function channel_image( $meta_boxes ) {
    $prefix = '';

    $meta_boxes[] = [
        'title'  => __( 'channel image', 'channel-image' ),
        'taxonomies' => ['channel', 'network', 'nationality', 'language', 'keyword', 'by-year', 'genre', 'country'],
        'fields' => [
            [
                'name' => __( 'logo', 'channel-image' ),
                'id'   => $prefix . 'logo',
                'type' => 'image_advanced',
            ],
        ],
    ];

    return $meta_boxes;
}