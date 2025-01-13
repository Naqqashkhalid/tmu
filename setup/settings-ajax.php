<?php

add_action( 'wp_ajax_tmu_settings', 'tmu_settings_handler' );
add_action( 'wp_ajax_nopriv_tmu_settings', 'tmu_settings_handler' );

function tmu_settings_handler(){
  $option = $_POST[ 'option' ];
  $value = $_POST[ 'value' ];
  update_option( $option, $value );
  echo $option.': '.$value;

	exit;
}