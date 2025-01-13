<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}
$term = get_queried_object();
$site_url = get_site_url();
$permalink = $site_url.$_SERVER['REQUEST_URI'];

wp_register_script('tag_loadmore', plugin_dir_url( __DIR__ ) . 'src/js/ajax-tag.js', array( 'jquery' ), 1.1, true);
wp_enqueue_script( 'tag_loadmore' );
wp_localize_script( 'tag_loadmore', 'tag_loadmore_params', array('ajaxurl' => admin_url( 'admin-ajax.php' )));

$schema = default_schema($term->name, $permalink);
$schema .= '<script type="application/ld+json">{"@context": "https://schema.org","@type": "ItemList","itemListElement": [';
$count = 0;

$featured = new WP_Query([ 'tag' => $term->slug, 'post_type' => 'post', 'posts_per_page' => 4 ]);
$all_posts = new WP_Query([ 'tag' => $term->slug, 'post_type' => 'post', 'posts_per_page' => 10, 'offset' => 4 ]);

global $wpdb;

$post_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title = %s AND post_type IN ('movie', 'tv', 'drama', 'people') AND post_status = 'publish' LIMIT 1", wp_specialchars_decode($term->name)));

$data = '<link rel="stylesheet" type="text/css" href="'.plugin_dir_url( __DIR__ ).'src/css/archive-tag.css">';
$data .= '<div class="content-area" id="primary">';
  $data .= '<main class="site-main" id="main">';
    $data .= '<section class="archive-container">';
      $data .= '<div class="archive-header">';
        $data .= '<ul class="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">';
          $data .= '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a itemprop="item" href="'.$site_url.'" title="Home"><span itemprop="name">Home</span></a><meta itemprop="position" content="1" /></li>';
          $data .= '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"> / <meta itemprop="item" content="'.$permalink.'" /><span itemprop="name">'.$term->name.'</span><meta itemprop="position" content="2" /></li>';
        $data .= '</ul>';
        $data .= '<h1>'.$term->name.'</h1>';
        $data .= '<div class="term-description">'.term_description( $term->term_id ).'</div>';
      $data .= '</div>';
      $data .= '<hr>';
      $data .= '<div class="listing-featured">';
      if ($featured->have_posts()) {
        while ($featured->have_posts()) {
          $featured->the_post();
          $data .= ($count === 0 ? '<div class="featured-primary">'.featured_post_template(true) : ($count === 1 ? '<div class="featured-secondary">' : '').featured_post_template(false)).($count === 0 ? '</div>' : '');
          $schema .= ($count !== 0 ? ',' : '' ).'{"@type": "ListItem","position": "'.(++$count).'","url": "'.get_permalink().'"}';
        }
        $data .= $count > 1 ? '</div>' : '';
        wp_reset_postdata();
      }

      $data .= '</div>';

      $data .= '<div class="parent-post">';
      $data .= parent_post($post_id);
      $data .= '</div>';

      
      if ($all_posts->have_posts()) {
        $data .= '<div class="tag-posts-list">';
          $data .= '<div class="section-title">LATEST</div>';
          $data .= '<div class="tag-posts-block">';
            while ($all_posts->have_posts()) {
              $all_posts->the_post();
              $data .= tag_post_template(get_the_ID());
              $schema .= ($count !== 0 ? ',' : '' ).'{"@type": "ListItem","position": "'.(++$count).'","url": "'.get_permalink().'"}';
            }
            wp_reset_postdata();
          $data .= '</div>';
          $data .= '<button class="loadmore" data-tag="'.$term->term_id.'" data-total="'.$all_posts->found_posts.'" data-page="1">Load More</button>';
        $data .= '</div>';
      }
      $data .= '</section>';
    $data .= '</main>';
  $data .= '</div>';

  $schema .= ']}</script>';


  add_action('wp_head', function() use ($schema) { echo $schema; });
  get_header();
  echo $data;  
  do_action( 'generate_after_primary_content_area' );
  generate_construct_sidebars();
  get_footer();


function featured_post_template($primary=false){
  $title = get_the_title();
  $data = '<article class="single-post">';
    $data .= '<a class="img-link" href="'.get_permalink().'" title="'.$title.'">';
      $data .= '<div class="image">';
        $data .= '<img '.(has_post_thumbnail() ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url(get_the_ID(), 'full').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ).' alt="'.$title.'" width="100%" height="100%">';
      $data .= '</div>';
    $data .= '</a>';
    $data .= '<div class="info">';
      $data .= '<h3 class="title"><a href="'.get_permalink().'" title="'.$title.'">'.$title.'</a></h3>';
      $data .= '<p class="excerpt">'.($primary ? wp_trim_words(wp_strip_all_tags(get_the_content()), 60) : '').'</p>';
    $data .= '</div>';
  $data .= '</article>';
  return $data;
}

function parent_post($post_id) {
  $person = get_post_type($post_id) === 'people';
  $title = get_the_title($post_id);
  $data = '<div class="main-post">';
    $data .= '<div class="poster-container '.($person ? 'person-image' : '').'">';
      $data .= '<div class="poster"><img '.(has_post_thumbnail($post_id) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url($post_id, 'full').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ).' alt="'.$title.'" width="100%" height="100%"></div>';
    $data .= '</div>';
    $data .= '<div class="details '.($person ? 'person-details' : '').'">';
      $data .= '<h3><a href="'.get_permalink($post_id).'" title="'.$title.'">'.$title.'</a></h3>';
      $data .= parent_post_details($post_id);
    $data .= '</div>';
  $data .= '</div>';
  return $data;
}

function parent_post_details($post_id){
  global $wpdb;
  $post_type = get_post_type($post_id);
  $data = '';
  if (in_array($post_type, ['movie', 'tv', 'drama', 'people']) ):
    $column = $post_type === 'tv' ? 'tv_series' : ($post_type === 'drama' ? 'dramas' : $post_type);
    $prefix = $wpdb->prefix.'tmu_';
    $table_name = $prefix.($column === 'movie' ? 'movies' : $column);
    $result = $wpdb->get_row("SELECT t.* FROM $table_name t JOIN {$wpdb->prefix}posts AS posts ON (t.ID = posts.ID) WHERE t.ID=$post_id AND posts.post_status = 'publish'");
    if ($result && $post_type === 'people'):
      if ($result->profession === 'Acting') { $professions = $result->gender === 'Male' ? 'Actor' : 'Actress'; } else {
        $professions = $wpdb->get_col("SELECT DISTINCT job FROM ( SELECT job FROM {$prefix}tv_series_crew WHERE person = $post_id UNION ALL SELECT job FROM {$prefix}dramas_crew WHERE person = $post_id UNION ALL SELECT job FROM {$prefix}movies_crew WHERE person = $post_id ) AS combined_jobs");
        $professions = $professions ? implode(', ', array_map(function($job){ return $job; }, $professions)) : $result->profession;
      }
      // $tv_series = $wpdb->get_results("SELECT DISTINCT tv_series FROM ( SELECT tv_series FROM {$prefix}tv_series_crew WHERE person = $post_id UNION ALL SELECT tv_series FROM {$prefix}tv_series_cast WHERE person = $post_id ) AS combined_tv_series");
      // $movies = $wpdb->get_results("SELECT DISTINCT movie FROM ( SELECT movie FROM {$prefix}movies_crew WHERE person = $post_id UNION ALL SELECT movie FROM {$prefix}movies_cast WHERE person = $post_id ) AS combined_movies");
      // $dramas = $wpdb->get_results("SELECT DISTINCT dramas FROM ( SELECT dramas FROM {$prefix}dramas_crew WHERE person = $post_id UNION ALL SELECT movie FROM {$prefix}movies_cast WHERE person = $post_id ) AS combined_movies");

      $known_for = $result->known_for ? unserialize($result->known_for) : '';

      $data .= '<div class="short-details person">';
        $data .= $result->nick_name ? '<div class="detail-item"><span class="item-key">NICKNAME</span><span class="item-value">'.$result->nick_name.'</span></div>':'';
        $data .= $result->date_of_birth ? '<div class="detail-item"><span class="item-key">BIRTHDATE</span><span class="item-value">'.date('F d, Y',strtotime($result->date_of_birth)).'</span></div>' : '';
        $data .= $result->birthplace ? '<div class="detail-item"><span class="item-key">BIRTHPLACE</span><span class="item-value">'.$result->birthplace.'</span></div>' : '';
        $data .= $professions ? '<div class="detail-item"><span class="item-key">PROFESSION</span><span class="item-value">'.$professions.'</span></div>' : '';
        $data .= $result->net_worth ? '<div class="detail-item"><span class="item-key">NETWORTH</span><span class="item-value">'.'$'.nice_numbers($result->net_worth).'</span></div>' : '';
        $data .= $known_for ? '<div class="detail-item"><span class="item-key">NOTABLE PROJECTS</span><span class="item-value">'.implode(', ', array_slice(array_map(fn($project) => get_linked_post($project), $known_for), 0,4)).'</span></div>' : '';
      $data .= '</div>';
    endif;
    if ($result && $post_type !== 'people'):
      $director = $wpdb->get_var($wpdb->prepare("SELECT crew.person FROM {$table_name}_crew crew JOIN {$wpdb->prefix}posts AS posts ON (crew.ID = posts.ID) WHERE crew.$column = %d AND (crew.job='Director' OR crew.job LIKE '%Co-Director%' OR crew.job LIKE '%Assistant Director%') AND posts.post_status = 'publish' ORDER BY CASE WHEN crew.job='Director' THEN 1 ELSE 2 END", $post_id));
      $producer = $wpdb->get_var($wpdb->prepare("SELECT crew.person FROM {$table_name}_crew crew JOIN {$wpdb->prefix}posts AS posts ON (crew.ID = posts.ID) WHERE crew.$column = %d AND (crew.job = 'Producer' OR crew.job LIKE '%Co-Producer%' OR crew.job LIKE '%Executive Producer%' OR crew.job LIKE '%Associate Producer%') AND posts.post_status = 'publish' ORDER BY CASE WHEN crew.job = 'Producer' THEN 1 ELSE 2 END", $post_id));
      $credits = $result->star_cast ? unserialize($result->star_cast) : '';
      $casts = main_post_casts_with_image($credits);

      $data .= '<div class="parent-meta">';
        $data.= '<span>'.($post_type==='tv' ? 'TV' : ucfirst($post_type)).'</span>';
        $data.= '<span>'.$result->certification.'</span>';
        $data.= '<span>'.date('F d, Y', $result->release_timestamp).'</span>';
      $data .= '</div>';
      
      $data .= '<div class="genres">';
        $data .= get_linked_terms('genre', '', $post_id);
      $data .= '</div>';

      $data .= '<div class="short-details">';
        $data .= $director ? '<div class="detail-item"><span class="item-key">Director</span><span class="item-value">'.get_the_title($director).'</span></div>' : '';
        $data .= $producer ? '<div class="detail-item"><span class="item-key">Producer</span><span class="item-value">'.get_the_title($producer).'</span></div>' : '';
        $data .= $result->runtime ? '<div class="detail-item"><span class="item-key">Runtime</span><span class="item-value">'.$result->runtime.' minutes</span></div>' : '';
        $data .= isset($result->last_season) && $result->last_season ? '<div class="detail-item"><span class="item-key">Seasons</span><span class="item-value">'.$result->last_season.'</span></div>' : '';
        $data .= $result->production_house ? '<div class="detail-item"><span class="item-key">Studio</span><span class="item-value">'.$result->production_house.'</span></div>' : '';
      $data .= '</div>';

      $data .= '<div class="casts-container">';
        $data .= '<div class="casts-header"><div class="title">CAST</div><a href="'.get_permalink($post_id).'#cast-and-crew" title="SEE ALL CAST & CREW">SEE ALL CAST & CREW</a></div>';
        $data .= '<div class="cast-list">'.$casts.'</div>';
      $data .= '</div>';
    endif;
  endif;
  return $data;
}

function main_post_casts_with_image($credits){
  $data = '';
  if($credits && is_array($credits)):
    foreach ( $credits as $credit ) {
      if(isset($credit[ 'person' ]) && $credit[ 'person' ]):
        $title = get_the_title($credit[ 'person' ]);
        $data .= '<div class="credit-block">
        <div class="credit-img">'.(has_post_thumbnail($credit[ 'person' ]) ? '<img src="'.plugin_dir_url( __DIR__ ) . 'src/icons/no-image.svg" data-src="'.get_the_post_thumbnail_url($credit[ 'person' ], 'medium').'" alt="no-image" class="lazyload">' : '<img src="" data-src="'.plugin_dir_url( __DIR__ ) . 'src/icons/no-image.svg" alt="'.$title.'" class="lazyload">').'</div>
          <div class="credit-details">
            <div class="credit-name"><a href="'.get_permalink($credit[ 'person' ]).'" class="cast-name" title="'.$title.'">'.$title.'</a></div>
            <div class="credit-character">'.(isset($credit['department']) ? ($job ? (stripslashes( $credit[ $job ] ) ?? 'TBA') : (isset($credit[ clean_job_string($credit['department']).'_job' ]) ? stripslashes($credit[ clean_job_string($credit['department']).'_job' ]) : 'TBA' )) : (isset($credit['character']) && $credit['character'] ? stripslashes($credit['character']) : 'TBA')).'</div>
          </div>
        </div>';
    endif;  
    }
  endif;
  return $data;
}

function tag_post_template($post_id){
  $title = get_the_title($post_id);
  $permalink = get_permalink($post_id);
  $author_id = get_post_field ('post_author', $post_id);
  $author_url = get_author_posts_url($author_id);
  $author_name = get_the_author_meta( 'display_name', $author_id );
  $data = '<article>';
    $data .= '<a href="'.$permalink.'" class="post-image" title="'.$title.'">';
      $data .= (has_post_thumbnail($post_id) ? '<img src="'.plugin_dir_url( __DIR__ ) . 'src/icons/no-image.svg" data-src="'.get_the_post_thumbnail_url($post_id, 'medium').'" alt="no-image" class="lazyload">' : '<img src="" data-src="'.plugin_dir_url( __DIR__ ) . 'src/icons/no-image.svg" alt="'.$title.'" class="lazyload">');
    $data .= '</a>';
    
    $data .= '<div class="post-content">';
      $data .= '<div class="post-categories"></div>';
      $data .= '<h3 class="post-title"><a href="'.$permalink.'"title="'.$title.'">'.$title.'</a></h3>';
      $data .= '<p class="excerpt">'.wp_trim_words(wp_strip_all_tags(get_the_content('', false, $post_id)), 30).'</p>';
      $data .= '<div class="meta"><span>By <a href="'.$author_url.'"title="'.$author_name.'">'.$author_name.'</a></span>'.get_the_date( 'F d, Y', $post_id ).'<span></span></div>';
    $data .= '</div>';
  $data .= '</article>';

  return $data;
}