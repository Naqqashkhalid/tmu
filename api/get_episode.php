<?php

function get_term_posts_with_count($taxonomy, $term_name, $post_type = 'tv') {
  $args = array(
    'tax_query' => array(
      array(
        'taxonomy' => $taxonomy,
        'field' => 'name', // Use 'name' to search by term name
        'terms' => array( $term_name ),
      )
    ),
    'post_type' => $post_type,
    'posts_per_page' => -1, // Get all posts (adjust if needed)
  );

  $query = new WP_Query( $args );

  $posts = array();
  if ( $query->have_posts() ) {
    while ( $query->have_posts() ) {
      $query->the_post();
      $title = get_the_title();
      $posts[] = ['ID' => get_the_ID(),'title' => ($title ? str_replace("&#8211;", "-", get_the_title()) : '')];
    }
    wp_reset_postdata();
  }

  $count = $query->found_posts; // Get the total number of posts

  return array(
    'count' => $count,
    'posts' => $posts,
  );
}


function delete_episodes_with_title($episode_title) {
  $args = array(
    'name' => $episode_title,
    'post_type' => 'episode',
    'posts_per_page' => -1, // Get all posts (adjust if needed)
    'fields' => 'ids',
  );

  $query = new WP_Query( $args );

  $posts = array();
  if ( $query->have_posts() ) {
    while ( $query->have_posts() ) {
      $query->the_post();
      $deleted = wp_delete_post(get_the_ID());
    }
    wp_reset_postdata();
  }
}