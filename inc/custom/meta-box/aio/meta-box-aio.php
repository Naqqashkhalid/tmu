<?php

defined( 'ABSPATH' ) || die;
update_option('meta_box_updater', ['status' => 'active', 'api_key' => '*****************']);
define( 'MBAIO_DIR', __DIR__ );

require __DIR__ . '/src/Loader.php';
require __DIR__ . '/src/Settings.php';
require __DIR__ . '/vendor/meta-box/dependency/MetaBox.php';
require __DIR__ . '/vendor/meta-box/dependency/Plugins.php';

new MBAIO\Loader;
new MBAIO\Settings;

new MetaBox\Dependency\MetaBox( 'Meta Box AIO', [
	// Translators: %1$s - the plugin name, %2$s - Meta Box, %3$s - action.
	'message'  => __( '%1$s requires %2$s to function correctly. %3$s.', 'meta-box-aio' ),
	'install'  => __( 'Install now', 'meta-box-aio' ),
	'activate' => __( 'Activate now', 'meta-box-aio' ),
] );
