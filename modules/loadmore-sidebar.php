<?php
/**
 * Sidebar Load More Ajax
 *
 * @package green_entertainment
 */

add_action( 'wp_enqueue_scripts', 'add_sidebar_loadmore_scripts' );
function add_sidebar_loadmore_scripts() {
    if ( is_single() && (get_post_type() === 'movie' || get_post_type() === 'tv' || get_post_type() === 'drama' || get_post_type() === 'people') ) {
        wp_register_script('sidebar_loadmore', plugin_dir_url( __DIR__ ) . 'src/js/sidebar-loadmore.js', array( 'jquery' ), 1.1, true);
        wp_enqueue_script( 'sidebar_loadmore' );
        wp_localize_script( 'sidebar_loadmore', 'sidebar_loadmore_params', array('ajaxurl' => admin_url( 'admin-ajax.php' )));
	}
}

add_action( 'wp_ajax_sidebar_loadmore', 'sidebar_loadmore_handler' );
add_action( 'wp_ajax_nopriv_sidebar_loadmore', 'sidebar_loadmore_handler' );

function sidebar_loadmore_handler(){
  $term = $_POST[ 'term' ];
  $ppp = $_POST[ 'ppp' ];
  $page = $_POST[ 'page' ];
  $post_type = $_POST[ 'post_type' ];
  $post_id = $_POST[ 'post_id' ];
  $profession = $_POST[ 'profession' ];
	
	$results = get_sidebar_posts_ajax($post_type, $term, $ppp, $page, $post_id, $profession);

	if($results && $post_type !== 'people') foreach ($results as $result) loadmore_item_template_ajax($result);
	if($results && $post_type === 'people') foreach ($results as $result) loadmore_item_template_people($result);

	die;
}

function get_sidebar_posts_ajax($post_type, $term_id, $ppp, $page, $post_id, $profession){
  global $wpdb;
  $table_name = $wpdb->prefix.'tmu_'.$post_type;
  $offset = $ppp*$page;

  if ($term_id == 'recent') {
    $tax_query = ''; $sort_query = '';

    if ($post_type === 'movies') {
        $sort_query = "ORDER BY $table_name.release_timestamp ASC";
    } elseif ($post_type === 'dramas') {
        $tax_query .= "LEFT JOIN ( SELECT `dramas`, MAX(`air_date`) AS last_air_date FROM {$table_name}_episodes WHERE unix_timestamp(`air_date`)<=unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) GROUP BY dramas ) AS last_aired ON {$table_name}.ID = last_aired.`dramas`";
        $sort_query = "ORDER BY last_aired.last_air_date DESC";
    } else {
        $tax_query .= "LEFT JOIN ( SELECT `tv_series`, MAX(`air_date`) AS last_air_date FROM {$table_name}_episodes WHERE unix_timestamp(`air_date`)<=unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) GROUP BY tv_series ) AS last_aired ON {$table_name}.ID = last_aired.`tv_series`";
        $sort_query = "ORDER BY last_aired.last_air_date DESC";
    }

    $release_query = $post_type === 'movies' ? "AND release_timestamp>unix_timestamp(DATE_ADD(NOW(),interval 3 hour))" : "";
    
    $results = $wpdb->get_col("SELECT $table_name.ID FROM $table_name $tax_query LEFT JOIN wp_posts AS posts ON ($table_name.ID = posts.ID) WHERE 1=1 $release_query AND $table_name.ID != $post_id AND posts.post_status = 'publish' GROUP BY $table_name.ID $sort_query LIMIT $ppp OFFSET $offset");
  } elseif ($term_id == 'born-today') {
    $today_month = date('m');
    $today_day = date('d');

    $results = $wpdb->get_results("SELECT `ID`, `date_of_birth` FROM $table_name WHERE MONTH(date_of_birth) = '$today_month' AND DAY(date_of_birth) = '$today_day' AND NOT (`ID` = $post_id) AND dead_on IS NULL LIMIT $ppp OFFSET $offset");
  } elseif ($term_id == 'profession') {
    $results = $wpdb->get_results("SELECT * FROM $table_name WHERE `profession`='{$profession}' AND `ID` != $post_id ORDER BY no_movies DESC LIMIT $ppp OFFSET $offset");
  } else {
    $tax_query = " LEFT JOIN wp_term_relationships AS tt1 ON ($table_name.ID = tt1.object_id)";
    $term_query = " tt1.term_taxonomy_id IN (".$term_id.")";

    $results = $term_id ? $wpdb->get_col("SELECT ID FROM $table_name $tax_query WHERE $term_query AND `ID` != $post_id AND release_timestamp<unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) GROUP BY ID ORDER BY `release_timestamp` DESC LIMIT $ppp OFFSET $offset") : '';
  }

  return $results;

}

function loadmore_item_template_ajax($result){
  $permalink = get_permalink($result);
  ?>
  <div class="item-box">
        <a class="item-poster" href="<?= $permalink ?>">
            <img src="<?= has_post_thumbnail($result) ? get_the_post_thumbnail_url($result, 'full') : plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp' ?>" alt="<?= get_the_title($result) ?>" width="100%" height="100%">
        </a>
        <div class="item-details">
            <h3><a href="<?= $permalink ?>"><?= get_the_title($result) ?></a></h3>
            <div class="genres"><?= implode(', ', wp_get_object_terms( $result, 'genre', array('fields' => 'names') )); ?></div>
        </div>
    </div>
  <?php
}

function loadmore_item_template_people($result){
  $title = get_the_title($result->ID);
  if ($title):
      $permalink = get_permalink($result->ID);
      ?>
        <div class="circle-box">
            <a class="person-poster" href="<?= $permalink ?>">
                <img src="<?= has_post_thumbnail($result->ID) ? get_the_post_thumbnail_url($result->ID, 'full') : plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp' ?>" alt="<?= $title ?>" width="100%" height="100%">
            </a>
            <div class="person-details">
                <h3><a href="<?= $permalink ?>"><?= $title ?></a></h3>
                <div class="profession"><?php rwmb_the_value( 'profession', '', $result->ID ); ?></div>
            </div>
        </div>
      <?php
  endif;
}