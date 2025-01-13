<?php

add_shortcode('schedule', 'dramas_schedule');
function dramas_schedule($atts=[]){

	// $attributes = shortcode_atts( [ 'title' => false ], $atts );

	// var_dump($atts['channel']);

	global $wpdb;
	$table_name = $wpdb->prefix.'tmu_dramas';
	$count = 0;
	$current_timestamp = time() + (148 * 60 * 60);
	$channel_query = '';
  	$tax_query = '';
  	$post_join = "JOIN {$wpdb->prefix}posts AS posts ON (drama.ID = posts.ID)";
	$where = "release_timestamp<$current_timestamp AND `finished`='0' AND posts.post_status = 'publish'";
	if (isset($atts['channel']) && $atts['channel']) {
		$term = get_term_by( 'slug', $atts['channel'], 'channel' );
		$term_id = $term && isset($term->term_id) ? $term->term_id : '';
		if ($term_id) {
	    $channel_query = "AND tt1.term_taxonomy_id IN (".$term_id.")";
	    $tax_query = "LEFT JOIN wp_term_relationships AS tt1 ON (drama.ID = tt1.object_id)";
		}
	}

	$monday = $wpdb->get_results("SELECT drama.`ID`,drama.`schedule_timestamp`,drama.`schedule_time`,drama.`average_rating`,drama.`vote_count` FROM $table_name as drama $post_join $tax_query WHERE $where $channel_query AND `schedule_day` LIKE '%Monday%' ORDER BY `schedule_timestamp` ASC");
	$tuesday = $wpdb->get_results("SELECT drama.`ID`,drama.`schedule_timestamp`,drama.`schedule_time`,drama.`average_rating`,drama.`vote_count` FROM $table_name as drama $post_join $tax_query WHERE $where $channel_query AND `schedule_day` LIKE '%Tuesday%' ORDER BY `schedule_timestamp` ASC");
	$wednesday = $wpdb->get_results("SELECT drama.`ID`,drama.`schedule_timestamp`,drama.`schedule_time`,drama.`average_rating`,drama.`vote_count` FROM $table_name as drama $post_join $tax_query WHERE $where $channel_query AND `schedule_day` LIKE '%Wednesday%' ORDER BY `schedule_timestamp` ASC");
	$thursday = $wpdb->get_results("SELECT drama.`ID`,drama.`schedule_timestamp`,drama.`schedule_time`,drama.`average_rating`,drama.`vote_count` FROM $table_name as drama $post_join $tax_query WHERE $where $channel_query AND `schedule_day` LIKE '%Thursday%' ORDER BY `schedule_timestamp` ASC");
	$friday = $wpdb->get_results("SELECT drama.`ID`,drama.`schedule_timestamp`,drama.`schedule_time`,drama.`average_rating`,drama.`vote_count` FROM $table_name as drama $post_join $tax_query WHERE $where $channel_query AND `schedule_day` LIKE '%Friday%' ORDER BY `schedule_timestamp` ASC");
	$saturday = $wpdb->get_results("SELECT drama.`ID`,drama.`schedule_timestamp`,drama.`schedule_time`,drama.`average_rating`,drama.`vote_count` FROM $table_name as drama $post_join $tax_query WHERE $where $channel_query AND `schedule_day` LIKE '%Saturday%' ORDER BY `schedule_timestamp` ASC");
	$sunday = $wpdb->get_results("SELECT drama.`ID`,drama.`schedule_timestamp`,drama.`schedule_time`,drama.`average_rating`,drama.`vote_count` FROM $table_name as drama $post_join $tax_query WHERE $where $channel_query AND `schedule_day` LIKE '%Sunday%' ORDER BY `schedule_timestamp` ASC");

	global $post;
	$heading = (is_home() ? 'h3' : 'h2');
	$today = date("l");

	$data = schedule_styles().'<div class="schedule_container"><div class="dramas_schedule">
		<div class="prev arrows"><i class="arrow left"></i></div>
		<div class="days">
			<div class="day '.($today=== 'Monday' ? 'active' : '').'" for="monday">Monday</div>
			<div class="day '.($today=== 'Tuesday' ? 'active' : '').'" for="tuesday">Tuesday</div>
			<div class="day '.($today=== 'Wednesday' ? 'active' : '').'" for="wednesday">Wednesday</div>
			<div class="day '.($today=== 'Thursday' ? 'active' : '').'" for="thursday">Thursday</div>
			<div class="day '.($today=== 'Friday' ? 'active' : '').'" for="friday">Friday</div>
			<div class="day '.($today=== 'Saturday' ? 'active' : '').'" for="saturday">Saturday</div>
			<div class="day '.($today=== 'Sunday' ? 'active' : '').'" for="sunday">Sunday</div>
		</div>
		<div class="next arrows"><i class="arrow right"></i></div>
	</div>';

	$schema = '<script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "ItemList",
    "itemListElement": [';

  $combined_array = array_merge($monday, $tuesday, $wednesday, $thursday, $friday, $saturday, $sunday);
  $all_results = array_unique($combined_array, SORT_REGULAR);
  if ($all_results) {
  	foreach ($all_results as $result) {
  		$schema .= ($count !== 0 ? ', ' : '').tv_item_list_schema($result, $count);
	  	$count++;
  	}
  }

	$data .= '<div class="archive-dramas" id="monday" '.($today=== 'Monday' ? '' : 'style="display: none;"').'>';
	if ($monday) {
		usort($monday, 'compare_schedule_timestamp');
		foreach ($monday as $result) {
	  		$data .= schedule_template($result, $heading);
	  	}
	}
	$data .= '</div>';

	$data .= '<div class="archive-dramas" id="tuesday" '.($today=== 'Tuesday' ? '' : 'style="display: none;"').'>';
	if ($tuesday) {
		usort($tuesday, 'compare_schedule_timestamp');
		foreach ($tuesday as $result) {
	  		$data .= schedule_template($result, $heading);
	  	}
	}
	$data .= '</div>';

	$data .= '<div class="archive-dramas" id="wednesday" '.($today=== 'Wednesday' ? '' : 'style="display: none;"').'>';
	if ($wednesday) {
		usort($wednesday, 'compare_schedule_timestamp');
		foreach ($wednesday as $result) {
	  		$data .= schedule_template($result, $heading);
	  	}
	}
	$data .= '</div>';

	$data .= '<div class="archive-dramas" id="thursday" '.($today=== 'Thursday' ? '' : 'style="display: none;"').'>';
	if ($thursday) {
		usort($thursday, 'compare_schedule_timestamp');
		foreach ($thursday as $result) {
	  		$data .= schedule_template($result, $heading);
	  	}
	}
	$data .= '</div>';

	$data .= '<div class="archive-dramas" id="friday" '.($today=== 'Friday' ? '' : 'style="display: none;"').'>';
	if ($friday) {
		usort($friday, 'compare_schedule_timestamp');
		foreach ($friday as $result) {
	  		$data .= schedule_template($result, $heading);
	  	}
	}
	$data .= '</div>';

	$data .= '<div class="archive-dramas" id="saturday" '.($today=== 'Saturday' ? '' : 'style="display: none;"').'>';
	if ($saturday) {
		usort($saturday, 'compare_schedule_timestamp');
		foreach ($saturday as $result) {
	  		$data .= schedule_template($result, $heading);
	  	}
	}
	$data .= '</div>';

	$data .= '<div class="archive-dramas" id="sunday" '.($today=== 'Sunday' ? '' : 'style="display: none;"').'>';
	if ($sunday) {
		usort($sunday, 'compare_schedule_timestamp');
		foreach ($sunday as $result) {
	  		$data .= schedule_template($result, $heading);
	  	}
	}
	$data .= '</div>';
	$data .= !is_home() ? term_footer_links('', 'country', $current_links=false) : '';
	$data .= '</div>';
	$schema .= ']
  }
</script>';

	return $data.'
<script>
const dayElements = document.querySelectorAll(".dramas_schedule .days .day");
const nextButton = document.querySelector(".dramas_schedule .next");
const prevButton = document.querySelector(".dramas_schedule .prev");

function activateDay(dayElement) {
  dayElements.forEach(day => day.classList.remove("active"));
  dayElement.classList.add("active");

  const targetDayId = dayElement.getAttribute("for");
  const dramaElements = document.querySelectorAll(".archive-dramas");
  dramaElements.forEach(drama => drama.style.display = "none");
  document.getElementById(targetDayId).style.display = "flex";
}

dayElements.forEach(day => {
  day.addEventListener("click", function() {
    activateDay(this);
  });
});

nextButton.addEventListener("click", () => {
  const activeDay = Array.from(dayElements).find(day => day.classList.contains("active"));
  const nextIndex = (Array.from(dayElements).indexOf(activeDay) + 1) % dayElements.length;
  activateDay(dayElements[nextIndex]);
});

prevButton.addEventListener("click", () => {
  const activeDay = Array.from(dayElements).find(day => day.classList.contains("active"));
  const prevIndex = (Array.from(dayElements).indexOf(activeDay) - 1 + dayElements.length) % dayElements.length;
  activateDay(dayElements[prevIndex]);
});
</script>'.$schema;
	
}


function schedule_styles(){
	return '
<style>
	h1 { text-align: center; }
	.dramas_schedule {
		display: flex;
		justify-content: center;
		margin: 40px 0;
		gap: 10px;
	}
	.dramas_schedule .days {
		display: flex;
		justify-content: center;
		gap: 10px;
	}
	.dramas_schedule .days .day {
    color: #ffffff;
  	background: #272727;
    font-size: 1.1em;
    font-weight: 500;
    line-height: 1.4em;
    padding: 8px 16px 8px 16px;
    cursor: pointer;
    text-transform: uppercase;
	}

	.dramas_schedule .days .day.active, .dramas_schedule .days .day:hover {
		color: #272727;
    background: #02c8f0;
	}

	.archive-dramas {
		display: flex;
		column-gap: 2%;
		row-gap: 15px;
	  flex-wrap: wrap;
	}
	.drama-box {
		display: block;
    position: relative;
    width: 18.4%;
    margin-bottom: 20px;
    text-align: center;
    box-shadow: 0 4px 8px #0000001a;
    transition: transform .3s ease-in-out;
    transform-style: preserve-3d;
    border-radius: 10px;
    border: 1px solid #ddd;
    overflow: hidden;
	}
	.drama-box:hover {
    transform: translateY(-5px) translateZ(20px) rotateX(5deg) rotateY(5deg);
    box-shadow: 0 10px 20px #0003, 0 0 15px #0000001a;
    border-radius: 10px;
	}
	.drama-poster {
		display: block;
    height: 0;
    width: 100%;
    padding-bottom: 140%;
    position: relative;
	}
	.drama-poster img {
		display: block;
		position: absolute;
    top: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
	}
	.drama-details { text-align: center; }
	.archive-dramas .drama-title {
    font-size: 17px;
    font-weight: bold;
    line-height: 1.25;
    color: #0c0c0f;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-align: center;
    margin-bottom: 0;
    margin-top: 5px;
	}
	.drama-details .schedule-time {
		letter-spacing: 0.65px;
    color: #ffffff;
    overflow: hidden;
    max-width: 100%;
    display: inline-block;
    background: #232323 !important;
    font-size: 13px;
    font-weight: bold;
    padding: 5px 5px;
    border-radius: 4px;
    margin-top: 5px;
    margin-bottom: 10px;
	}
	.arrows {
		display: none;
    align-items: center;
    background: #353535;
    color: white;
    border-color: white;
    padding: 5px 18px 5px 18px;
    height: 42px;
    margin-top: 3px;
	}
	.right {
    transform: rotate(-45deg);
    -webkit-transform: rotate(-45deg);
	}
	.left {
    transform: rotate(135deg);
    -webkit-transform: rotate(135deg);
	}
	.arrow {
    border: solid #fff;
    border-width: 0 2px 2px 0;
    display: inline-block;
    padding: 4px;
	}
	.next {
		border-radius: 0 5px 5px 0;
	}
	.prev {
		border-radius: 5px 0 0 5px;
	}

	.footer-links-container, .faqs { padding: 20px; background-color: #fff; margin-top: 20px }
	.footer-links-container h3 { font-weight: 800; font-size: 20px; }
	.footer-links { display: flex; column-gap: 2%; row-gap: 20px; flex-wrap: wrap; margin-top: 20px; }
	.footer-links a { display: block; width: 23.5%; font-size: 14px; font-weight: 600; }

	@media (max-width: 900px) {
		.arrows { display: flex; }
		.dramas_schedule .days { width: 100%; text-align: center; }
		.dramas_schedule .days .day:not(.active) { display: none; }
		.dramas_schedule .days .day.active { font-weight: 700; font-size: 1.5em; padding: 0 20px; width: 100%; }
		.day.active { color: #272727!important; background: #ffffff!important; border-bottom: 2px solid #272727!important; }
	}

	@media (max-width: 768px) {
		.drama-box { width: 49%; }
		.archive-dramas .drama-title { font-size: 14px; margin-bottom: 4px!important; }
		.schedule-time { font-size: 10px!important; padding: 5px 5px; margin-top: 5px!important; }
		.footer-links-container { padding:10px }
	  .footer-links-container h3 { font-size: 19px; }
	  .footer-links { row-gap: 5px; }
	  .footer-links a { width: 100%; }
	}

</style>
';
}

function schedule_template($result, $heading){
	$data = '<a class="drama-box" href="'.get_permalink($result->ID).'">';
		$data .= '<div class="drama-poster">';
			$data .= '<img '.(has_post_thumbnail($result->ID) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url($result->ID, 'full').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif"') ).' alt="'.get_the_title($result->ID).'" width="100%" height="100%">';
		$data .= '</div>';
		
		$data .= '<div class="drama-details">';
			$data .= '<'.$heading.' class="drama-title">'.get_the_title($result->ID).'</'.$heading.'>';
			$data .= '<p class="schedule-time">'.date( 'h:i A', $result->schedule_timestamp ).'</p>';
		$data .= '</div>';
	$data .= '</a>';

	return $data;
}

function compare_schedule_timestamp($a, $b) {
    return strtotime($a->schedule_time) - strtotime($b->schedule_time);
}

function tv_item_list_schema($result, $count){

	$tmdb_rating['average'] = $result->average_rating;
  $tmdb_rating['count'] = $result->vote_count;
  global $wpdb;
  $rating = $wpdb->get_row("SELECT
    d.dramas,
    AVG(e.avg_rating) AS average_drama_rating,
    SUM(e.comment_count) AS total_comments
FROM
    wp_tmu_dramas_episodes d
JOIN (
    SELECT
        comment_post_ID AS episode_id,
        AVG(IF(comment_rating > 0, comment_rating, NULL)) AS avg_rating,
        COUNT(IF(comment_rating > 0, 1, NULL)) AS comment_count
    FROM
        wp_comments
    GROUP BY
        comment_post_ID
) e ON d.id = e.episode_id
WHERE
    d.dramas = {$result->ID}
GROUP BY
    d.dramas;");

  if(isset($rating->average_drama_rating)) $tmdb_rating['average'] = ($tmdb_rating['average']+$rating->average_drama_rating)/2;
  if(isset($rating->total_comments)) $tmdb_rating['count'] += $rating->total_comments;
  $comments = get_comments(array('post_id' => $result->ID, 'status' => 'approve'));
  $average_ratings = get_average_ratings($comments, $tmdb_rating);
	return '{
        "@type": "ListItem",
        "position": "'.($count+1).'",
        "item": {
          "@type": "TVSeries",
          "url": "'.get_permalink($result->ID).'",
          "name": "'.get_the_title($result->ID).'",
          '.(has_post_thumbnail($result->ID) ? '"image": "'.get_the_post_thumbnail_url($result->ID).'",' : '').'
          "dateCreated": "'.get_the_date( 'Y-m-d', $result->ID ).'",
            "aggregateRating": {
            "@type": "AggregateRating",
            "bestRating": "10",
            "worstRating": "1",
            "ratingValue": "'.$average_ratings['average'].'",
            "ratingCount": "'.($average_ratings['count'] ? $average_ratings['count'] : 1).'"
          }
          }
      }';
}