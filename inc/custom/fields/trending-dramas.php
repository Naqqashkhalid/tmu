<?php
if (get_option( 'tmu_dramas' ) === 'on') { add_filter( 'rwmb_meta_boxes', 'trending_dramas' ); }

function trending_dramas( $meta_boxes ) {
    $prefix = '';

    $meta_boxes[] = [
        'title'          => __( 'Trending', 'trending-dramas' ),
        'id'             => 'trending',
        'settings_pages' => ['trending-dramas'],
        'fields'         => [
            [
                'name'        => __( 'Trending Youtube', 'trending-dramas' ),
                'id'          => $prefix . 'trending_youtube',
                'type'        => 'post',
                'post_type'   => ['drama'],
                'field_type'  => 'select_advanced',
                'multiple'    => true,
                'placeholder' => __( 'Select Top 10 Trending Dramas on YouTube', 'trending-dramas' ),
            ],
            [
                'name'        => __( 'Trending TV', 'trending-dramas' ),
                'id'          => $prefix . 'trending_tv',
                'type'        => 'post',
                'post_type'   => ['drama'],
                'field_type'  => 'select_advanced',
                'multiple'    => true,
                'placeholder' => __( 'Select Top 10 Trending Dramas on TV', 'trending-dramas' ),
            ],
            [
                'name'        => __( 'Trending Our Recommendation', 'trending-dramas' ),
                'id'          => $prefix . 'trending_our_recommendation',
                'type'        => 'post',
                'post_type'   => ['drama'],
                'field_type'  => 'select_advanced',
                'multiple'    => true,
                'placeholder' => __( 'Select Top 10 Trending Dramas on TV', 'trending-dramas' ),
            ],
        ],
    ];

    return $meta_boxes;
}