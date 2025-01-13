<?php

function searchfilter($query) { if ($query->is_search && !is_admin() ) { $query->set('post_type',array('page', 'post', 'people', 'drama', 'movie', 'tv', 'season')); set_query_var('posts_per_archive_page', 1); } return $query; }
add_filter('pre_get_posts','searchfilter');

function search_results( $query ) {
    $searchQ = sanitize_text_field( $query );
    $order_title = ucwords($searchQ); $query_words = [];
    global $wpdb;

    $movie_results = $wpdb->get_results("SELECT ID,post_title FROM $wpdb->posts WHERE post_title LIKE '%{$searchQ}%' AND post_type = 'movie' AND post_status = 'publish' ORDER BY CASE WHEN post_title LIKE '{$order_title}%' = 1 THEN 1 ELSE 0 END DESC LIMIT 5");
    $tv_results = $wpdb->get_results("SELECT ID,post_title FROM $wpdb->posts WHERE post_title LIKE '%{$searchQ}%' AND post_type = 'tv' AND post_status = 'publish' ORDER BY CASE WHEN post_title LIKE '{$order_title}%' = 1 THEN 1 ELSE 0 END DESC LIMIT 5");
    $drama_results = $wpdb->get_results("SELECT ID,post_title FROM $wpdb->posts WHERE post_title LIKE '%{$searchQ}%' AND post_type = 'drama' AND post_status = 'publish' ORDER BY CASE WHEN post_title LIKE '{$order_title}%' = 1 THEN 1 ELSE 0 END DESC LIMIT 5");
    $people_results = $wpdb->get_results("SELECT ID,post_title FROM $wpdb->posts WHERE post_title LIKE '%{$searchQ}%' AND post_type = 'people' AND post_status = 'publish' ORDER BY CASE WHEN post_title LIKE '{$order_title}%' = 1 THEN 1 ELSE 0 END DESC LIMIT 5");

    $post_results = $wpdb->get_results("SELECT ID,post_title FROM $wpdb->posts WHERE post_title LIKE '%{$searchQ}%' AND post_type = 'post' AND post_status = 'publish' ORDER BY CASE WHEN post_title LIKE '{$order_title}%' = 1 THEN 1 ELSE 0 END DESC LIMIT 5");

    $total_movies = $wpdb->get_var( "SELECT COUNT(*) AS count FROM $wpdb->posts WHERE post_title LIKE '%{$searchQ}%' AND post_type = 'movie' AND post_status = 'publish'");
    $total_tv = $wpdb->get_var( "SELECT COUNT(*) AS count FROM $wpdb->posts WHERE post_title LIKE '%{$searchQ}%' AND post_type = 'tv' AND post_status = 'publish'");
    $total_dramas = $wpdb->get_var( "SELECT COUNT(*) AS count FROM $wpdb->posts WHERE post_title LIKE '%{$searchQ}%' AND post_type = 'drama' AND post_status = 'publish'");
    $total_people = $wpdb->get_var( "SELECT COUNT(*) AS count FROM $wpdb->posts WHERE post_title LIKE '%{$searchQ}%' AND post_type = 'people' AND post_status = 'publish'");
    $total_posts = $wpdb->get_var( "SELECT COUNT(*) AS count FROM $wpdb->posts WHERE post_title LIKE '%{$searchQ}%' AND post_type = 'post' AND post_status = 'publish'");

    if (!$total_movies && !$total_tv && !$total_dramas && !$total_people && !$total_posts) {
        $string = str_replace(' ', '-', $order_title); // Replaces all spaces with hyphens.
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.

        $searchQ = preg_replace('/-+/', '-', $string);
        $keywords = explode( '-', $searchQ );

        $stopwords = [ // Add more common words to exclude
          'of', 'is', 'are', 'the', 'a', 'an', 'this'
        ];

        $filtered_keywords = count($keywords)>1 ? array_filter($keywords, function ($keyword) use ($stopwords) {
          return !in_array(strtolower($keyword), $stopwords);
        }) : $keywords;

        $query_words = array_map(function ($keyword) { return "'%".$keyword."%'"; }, $filtered_keywords);

        $complete_query = implode( ' OR post_title LIKE ', $query_words );
        $complete_order = implode(', ',array_map(function($word){ return "CASE WHEN post_title LIKE '".$word."%' = 1 THEN 1 ELSE 0 END DESC"; }, $filtered_keywords));

        global $wpdb;
        
        $movie_results = $wpdb->get_results("SELECT ID,post_title FROM $wpdb->posts WHERE (post_title LIKE $complete_query) AND post_type = 'movie' AND post_status = 'publish' ORDER BY $complete_order LIMIT 5");
        $tv_results = $wpdb->get_results("SELECT ID,post_title FROM $wpdb->posts WHERE (post_title LIKE $complete_query) AND post_type = 'tv' AND post_status = 'publish' ORDER BY $complete_order LIMIT 5");
        $drama_results = $wpdb->get_results("SELECT ID,post_title FROM $wpdb->posts WHERE (post_title LIKE $complete_query) AND post_type = 'drama' AND post_status = 'publish' ORDER BY $complete_order LIMIT 5");
        $people_results = $wpdb->get_results("SELECT ID,post_title FROM $wpdb->posts WHERE (post_title LIKE $complete_query) AND post_type = 'people' AND post_status = 'publish' ORDER BY $complete_order LIMIT 5");
        $post_results = $wpdb->get_results("SELECT ID,post_title FROM $wpdb->posts WHERE (post_title LIKE $complete_query) AND post_type = 'post' AND post_status = 'publish' ORDER BY $complete_order LIMIT 5");

        $total_movies = $wpdb->get_var( "SELECT COUNT(*) AS count FROM $wpdb->posts WHERE (post_title LIKE $complete_query) AND post_type = 'movie' AND post_status = 'publish'");
        $total_tv = $wpdb->get_var( "SELECT COUNT(*) AS count FROM $wpdb->posts WHERE (post_title LIKE $complete_query) AND post_type = 'tv' AND post_status = 'publish'");
        $total_dramas = $wpdb->get_var( "SELECT COUNT(*) AS count FROM $wpdb->posts WHERE (post_title LIKE $complete_query) AND post_type = 'drama' AND post_status = 'publish'");
        $total_people = $wpdb->get_var( "SELECT COUNT(*) AS count FROM $wpdb->posts WHERE (post_title LIKE $complete_query) AND post_type = 'people' AND post_status = 'publish'");
        $total_posts = $wpdb->get_var( "SELECT COUNT(*) AS count FROM $wpdb->posts WHERE (post_title LIKE $complete_query) AND post_type = 'people' AND post_status = 'publish'");
    }

    return ['movie' => $movie_results, 'tv' => $tv_results, 'drama' => $drama_results, 'people' => $people_results, 'post' => $post_results, 'total_movies' => $total_movies, 'total_tv' => $total_tv, 'total_drama' => $total_dramas, 'total_people' => $total_people, 'total_posts' => $total_posts, 'query_words' => isset($filtered_keywords) ? $filtered_keywords : [$searchQ]];
}






add_action( 'wp_enqueue_scripts', 'add_search_scripts' );
function add_search_scripts() {
    if ( is_search() ) {
        wp_register_script('ajax_search', plugin_dir_url( __DIR__ ) . 'src/js/ajax-search.js', array( 'jquery' ), 1.1, true);
        wp_enqueue_script( 'ajax_search' );
        wp_localize_script( 'ajax_search', 'ajax_search_params', array('ajaxurl' => admin_url( 'admin-ajax.php' )));
        wp_enqueue_style( 'search_css', plugin_dir_url( __DIR__ ) . 'src/css/search.css', array(), '1.1', 'all' );
    }
}

add_action( 'wp_ajax_search', 'search_ajax_search' );
add_action( 'wp_ajax_nopriv_search', 'search_ajax_search' );

function search_ajax_search(){
    // prepare our arguments for the query
    $searchQ = $_POST[ 'query' ];
    $page = (int)$_POST[ 'page' ];
    $type = $_POST[ 'type' ];
    $total = $_POST[ 'total' ];

    $offset = $page*5;

    $query_words = array_map(function ($keyword) { return "'%".$keyword."%'"; }, $searchQ);

    $complete_query = implode( ' OR post_title LIKE ', $query_words );
    $complete_order = implode(', ',array_map(function($word){ return "CASE WHEN post_title LIKE '".$word."%' = 1 THEN 1 ELSE 0 END DESC"; }, $searchQ));
    
    global $wpdb;
    $results = $wpdb->get_results("SELECT ID,post_title FROM $wpdb->posts WHERE (post_title LIKE $complete_query) AND post_type = '$type' AND post_status = 'publish' ORDER BY $complete_order LIMIT 5 OFFSET $offset");

    if ($results) {
        foreach ($results as $result) { ?>
            <a href="<?= get_permalink($result->ID) ?>" class="single-result" title="<?= $result->post_title ?>">
                <?php if($type !== 'post'): ?><div class="image-container"><div class="result-image"><img src="<?= has_post_thumbnail($result->ID) ? get_the_post_thumbnail_url($result->ID, 'medium') : plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif' ?>" alt="<?= $result->post_title ?>"></div></div><?php endif; ?>
                <div class="result-details">
                    <div class="result-title"><?= $result->post_title ?></div>

                    <?php if ($type == 'people') {
                        global $wpdb;
                        $movies_cast_table = $wpdb->prefix.'tmu_movies_cast';
                        $movies_crew_table = $wpdb->prefix.'tmu_movies_crew';
                        $tv_series_cast_table = $wpdb->prefix.'tmu_tv_series_cast';
                        $tv_series_crew_table = $wpdb->prefix.'tmu_tv_series_crew';
                        $dramas_cast_table = $wpdb->prefix.'tmu_dramas_cast';
                        $dramas_crew_table = $wpdb->prefix.'tmu_dramas_crew';

                        $tv_movie = $wpdb->get_var("SELECT movie FROM $movies_cast_table WHERE `person`=$result->ID ORDER BY `release_year` DESC");
                        $tv_movie = $tv_movie ? $tv_movie : $wpdb->get_var("SELECT tv_series FROM $tv_series_cast_table WHERE `person`=$result->ID ORDER BY `release_year` DESC");
                        $tv_movie = $tv_movie ? $tv_movie : $wpdb->get_var("SELECT dramas FROM $dramas_cast_table WHERE `person`=$result->ID ORDER BY `release_year` DESC");
                        $tv_movie = $tv_movie ? $tv_movie : $wpdb->get_var("SELECT movie FROM $movies_crew_table WHERE `person` = $result->ID ORDER BY `release_year` DESC");
                        $tv_movie = $tv_movie ? $tv_movie : $wpdb->get_var("SELECT tv_series FROM $tv_series_crew_table WHERE `person`=$result->ID ORDER BY `release_year` DESC");
                        $tv_movie = $tv_movie ? $tv_movie : $wpdb->get_var("SELECT dramas FROM $dramas_crew_table WHERE `person`=$result->ID ORDER BY `release_year` DESC"); ?>
                        <div class="profession"><?php rwmb_the_value( 'profession', '', $result->ID ); ?></div>
                        <?php if($tv_movie) { ?><div class="movie"><?= get_the_title($tv_movie) ?> (<?= implode(', ', wp_get_object_terms( $tv_movie, 'by-year', array('fields' => 'names') )) ?>)</div><?php } ?>
                    <?php } elseif($type == 'post') { ?>
                        <div class="extra-info"><span class="release_year"><?= get_the_date('F d, Y', $result->ID) ?></span> <span class="post_type">News</span></div>
                    <?php } else {
                        $star_casts = rwmb_meta( 'star_cast', '', $result->ID );
                        $casts = $star_casts ? array_slice($star_casts,0,2) : []; ?>
                        <div class="extra-info"><span class="release_year"><?= implode(', ', wp_get_object_terms( $result->ID, 'by-year', array('fields' => 'names') )) ?></span> <span class="post_type"><?= $type == 'movie' ? 'Movie' : ($type == 'drama' ? 'Drama' : 'TV Series') ?></span></div>
                        <div class="main_casts"><?= implode(', ', array_map(function ($cast){ return get_the_title($cast['person']); },$casts)) ?></div>
                    <?php } ?>
                </div>
            </a>
        <?php }
    }

    die;
}