<?php
/**
 * Add title and meta tags to post header using PHP in WordPress
 */

function seo_meta_tags($title='') {
    remove_action( 'wp_head', '_wp_render_title_tag', 1 );
    // $options = get_theme_mods();
        
    $title = get_seo_title($title);
    $description = get_seo_description($title);
    $permalink = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $post_type = is_tax() || is_tag() || is_category() ? get_queried_object()->taxonomy : get_post_type();
    $keywords = meta_keywords($post_type);
    $image_url = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(),'full') : plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp';
    $robots = meta_robots($post_type);
    echo str_replace("max-image-preview:large", ($robots ?? 'noindex, nofollow').', max-image-preview:large, max-snippet:-1', ob_get_clean());
    ?>
    <title><?= $title ?></title>
    <meta name="description" content="<?= $description ?>" />
    <link rel="canonical" href="<?= $permalink ?>"/>
    <meta name="keywords" content="<?= $keywords ?>" />
    <meta property="og:type" content="<?= get_og_type($post_type) ?>" />
    <meta property="og:title" content="<?= $title ?>" />
    <meta property="og:description" content="<?= $description ?>" />
    <meta property="og:url" content="<?= $permalink ?>" />
    <meta property="og:site_name" content="<?= get_bloginfo('name') ?>" />
    <meta property="og:image" content="<?= $image_url ?>" />
    <meta property="og:image:secure_url" content="<?= $image_url ?>" />
    <?= get_post_image_details() ?>
    <meta name="twitter:card" content="summary_large_image"/>
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?= $title ?>" />
    <meta name="twitter:description" content="<?= $description ?>" />
    <meta name="twitter:image" content="<?= $image_url ?>" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <?php if($_SERVER['REQUEST_URI'] !== '/drama/' && $_SERVER['REQUEST_URI'] !== '/tv/' && $_SERVER['REQUEST_URI'] !== '/movie/' && $_SERVER['REQUEST_URI'] !== '/people/' && $_SERVER['REQUEST_URI'] !== '/video/' && $_SERVER['REQUEST_URI'] !== '/episode/' && $_SERVER['REQUEST_URI'] !== '/drama-episode/') { ?><link rel="alternate" type="application/rss+xml" title="<?= $title ?> &raquo; Feed" href="<?= $permalink ?>feed/" /> <?php } ?>
    <?php
}
if ( !is_plugin_active( 'seo-by-rank-math/rank-math.php' ) ) add_action('wp_head', 'seo_meta_tags', 1);


function get_og_type($post_type){
    if (is_front_page() || $post_type === 'page') return 'website';
    if (is_archive() || is_tax()) return 'article';
    if (is_single()) {
        if ($post_type === 'post') return 'article';
        if ($post_type === 'tv' || $post_type === 'drama' || $post_type === 'season') return 'video.tv_show';
        if ($post_type === 'movie') return 'video.movie';
        if ($post_type === 'episode' || $post_type === 'drama-episode') return 'video.episode';
        if ($post_type === 'video') return 'video.other';
        if ($post_type === 'people') return 'profile';
    }
    return 'website';
}

function get_post_image_details() {
    if (is_single()) {
        $image_id = get_post_thumbnail_id(get_the_ID());

        if ($image_id) {
            $image_src = wp_get_attachment_image_src($image_id, 'full');

            if ($image_src) {
                list($src, $width, $height) = $image_src;

                // Get image alt text
                $alt_text = trim(strip_tags(get_post_meta($image_id, '_wp_attachment_image_alt', true)));

                // Get image type (mime type)
                $image_type = get_post_mime_type($image_id);

                return '<meta property="og:image:width" content="'.$width.'" />
    <meta property="og:image:height" content="'.$height.'" />
    <meta property="og:image:alt" content="'.$alt_text.'" />
    <meta property="og:image:type" content="'.$image_type.'" />
';
            }
        }
    }
}