<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

$term = get_queried_object();
$tax = ucfirst($term->taxonomy);
$post_type = get_post_type();
$options = get_options(["tmu_dramas","tmu_movies","tmu_tv_series"]);
$temp = false;
$faqs = term_faqs($term->term_id, $term->taxonomy);
if (is_tax( 'keyword' ) || is_tax( 'genre' ) || is_tax( 'country' ) || is_tax( 'channel' ) || is_tax( 'network' ) || is_tax( 'by-year' ) || is_tax( 'language' )) {
  $movies = get_term_posts('movie', $term->term_id, 12);
  $tv_series = get_term_posts('tv', $term->term_id, 12);
  $dramas = get_term_posts('drama', $term->term_id, 12);
  $temp = true;
} else {
  $results = get_term_posts($post_type, $term->term_id, 12);
}

if ($temp) { if (is_tax( 'channel' )) {
    $logo = wp_get_attachment_image_url(get_term_meta( $term->term_id, 'logo', true ), 'full');
    $heading = '<div class="channel-term">'.($logo ? '<img src="'.$logo.'" class="term-logo" alt="'.$term->name.'" width="100%" height="100%">' : '').'<h1 class="term-title">'.$term->name.' Dramas</h1></div>';
  } else { $heading = '<h1>'.($options['tmu_dramas'] === 'on' ? (is_tax( 'genre' ) ? 'Pakistani ' : (is_tax( 'by-year' ) ? 'Pakistani Dramas ' : '')) : '').$term->name.($options['tmu_dramas'] === 'on' && !is_tax( 'by-year' ) ? ' Dramas' : ($options['tmu_movies'] === 'on' ? ($options['tmu_tv_series'] === 'on' ? ' Movies & TV Series' : ' Movies') : ($options['tmu_tv_series'] === 'on' ? ' TV Series' : ''))).'</h1>'; }
} else { $heading = '<h1>'.$term->name.' Celebrities</h1>'; }

$site_url = get_site_url();
$permalink = $site_url.$_SERVER['REQUEST_URI'];

$schema = default_schema($term->name, $permalink).'<script type="application/ld+json"> { "@context": "https://schema.org", "@type": "ItemList", "itemListElement": [';

$count = 0;
$data = '<link rel="stylesheet" href="'.green_dir_url.'src/css/term.css">';
$data .= '<div class="content-area" id="primary">';
  $data .= '<main class="site-main" id="main">';
    $data .= '<section class="archive-container">';
      $data .= '<div class="archive-header">';
        $data .= '<ul class="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">';
          $data .= '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a itemprop="item" href="'.$site_url.'" title="Home"><span itemprop="name">Home</span></a><meta itemprop="position" content="1" /></li>';
          $data .= '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"> / <a itemprop="item" href="'.$site_url.'/'.$term->taxonomy.'/" title="'.$tax.'"><span itemprop="name">'.$tax.'</span></a><meta itemprop="position" content="2" /></li>';
          $data .= '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"> / <meta itemprop="item" content="'.$permalink.'" /><span itemprop="name">'.$term->name.'</span><meta itemprop="position" content="3" /></li>';
        $data .= '</ul>';
        
        $data .= '<div class="term-header">';
          $data .= $heading;
          if (!is_tax( 'nationality' )) {
            $data .= '<div class="sort-container">';
              $data .= '<div class="sort-posts"><span class="sort_by">Sort By:</span> <span class="selected" data-current-active="recommended"><span class="selectedText">Recommended</span> <svg width="16" height="16" class="icon_svg"><path d="M8 10.25a.746.746 0 0 1-.525-.215l-3.055-3a.75.75 0 0 1 1.05-1.07L8 8.449l2.53-2.484a.75.75 0 0 1 1.05 1.07l-3.055 3A.746.746 0 0 1 8 10.25Z"></path></svg></span></div>';
              $data .= '<div class="sort-options"><div class="sort-option active" data-sort="recommended">Recommended</div><div class="sort-option" data-sort="rating">Top Rated</div><div class="sort-option" data-sort="upcoming">Upcoming</div></div>';
            $data .= '</div>';
          }
        $data .= '</div>';
        $data .= '<div>'.term_description($term->term_id).'</div>';

        if ($temp && $movies) {
          $total_pages = ceil($movies['max_num_pages']/18);
          $data .= '<div class="movies term-items">';
            $data .= '<div class="heading"><h2 class="weight-700 font-size-22">'.$term->name.' Movies</h2></div>';
            $data .= '<div class="items-container" id="movies">';
              foreach ($movies as $result) if (isset($result->ID)) { $data .= item_template($result, $temp); $schema .= ($count !== 0 ? ',' : '' ).listitem_schema($result, $count, 'movie'); $count++; }
            $data .= '</div>';
            $data .= '<div class="button" data-type="movie" data-term="'.$term->term_id.'" data-items="18" data-page="1" data-total="'.$total_pages.'" data-for="movies" style="'.($total_pages>1 ? '' : 'display:none').'">Load More</div>';
          $data .= '</div>';
        }

        if ($temp && $tv_series) {
          $total_pages = ceil($tv_series['max_num_pages']/18);
          $data .= '<div class="tv-series term-items">';
            $data .= '<div class="heading"><h2 class="weight-700 font-size-22">'.$term->name.' TV Series</h2></div>';
            $data .= '<div class="items-container" id="tv-series">';
              foreach ($tv_series as $result) if (isset($result->ID)) { $data .= item_template($result, $temp); $schema .= ($count !== 0 ? ',' : '' ).listitem_schema($result, $count, 'tv'); $count++; }
            $data .= '</div>';
            $data .= '<div class="button" data-type="tv" data-term="'.$term->term_id.'" data-items="18" data-page="1" data-total="'.$total_pages.'" data-for="tv-series" style="'.($total_pages>1 ? '' : 'display:none').'">Load More</div>';
          $data .= '</div>';
        }

        if ($temp && $dramas) {
          $total_pages = ceil($dramas['max_num_pages']/18);
          $data .= '<div class="tv-series term-items">';
            $data .= '<div class="items-container" id="dramas">';
              foreach ($dramas as $result) if (isset($result->ID)) { $data .= item_template($result, $temp, true); $schema .= ($count !== 0 ? ',' : '' ).listitem_schema($result, $count, 'tv'); $count++; }
            $data .= '</div>';
            $data .= '<div class="button" data-type="drama" data-term="'.$term->term_id.'" data-items="18" data-page="1" data-total="'.$total_pages.'" data-for="dramas" style="'.($total_pages>1 ? '' : 'display:none').'">Load More</div>';
          $data .= '</div>';
        }

        if (!$temp && $results) {
          $total_pages = ceil($results['max_num_pages']/36);
          $data .= '<div class="term-items">';
            $data .= '<div class="items-container" id="taxonomies">';
              foreach ($results as $result) if (isset($result->ID)) { $data .= item_template($result, $temp); $schema .= ($count !== 0 ? ', ' : '' ).'{ "@type": "ListItem", "position": "'.($count+1).'", "url": "'.$permalink.'" }'; $count++; }
            $data .= '</div>';
            $data .= '<div class="button" data-type="'.$post_type.'" data-term="'.$term->term_id.'" data-items="36" data-page="1" data-total="'.$total_pages.'" data-for="taxonomies" style="'.($total_pages>1 ? '' : 'display:none').'">Load More</div>';
          $data .= '</div>';
        }
        wp_reset_postdata();
        $data .= term_footer_links($term->term_id, $term->taxonomy);
        $data .= $faqs['data'];
      $data .= '</div>';
      $data .= term_sidebar();
    $data .= '</section>';
  $data .= '</main>';
$data .= '</div>';

$schema .= '] }</script>'.$faqs['schema'];

add_action('wp_head', function() use ($schema) { echo $schema; });
get_header();
echo  $data;
do_action( 'generate_after_primary_content_area' );

generate_construct_sidebars();

get_footer();

function get_term_posts($post_type, $term_id, $post_no){

  $release = $post_type === 'movie' || $post_type === 'tv' || $post_type === 'drama' ? true : false;

  global $wpdb;
  $table_name = $wpdb->prefix.($post_type === 'movie' ? 'tmu_movies' : ($post_type === 'tv' ? 'tmu_tv_series' : ($post_type === 'drama' ? 'tmu_dramas' : ($post_type === 'people' ? 'tmu_people' : $post_type))));

  $select_query = $release ? $table_name.".ID,".$table_name.".release_timestamp,".$table_name.".average_rating,".$table_name.".vote_count" : ($post_type === 'people' ? $table_name.'.ID,'.$table_name.'.date_of_birth,'.$table_name.'.profession,'.$table_name.'.no_movies' : $table_name.'.ID');

  $posts = $post_type === 'post' ? 'wp_posts' : 'posts';
  $post_query = $post_type !== 'post' ? "LEFT JOIN wp_posts AS posts ON ($table_name.ID = posts.ID)" : '';

  $tax_query = "LEFT JOIN wp_term_relationships AS tt1 ON ($table_name.ID = tt1.object_id) Where tt1.term_taxonomy_id IN (".$term_id.")";
  $additional_query = $release ? "AND release_timestamp<unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) ORDER BY `release_timestamp` DESC" : ( $post_type === 'people' ? "ORDER BY `no_movies` DESC" : "ORDER BY `ID` DESC");
  
  $results = $wpdb->get_results("SELECT $select_query FROM $table_name $post_query $tax_query AND $posts.post_status = 'publish' $additional_query LIMIT $post_no");
  if ($results) $results['max_num_pages'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_name $post_query $tax_query AND $posts.post_status = 'publish' $additional_query");

  return $results;
}

function item_template($data, $release, $drama=false){
  if ($data && isset($data->ID)) {
    $title = get_the_title($data->ID);
    $permalink = get_permalink($data->ID);
    
    $item_data = '<div class="term-item '.($drama ? 'drama-term-item' : '').'">';
      $item_data .= '<a href="'.$permalink.'" class="item-poster" title="'.$title.'">';
        $item_data .= '<img '.(has_post_thumbnail($data->ID) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url($data->ID, 'full').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ).' alt="'.$title.'" width="100%" height="100%">';
      $item_data .= '</a>';
      $item_data .= '<div class="item-details">';
        $item_data .= '<h3><a href="'.$permalink.'" title="'.$title.'">'.$title.'</a></h3>';
        $item_data .= $drama ? '<a href="'.$permalink.'episodes/" class="permalink" title="View All Episodes">All Episodes</a>' : '';
        $item_data .= !$drama && $release && isset($data->release_timestamp) && $data->release_timestamp ? '<p class="release-date">Release: '.date( 'd M Y', $data->release_timestamp ).'</p>' : '';
        $item_data .= is_tax( 'nationality' ) && isset($data->date_of_birth) && $data->date_of_birth ? '<p class="release-date">Born ON: '.date( 'd M Y', strtotime( $data->date_of_birth ) ).'</p>' : '';
      $item_data .= '</div>';
    $item_data .= '</div>';
  }
  return $item_data;
}

function term_faqs($term_id, $taxonomy){
  $schema = $data = '';
  if ($taxonomy === 'channel' || $taxonomy === 'genre') {
    $faqs = rwmb_meta( 'faqs', [ 'object_type' => 'term' ], $term_id );
    if ($faqs) {
      $schema .= '<script type="application/ld+json">{ "@context": "https://schema.org", "@type": "FAQPage", "mainEntity": [';
      $data .= '<div class="faqs"><h3>Frequently Asked Questions and Answers</h3><div class="faqs-block">'; $count=0;
      foreach ($faqs as $faq) {
        $schema .= ($count++ !== 0 ? ',' : '').'{"@type": "Question", "name": "'.($faq[ 'question' ] ?? '').'?","acceptedAnswer": {"@type": "Answer","text": "'.preg_replace('/[\n\r\"]/', '', wp_strip_all_tags($faq[ 'answer' ] ?? '')).'"}}';
        $data .= '<div class="faq-single"><p class="question">'.($faq[ 'question' ] ?? '').'</p><div class="answer">'.($faq[ 'answer' ] ?? '').'</div></div>';
      }
      $data .= '</div></div>';
      $schema .= ']}</script>';
    }
  }

  return ['data' => $data, 'schema' => $schema];
}