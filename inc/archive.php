<?php

add_action( 'template_redirect', 'custom_archive_template' );

function custom_archive_template() {

  if (is_front_page() || is_home()) {
    if (file_exists( $home_template = plugin_dir_path( __FILE__ ).'home.php' )) {
      load_template( $home_template );
      exit;
    }
  }

  if (is_single() && is_singular() && file_exists( $single_post = plugin_dir_path( __FILE__ ).'single-post.php') ) {
    load_template( $single_post );
    exit;
  }

  if ( is_archive() ) {

    if (get_queried_object()->name=='movie' && file_exists( $movie_template = plugin_dir_path( __FILE__ ).'archive-movies.php' ) ) {
      load_template( $movie_template );
      exit;
    }

    if (get_queried_object()->name=='tv' && file_exists( $tv_template = plugin_dir_path( __FILE__ ).'archive-tv-series.php' ) ) {
      load_template( $tv_template );
      exit;
    }

    if (get_queried_object()->name=='drama' && file_exists( $tv_template = plugin_dir_path( __FILE__ ).'archive-dramas.php' ) ) {
      load_template( $tv_template );
      exit;
    }

    if (get_queried_object()->name=='people' && file_exists( $people_template = plugin_dir_path( __FILE__ ).'archive-people.php' ) ) {
      load_template( $people_template );
      exit;
    }

    if (get_queried_object()->name=='episode' && file_exists( $episode_template = plugin_dir_path( __FILE__ ).'archive-episode.php' ) ) {
      load_template( $episode_template );
      exit;
    }

    if (get_queried_object()->name=='drama-episode' && file_exists( $drama_episode_template = plugin_dir_path( __FILE__ ).'archive-drama-episode.php' ) ) {
      load_template( $drama_episode_template );
      exit;
    }

    if (get_queried_object()->name=='video' && file_exists( $tv_template = plugin_dir_path( __FILE__ ).'archive-videos.php' ) ) {
      load_template( $tv_template );
      exit;
    }

    if (is_tax() && file_exists( $archive_template = plugin_dir_path( __FILE__ ).'archive-term.php' ) ) {
      load_template( $archive_template );
      exit;
    }

    if (is_tag() && file_exists( $archive_tag = plugin_dir_path( __FILE__ ).'archive-tag.php' ) ) {
      load_template( $archive_tag );
      exit;
    }

    if ((is_category() || is_author()) && file_exists( $default_archive = plugin_dir_path( __FILE__ ).'default-archive.php' ) ) {
      load_template( $default_archive );
      exit;
    }

  }

  global $wp_query;
  if ((is_search() || $wp_query->is_search) && file_exists( $search_template = plugin_dir_path( __FILE__ ).'archive-search.php' )) {
    load_template( $search_template );
    exit;
  }

}