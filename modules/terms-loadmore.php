<?php
/**
 * Taxonomy Terms Load More Ajax
 *
 * @package green_entertainment
 */

add_action( 'wp_enqueue_scripts', 'add_terms_loadmore_scripts' );
function add_terms_loadmore_scripts() {
    if ( is_archive() && is_tax() ) {
        wp_register_script('terms_loadmore', plugin_dir_url( __DIR__ ) . 'src/js/terms-loadmore.js', array( 'jquery' ), 1.1, true);
        wp_enqueue_script( 'terms_loadmore' );
        wp_localize_script( 'terms_loadmore', 'terms_loadmore_params', array('ajaxurl' => admin_url( 'admin-ajax.php' )));
	}
}

add_action( 'wp_ajax_terms_loadmore', 'terms_loadmore_handler' );
add_action( 'wp_ajax_nopriv_terms_loadmore', 'terms_loadmore_handler' );

function terms_loadmore_handler(){
  $term = $_POST[ 'term' ];
	$post_type = $_POST[ 'post_type' ];
	$page = $_POST[ 'page' ];
	$ppp = $_POST[ 'ppp' ];
  $sort = $_POST[ 'sort' ];

	$temp = $post_type === 'movie' || $post_type === 'tv' || $post_type === 'drama' ? true : false;
	
	$results = get_term_posts_ajax($post_type, $term, $ppp, $page, $temp, $sort);

	if($results) foreach ($results as $result) item_template_ajax($result, $temp, $post_type === 'drama' ? true : false);
	if (!$results) echo '';
	die;
}

function get_term_posts_ajax($post_type, $term_id, $post_no, $page, $release, $sort){
  global $wpdb;
  $table_name = $wpdb->prefix.'tmu_'.($post_type === 'movie' ? 'movies' : ($post_type === 'tv' ? 'tv_series' : ($post_type === 'drama' ? 'dramas' : ($post_type === 'people' ? 'people' : $post_type))));
  $offset = $page != 0 ? $post_no*$page : 0;

  // $rating_select = $sort === 'rating' ?
  //   ", CASE
  //       WHEN {$table_name}.average_rating > 0 THEN 
  //           ({$table_name}.average_rating + 
  //            (SELECT AVG(c.comment_rating)
  //             FROM {$wpdb->prefix}comments c
  //             WHERE (
  //               c.comment_post_ID IN (
  //                 SELECT ee.ID
  //                 FROM {$table_name}_episodes ee
  //                 WHERE ee.dramas = {$table_name}.ID
  //             ) OR c.comment_post_ID OR {$table_name}.ID )
  //             AND c.comment_rating > 0)
  //           ) / 2
  //       ELSE
  //           (SELECT AVG(c.comment_rating)
  //            FROM {$wpdb->prefix}comments c
  //            WHERE (c.comment_post_ID IN (
  //                     SELECT ee.ID
  //                     FROM {$table_name}_episodes ee
  //                     WHERE ee.dramas = {$table_name}.ID
  //                   ) OR c.comment_post_ID OR {$table_name}.ID)
  //            AND c.comment_rating > 0)
  //   END AS final_average" : '';
  
  $sort_query = $sort === 'rating' ? "ORDER BY total_average_rating DESC, total_vote_count DESC" : ($sort === 'upcoming' ? "AND release_timestamp>unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) ORDER BY `release_timestamp` ASC" : "AND release_timestamp<unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) ORDER BY `release_timestamp` DESC");

  $select_query = $release ? $table_name.".ID,{$table_name}.release_timestamp".$rating_select : ($post_type === 'people' ? $table_name.'.ID,'.$table_name.'.date_of_birth,'.$table_name.'.profession,'.$table_name.'.no_movies' : $table_name.'.ID');

  $posts = $post_type === 'post' ? $wpdb->prefix.'posts' : 'posts';
  $post_query = $post_type !== 'post' ? "LEFT JOIN {$wpdb->prefix}posts AS posts ON ($table_name.ID = posts.ID)" : '';

  $tax_query = "LEFT JOIN {$wpdb->prefix}term_relationships AS tt1 ON ($table_name.ID = tt1.object_id) Where tt1.term_taxonomy_id IN (".$term_id.")";
  $additional_query = $release ? $sort_query : ( $post_type === 'people' ? "ORDER BY `no_movies` DESC" : "ORDER BY `ID` DESC");

  

  $results = $wpdb->get_results("SELECT $select_query FROM $table_name $post_query $tax_query AND $posts.post_status = 'publish' $additional_query LIMIT $post_no OFFSET $offset");

  return $results;
}

function item_template_ajax($data, $release, $drama){
  if ($data && isset($data->ID)) {
    $title = get_the_title($data->ID);
    $permalink = get_permalink($data->ID);
    ?>
    <div class="term-item <?= $drama ? 'drama-term-item' : '' ?>">
      <a href="<?= $permalink ?>" class="item-poster">
        <img src="<?= has_post_thumbnail($data->ID) ? get_the_post_thumbnail_url($data->ID, 'full') : plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp' ?>" alt="<?= $title ?>" width="100%" height="100%">
      </a>
      <div class="item-details">
        <h3><a href="<?= $permalink ?>"><?= $title ?></a></h3>
        <?php if($drama) { ?>
          <a href="<?= $permalink ?>episodes/" class="permalink">All Episodes</a>
        <?php } elseif($release && isset($data->release_timestamp) && $data->release_timestamp) { ?>
          <p class="release-date">RELEASED ON: <?= date( 'd M Y', $data->release_timestamp ) ?></p>
        <?php } elseif (is_tax( 'nationality' ) && isset($data->date_of_birth) && $data->date_of_birth) { ?>
          <p class="release-date">Born ON: <?= date( 'd M Y', strtotime( $data->date_of_birth ) ) ?></p>
        <?php } ?>
      </div>
    </div>
    <?php
  }
}