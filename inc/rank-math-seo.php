<?php

add_filter('rank_math/frontend/title', 'seo_title');
add_filter( 'rank_math/frontend/description', 'seo_excerpt');

function seo_title($title) {
  
  return get_seo_title($title);

  // $post_type = get_post_type();
  // $post_id = get_the_ID();
  // $site_title = get_bloginfo( 'name' );
  
  // if (is_single() && is_singular()) {
  //   if($post_type == 'movie'){
  //     $year = implode(', ', wp_get_object_terms( $post_id, 'by-year', array('fields' => 'names') ));
  //     $title .= ' ('.$year.') Movie Release Date, Cast, Review, Trailers | '.$site_title;
  //   } elseif ($post_type == 'tv') {
  //     $year = implode(', ', wp_get_object_terms( $post_id, 'by-year', array('fields' => 'names') ));
  //     $title .= ' ('.$year.') Cast & Crew, Release Date, Review, Trailers | '.$site_title;
  //   } elseif ($post_type == 'people') {
  //     $title .= ': Biography, Movies and TV shows list - '.$site_title;
  //   } elseif ($post_type == 'episode') {
  //     $title .= ': Where to watch - Eonline';
  //   } elseif ($post_type == 'video') {
  //     $title = get_the_title(rwmb_meta( 'post_id' )).': '.$title.' - '.$site_title;
  //   }
  // }

  // if (is_archive()) {
  //   if (is_tax()) {
  //     if ($post_type == 'episode' && is_tax( 'season' )) {
  //       $season_id = get_queried_object_id();
  //       $data = explode(" - Season ",$title);
  //       $year = date("Y", strtotime(rwmb_meta( 'air_date', [ 'object_type' => 'term' ], $season_id )));
  //       $title = $data[0].' Season '.$data[1].($year ? ' ('.$year.')' : '').' Episode List - '.$site_title;
  //     } elseif(is_tax( 'nationality' )) {
  //       $term = get_queried_object();
  //       $title = $term->name.' Celebrities - '.$site_title;
  //     } else {
  //       $term = get_queried_object();
  //       $title = $term->name.' Movies & TV Series - '.$site_title;
  //     }
  //   } else {
  //     if($post_type == 'movie'){
  //       $year = date("Y");
  //       $title = 'New Movies '.$year.' - upcoming hollywood movies '.$year.' - '.$site_title;
  //     } elseif ($post_type == 'tv') {
  //       $title = 'Best TV Shows '.date("F Y").' - '.$site_title;
  //     } elseif ($post_type == 'people') {
  //       $title = 'Most famous Actors | Hollywood Celebrities - '.$site_title;
  //     } elseif ($post_type == 'episode') {
  //       $title = 'Watch Latest TV Series Episodes - '.$site_title;
  //     } elseif ($post_type == 'video') {
  //       $title = 'Watch New Movies & TV Show Trailers - '.$site_title;
  //     }
  //   }
  // }

  // return $title;
}

function seo_excerpt( $description ) {

  return get_seo_description($description);

  // $title = $description;
  // $post_type = get_post_type();
  // $post_id = get_the_ID();
  // global $wpdb;
  
  // if (is_single() && is_singular()) {
  //   if($post_type == 'movie'){
  //     // $language = implode(', ', wp_get_object_terms( $post_id, 'language', array('fields' => 'names') ));
  //     $movie = $wpdb->get_row($wpdb->prepare("SELECT release_timestamp,star_cast FROM {$wpdb->prefix}tmu_movies WHERE `ID` = %d", $post_id), ARRAY_A);
  //     $release = $movie['release_timestamp'] ? date( 'd F Y', $movie['release_timestamp'] ) : '';
  //     $starcast = implode(', ', array_map(function ($cast) { return isset($cast['person']) ? get_the_title($cast['person']) : ''; }, ($movie['star_cast'] ? unserialize($movie['star_cast']) : [])));
  //     $director = $wpdb->get_var($wpdb->prepare("SELECT person FROM {$wpdb->prefix}tmu_movies_crew WHERE movie = %d AND job='Director'", $post_id));

  //     $description = $title.( (strtotime('today GMT') > $movie[ 'release_timestamp' ]) ? ' was released in theaters on ' : ' is all set to hit theaters on ').$release.'. '.$title.' directed by '.get_the_title($director).',  '.$starcast.' played the primary leads.';
  //   }
  //   elseif ($post_type == 'tv') {
  //     $tv = $wpdb->get_row($wpdb->prepare("SELECT release_timestamp,network,star_cast FROM {$wpdb->prefix}tmu_tv_series WHERE `ID` = %d", $post_id), ARRAY_A);
  //     $release = $tv['release_timestamp'] ? date( 'd F Y', $tv['release_timestamp'] ) : '';
  //     $starcast = implode(', ', array_map(function ($cast) { return isset($cast['person']) ? get_the_title($cast['person']) : ''; }, ($tv['star_cast'] ? unserialize($tv['star_cast']) : [])));
  //     $crew_table = $wpdb->prefix . 'tmu_tv_series_crew';
  //     $director = $wpdb->get_var($wpdb->prepare("SELECT person FROM {$crew_table} WHERE tv_series = %d AND job='Director'", $post_id));
  //     // $language = implode(', ', wp_get_object_terms( $post_id, 'language', array('fields' => 'names') ));

  //     $description = $title.' TV Show - Release Date, Date, Trailer, Review and Other Details. '.$title.( (strtotime('today GMT') > $tv[ 'release_timestamp' ]) ? ' was released on ' : ' is all set to air on ').($tv['network'] ? $tv['network'].' on ' : '').$release.'. '.$title.' directed by '.get_the_title($director).', '.$starcast.' played the primary leads.';;
  //   }
  //   elseif ($post_type == 'people') {
  //     $person = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}tmu_people WHERE `ID` = %d", $post_id), ARRAY_A);
  //     $person['name'] = get_the_title();
  //     $person['refer'] = $person['gender'] === 'Male' ? 'He' : 'She';
  //     $person['age'] = calculate_age($person['date_of_birth'], $person['dead_on']);
  //     $nationality = implode(', ', wp_get_object_terms( $post_id, 'nationality', array('fields' => 'names') ));
      
  //     $description = ($person['date_of_birth'] && $person[ 'birthplace' ] && $nationality) ? ($person['name'].' is an '.$nationality.' actor. '.$person['refer'].' was born on '.date( 'd F Y', strtotime($person['date_of_birth']) ).' in '.$person[ 'birthplace' ].'. '.$person['refer'].($person['dead_on'] ? ' was ' : ' is currently ').$person['age'].' years old.') : "We don't have a biography for ".$person['name'];
  //   }
  //   elseif ($post_type == 'episode') {
  //     $description = ' '.rwmb_meta( 'overview' );
  //   }
  //   elseif ($post_type == 'video') {
  //     $description = get_the_content(rwmb_meta( 'post_id' ));
  //   }
  //   elseif (is_tax( 'season' )) {
  //     $description .= ' ('.date("Y", strtotime(rwmb_meta( 'air_date', [ 'object_type' => 'term' ], get_queried_object_id() ))).'): Episodes list, reviews, release dates and where to watch online.';
  //   }
  // }

  // if (is_archive()) {
  //   if (is_tax()) {
  //     if ($post_type == 'episode' && is_tax( 'season' )) {
  //       $season = get_queried_object();
  //       $data = explode(" - Season ",$season->name);
  //       $description = 'List of episodes for '.$data[0].' ('.date("Y", strtotime(rwmb_meta( 'air_date', [ 'object_type' => 'term' ], $season->term_id ))).'): Season '.$data[1];
  //     } elseif(is_tax( 'nationality' )) {
  //       $term = get_queried_object();
  //       $title = $term->name.' Celebrities from Eonline';
  //     } else {
  //       $term = get_queried_object();
  //       $description = $term->name.' Movies & TV Series from Eonline';
  //     }
  //   } else {
  //     if($post_type == 'movie'){
  //       $year = date("Y");
  //       $description = 'Latest Movies '.$year.': Find the list of new movies released in the year '.$year.'. Stay updated about the latest Hollywood movies, their ratings, reviews & much more from Eonline';
  //     }
  //     elseif ($post_type == 'tv') {
  //       $description = 'Best TV Shows – Get latest updates on best television shows, TV Series Reviews, popular television shows at eonline.live';
  //     }
  //     elseif ($post_type == 'people') {
  //       $description = 'Most Famous Actors, celebrity list, top celebrities, celebrities news, celebrities female, celebrities male, Hollywood celebrities';
  //     }
  //     elseif ($post_type == 'episode') {
  //       $description = ' '.rwmb_meta( 'overview' );
  //     }
  //     elseif ($post_type == 'video') {
  //       $description = 'Watch New Movies & TV Show Trailers - Eonline';
  //     }
  //   }
  // }
  
	// return $description;
}