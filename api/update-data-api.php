<?php

require_once __DIR__ . '/tmdb-data.php';
require_once __DIR__ . '/filters.php';

require_once __DIR__ . '/get_episode.php';
require_once __DIR__ . '/get_movie.php';
require_once __DIR__ . '/get_person.php';
require_once __DIR__ . '/get_season.php';
require_once __DIR__ . '/get_tv_series.php';

require_once __DIR__ . '/set_episode.php';
require_once __DIR__ . '/set_movie.php';
require_once __DIR__ . '/set_person.php';
require_once __DIR__ . '/set_season.php';
require_once __DIR__ . '/set_tv_series.php';
require_once __DIR__ . '/set_drama.php';
require_once __DIR__ . '/set_drama_episode.php';
require_once __DIR__ . '/process_credits.php';
require_once __DIR__ . '/tmdb-custom-updates-page.php';

add_action( 'save_post', 'tmdb_data' );
function tmdb_data($post_id){

  if (function_exists('rocket_clean_post')) { rocket_clean_post($post_id); }

  if ( !isset($_POST['post_type']) || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !current_user_can( 'edit_post' , $post_id ) ) return $post_id;
  
  if('movie' == $_POST['post_type']) add_movie($post_id);
  if('tv' == $_POST['post_type']) add_tv($post_id);
  if('people' == $_POST['post_type']) add_person($post_id);
  if('episode' == $_POST['post_type']) add_episode($post_id);
  if('drama' == $_POST['post_type']) add_drama($post_id);
  if('drama-episode' == $_POST['post_type']) add_drama_episode($post_id);
  if('season' == $_POST['post_type']) add_season($post_id);

  if('video' == $_POST['post_type']) {
      if (!has_post_thumbnail($post_id) && isset($_POST['video_data']['source']) && $_POST['video_data']['source']) {
          $url = 'http://img.youtube.com/vi/'.$_POST['video_data']['source'].'/maxresdefault.jpg';
          $url = image_exist($url) ? $url : (image_exist('http://img.youtube.com/vi/'.$_POST['video_data']['source'].'/sddefault.jpg') ? 'http://img.youtube.com/vi/'.$_POST['video_data']['source'].'/sddefault.jpg' : 'http://img.youtube.com/vi/'.$_POST['video_data']['source'].'/hqdefault.jpg');
          $attachment_id = upload_images_from_urls($url, $post_id, get_the_title($post_id));
          add_post_meta($post_id,'_thumbnail_id',$attachment_id);
      }
  }

  return $post_id;
}

function image_exist($url, $max_size = 1024) {
  $image_size = @getimagesize($url); // Suppress potential errors
  if ($image_size && $image_size[0] > 0 && $image_size[1] > 0) {
    $image_data = @file_get_contents($url); // Suppress potential errors
    if ($image_data) {
      $image_size_bytes = strlen($image_data);
      return $image_size_bytes <= $max_size;
    }
  }
  return false;
}