<?php header('Content-Type: application/xml; charset=utf-8'); ?>
<?xml version="1.0" encoding="UTF-8" ?> 
<sitemapindex xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"> 

<?php
global $wpdb;
$site_url = "https://{$_SERVER['SERVER_NAME']}";
$post_types = get_post_types( array( 'public' => true ) );
foreach ($post_types as $post_type) {
	$robots = $wpdb->get_var("SELECT robots FROM {$wpdb->prefix}tmu_seo_options WHERE post_type = '{$post_type}' AND section='single'");
	if ($robots === 'index, follow') {
	$total = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = '{$post_type}'");
	$pages = ceil($total/10000);
	for ($i=1; $i <= $pages; $i++) { ?>
	<sitemap> 
        <loc><?= $site_url ?>/sitemap-<?= $post_type ?>-<?= $i ?>.xml</loc>
        <lastmod><?= get_latest_post_modified_time_iso8601($post_type) ?></lastmod>
    </sitemap>
	<?php }
	}
	
}

$taxonomies = get_taxonomies(array('public' => true), 'objects');
foreach ($taxonomies as $tax => $taxonomy) { if($tax != 'post_format'){
  $robots = $wpdb->get_var("SELECT robots FROM {$wpdb->prefix}tmu_seo_options WHERE post_type = '{$tax}' AND section='taxonomy'");
  if ($robots === 'index, follow') { ?>
	<sitemap> 
        <loc><?= $site_url ?>/sitemap-<?= $tax ?>.xml</loc>
        <lastmod><?= get_latest_term_modified_time($tax) ?></lastmod>
    </sitemap>
  <?php }
} }

$news = $wpdb->get_var("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'article_type' AND  meta_value = 'NewsArticle' LIMIT 1");
if ($news) { ?>
	<sitemap> 
    	<loc><?= $site_url ?>/sitemap-news.xml</loc>
    	<lastmod><?= get_latest_post_modified_time_iso8601('post') ?></lastmod>
    </sitemap>
<?php }

$is_taxonomy = $wpdb->get_var("SELECT robots FROM {$wpdb->prefix}tmu_seo_options WHERE post_type = 'taxonomy' AND section='archive' AND robots='index, follow' LIMIT 1");
if ($is_taxonomy) { ?>
	<sitemap> 
    	<loc><?= $site_url ?>/sitemap-taxonomies.xml</loc>
    	<lastmod><?= get_latest_post_modified_time_iso8601() ?></lastmod>
    </sitemap>
<?php } ?>
</sitemapindex>


<?php

function get_latest_post_modified_time_iso8601($post_type=array('post', 'drama', 'movie', 'people', 'tv')) {
    $args = array(
    		'post_type' => $post_type,
        'posts_per_page' => 1,
        'orderby' => 'modified',
        'order' => 'DESC'
    );

    $latest_post = get_posts($args);

    if ($latest_post) {
        $post_id = $latest_post[0]->ID;
        $modified_time = get_post_modified_time('Y-m-d\TH:i:s+00:00', true, $post_id);
        return $modified_time;
    } else {
        return false;
    }
}


function get_latest_term_modified_time($taxonomy) {
    $terms = get_terms(array(
        'taxonomy' => $taxonomy,
        'hide_empty' => false
    ));

    $latest_modified_time = false;

    foreach ($terms as $term) {
        $args = array(
            'tax_query' => array(
                array(
                    'taxonomy' => $taxonomy,
                    'field'    => 'slug',
                    'terms'    => array($term->slug)
                )
            ),
            'post_type' => array('post', 'drama', 'movie', 'people', 'tv'),
            'posts_per_page' => 1,
            'orderby' => 'modified',
            'order' => 'DESC'
        );

        $latest_post = get_posts($args);

        if ($latest_post) {
            $post_modified_time = get_post_modified_time('Y-m-d\TH:i:s+00:00', true, $latest_post[0]->ID);
            if (!$latest_modified_time || $post_modified_time > $latest_modified_time) {
                $latest_modified_time = $post_modified_time;
            }
        }
    }

    return $latest_modified_time;
}