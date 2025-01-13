<?php

function sitemap_template($tax, $post_type, $page, $is_taxonomies, $is_news){
header('Content-Type: application/xml; charset=utf-8'); ?>
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" <?= $is_news ? 'xmlns:news="http://www.google.com/schemas/sitemap-news/0.9"' : '' ?> xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"  <?= $post_type === 'video' ? 'xmlns:video="http://www.google.com/schemas/sitemap-video/1.1"' : '' ?>>

	<?php
	global $wpdb;
	if ($post_type) {
		$robots = $wpdb->get_var("SELECT robots FROM {$wpdb->prefix}tmu_seo_options WHERE post_type = '{$post_type}' AND section='single'");
		if ($robots === 'index, follow') {
			$offset = $page > 1 ? 'OFFSET '.(($page-1) * 10000) : '';
			$posts = $wpdb->get_results("SELECT ID,post_name,post_modified,post_content FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = '{$post_type}' LIMIT 10000 $offset");
			foreach ($posts as $post) {
			    $permalink = get_permalink($post->ID);
			    if (!str_contains($permalink, '?')) {
					$postdate = explode(" ", $post->post_modified);
				    $modified = '<lastmod>'.$postdate[0].'T'.$postdate[1].'+00:00</lastmod>';

					$image = ''; $images_urls = [];
					if(has_post_thumbnail($post->ID)) $image = '<image:image><image:loc>'.get_the_post_thumbnail_url($post->ID).'</image:loc><image:title>'.get_the_title($post->ID).'</image:title></image:image>';
				    if($post->post_content) preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', $post->post_content, $images_urls);
				    $images_urls = $images_urls[1] ?? [];
				    if (in_array($post_type, ['drama', 'movie', 'tv', 'people'])) {
						$table_name = $wpdb->prefix.'tmu_'.($post_type === 'movie' ? 'movies' : ($post_type === 'drama' ? 'dramas' : ($post_type === 'tv' ? 'tv_series' : $post_type)));
						$search = $post_type === 'people' ? 'photos' : 'images';
						$images_ids = $wpdb->get_var("SELECT $search FROM $table_name WHERE ID=$post->ID");
						$images_ids = $images_ids ? unserialize($images_ids) : '';
						if ($images_ids) foreach ($images_ids as $image_id) { $img_url = wp_get_attachment_image_url( $image_id, 'full' ); if($img_url) { $images_urls[] = $img_url; } }
					}
			    ?>
	
	<url>
	    <loc><?= $permalink ?></loc>
	    <?= $modified ?>

	    <?= $image ?>
	    
	    <?php if($images_urls) foreach ($images_urls as $image_url) echo '<image:image><image:loc>'.$image_url.'</image:loc></image:image>'; ?>
	
	</url>

				<?php }
			}
		}
	} elseif ($tax) {
		$robots = $tax ? $wpdb->get_var("SELECT robots FROM {$wpdb->prefix}tmu_seo_options WHERE post_type = '{$tax}' AND section='taxonomy'") : '';
		if ($robots === 'index, follow') {
			$terms = get_terms([ 'taxonomy' => $tax, 'hide_empty' => true ]);
			foreach ($terms as $term) { ?>
	<url>
	    <loc><?= get_term_link( $term ) ?></loc>
	</url>
			<?php }
		}
	} elseif($is_news) {
	  $news = $wpdb->get_col("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'article_type' AND  meta_value = 'NewsArticle' ORDER BY post_id DESC LIMIT 30");
	  if ($news) {
		foreach ($news as $post_id) { $title = get_the_title($post_id); ?>
	<url>
	  <loc><?= get_permalink($post_id) ?></loc>
	  <news:news>
	    <news:publication>
	      <news:name><?= $title ?></news:name>
	      <news:language>en</news:language>
	    </news:publication>
	    <news:publication_date><?= get_the_date( 'Y-m-d', $post_id ) ?></news:publication_date>
	    <news:title><?= $title ?></news:title>
	  </news:news>
	 </url>
	  <?php }
	  }
	}

	if ($is_taxonomies) {
		$taxonomies = $wpdb->get_col("SELECT name FROM {$wpdb->prefix}tmu_seo_options WHERE post_type = 'taxonomy' AND section='archive' AND robots='index, follow'");
		if ($taxonomies) {
			foreach ($taxonomies as $taxonomy) { ?>
	<url>
	    <loc><?= get_site_url().'/'.substr($taxonomy, 4).'/' ?></loc>
	</url>
			<?php }
		}
	} ?>
</urlset>
<?php }

// $terms = get_terms([ 'taxonomy' => $tax, 'hide_empty' => true ]);