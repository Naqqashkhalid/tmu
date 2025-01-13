<?php

add_action( 'wp_ajax_tag_loadmore', 'tag_loadmore_handler' );
add_action( 'wp_ajax_nopriv_tag_loadmore', 'tag_loadmore_handler' );

function tag_loadmore_handler(){
  $tag = $_POST[ 'tag' ];
  $offset = ($_POST[ 'page' ]*10)+4;
  global $wpdb;
  $results = $wpdb->get_col("SELECT object_id FROM wp_term_relationships WHERE term_taxonomy_id = $tag LIMIT 10 OFFSET $offset");
  if($results) foreach ($results as $result) echo tag_post_template_for_ajax($result);
  
  die;
}

function tag_post_template_for_ajax($post_id){
  $title = get_the_title($post_id);
  $permalink = get_permalink($post_id);
  $author_id = get_post_field ('post_author', $post_id);
  $author_url = get_author_posts_url($author_id);
  $author_name = get_the_author_meta( 'display_name', $author_id );
  $data = '<article>';
    $data .= '<a href="'.$permalink.'" class="post-image">';
      $data .= (has_post_thumbnail($post_id) ? '<img src="'.plugin_dir_url( __DIR__ ) . 'src/icons/no-image.svg" data-src="'.get_the_post_thumbnail_url($post_id, 'medium').'" alt="no-image" class="lazyload">' : '<img src="" data-src="'.plugin_dir_url( __DIR__ ) . 'src/icons/no-image.svg" alt="'.$title.'" class="lazyload">');
    $data .= '</a>';
    
    $data .= '<div class="post-content">';
      $data .= '<div class="post-categories"></div>';
      $data .= '<h3 class="post-title"><a href="'.$permalink.'">'.$title.'</a></h3>';
      $data .= '<p class="excerpt">'.wp_trim_words(wp_strip_all_tags(get_the_content('', false, $post_id)), 30).'</p>';
      $data .= '<div class="meta"><span>By <a href="'.$author_url.'">'.$author_name.'</a></span>'.get_the_date( 'F d, Y', $post_id ).'<span></span></div>';
    $data .= '</div>';
  $data .= '</article>';

  return $data;
}