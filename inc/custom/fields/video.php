<?php
add_filter( 'rwmb_meta_boxes', 'video' );

function video( $meta_boxes ) {
    $prefix = '';
    global $wpdb;

    $options = [ 'Trailer' => __( 'Trailer', 'video' ), 'Clip'    => __( 'Clip', 'video' ), 'Feature' => __( 'Feature', 'video' ), 'Teaser'  => __( 'Teaser', 'video' ), ];
    
    if (get_option( 'tmu_dramas' ) === 'on') {
        $options['OST'] = __( 'OST', 'video' );
    }

    $meta_boxes[] = [
        'title'        => __( 'Video', 'video' ),
        'id'           => 'video',
        'post_types'   => ['video'],
        'storage_type' => 'custom_table',
        'table'        => $wpdb->prefix.'tmu_videos',
        'fields'       => [
            [
                'name'   => __( 'Video Data', 'video' ),
                'id'     => $prefix . 'video_data',
                'type'   => 'group',
                'fields' => [
                    [
                        'name'    => __( 'Source', 'video' ),
                        'id'      => $prefix . 'source',
                        'type'    => 'text',
                        'columns' => 6,
                    ],
                    [
                        'name'    => __( 'Content Type', 'video' ),
                        'id'      => $prefix . 'content_type',
                        'type'    => 'select',
                        'options' => $options,
                        'columns' => 6,
                    ],
                ],
            ],
            [
                'name'          => __( 'Post ID', 'video' ),
                'id'            => $prefix . 'post_id',
                'type'          => 'post',
                'post_type'     => ['tv', 'movie', 'people', 'drama'],
                'field_type'    => 'select_advanced',
                'admin_columns' => [
                    'position'   => 'after title',
                    'title'      => 'Movie/TV Series/Drama',
                    'sort'       => true,
                    'searchable' => true,
                    'filterable' => true,
                    'link'       => 'edit',
                ],
            ],
        ],
    ];

    return $meta_boxes;
}