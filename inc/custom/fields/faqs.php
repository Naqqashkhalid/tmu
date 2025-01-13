<?php

add_filter( 'rwmb_meta_boxes', 'faqs' );

function faqs( $meta_boxes ) {
    $prefix = '';

    $meta_boxes[] = [
        'title'      => __( 'FAQs', 'faqs' ),
        'id'         => 'faqs',
        'taxonomies' => ['channel', 'genre'],
        'fields'     => [
            [
                'name'       => __( 'FAQs', 'faqs' ),
                'id'         => $prefix . 'faqs',
                'type'       => 'group',
                'clone'      => true,
                'sort_clone' => true,
                'fields'     => [
                    [
                        'name'    => __( 'Question', 'faqs' ),
                        'id'      => $prefix . 'question',
                        'type'    => 'text',
                        'columns' => 11,
                    ],
                    [
                        'name'       => __( 'Answer', 'faqs' ),
                        'id'         => $prefix . 'answer',
                        'type'       => 'wysiwyg',
                        'options'    => [
                            'textarea_rows' => 1,
                        ],
                    ],
                ],
            ],
        ],
    ];

    return $meta_boxes;
}