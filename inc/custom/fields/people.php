<?php
add_filter( 'rwmb_meta_boxes', 'people' );

function people( $meta_boxes ) {
    $prefix = '';
    global $wpdb;

    $meta_boxes[] = [
        'title'        => __( 'People', 'people' ),
        'id'           => 'people',
        'post_types'   => ['people'],
        'storage_type' => 'custom_table',
        'table'        => $wpdb->prefix.'tmu_people',
        'fields'       => [
            [
                'name'        => __( 'Known For', 'people' ),
                'id'          => $prefix . 'known_for',
                'type'        => 'post',
                'post_type'   => ['tv', 'movie', 'drama'],
                'multiple'    => true,
                'placeholder' => __( 'Known For', 'people' ),
            ],
            [
                'name'    => __( 'TMDB ID', 'people' ),
                'id'      => $prefix . 'tmdb_id',
                'type'    => 'text',
                'columns' => 2,
            ],
            [
                'name'    => __( 'Gender', 'people' ),
                'id'      => $prefix . 'gender',
                'type'    => 'select',
                'options' => [
                    'Male'          => __( 'Male', 'people' ),
                    'Female'        => __( 'Female', 'people' ),
                    'Not Specified' => __( 'Not Specified', 'people' ),
                ],
                'columns' => 2,
            ],
            [
                'name'    => __( 'Nick Name', 'people' ),
                'id'      => $prefix . 'nick_name',
                'type'    => 'text',
                'columns' => 4,
            ],
            [
                'name'    => __( 'Marital Status', 'people' ),
                'id'      => $prefix . 'marital_status',
                'type'    => 'select',
                'options' => [
                    'Single'            => __( 'Single', 'people' ),
                    'Married'           => __( 'Married', 'people' ),
                    'Divorced'          => __( 'Divorced', 'people' ),
                    'Widowed'           => __( 'Widowed', 'people' ),
                    'Separated'         => __( 'Separated', 'people' ),
                    'In a Relationship' => __( 'In a Relationship', 'people' ),
                    'Committed'         => __( 'Committed', 'people' ),
                    'It\'s Complicated' => __( 'It\'s Complicated', 'people' ),
                ],
                'columns' => 2,
            ],
            [
                'name'          => __( 'Profession', 'people' ),
                'id'            => $prefix . 'profession',
                'type'          => 'text',
                'admin_columns' => [
                    'position' => 'after title',
                    'sort'     => true,
                ],
                'columns'       => 2,
            ],
            [
                'name'    => __( 'Date Of Birth', 'people' ),
                'id'      => $prefix . 'date_of_birth',
                'type'    => 'date',
                'columns' => 4,
            ],
            [
                'name'    => __( 'Birthplace', 'people' ),
                'id'      => $prefix . 'birthplace',
                'type'    => 'text',
                'columns' => 4,
            ],
            [
                'name'    => __( 'Dead On', 'people' ),
                'id'      => $prefix . 'dead_on',
                'type'    => 'date',
                'columns' => 4,
            ],
            [
                'name'   => __( 'basic', 'people' ),
                'id'     => $prefix . 'basic',
                'type'   => 'group',
                'fields' => [
                    [
                        'name'    => __( 'Height', 'people' ),
                        'id'      => $prefix . 'height',
                        'type'    => 'text',
                        'columns' => 6,
                    ],
                    [
                        'name'    => __( 'Weight', 'people' ),
                        'id'      => $prefix . 'weight',
                        'type'    => 'text',
                        'columns' => 6,
                    ],
                    [
                        'name'   => __( 'Parents', 'people' ),
                        'id'     => $prefix . 'parents',
                        'type'   => 'group',
                        'fields' => [
                            [
                                'name'    => __( 'Father', 'people' ),
                                'id'      => $prefix . 'father',
                                'type'    => 'text',
                                'columns' => 6,
                            ],
                            [
                                'name'    => __( 'Mother', 'people' ),
                                'id'      => $prefix . 'mother',
                                'type'    => 'text',
                                'columns' => 6,
                            ],
                        ],
                    ],
                    [
                        'name'      => __( 'Spouse', 'people' ),
                        'id'        => $prefix . 'spouse',
                        'type'      => 'post',
                        'post_type' => ['people'],
                        'add_new'   => true,
                        'visible'   => [
                            'when'     => [['marital_status', '=', 'Married']],
                            'relation' => 'or',
                        ],
                    ],
                    [
                        'name' => __( 'Siblings', 'people' ),
                        'id'   => $prefix . 'siblings',
                        'type' => 'text',
                        'columns'     => 6,
                    ],
                    [
                        'name'    => __( 'Popularity', 'people' ),
                        'id'      => $prefix . 'popularity',
                        'type'    => 'text',
                        'placeholder' => __( 'Popularity ...', 'people' ),
                        'columns'     => 6,
                    ],
                ],
            ],
            [
                'name'       => __( 'Social Media Account', 'people' ),
                'id'         => $prefix . 'social_media_account',
                'type'       => 'group',
                'clone'      => true,
                'sort_clone' => true,
                'max_clone'  => 4,
                'fields'     => [
                    [
                        'name'        => __( 'Platform', 'people' ),
                        'id'          => $prefix . 'platform',
                        'type'        => 'select',
                        'options'     => [
                            'Facebook'  => __( 'Facebook', 'people' ),
                            'Instagram' => __( 'Instagram', 'people' ),
                            'YouTube'   => __( 'YouTube', 'people' ),
                            'X'         => __( 'X', 'people' ),
                        ],
                        'placeholder' => __( 'Select', 'people' ),
                        'columns'     => 4,
                    ],
                    [
                        'name'    => __( 'Url', 'people' ),
                        'id'      => $prefix . 'url',
                        'type'    => 'url',
                        'columns' => 8,
                    ],
                ],
            ],
            [
                'name' => __( 'Net worth', 'people' ),
                'id'   => $prefix . 'net_worth',
                'type' => 'text',
            ],
            [
                'name'       => __( 'Videos', 'people' ),
                'id'         => $prefix . 'videos',
                'type'       => 'post',
                'post_type'  => ['video'],
                'field_type' => 'select_advanced',
                'add_new'    => true,
                'multiple'   => true,
            ],
            [
                'name'       => __( 'Photos', 'people' ),
                'id'         => $prefix . 'photos',
                'type'       => 'image_advanced',
                'max_status' => false,
            ],
            [
                'name'            => __( 'Name', 'people' ),
                'id'              => $prefix . 'name',
                'type'            => 'text',
                'columns'         => 1,
                'hide_from_rest'  => true,
                'hide_from_front' => true,
                'visible'         => [
                    'when'     => [['profession', '=', '9.9999999999e+22']],
                    'relation' => 'or',
                ],
            ],
        ],
    ];

    return $meta_boxes;
}