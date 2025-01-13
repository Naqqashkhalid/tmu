<?php

if (get_option( 'tmu_dramas' ) === 'on') {
    add_filter( 'mb_settings_pages', 'trending_dramas_page' );
}

function trending_dramas_page( $settings_pages ) {
    $settings_pages[] = [
        'menu_title' => __( 'Trending Dramas', 'trending-dramas-page' ),
        'id'         => 'trending-dramas',
        'position'   => 25,
        'parent'     => 'tmu-settings',
        'columns'    => 1,
        'icon_url'   => 'dashicons-admin-generic',
    ];

    return $settings_pages;
}