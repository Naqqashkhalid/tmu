<?php
/**
 * Plugin Name: TMU
 * Description: The Movie Database
 * Version:     1.0
 * Author:      Rashid ur Rehman
 * Author URI:  https://greentech.guru/
 * Licence:     MIT
 * License URI: http://opensource.org/licenses/MIT
 **/

define( 'green_dir_url', plugin_dir_url( __FILE__ ) );
require_once __DIR__ . '/inc/functions.php';
require_once __DIR__ . '/inc/comments.php';
require_once __DIR__ . '/api/update-data-api.php';
require_once __DIR__ . '/modules/includes.php';
require_once __DIR__ . '/setup/includes.php';
require_once __DIR__ . '/shortcodes/includes.php';
require_once __DIR__ . '/seo/includes.php';
if ( !is_plugin_active( 'seo-by-rank-math/rank-math.php' ) ) require_once __DIR__ . '/sitemap/main.php';


register_activation_hook(__FILE__, 'check_create_table');

