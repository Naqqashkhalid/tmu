<?php
/**
 * Upcoming Posts of Current Year.
 *
 * @package green_entertainment
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function module_2($post_type=''){
	$current_year = date("Y"); $next_year = strtotime("1 January ".$current_year+1);
	global $wpdb;
	$table_name = $post_type ? $wpdb->prefix.'tmu_'.($post_type === 'movie' ? 'movies' : ($post_type === 'tv' ? 'tv_series' : 'dramas')) : '';
	
	if($post_type == 'tv') {
		$query = "SELECT t1.`ID`, t2.air_date_timestamp AS release_timestamp,t1.`credits` FROM $table_name t1 LEFT JOIN `{$table_name}_seasons` t2 ON t1.`ID` = t2.tv_series JOIN {$wpdb->prefix}posts AS posts ON (t1.ID = posts.ID) WHERE t2.air_date_timestamp > unix_timestamp(DATE_ADD(NOW(), INTERVAL 3 HOUR)) AND posts.post_status = 'publish' ORDER BY t2.air_date_timestamp ASC LIMIT 10";
	} else {
		$query = "SELECT t.ID, t.release_timestamp,t.credits FROM $table_name t JOIN {$wpdb->prefix}posts AS posts ON (t.ID = posts.ID) WHERE release_timestamp>unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) AND posts.post_status = 'publish' ORDER BY t.release_timestamp ASC LIMIT 10";
	}
	$results = $wpdb->get_results($query);

	if ($results) {
		?><div class="module_2">
			<div class="heading"><h2>Upcoming <?= ($post_type === 'tv' ? 'TV Series' : ($post_type === 'drama' ? 'Pakistani Dramas' : 'Movies')) ?> <?= $current_year ?></h2></div>
			<div class="table_scrollbar">
				<table cellpadding="1" cellspacing="1">
                	<tbody>
                		<tr><th class="head_reldate"><h3>Release Date</h3></th><th class="head_movie_name"><h3><?= ($post_type === 'tv' ? 'TV Show' : ($post_type === 'movie' ? 'Movie' : 'Drama')) ?> Name</h3></th><th class="head_cast"><h3>Cast</h3></th></tr>
					<?php
					  	foreach ($results as $result) { ?>
					  		<tr><td><?= date( 'd M Y', $result->release_timestamp ) ?></td><td><?= get_the_title($result->ID) ?></td><td><?= cast_with_limit($result->ID, 10, $post_type) ?></td></tr>
				  	<?php }  ?>
					</tbody>
				</table>
		  	</div>
		</div><?php
	}
}

function cast_with_limit($post_id, $limit, $post_type=''){
	global $wpdb;
	$post_type = $post_type ?? get_post_type($post_id);
	$table_name = $wpdb->prefix.'tmu_'.($post_type === 'tv' ? 'tv_series' : ($post_type === 'drama' ? 'dramas' : 'movies'));
	$column = $post_type === 'tv' ? 'tv_series' : ($post_type==='drama' ? 'dramas' : $post_type);
	$casts = $wpdb->get_col("SELECT cast.person FROM {$table_name}_cast cast JOIN {$wpdb->prefix}posts AS posts ON (cast.$column = posts.ID) WHERE cast.$column=$post_id AND posts.post_status = 'publish' LIMIT $limit");
	return $casts ? implode(', ', array_map(function($cast){
			return isset($cast) ? get_the_title($cast) : '';
		}, $casts)) : '';
}