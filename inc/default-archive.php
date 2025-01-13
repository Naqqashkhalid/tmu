<?php
/**
 * The template for displaying Archive pages.
 *
 * @package GeneratePress
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

get_header(); ?>

  <link rel="stylesheet" type="text/css" href="<?= plugin_dir_url( __DIR__ ) ?>src/css/default-archive.css">
  <div <?php generate_do_attr( 'content' ); ?>>
    <main <?php generate_do_attr( 'main' ); ?>>
      <?php
      /**
       * generate_before_main_content hook.
       *
       * @since 0.1
       */
      do_action( 'generate_before_main_content' );

      if ( generate_has_default_loop() ) {
        if ( have_posts() ) :

          /**
           * generate_archive_title hook.
           *
           * @since 0.1
           *
           * @hooked generate_archive_title - 10
           */
          do_action( 'generate_archive_title' );

          /**
           * generate_before_loop hook.
           *
           * @since 3.1.0
           */
          do_action( 'generate_before_loop', 'archive' );

          $schema = '<script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "ItemList",
    "itemListElement": [';
          $count = 0;
          ?>

          <div class="archive-blog">
            <?php while ( have_posts() ) :
                the_post();
                $post_id = get_the_ID();
                $permalink = get_permalink($post_id);
                $title = get_the_title($post_id);
                $image_attributes = has_post_thumbnail($post_id) ? wp_get_attachment_image_src( get_post_thumbnail_id($post_id), 'full' ) : ''; ?>
                <a class="single-post" href="<?= $permalink ?>" title="<?= $title ?>">
                  <div class="post-thumb"><img <?= ($image_attributes ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/icons/silver-back-land.svg" data-src="'.$image_attributes[0].'" class="lazyload" width="'.$image_attributes[1].'" height="'.$image_attributes[2].'"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/icons/silver-back-land.svg" width="100%" height="100%"') ) ?> alt="<?= $title ?>"></div>
                  <h2><?= $title ?></h2>
                </a>
            <?php
            $schema .= ($count !== 0 ? ',
' : '' ).'{
        "@type": "ListItem",
        "position": "'.($count+1).'",
        "url": "'.$permalink.'"
      }';
        $count++;
            endwhile; ?>
          </div>
          <?php

          $schema .= ']
  }
</script>';
          echo $schema;

          /**
           * generate_after_loop hook.
           *
           * @since 2.3
           */
          do_action( 'generate_after_loop', 'archive' );

        else :

          generate_do_template_part( 'none' );

        endif;
      }

      /**
       * generate_after_main_content hook.
       *
       * @since 0.1
       */
      do_action( 'generate_after_main_content' );
      ?>
    </main>
  </div>

  <?php
  /**
   * generate_after_primary_content_area hook.
   *
   * @since 2.0
   */
  do_action( 'generate_after_primary_content_area' );

  generate_construct_sidebars();

  get_footer();
