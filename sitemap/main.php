<?php

add_action('parse_request', 'sitemap');

function sitemap() {
	$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	$segments = explode('/', $uri);
	if(isset($segments[1]) && is_sitemap($segments[1])):
		$data = substr($segments[1], 8, -4);
		$parts = explode('-', $data);
		$total_parts = count($parts);
		$post_type = '';
		if ($total_parts >= 1 && $segments[1] !== 'sitemap.xml') {
			$tax = $total_parts === 1 && $parts[0] !== 'news' ? $parts[0] : '';
			$page_no = $post_type ? (int)$parts[1] : '';
			$is_taxonomies = $parts[0] === 'taxonomies';
			$is_news = $parts[0] === 'news';
			if ($total_parts === 3 && $parts[0] === 'drama' && $parts[1] === 'episode') {
				$post_type = 'drama-episode';
				$page_no = (int)$parts[2];
			} else if($parts[0] === 'by' && $parts[1] === 'year') {
				$tax = $parts[0].'-'.$parts[1];
				if($total_parts === 3) $page_no = (int)$parts[2];
			} else {
				$post_type = $total_parts === 2 ? $parts[0] : '';
			}
			require_once __DIR__ . '/sitemap-template.php';
			sitemap_template($tax, $post_type, $page_no, $is_taxonomies, $is_news);
		} else {
			require_once __DIR__ . '/sitemap_types.php';
		}
		exit;
	endif;
}

function is_sitemap($string) {
    return strpos($string, 'sitemap') === 0 && strrpos($string, '.xml') === strlen($string) - 4;
}