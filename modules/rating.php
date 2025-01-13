<?php

/**
 * Create and Display Rating Only
 *
 * @package green_entertainment
 */

function rate_this(){
    $post_id = get_the_ID();
    $title = get_the_title();
    $user_ip = get_client_ip();
    $post_type = get_post_type($post_id);

    global $wpdb;
    $table_name = $wpdb->prefix.'comments';
    $comment_rating = $wpdb->get_var("SELECT comment_rating FROM $table_name WHERE comment_post_ID=$post_id AND comment_author_IP='$user_ip'");

    $html = '<button class="rate-this-button">';
    $html .= $comment_rating ? 'Your Rating '.$comment_rating.'/10' : '<svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" data-testid="StarBorderOutlinedIcon" class="svg-icon m-auto -ml-4 mr-8 icon-sm" height="1em" width="1em"><path d="m22 9.24-7.19-.62L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21 12 17.27 18.18 21l-1.63-7.03L22 9.24zM12 15.4l-3.76 2.27 1-4.28-3.32-2.88 4.38-.38L12 6.1l1.71 4.04 4.38.38-3.32 2.88 1 4.28L12 15.4z"></path></svg> Rate this';
    $html .= '</button>';

    $html .= '<div class="rate-this-pop-modal" style="display: none;">';
    $html .= '<div class="rate-container">';
    $html .= '<div class="rate-top">';
    $html .= '<div class="text-top">Rate this ' . ($post_type==='tv' ? 'TV Show' : ($post_type==='episode' || $post_type==='drama-episode' ? 'Episode' : ($post_type==='drama' ? 'Drama' : 'Movie'))) . '</div>';
    $html .= '<button class="close-pop-modal"><svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" data-testid="CloseOutlinedIcon" class="svg-icon icon-sm" height="1em" width="1em"><path d="M19 6.41 17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"></path></svg></button>';
    $html .= '</div>';
    $html .= '<div class="rate-details">';
    $html .= '<div class="rate-item">';
    $html .= '<div class="rate-item-poster"><img src="' . (has_post_thumbnail($post_id) ? get_the_post_thumbnail_url($post_id, 'medium') : plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp') . '" alt="Poster for ' . $title . '"></div>';
    $html .= '<div class="rate-item-text">';
    $html .= '<div>';
    if ($post_type==='episode') {
        $series_id = rwmb_meta( 'tv_series' );
        $html .= '<a href="' . get_permalink($series_id) . '" style="color: black; font-weight: 600; font-size: 20px; text-decoration: underline !important;">' . get_the_title($series_id) . '</a>';
    }
    $html .= '</div>';
    $html .= '<div>' . ($post_type==='episode' ? rwmb_meta( 'episode_title', '', $post_id ) : $title) . '</div>';
    $html .= '<div>';
    if ($post_type!=='episode') {
        $html .= implode(', ', wp_get_object_terms( $post_id, 'by-year', array('fields' => 'names') ));
    }
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';

    $html .= '<div class="select-stars-rating">';
    for ($i = 10; $i >= 1; $i--) {
        $html .= '<input type="radio" name="select-stars-rating" value="' . $i . '">';
    }
    $html .= '</div>';

    $html .= '<button class="rate-submit-button" data-post-id="' . $post_id . '">Rate</button>';

    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';

    return $html;
}



add_action( 'wp_enqueue_scripts', 'add_rating_scripts' );
function add_rating_scripts() {
    if ((get_post_type() == 'movie' || get_post_type() == 'tv' || get_post_type() == 'episode' || get_post_type() == 'drama' || get_post_type() == 'drama-episode') && is_singular()) {
        wp_register_script('rating', plugin_dir_url( __DIR__ ) . 'src/js/rating.js', array( 'jquery' ), 1.1, true);
        wp_enqueue_script( 'rating' );
        wp_localize_script( 'rating', 'rating_params', array('ajaxurl' => admin_url( 'admin-ajax.php' )));
        wp_enqueue_style( 'rating_css', plugin_dir_url( __DIR__ ) . 'src/css/rating.css', array(), '1.1', 'all' );
     }
}

add_action( 'wp_ajax_rating', 'rating_handler' );
add_action( 'wp_ajax_nopriv_rating', 'rating_handler' );

function rating_handler(){
    $rating = $_POST[ 'rating' ];
    $post_id = $_POST[ 'postID' ];
    $comment_author_IP = get_client_ip();

    global $wpdb;
    $table_name = $wpdb->prefix.'comments';
    $comment = $wpdb->get_row("SELECT comment_ID,comment_rating FROM $table_name WHERE comment_post_ID=$post_id AND comment_author_IP='$comment_author_IP'");
    $tRating = get_rating($post_id);
    $total_rate = $tRating['average'] * $tRating['count'];
    if (!$comment) {
        $comment_ID = wp_insert_comment(['comment_post_ID' => $post_id, 'comment_author_IP' => $comment_author_IP]);
        $total_rate += $rating;
        ++$tRating['count'];
    } else {
        $comment_ID = $comment->comment_ID;
        $total_rate += $rating-$comment->comment_rating;
    }
    $tRating['average'] = number_format($total_rate / $tRating['count'], 1);
    
    $wpdb->update($table_name, [ 'comment_rating' => $rating ], ['comment_ID' => $comment_ID], ['%d'], ['%d']);
    
    $post_type = get_post_type($post_id);
    $col = $post_type === 'drama' ? 'dramas' : ($post_type === 'tv' ? 'tv_series' : ($post_type === 'episode' ? 'tv_series_episodes' : ($post_type === 'drama-episode' ? 'dramas_episodes' : ($post_type === 'movie' ? 'movies' : ''))));
    $table_name = $col ? $wpdb->prefix.'tmu_'.$col : '';
    $wpdb->update($table_name, [ 'total_average_rating' => $tRating['average'], 'total_vote_count' => $tRating['count']], ['ID' => $post_id], ['%f', '%d'], ['%d']);
    // $movie = $wpdb->get_row("SELECT average_rating,vote_count FROM {$wpdb->prefix}tmu_{$post_type} WHERE `ID` = $post_id");

    // $tmdb_rating['average'] = $movie->average_rating ?? 0;
    // $tmdb_rating['count'] = $movie->vote_count ?? 0;

    // if ($post_type === 'tv_series' || $post_type === 'dramas') {
    //     $eps = $wpdb->get_results("SELECT ID,average_rating,vote_count FROM {$wpdb->prefix}tmu_{$post_type}_episodes WHERE $post_type = $post_id");
    //     if ($eps) {
    //         $ep_rating = $tmdb_rating['average']*$tmdb_rating['count'];
    //         foreach ($eps as $ep) {
    //             $ep_comments = get_comments(array('post_id' => $ep->ID, 'status' => 'approve'));
    //             foreach($ep_comments as $comment):
    //                 $ep_rating += isset($comment->comment_rating) && $comment->comment_rating ? (int)$comment->comment_rating : 0;
    //                 $tmdb_rating['count']++;
    //             endforeach;
    //             $ep_rating = $ep_rating+($ep->average_rating*$ep->vote_count);
    //             $tmdb_rating['count'] += $ep->vote_count;
    //         }
    //         $tmdb_rating['average'] = $tmdb_rating['count'] ? number_format(($ep_rating/$tmdb_rating['count']), 1) : 5;
    //     }
    // }

    // $comments = get_comments(array('post_id' => $post_id, 'status' => 'approve'));
    // $average_ratings = get_average_ratings($comments, $tmdb_rating, ($new_rate ? $rating : 0));

    echo json_encode($tRating);

    die;
}


function get_client_ip() {
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
       $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    return $ipaddress;
}