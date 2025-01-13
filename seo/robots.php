<?php

function meta_robots($post_type){
    global $wpdb;
    $table_name = $wpdb->prefix.'tmu_seo_options';
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $segments = explode('/', $uri);
    if(isset($segments[1]) && isset($segments[3]) && $segments[1] === 'drama' && ($segments[3] === 'episodes')) return $wpdb->get_var("SELECT robots FROM $table_name WHERE name = 'drama-episodes'");

    if (is_front_page()) return $wpdb->get_var("SELECT robots FROM $table_name WHERE name = 'homepage'");
    if (is_tax() || is_tag() || is_category()) return $wpdb->get_var("SELECT robots FROM $table_name WHERE post_type = '{$post_type}' AND section = 'taxonomy'");
    if (is_archive()) return $wpdb->get_var("SELECT robots FROM $table_name WHERE post_type = '{$post_type}' AND section = 'archive'");
    if (is_single() || is_page()) return $wpdb->get_var("SELECT robots FROM $table_name WHERE post_type = '{$post_type}' AND section = 'single'");
    if(isset($segments[1]) && (!isset($segments[2]) || !$segments[2])) {
        if(taxonomy_exists($segments[1]) && $segments[1]!='post_tag') {
            return $wpdb->get_var("SELECT robots FROM {$table_name} WHERE name = 'tax-{$segments[1]}'");
        }
    }
}