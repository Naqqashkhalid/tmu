<?php

function archive_taxonomy($slug){

  $title = ($slug==='by-year' ? 'Year' : ucfirst($slug)).'s';
  $site_url = get_site_url();
  $permalink = $site_url.$_SERVER['REQUEST_URI'];

  remove_action( 'wp_head', '_wp_render_title_tag', 1 );
  add_action('wp_head', function () use($title, $permalink, $slug) {meta_inf_archive($title); echo default_schema($title, $permalink, 'tax-'.$slug, true);});

  get_header(); ?>

  <div <?php generate_do_attr( 'content' ); ?> style="width: 100%;">
    <main <?php generate_do_attr( 'main' ); ?>>
      <?php do_action( 'generate_before_main_content' ); ?>
      <section>
        <ul class="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
          <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a itemprop="item" href="<?= $site_url ?>" title="Home"><span itemprop="name">Home</span></a><meta itemprop="position" content="1" /></li>
          <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"> / <meta itemprop="item" content="<?= $permalink ?>" /><span itemprop="name"><?= $title ?></span><meta itemprop="position" content="2" /></li>
        </ul>

        <h1>All <?= $title ?></h1>  
        <ul class="term-list">
          <?php
          $terms = get_terms( array( 'taxonomy' => $slug ) );

          if ($slug==='by-year') {
            $terms = array_reverse($terms);
          }

          if ( ! empty( $terms ) ) {
            foreach ( $terms as $term ) {
               $logo = wp_get_attachment_image_url(get_term_meta( $term->term_id, 'logo', true ), 'full');
              echo '<li><a href="' . get_term_link( $term ) . '" title="'.$term->name.'">'.($logo ? '<div class="image-container"><img src="'.$logo.'" class="term-logo" alt="'.$term->name.'" width="100%" height="100%"></div>' : '').'<p class="term-title">'. $term->name .(!$logo ? ' (' . $term->count . ')' : '').'</p></a></li>';
            }
          }
          ?>
        </ul>
      </section>
      <?= term_footer_links('', $slug, false) ?>
      <?php do_action( 'generate_after_main_content' ); ?>
    </main>
  </div>

  <?php

  do_action( 'generate_after_primary_content_area' );

  get_footer();

  wp_reset_postdata();

}


function meta_inf_archive($title){
  $permalink = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
  ?>
    <title>All <?= $title ?></title>
    <meta name="description" content="All <?= $title ?>" />
        <meta property="og:url"           content="<?= $permalink ?>" />
        <meta property="og:type"          content="website" />
        <meta property="og:title"         content="All <?= $title ?>" />
        <meta property="og:description"   content="All <?= $title ?>" />
        <meta name="robots" content="follow, index, max-snippet:-1, max-image-preview:-1, max-image-preview:large"/>
    <link rel="canonical" href="<?= $permalink ?>" />
    <link rel="stylesheet" href="<?= plugin_dir_url( __DIR__ ) ?>src/css/tax.css">
<?php }