<?php
/**
 * Birthdays Today
 *
 * @package green_entertainment
 */

function birthday_today(){
	global $wpdb;
    $table_name = $wpdb->prefix.'tmu_people';

    $today_month = date('m');
	$today_day = date('d');
	$data = '';
	// get today's birthdays
	$results = $wpdb->get_results("SELECT t.`ID`, t.`date_of_birth` FROM $table_name t JOIN {$wpdb->prefix}posts AS posts ON (t.ID = posts.ID) WHERE MONTH(t.date_of_birth) = '$today_month' AND DAY(t.date_of_birth) = '$today_day' AND t.profession='Acting' AND (t.dead_on IS NULL OR t.dead_on = '') AND posts.post_status = 'publish'");

	if ($results) {
		foreach ($results as $result) {
			$title = get_the_title($result->ID);
			if ($title) {
				$permalink = get_permalink($result->ID);

				// $movies_cast_table = $wpdb->prefix.'tmu_movies_cast';
				// $movies_crew_table = $wpdb->prefix.'tmu_movies_crew';
				// $tv_series_cast_table = $wpdb->prefix.'tmu_tv_series_cast';
				// $tv_series_crew_table = $wpdb->prefix.'tmu_tv_series_crew';

				// $tv_movie = $wpdb->get_var($wpdb->prepare("SELECT movie FROM $movies_cast_table WHERE `person`=%d ORDER BY `release_year` DESC", $result->ID));
				// $tv_movie = $tv_movie ? $tv_movie : $wpdb->get_var($wpdb->prepare("SELECT tv_series FROM $tv_series_cast_table WHERE `person`=%d ORDER BY `release_year` DESC", $result->ID));
				// $tv_movie = $tv_movie ? $tv_movie : $wpdb->get_var($wpdb->prepare("SELECT movie FROM $movies_crew_table WHERE `person` = %d ORDER BY `release_year` DESC", $result->ID));
				// $tv_movie = $tv_movie ? $tv_movie : $wpdb->get_var($wpdb->prepare("SELECT tv_series FROM $tv_series_crew_table WHERE `person`=%d ORDER BY `release_year` DESC", $result->ID));
				// $year = $tv_movie ? implode(', ', wp_get_object_terms( $tv_movie, 'by-year', array('fields' => 'names') )) : '';

				// $movie = ($tv_movie ?  : '<a href="'.get_permalink($tv_movie).'" class="movie">'.get_the_title($tv_movie).($year ? ' ('.$year.')' : '').'</a>');

				$data .= '<div class="circle-box">
		                <a class="person-poster" href="<?= $permalink ?>">
		                	<img '.(has_post_thumbnail($result->ID) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/icons/no-image.svg" data-src="'.get_the_post_thumbnail_url($result->ID, 'full').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/icons/no-image.svg"') ).' alt="'.$title.'" width="100%" height="100%">
		                </a>
		                <div class="person-details">
		                    <h3><a href="'.$permalink.'">'.$title.'</a></h3>
		                    <div class="profession">'.rwmb_get_value( 'profession', '', $result->ID ).'</div>
		                    <p class="movie">'.calculate_age($result->date_of_birth).'</p>
		                </div>
		            </div>';
			}
		}
	}
	return $data;
}

// '('.calculate_age($result->date_of_birth).'Y)'

function birthday_today_homepage(){
	global $wpdb;
    $table_name = $wpdb->prefix.'tmu_people';

    $today_month = date('m');
	$today_day = date('d');
	// get today's birthdays
	$results = $wpdb->get_results("SELECT t.`ID`, t.`date_of_birth` FROM $table_name t JOIN {$wpdb->prefix}posts AS posts ON (t.ID = posts.ID) WHERE MONTH(t.date_of_birth) = '$today_month' AND DAY(t.date_of_birth) = '$today_day' AND t.profession='Acting' AND (t.dead_on IS NULL OR t.dead_on = '') AND posts.post_status = 'publish'");

	if ($results) { ?>
		<div class="scrollable-section" data-scroll-target="#today-birthday">
      		<div class="heading birthdays">
				 <h2>Born Today</h2><a href="<?= get_post_type_archive_link('people') ?>" class="born-today-view-button">View All</a><!--<div class="scroll-btns"><button class="scroll-btn scroll-today-birthday-left" data-direction="-1" onclick="scrollRelease(this)"><?php button_left() ?></button><button class="scroll-btn scroll-today-birthday-right" data-direction="1" onclick="scrollRelease(this)"><?php button_right() ?></button></div> -->
			</div>
			<div class="date-today">People Born on <?= date('d F') ?></div>
			<div class="today-birthday-flex scrollable-content birthdays-today" id="today-birthday">
    			<?php
				foreach ($results as $result) {
					$title = get_the_title($result->ID);
					if ($title):
				        $permalink = get_permalink($result->ID);
				        ?>
				            <a class="circle-box" href="<?= $permalink ?>">
				                <div class="person-poster">
				                	<img <?= (has_post_thumbnail($result->ID) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/icons/no-image.svg" data-src="'.get_the_post_thumbnail_url($result->ID, 'full').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/icons/no-image.svg"') ) ?> alt="<?= $title ?>" width="100%" height="100%">
				                </div>
				                <div class="person-details">
				                    <h3><?= $title ?></h3>
				                    <p class="person-age"><?= calculate_age($result->date_of_birth) ?></p>
				                </div>
				            </a>
				        <?php
				    endif;
				} ?>
			</div>
    	</div>
	<?php }
}