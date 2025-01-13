<?php
/**
 * The Template for displaying all single posts.
 *
 * @package GeneratePress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$post_type = get_post_type();
$sidebar = ($post_type === 'movie' || $post_type === 'drama' || $post_type === 'drama-episode' || $post_type === 'tv' || $post_type === 'people' || $post_type === 'episode' || $post_type === 'post') ? true : false;

get_header();


if ($sidebar) styles_sidebar($post_type); ?>

	<div <?php generate_do_attr( 'content' ); echo (($post_type == 'video' && is_singular()) ? 'style="width:100%!important"' : '') ?>>
		<main class="site-main <?= $sidebar ? 'main-with-sidebar' : '' ?>" id="main">
			<?php if ($post_type == 'video') { $video = rwmb_meta( 'video_data' ); if($video && function_exists('ytplayer')) echo ytplayer(extractYouTubeId($video['source'])); } ?>
			<div class="content-wrapper">
				<?php
				/**
				 * generate_before_main_content hook.
				 *
				 * @since 0.1
				 */
				do_action( 'generate_before_main_content' );

				if ( generate_has_default_loop() ) {
					while ( have_posts() ) :

						the_post();

						single_template_generate();

						do_action( 'green_after_do_template_part', 'single' );

					endwhile;
				}

				/**
				 * generate_after_main_content hook.
				 *
				 * @since 0.1
				 */
				do_action( 'generate_after_main_content' );
				?>
			</div>
			
			<?php if ($sidebar) { ?>
				<div class="sidebar <?= $post_type === 'people' ? '' : 'item-sidebar' ?>"><div class ="sidebar-cont">
				<?php
					global $wpdb;
					$post_id = get_the_ID();
					if ($post_type === 'people') {
						$profession = rwmb_get_value( 'profession', '', get_the_ID() );
						$table_name = $wpdb->prefix.'tmu_people';
						$ppp = 15;
						
						$today_month = date('m');
						$today_day = date('d');
						// get today's birthdays
						$results1 = $wpdb->get_results("SELECT t.`ID`, t.`date_of_birth` FROM $table_name t JOIN {$wpdb->prefix}posts AS posts ON (t.ID = posts.ID) WHERE MONTH(t.date_of_birth) = '$today_month' AND DAY(t.date_of_birth) = '$today_day' AND NOT (t.`ID` = $post_id) AND dead_on IS NULL AND posts.post_status = 'publish' LIMIT $ppp");
						$total_count1 = $wpdb->get_var("SELECT COUNT(*) FROM $table_name t JOIN {$wpdb->prefix}posts AS posts ON (t.ID = posts.ID) WHERE MONTH(t.date_of_birth) = '$today_month' AND DAY(t.date_of_birth) = '$today_day' AND t.dead_on IS NULL AND posts.post_status = 'publish'");
						$total_pages1 = ceil($total_count1/$ppp);

						if ($results1) {
							?>
							<div class="heading"><h3>Born Today</h3></div>
							<div class="all_items" id="list-1">
							<?php
							foreach ($results1 as $result) {
								$title = get_the_title($result->ID);
								if($title):
							        $permalink = get_permalink($result->ID);
							        ?>
							            <div class="circle-box">
							                <a class="person-poster" href="<?= $permalink ?>" title="<?= $title ?>">
							                	<img <?= (has_post_thumbnail($result->ID) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url($result->ID, 'full').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ) ?> alt="<?= $title ?>" width="100%" height="100%">
							                </a>
							                <div class="person-details">
							                    <h3><a href="<?= $permalink ?>" title="<?= $title ?>"><?= $title ?></a></h3>
							                    <div class="profession"><?php rwmb_the_value( 'profession', '', $result->ID ); ?></div>
							                </div>
							            </div>
							        <?php
							    endif;
							}
							?>
							</div>
							<?php if ($total_pages1 != 1): ?>
								<div class="button loadmore" data-type="<?= $post_type ?>" data-term="born-today" data-page="1" data-ppp="<?= $ppp ?>" data-post="<?= $post_id ?>" data-for="list-1" data-total="<?= $total_pages1 ?>">Load More</div>
							<?php
							endif;
						}

						$results2 = $profession ? $wpdb->get_results("SELECT * FROM $table_name t JOIN {$wpdb->prefix}posts AS posts ON (t.ID = posts.ID) WHERE t.`profession`='{$profession}' AND t.`ID` != $post_id AND posts.post_status = 'publish' ORDER BY t.no_movies DESC LIMIT $ppp") : '';
						$total_count2 = $profession ? $wpdb->get_var("SELECT COUNT(*) FROM $table_name t JOIN {$wpdb->prefix}posts AS posts ON (t.ID = posts.ID) WHERE t.`profession`='{$profession}' AND t.`ID` != $post_id AND posts.post_status = 'publish'") : 0;
						$total_pages2 = ceil($total_count2/$ppp);

						if ($results2) {
							?>
							<div class="heading"><h3>Celebs In Spotlight</h3></div>
							<div class="all_items" id="list-2">
							<?php
							foreach ($results2 as $result) {
								$title = get_the_title($result->ID);
						        $permalink = get_permalink($result->ID);
						        ?>
						            <div class="circle-box">
						                <a class="person-poster" href="<?= $permalink ?>" title="<?= $title ?>">
						                    <img <?= (has_post_thumbnail($result->ID) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url($result->ID, 'full').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ) ?> alt="<?= $title ?>" width="100%" height="100%">
						                </a>
						                <div class="person-details">
						                    <h3><a href="<?= $permalink ?>" title="<?= $title ?>"><?= $title ?></a></h3>
						                    <div class="profession"><?php rwmb_the_value( 'profession', '', $result->ID ); ?></div>
						                </div>
						            </div>
						        <?php
							}
							?>
							</div>
							<?php if ($total_pages2 != 1): ?>
								<div class="button loadmore" data-type="<?= $post_type ?>" data-term="profession" data-profession="<?= $profession ?>" data-page="1" data-ppp="<?= $ppp ?>" data-post="<?= $post_id ?>" data-for="list-2" data-total="<?= $total_pages2 ?>">Load More</div>
							<?php
							endif;
						}
					} elseif ($post_type === 'movie' || $post_type === 'tv' || $post_type === 'drama' ) {
						$table_name = $wpdb->prefix.(($post_type === 'movie') ? 'tmu_movies' : (($post_type === 'tv') ? 'tmu_tv_series' : 'tmu_dramas'));
						$tax_query = ''; $sort_query = ''; $ppp = 10;

						if ($post_type == 'movie') {
				            $sort_query = "ORDER BY t.release_timestamp ASC";
				        } elseif($post_type == 'tv') {
				            $tax_query .= "LEFT JOIN ( SELECT `tv_series`, MAX(`air_date`) AS last_air_date FROM {$table_name}_episodes WHERE unix_timestamp(`air_date`)<=unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) GROUP BY tv_series ) AS last_aired ON t.ID = last_aired.`tv_series`";
				            $sort_query = "ORDER BY last_aired.last_air_date DESC";
				        } else {
				            $tax_query .= "LEFT JOIN ( SELECT `dramas`, MAX(`air_date`) AS last_air_date FROM {$table_name}_episodes WHERE unix_timestamp(`air_date`)<=unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) GROUP BY dramas ) AS last_aired ON t.ID = last_aired.`dramas`";
				            $sort_query = "ORDER BY last_aired.last_air_date DESC";
				        }

				        $release_query = $post_type === 'drama' || $post_type === 'tv' ? "" : "AND release_timestamp>unix_timestamp(DATE_ADD(NOW(),interval 3 hour))";
						
						$results = $wpdb->get_col("SELECT t.ID FROM $table_name t $tax_query JOIN wp_posts AS posts ON (t.ID = posts.ID) WHERE 1=1 $release_query AND t.ID != $post_id AND posts.post_status = 'publish' GROUP BY t.ID $sort_query LIMIT $ppp");

						$total_count1 = $wpdb->get_col("SELECT t.ID FROM $table_name t $tax_query JOIN wp_posts AS posts ON (t.ID = posts.ID) WHERE 1=1 $release_query AND t.ID != $post_id AND posts.post_status = 'publish' GROUP BY t.ID");
						$total_pages1 = ceil(count($total_count1)/$ppp);

						

						if ( $results ) {
							?>
							<div class="heading"><h3><?= $post_type === 'movie' ? 'Upcoming Movies' : ($post_type === 'tv' ? 'TV Series' : 'Dramas') ?></h3></div>
							<div class="all_items" id="list-1">
							<?php
						    foreach ($results as $result) {
						        $permalink = get_permalink($result);
						        $title = get_the_title($result);
						        ?>
						        <div class="item-box">
					                <a class="item-poster" href="<?= $permalink ?>" title="<?= $title ?>">
					                	<img <?= (has_post_thumbnail($result) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url($result, 'full').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ) ?> alt="<?= $title ?>" width="100%" height="100%">
					                </a>
					                <div class="item-details">
					                    <h3><a href="<?= $permalink ?>" title="<?= $title ?>"><?= $title ?></a></h3>
					                    <div class="genres"><?= implode(', ', wp_get_object_terms( $result, 'genre', array('fields' => 'names') )); ?></div>
					                </div>
					            </div>
						        <?php
						    }
						    ?>
							</div>
							<?php if ($total_pages1 != 1): ?>
								<div class="button loadmore" data-type="<?= ($post_type === 'movie' ? 'movies' : ($post_type === 'tv' ? 'tv_series' : 'dramas')) ?>" data-term="recent" data-page="1" data-ppp="<?= $ppp ?>" data-post="<?= $post_id ?>" data-for="list-1" data-total="<?= $total_pages1 ?>">Load More</div>
						    <?php
							endif;
						}



						$genres = implode(',', wp_get_object_terms( get_the_ID(), 'genre', array('fields' => 'ids') ));
						$results2 = '';
						if ($genres) {
							$tax_query = " LEFT JOIN wp_term_relationships AS tt1 ON (t.ID = tt1.object_id)";
							$genre_query = " tt1.term_taxonomy_id IN (".$genres.")";

							$results2 = $genres ? $wpdb->get_col("SELECT t.ID FROM $table_name t JOIN {$wpdb->prefix}posts AS posts ON (t.ID = posts.ID) $tax_query WHERE $genre_query AND t.`ID` != $post_id AND t.release_timestamp<unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) AND posts.post_status = 'publish' GROUP BY t.ID ORDER BY t.`release_timestamp` DESC LIMIT $ppp") : '';

							$total_count2 = $wpdb->get_col("SELECT t.ID FROM $table_name t JOIN {$wpdb->prefix}posts AS posts ON (t.ID = posts.ID) $tax_query WHERE $genre_query AND t.`ID` != $post_id AND t.release_timestamp<unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) AND posts.post_status = 'publish' GROUP BY t.ID");
							$total_pages2 = ceil(count($total_count2)/$ppp);
						}
						
						if ( $results2 ) {
							?>
							<div class="heading"><h3>Related <?= $post_type === 'movie' ? 'movies' : ($post_type === 'tv' ? 'TV Series' : 'Dramas') ?></h3></div>
							<div class="all_items" id="list-2">
							<?php
						    foreach ($results2 as $result) {
						        $permalink = get_permalink($result);
						        $title = get_the_title($result);
						        ?>
						        <div class="item-box">
					                <a class="item-poster" href="<?= $permalink ?>" title="<?= $title ?>">
					                    <img <?= (has_post_thumbnail($result) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url($result, 'full').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ) ?> alt="<?= $title ?>" width="100%" height="100%">
					                </a>
					                <div class="item-details">
					                    <h3><a href="<?= $permalink ?>" title="<?= $title ?>"><?= $title ?></a></h3>
					                    <div class="genres"><?= implode(', ', wp_get_object_terms( $result, 'genre', array('fields' => 'names') )); ?></div>
					                </div>
					            </div>
						        <?php
						    }
						    ?>
							</div>
							<?php if ($total_pages2 != 1): ?>
								<div class="button loadmore" data-type="<?= (($post_type === 'movie') ? 'movies' : ($post_type === 'tv' ? 'tv_series' : 'dramas')) ?>" data-term="<?= $genres ?>" data-page="1" data-ppp="<?= $ppp ?>" data-post="<?= $post_id ?>" data-for="list-2" data-total="<?= $total_pages2 ?>">Load More</div>
						    <?php
							endif;
						}

						wp_reset_postdata();
					} elseif ($post_type === 'post' || $post_type === 'episode' || $post_type === 'drama-episode') {
						?><div class="side-title">Latest Articles</div><div class="sidebar-items"><?php
						$recent_posts = wp_get_recent_posts([ 'numberposts' => 10, 'post_status' => 'publish' ]);
						foreach( $recent_posts as $post_item ) : ?>
								<a href="<?= get_permalink($post_item['ID']) ?>" class="siebar-single" title="<?= $post_item['post_title'] ?>">
									<?= get_the_post_thumbnail($post_item['ID'], 'full'); ?>
									<p class="item-title-side"><?= $post_item['post_title'] ?></p>
								</a>
						<?php endforeach; ?>
						</div>
					<?php }
//                    elseif ($post_type === 'episode') {
//                        $table_name = $wpdb->prefix.'tmu_tv_series_episodes';
//                        $episode = $wpdb->get_row("SELECT t.tv_series,t.episode_no,t.season_no FROM $table_name t JOIN {$wpdb->prefix}posts AS posts ON (t.ID = posts.ID) WHERE t.`ID` = $post_id AND posts.post_status = 'publish'");
//                        $results = $wpdb->get_results("SELECT t.ID,t.episode_title FROM $table_name t JOIN {$wpdb->prefix}posts AS posts ON (t.ID = posts.ID) WHERE t.tv_series=$episode->tv_series AND t.season_no=$episode->season_no AND NOT (t.`ID` = $post_id) AND posts.post_status = 'publish' ORDER BY t.`air_date` DESC LIMIT 18");
//
//                        if ( $results ) {
//                            ?>
<!--                            <div class="heading"><h3>Related Episodes</h3></div>-->
<!--                            <div class="episode_items">-->
<!--                            --><?php
//                            foreach ($results as $result) {
//                                $permalink = get_permalink($result->ID);
//                                ?>
<!--                                <div class="episode-box">-->
<!--                                    <a class="episode-poster" href="--><?php //= $permalink ?><!--" title="--><?php //= $result->episode_title ?><!--">-->
<!--                                        <img --><?php //= (has_post_thumbnail($result->ID) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url($result->ID, 'full').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ) ?><!-- alt="--><?php //= $result->episode_title ?><!--" width="100%" height="100%">-->
<!--                                    </a>-->
<!--                                    <div class="episode-details">-->
<!--                                        <a href="--><?php //= $permalink ?><!--" class="item-title-side" title="--><?php //= $result->episode_title ?><!--">--><?php //= $result->episode_title ?><!--</a>-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                                --><?php
//                            }
//                            ?><!--</div>--><?php
//                        }
//                        wp_reset_postdata();
//                    } elseif ($post_type === 'drama-episode') {
//                        $table_name = $wpdb->prefix.'tmu_dramas_episodes';
//                        $episode = $wpdb->get_row("SELECT t.dramas,t.episode_no FROM $table_name t JOIN {$wpdb->prefix}posts AS posts ON (t.ID = posts.ID) WHERE t.`ID` = $post_id AND posts.post_status = 'publish'");
//                        $results = $wpdb->get_results("SELECT t.ID,t.episode_title FROM $table_name t JOIN {$wpdb->prefix}posts AS posts ON (t.ID = posts.ID) WHERE t.dramas=$episode->dramas AND NOT (t.`ID` = $post_id) AND posts.post_status = 'publish' ORDER BY t.`air_date` DESC LIMIT 18");
//
//                        if ( $results ) {
//                            ?>
<!--                            <div class="heading"><h3>Related Episodes</h3></div>-->
<!--                            <div class="episode_items">-->
<!--                            --><?php
//                            foreach ($results as $result) {
//                                $permalink = get_permalink($result->ID);
//                                $title = get_the_title($result->ID);
//                                ?>
<!--                                <div class="episode-box">-->
<!--                                    <a class="episode-poster" href="--><?php //= $permalink ?><!--" title="--><?php //= $title ?><!--">-->
<!--                                        <img --><?php //= (has_post_thumbnail($result->ID) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.get_the_post_thumbnail_url($result->ID, 'full').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ) ?><!-- alt="--><?php //= $title ?><!--" width="100%" height="100%">-->
<!--                                    </a>-->
<!--                                    <div class="episode-details">-->
<!--                                        <a href="--><?php //= $permalink ?><!--" class="item-title-side" title="--><?php //= $title ?><!--">--><?php //= $title ?><!--</a>-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                                --><?php
//                            }
//                            ?><!--</div>--><?php
//                        }
//                        wp_reset_postdata();
//                    }
                ?>
				</div></div>
			<?php }
			
			if($post_type == 'video'): ?><div class="video_sidebar"><?= related_videos(get_the_ID(), rwmb_meta( 'post_id' )) ?></div><?php endif; ?>
		</main>
	</div>

	<?php
	/**
	 * generate_after_primary_content_area hook.
	 *
	 * @since 2.0
	 */
	do_action( 'generate_after_primary_content_area' );

	if(!($post_type == 'video' && is_singular())){ generate_construct_sidebars(); }

	// if ($post_type == 'post') {
	// 	$content .= '<div class="article-bottom-options">';
	// 	$tags = get_the_tags($post_id);
	// 	if ($tags) {
	// 		$content .= '<div class="tags">';
	// 		foreach ($tags as $tag) { $content .= '<a href="'.get_tag_link($tag).'">'.$tag->name.'</a>'; }
	// 		$content .= '</div>';
	// 	}
	// 	$content .= share_icons_blog_post_bottom();
	// $content .= '</div>';
	// }

	get_footer();




function single_template_generate() {
?>
<article id="post-<?php the_ID(); ?>" class="<?= esc_attr( implode( ' ', array_diff( get_post_class( '', get_the_ID() ), array( 'hentry' ) ) ) ); ?>">
	<div class="inside-article">
		<?php
		/**
		 * generate_before_content hook.
		 *
		 * @since 0.1
		 *
		 * @hooked generate_featured_page_header_inside_single - 10
		 */
// 		do_action( 'generate_before_content' );  // hide featured image

		if ( generate_show_entry_header() ) :
			?>
			<header <?php generate_do_attr( 'entry-header' ); ?>>
				<?php
				/**
				 * generate_before_entry_title hook.
				 *
				 * @since 0.1
				 */
				do_action( 'generate_before_entry_title' );

				if ( generate_show_title() && !(is_singular( 'movie' ) || is_singular( 'post' ) || is_singular( 'drama' ) || is_singular( 'drama-episode' ) || is_singular( 'tv' ) || is_singular( 'season' ) || is_singular( 'people' ) || is_singular( 'video' ) || is_singular( 'episode' )) ) {
					$params = generate_get_the_title_parameters();

					the_title( $params['before'], $params['after'] );

				}

				/**
				 * generate_after_entry_title hook.
				 *
				 * @since 0.1
				 *
				 * @hooked generate_post_meta - 10
				 */
				if(!is_singular( 'post' )) do_action( 'generate_after_entry_title' );
				?>
			</header>
			<?php
		endif;

		/**
		 * generate_after_entry_header hook.
		 *
		 * @since 0.1
		 *
		 * @hooked generate_post_image - 10
		 */
		do_action( 'generate_after_entry_header' );
		?>

		<div class="entry-content">
			<?php
			the_content();

			wp_link_pages(
				array(
					'before' => '<div class="page-links">' . __( 'Pages:', 'generatepress' ),
					'after'  => '</div>',
				)
			);
			?>
		</div>

		<?php
		/**
		 * generate_after_entry_content hook.
		 *
		 * @since 0.1
		 *
		 * @hooked generate_footer_meta - 10
		 */
		// do_action( 'generate_after_entry_content' );

		/**
		 * generate_after_content hook.
		 *
		 * @since 0.1
		 */
		do_action( 'generate_after_content' );
		?>
	</div>
</article>
<?php
}




function styles_sidebar($post_type){
?>
<style type="text/css">
	.heading { position: relative; }
	.heading h3 { position: relative; margin-top: 20px; font-weight: 800; font-size: 20px; padding-left: 10px; }
	.heading h3:before { content: ""; width: 5px; height: 100%; background-color: #F97316; display: block; position: absolute; left: 0; }
	.main-with-sidebar { display: flex; flex-wrap: wrap; gap:2% }
	.content-wrapper { width: 70% }
	.sidebar { width: 26%; position: relative;}
    .sidebar-cont { position: sticky; top: 0;}
	.circle-box .person-poster { width:60px!important; height: 60px!important; padding: 0!important; margin:0!important }
	.circle-box { display: flex; align-items: center; width: 100%; gap: 10px; padding: 10px 0; border-bottom: 1px solid #ddd; }
	.circle-box .person-details { width: calc(100% - 70px); }
	.circle-box .person-details h3 { font-size: 17px!important; margin:0!important; font-weight:600 }
	.circle-box .person-details h3:hover, .circle-box .person-poster:hover ~ .person-details h3 { text-decoration: underline!important; }
	.circle-box .person-details h3 a { display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: black!important }
	.circle-box .profession { color:black; margin:0!important; font-size: 14px; }

	.item-box { display: block; position: relative; margin-bottom:20px; background: black; border-radius: 10px; overflow: hidden; }
	.item-poster { display: block; height: 0; width: 100%; padding-bottom: 140%; position: relative; }
	.item-poster img { position: absolute; top: 0; width: 100%; height: 100%; object-fit: cover; }
	.item-details { padding: 7px; }
	.item-details h3 { font-size: 16px; font-weight: 600; margin: 0; }
	.item-details h3 a, .item-details .genres { display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: white; }
	.item-details .genres { font-size: 10px }
	.episode-details h3 { font-size: 13px; font-weight: 400; margin-bottom: 5px }
	.episode-poster { display: block; height: 0; width: 100%; padding-bottom: 56.4%; position: relative; }
	.episode-poster img { position: absolute; top: 0; width: 100%; height: 100%; object-fit: cover; }
	.all_items { position: relative; }
	.item-sidebar .all_items { display: flex; flex-wrap: wrap; gap: 6%; }
	.item-sidebar .item-box { width: 47%; }
	.loadmore { cursor: pointer; background: #F97316; color: #FFFFFF; background-color: #F97316; padding: 11px 25px; text-align: center; text-transform: uppercase; letter-spacing: 0.84px; font-size: 14px; font-weight: 700; width: 100%; border-radius: 10px; }
	.loadmore:hover { color: #FFFFFF; background-color: #ea580c; }
	.loading {
	    position: absolute;
	    top: 0;
	    width: 100%;
	    height: 100%;
	    background: #ddddddb0;
	    z-index: 9;
	}

	.cls-1 { fill: #0c0c0f!important; }
	.cls-1:hover { fill: white; }

	.lds-grid,
	.lds-grid div {
	  box-sizing: border-box;
	}
	.lds-grid {
	  display: inline-block;
	  width: 80px;
	  height: 80px;
	  position: absolute;
	  top: 50%;
	  left: 50%;
	}
	.lds-grid div {
	  position: absolute;
	  width: 16px;
	  height: 16px;
	  border-radius: 50%;
	  background: currentColor;
	  animation: lds-grid 1.2s linear infinite;
	}
	.lds-grid div:nth-child(1) {
	  top: 8px;
	  left: 8px;
	  animation-delay: 0s;
	}
	.lds-grid div:nth-child(2) {
	  top: 8px;
	  left: 32px;
	  animation-delay: -0.4s;
	}
	.lds-grid div:nth-child(3) {
	  top: 8px;
	  left: 56px;
	  animation-delay: -0.8s;
	}
	.lds-grid div:nth-child(4) {
	  top: 32px;
	  left: 8px;
	  animation-delay: -0.4s;
	}
	.lds-grid div:nth-child(5) {
	  top: 32px;
	  left: 32px;
	  animation-delay: -0.8s;
	}
	.lds-grid div:nth-child(6) {
	  top: 32px;
	  left: 56px;
	  animation-delay: -1.2s;
	}
	.lds-grid div:nth-child(7) {
	  top: 56px;
	  left: 8px;
	  animation-delay: -0.8s;
	}
	.lds-grid div:nth-child(8) {
	  top: 56px;
	  left: 32px;
	  animation-delay: -1.2s;
	}
	.lds-grid div:nth-child(9) {
	  top: 56px;
	  left: 56px;
	  animation-delay: -1.6s;
	}
	@keyframes lds-grid {
	  0%, 100% {
	    opacity: 1;
	  }
	  50% {
	    opacity: 0.5;
	  }
	}

	@media (max-width: 1040px) {
		.content-wrapper { width: 100% }
		.sidebar { width:100%; margin: 0; }
		.item-sidebar { padding: 0 20px; }

	}
</style>
<?php
}