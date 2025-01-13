<?php
  if ( ! defined( 'ABSPATH' ) ) { exit; }
  $post_type = get_post_type();
  $site_url = get_site_url();
  $permalink = $site_url.$_SERVER['REQUEST_URI'];
  
  $latest = latest_episodes('latest', 0);
  $upcoming = latest_episodes('upcoming', 12);

  $schema = default_schema(ucfirst($post_type), $permalink).'<script type="application/ld+json">{ "@context": "https://schema.org", "@type": "ItemList", "itemListElement": ['.$latest['schema'].$upcoming['schema'].']}</script>';
  add_action('wp_head', function() use ($post_type, $permalink,$schema) { echo default_schema(ucfirst($post_type), $permalink).$schema; });
  get_header();

  $data = '<link rel="stylesheet" href="'.plugin_dir_url( __DIR__ ).'src/css/archive-tv-series.css">';
  $data .= '<div  class="content-area" id="primary">';
    $data .= '<main  class="site-main" id="main">';
      $data .= '<section>';
        $data .= '<div class="archive-header">';
          $data .= '<ul class="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">';
            $data .= '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a itemprop="item" href="'.$site_url.'" title="Home"><span itemprop="name">Home</span></a><meta itemprop="position" content="1" /></li>';
            $data .= '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"> / <meta itemprop="item" content="'.$permalink.'" /><span itemprop="name">'.ucfirst($post_type).'</span><meta itemprop="position" content="2" /></li>';
          $data .= '</ul>';
          $data .= '<h1>TV Series Epidoses</h1>';
          
          $data .= '<div class="block-seperate">';
            $data .= $latest['data'];
          $data .= '</div>';
          
          $data .= '<div class="block-seperate">';
            $data .= $upcoming['data'];
          $data .= '</div>';
        
        $data .= '</div>';
      $data .= '</section>';
    $data .= '</main>';
  $data .= '</div>';

  echo $data;
  do_action( 'generate_after_primary_content_area' );
  generate_construct_sidebars();
  get_footer();