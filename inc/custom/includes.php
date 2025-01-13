<?php

require_once __DIR__ . '/meta-box/meta-box.php';
require_once __DIR__ . '/meta-box/aio/meta-box-aio.php';

require_once __DIR__ . '/post-types/tv.php';
require_once __DIR__ . '/post-types/dramas.php';
require_once __DIR__ . '/post-types/movies.php';
require_once __DIR__ . '/post-types/people.php';
require_once __DIR__ . '/post-types/videos.php';
require_once __DIR__ . '/post-types/tv-series-seasons.php';
require_once __DIR__ . '/post-types/episodes.php';
require_once __DIR__ . '/post-types/drama-episodes.php';

require_once __DIR__ . '/taxonomies/by-year.php';
require_once __DIR__ . '/taxonomies/network.php';
require_once __DIR__ . '/taxonomies/channel.php';
require_once __DIR__ . '/taxonomies/country.php';
require_once __DIR__ . '/taxonomies/genre.php';
require_once __DIR__ . '/taxonomies/keyword.php';
require_once __DIR__ . '/taxonomies/language.php';
require_once __DIR__ . '/taxonomies/nationality.php';
// require_once __DIR__ . '/taxonomies/seasons.php';

require_once __DIR__ . '/fields/blog-posts-list1.php';
require_once __DIR__ . '/fields/blog-posts-list2.php';
require_once __DIR__ . '/fields/fetch-data-movie.php';
require_once __DIR__ . '/fields/fetch-data-drama.php';
require_once __DIR__ . '/fields/fetch-data-tv-series.php';
require_once __DIR__ . '/fields/movie.php';
require_once __DIR__ . '/fields/people.php';
require_once __DIR__ . '/fields/tv-episode.php';
require_once __DIR__ . '/fields/tv-season.php';
require_once __DIR__ . '/fields/tv-series.php';
require_once __DIR__ . '/fields/drama.php';
require_once __DIR__ . '/fields/drama-episode.php';
require_once __DIR__ . '/fields/video.php';
require_once __DIR__ . '/fields/channel-image.php';
require_once __DIR__ . '/fields/trending-dramas.php';
require_once __DIR__ . '/fields/faqs.php';


require_once __DIR__ . '/comments-template.php';

add_filter('comments_template', 'custom_comments_template');

function custom_comments_template($template) {
  return __DIR__ . '/comments-basic.php'; // Replace with your actual filename if different
}


function remove_meta_box_page() {
    remove_menu_page('meta-box');
}
// add_action('admin_menu', 'remove_meta_box_page');