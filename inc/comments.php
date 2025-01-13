<?php

// add_filter( 'comment_text', 'display_rating_before_comment_text', 10, 2 );
// function display_rating_before_comment_text( $comment_text, $comment ) {
//     if ( is_singular( 'movie' ) || is_singular( 'tv' ) || is_singular( 'episode' ) ) {
//         $link = plugin_dir_url( __DIR__ ).'src/icons/fontawesome-webfont.woff2';
//         return $comment_text; 
//     } else {
//         return $comment_text; // No change for other post types
//     }
// }

// Move comments text area to bottom and remove extra fields like Cookies and Website
function fields_customization( $fields ) {
	// comment textarea to bottom
	$comment_field = $fields['comment'];
	unset( $fields['comment'] );
	$fields['comment'] = $comment_field;
	
	// remove website
	$fields['url'] = '';
	// remove cookies checkbox
	unset( $fields['cookies'] );

	// add placeholder to comment textarea
	$fields['comment'] = str_replace(
        '<textarea', 
        '<textarea placeholder="'.(is_singular( 'movie' ) || is_singular( 'tv' ) || is_singular( 'episode' ) || is_singular( 'drama-episode' ) || is_singular( 'drama' ) ? 'Please share your honest experience which helps others find great movies and shows to watch.' : 'Leave a Comment').'" rows="3"', 
        $fields['comment']
    );
	return $fields;
}
add_filter( 'comment_form_fields', 'fields_customization' );

// comment rating field
add_action( 'comment_form_logged_in_after', 'add_rating_field' );
add_action( 'comment_form_before_fields', 'add_rating_field' );
function add_rating_field() {

    echo (is_singular( 'movie' ) || is_singular( 'tv' ) || is_singular( 'episode' ) || is_singular( 'drama-episode' ) || is_singular( 'drama' )) ? get_rating_style().'<div class="rating">
        <input type="radio" name="rating" value="10">
        <input type="radio" name="rating" value="9">
        <input type="radio" name="rating" value="8">
        <input type="radio" name="rating" value="7">
        <input type="radio" name="rating" value="6">
        <input type="radio" name="rating" value="5">
        <input type="radio" name="rating" value="4">
        <input type="radio" name="rating" value="3">
        <input type="radio" name="rating" value="2">
        <input type="radio" name="rating" value="1">
      </div>' : get_rating_style();
}

function set_comment_form_defaults( $defaults ) {
	$defaults['comment_field'] = sprintf(
		'<p class="comment-form-comment"><label for="comment" class="screen-reader-text">%1$s</label><textarea id="comment" name="comment" cols="45" rows="8" required></textarea></p>',
		esc_html__( 'Comment', 'green-entertainment' )
	);

	$defaults['comment_notes_before'] = '';
	$defaults['comment_notes_after']  = '';
	$defaults['id_form']              = 'commentform';
	$defaults['id_submit']            = 'submit';
	$defaults['title_reply']          = is_singular( 'movie' ) || is_singular( 'tv' ) || is_singular( 'episode' ) || is_singular( 'drama-episode' ) || is_singular( 'drama' ) ? 'What did you think of it?</h3><h3 class="comment-sub-title">Pick a star rating' : 'Leave a Comment';
	$defaults['label_submit']         = 'Submit';

	return $defaults;
}
add_filter( 'comment_form_defaults', 'set_comment_form_defaults', 99 );


// store rating in database
function save_comment_rating( $comment_id ) {
    if ( isset( $_POST['rating'] ) && ( $_POST['rating'] != '' ) ) {
        global $wpdb; 
        $rating = intval( $_POST['rating'] );
        $post_id = $_POST['comment_post_ID'];

        // Check if 'comment_rating' column exists
        $column_exists = $wpdb->get_var( $wpdb->prepare(
            "SHOW COLUMNS FROM $wpdb->comments LIKE %s",
            'comment_rating' 
        ) );

        // If column doesn't exist, create it
        if ( empty( $column_exists ) ) {
            $wpdb->query( "ALTER TABLE $wpdb->comments ADD comment_rating TINYINT NOT NULL" );
        }

        $post_type = get_post_type($post_id);
        $col = $post_type === 'drama' ? 'dramas' : ($post_type === 'tv' ? 'tv_series' : ($post_type === 'episode' ? 'tv_series_episodes' : ($post_type === 'drama-episode' ? 'dramas_episodes' : ($post_type === 'movie' ? 'movies' : ''))));
        $table_name = $col ? $wpdb->prefix.'tmu_'.$col : '';
        $avg_rating = $wpdb->get_row("SELECT total_average_rating,total_vote_count FROM $table_name WHERE `ID` = $post_id");
        $tRating = ['average' => $avg_rating->total_average_rating, 'count' => $avg_rating->total_vote_count];
        // $tRating = get_rating($post_id);
        $total_rate = $tRating['average'] * $tRating['count'];
        $total_rate += $rating;
        ++$tRating['count'];
        
        $tRating['average'] = number_format($total_rate / $tRating['count'], 1);
        
        $wpdb->update($wpdb->comments, [ 'comment_rating' => $rating ], ['comment_ID' => $comment_id], ['%d'], ['%d']);
        
        
        $wpdb->update($table_name, [ 'total_average_rating' => $tRating['average'], 'total_vote_count' => $tRating['count']], ['ID' => $post_id], ['%f', '%d'], ['%d']);
    }
}
add_action( 'comment_post', 'save_comment_rating' );


function display_comment_rating($comment_ID){
    $comment = get_comment($comment_ID);
	  $output = '';
    if ($comment->comment_rating) {
        $rating = $comment->comment_rating;
        $output .= '<div class="user-rating"><div class="single_comment rating">';
        for ($i=10; $i >= 1; $i--) { 
            $output .= $rating == $i ? '<input type="radio" name="rating_'.$comment_ID.'" value="'.$i.'" checked disabled>' : '<input type="radio" name="rating" value="'.$i.'" disabled>';
        }
        $output .= '</div><div class="rate-value">'.($rating).'</div></div>';
    }
	return $output;
}

function get_rating_style(){
	return "
<style>
  #reply-title {
  	font-size: 20px;
    font-weight: 600;
  }
  .comments-title .title {
  	padding-left: 15px;
    padding-bottom: 10px;
  }
  .comments-title {margin-top: 30px; gap:5px;}
  .comments-title:before {
  	content: '';
    background-color: #02c8f0;
    width: 0.25rem;
    border-radius: 0.25rem;
    height: 83.333333%;
    position: absolute;
    left: 0;
  }
  .count-comments {
    font-weight: 400;
    font-size: 18px;
    line-height: 1.6;
    color: #757575;
  }

  .comment-form {
    padding: 20px;
    margin-top: 30px;
  	border-radius: 4px;
    box-shadow: 0 -2px 4px 0 rgb(0 0 0 / 8%), 0 2px 4px 0 rgb(0 0 0 / 10%);
    background-color: white;
    display:flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
    justify-content: center;
    margin-bottom: 5px;
  }

  .rate-this-pop-modal .select-stars-rating, .rating{
    position: relative;
    display: flex;
    margin: 10px 0;
    flex-direction: row-reverse;
    justify-content: center;
    width: 100%;
  }
  .review { width: 100%; }
  .rate-this-pop-modal .select-stars-rating input, .rating input{
    position: relative;
    width: 18px;
    height: 40px;
    display: flex;
    justify-content: center;
    align-items: center;
    -webkit-appearance: none;
    appearance: none;
    overflow: hidden;
    border: 0;
    padding: 0;
  }

  @font-face {
    font-family: 'fontAwesome'; /* Match the font-family above */
    src: url('".plugin_dir_url( __DIR__ )."src/icons/fontawesome-webfont.woff2') format('woff2'), /* Match your font file and format */
  }

  .rate-this-pop-modal .select-stars-rating input::before, .rating input::before{
    content: '\\f005';
    position: absolute;
    font-family: fontAwesome;
    font-size: 29px;
    position: absolute;
    left: 4px;
    color: #e3e3e6;
    transition: 0.5s;
  }
  .rate-this-pop-modal .select-stars-rating input:nth-child(2n + 1)::before, .rating input:nth-child(2n + 1)::before{
    left: initial;
    right: 4px;
  }
  .rate-this-pop-modal .select-stars-rating input:hover ~ input::before, .rating input:hover ~ input::before,
  .rate-this-pop-modal .select-stars-rating input:hover::before, .rating input:hover::before,
  .rate-this-pop-modal .select-stars-rating input:checked ~ input::before, .rating input:checked ~ input::before,
  .rate-this-pop-modal .select-stars-rating input:checked::before, .rating input:checked::before{
    color: #ea580c;
  }
  .single_comment { justify-content: left; margin: 0; width: unset;}
  .single_comment input { pointer-events: none; width: 11.2px; height: 18px; margin-left: -1px;}
  .single_comment input:nth-child(2n + 1)::before{ right: 2.4px; }
  .single_comment input:before { font-size: 16px; }
  .user-rating {display: flex; gap: 10px;} .user-rating:nth-child(1) {line-height: 1.1;}
  .comment-form #author, .comment-form #email { width: 49%; }
  
  .button {
  	margin-top: 5px;
  }

  .rate-this-pop-modal .select-stars-rating input:checked, .rating input:checked {
    box-shadow: none;
  }

  .comment-body {
    padding: 15px 10px;
    border: 1px solid #ebebeb;
    box-shadow: 0 1px 20px 0 rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
  }

  .comment-content {
    border: unset;
    border-top: 1px solid #ddd;
    padding: 15px 0 0 0;
    margin-top: 10px;
  }

  .comment .children {
    margin-top: -15px !important;
  }

  .vcard {font-size: 1.3rem;}

  .entry-meta.comment-metadata {
    font-size: 12px;
  }

  .rate-value {line-height: 1.2; font-size: 15px}

  .comment-author.vcard {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .comment-form-comment { width: 100%; }
  
  .comment-form-comment textarea::placeholder { color: #0000006b; font-size: 12px; }

  @media only screen and (max-width: 600px) { .comment-form #author, .comment-form #email { width: 100%; } }
</style>
";
}


// preload stars font
// function preload_custom_font() {
//     echo '<link rel="preload" href="' . plugin_dir_url( __DIR__ ) . 'src/icons/fontawesome-webfont.woff2" as="font" type="font/woff2" crossorigin>';
// }
// add_action( 'wp_head', 'preload_custom_font' );