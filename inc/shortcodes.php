<?php

add_shortcode( 'green_entertainment_list', 'get_green_entertainment_list' );


function get_green_entertainment_list($atts){
	global $wpdb;
  	$section = $atts['type'] == 'movie' ? 'movies' : ($atts['type'] == 'tv' ? 'tv_series' : '');
	$table_name = $section ? $wpdb->prefix.'tmu_'.$section : '';
	$qty = $atts['qty'] ? $atts['qty'] : 5;
	if ($table_name) {

		if ($atts['order'] == 'upcoming') {
			$results = $wpdb->get_results("SELECT `ID`,`release_date`,`original_title` FROM $table_name WHERE unix_timestamp(`release_date`)>unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) ORDER BY `release_date` DESC LIMIT $qty");
		} else {
			$results = $wpdb->get_results("SELECT `ID`,`release_date`,`original_title` FROM $table_name WHERE unix_timestamp(`release_date`)<unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) ORDER BY `release_date` DESC LIMIT $qty");
		}

		if ($results) {
			?><div class="green-item-list"><?php
			foreach ($results as $result) {
				$title = get_the_title($result->ID); ?>
	  			<a class="green-item-box" href="<?= get_permalink($result->ID) ?>" title="<?= $title ?>>">
		  			<div class="green-item-poster">
		  				<img src="<?= has_post_thumbnail($result->ID) ? get_the_post_thumbnail_url($result->ID, 'full') : plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif' ?>" alt="<?= $result->original_title ?>" width="100%" height="100%">
		  			</div>
		  			<div class="green-item-details">
		  				<h3><?= $title ?></h3>
		  				<p class="release-date">RELEASED ON: <?= date( 'd M Y', strtotime($result->release_date) ) ?></p>
		  			</div>
		  		</a>
	  		<?php
		  	}
		  	?></div><?php
		}
	}
}