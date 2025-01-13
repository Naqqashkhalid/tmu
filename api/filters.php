<?php


// add_action( 'wp_insert_post', 'check_duplicate_title' );

// function check_duplicate_title( $post_id ) {
// 	$post_id = get_the_ID() ? get_the_ID() : $post_id;
//   $post_type = get_post_type( $post_id );
//   $error = false;

//   // Check if it's our custom post type
//   if ( $post_type && ($post_type === 'movie' || $post_type === 'tv' || $post_type === 'episode') ) {
//     $post_title = get_the_title( $post_id );

//     if ($post_title) {
//     	$args = array(
// 		    'post_type' => $post_type,
// 		    'name' => $post_title, // Use title for comparison
// 		    'fields' => 'ids', // Only return post IDs
// 		  );

// 		  $existing_post = get_posts( $args );

// 		  if (is_array($existing_post)) {
// 		  	foreach ($existing_post as $post) {
// 		  		if ( $post && $post != $post_id ) {
// 				  	wp_delete_post( $post_id );
// 				  	$error = true;
// 				  }
// 		  	}
// 		  }

// 		  if ($error) {
// 		  	$error_message = 'Error: A post with the same title already exists. found:'.$existing_post[0].' and current: '.$post_id;
// 				wp_die( $error_message, __( 'Post Edit Error' ), array( 'response' => 409 ) ); // Set error code 409 (Conflict)
// 		  }
//     }
    
//   }

//   return $post_id;
  
// }

