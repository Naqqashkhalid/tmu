<?php

/* Custom API Data Updates Page */

// function custom_options_menu() {
//     add_menu_page(
//         'API Updates Options',
//         'API Updates',
//         'manage_options',
//         'tmdb-api-update',
//         'tmdb_api_update_page'
//     );
// }
// add_action('admin_menu', 'custom_options_menu');

function tmdb_api_update_page() {
	wp_register_script('api_update', plugin_dir_url( __DIR__ ) . 'src/js/api-update-ajax.js', array( 'jquery' ), 1.1, true);
    wp_enqueue_script( 'api_update' );
    wp_localize_script( 'api_update', 'api_update_params', array('ajaxurl' => admin_url( 'admin-ajax.php' )));

	$options = get_options( ['tmu_movies', 'tmu_tv_series', 'tmu_dramas'] );
	tmdb_page_styles();
	?>
    <div class="tmdb-api-updates-container wrap">
        <div class="api-sidebar">
        <?php if ($options['tmu_movies']==='on') { ?>
        	<div class="heading">Movie</div>
        	<div class="movie items">
        		<div class="item active" data-for="add-movie">Add Movie</div>
        		<div class="item" data-for="update-movie">Update Movie</div>
        		<div class="item" data-for="delete-movie">Delete Movie</div>
        	</div>
        	<?php } ?>
        	<?php if ($options['tmu_tv_series']==='on') { ?>
        	<div class="heading">TV Series</div>
        	<div class="tv items">
        		<div class="item <?= $options['tmu_movies']==='off' ? 'active' : '' ?>" data-for="add-tv">Add TV Series</div>
        		<div class="item" data-for="update-tv">Update TV Series</div>
        		<div class="item" data-for="delete-tv">Delete TV Series</div>
        	</div>
        	<?php } ?>
        	<?php if ($options['tmu_dramas']==='on') { ?>
        	<div class="heading">Drama</div>
        	<div class="drama items">
        		<div class="item  <?= $options['tmu_movies']==='off' && $options['tmu_tv_series']==='off' ? 'active' : '' ?>" data-for="add-drama">Add Drama</div>
        		<div class="item" data-for="update-drama">Update Drama</div>
        		<div class="item" data-for="delete-drama">Delete Drama</div>
        	</div>
        	<?php } ?>
        </div>
        <div class="api-content">
        	<!-- Movie -->
        	<?php if ($options['tmu_movies']==='on') { ?>
        	<div class="item-content" id="add-movie" style="display: block;">
        		<div class="heading">Add Movie</div>
        		<div class="form" id="add-movie-form">
        			<input type="text" id="add-movie-tmdb-id" name="tmdb_id" placeholder="TMDB ID">
        			<!-- <div class="checkboxes">
        				<label class="container">Credits
						  <input type="checkbox" id="add-movie-credits" name="credits" value="checked">
						  <span class="checkmark"></span>
						</label>
						<label class="container">Images
						  <input type="checkbox" id="add-movie-images" name="images" value="checked">
						  <span class="checkmark"></span>
						</label>
						<label class="container">Videos
						  <input type="checkbox" id="add-movie-videos" name="videos" value="checked">
						  <span class="checkmark"></span>
						</label>
        			</div> -->
        			<div class="button" data-type="movie" data-action="add">Add Movie</div>
        		</div>
        		<div class="processloading"><div class="loading-movie-add processing"></div></div>
        	</div>

        	<div class="item-content" id="update-movie" style="display: none;">
        		<div class="heading">Update Movie</div>
        		<div class="form" id="update-movie-form">
        			<input type="text" id="update-movie-post-id" name="post_id" placeholder="Post ID">
        			<input type="text" id="update-movie-tmdb-id" name="tmdb_id" placeholder="TMDB ID">
        			<div class="checkboxes">
        				<label class="container">Credits
						  <input type="checkbox" id="update-movie-credits" name="credits" value="checked">
						  <span class="checkmark"></span>
						</label>
						<label class="container">Images
						  <input type="checkbox" id="update-movie-images" name="images" value="checked">
						  <span class="checkmark"></span>
						</label>
						<label class="container">Videos
						  <input type="checkbox" id="update-movie-videos" name="videos" value="checked">
						  <span class="checkmark"></span>
						</label>
        			</div>
        			<div class="button" data-type="movie" data-action="update">Update Movie</div>
        		</div>
        		<div class="processloading"><div class="loading-movie-update processing"></div></div>
        	</div>

        	<div class="item-content" id="delete-movie" style="display: none;">
        		<div class="heading">Delete Movie</div>
        		<div class="form" id="delete-movie-form">
        			<input type="text" id="delete-movie-post-id" name="post_id" placeholder="Post ID">
        			<input type="text" id="delete-movie-tmdb-id" name="tmdb_id" placeholder="TMDB ID">
        			<div class="checkboxes">
        				<label class="container">Credits
						  <input type="checkbox" id="delete-movie-credits" name="credits" value="checked">
						  <span class="checkmark"></span>
						</label>
						<label class="container">Images
						  <input type="checkbox" id="delete-movie-images" name="images" value="checked">
						  <span class="checkmark"></span>
						</label>
						<label class="container">Videos
						  <input type="checkbox" id="delete-movie-videos" name="videos" value="checked">
						  <span class="checkmark"></span>
						</label>
        			</div>
        			<div class="button" data-type="movie" data-action="delete">Delete Movie</div>
        		</div>
        		<div class="processloading"><div class="loading-movie-delete processing"></div></div>
        	</div>
        	<?php } ?>

        	<!-- TV Series -->
        	<?php if ($options['tmu_tv_series']==='on') { ?>
        	<div class="item-content" id="add-tv" style="display: <?= $options['tmu_movies']==='off' ? 'block' : 'none' ?>;">
        		<div class="heading">Add TV Series</div>
        		<div class="form" id="add-tv-form">
        			<input type="text" id="add-tv-tmdb-id" name="tmdb_id" placeholder="TMDB ID">
        			<!-- <div class="checkboxes">
        				<label class="container">Credits
						  <input type="checkbox" id="add-tv-credits" name="credits" value="checked">
						  <span class="checkmark"></span>
						</label>
						<label class="container">Images
						  <input type="checkbox" id="add-tv-images" name="images" value="checked">
						  <span class="checkmark"></span>
						</label>
						<label class="container">Videos
						  <input type="checkbox" id="add-tv-videos" name="videos" value="checked">
						  <span class="checkmark"></span>
						</label>
						<label class="container">All Seasons
						  <input type="checkbox" id="add-tv-all-seasons" name="all_seasons" value="checked">
						  <span class="checkmark"></span>
						</label>
						<input type="text" class="season_no" id="add-tv-season-no" name="season_no" placeholder="Add season no">
        			</div> -->
        			<div class="button" data-type="tv" data-action="add">Add TV Series</div>
        		</div>
        		<div class="processloading"><div class="loading-tv-add processing"></div></div>
        	</div>

        	<div class="item-content" id="update-tv" style="display: none;">
        		<div class="heading">Update TV Series</div>
        		<div class="form" id="update-tv-form">
        			<input type="text" id="update-tv-post-id" name="post_id" placeholder="Post ID">
        			<input type="text" id="update-tv-tmdb-id" name="tmdb_id" placeholder="TMDB ID">
        			<div class="checkboxes">
        				<label class="container">Credits
						  <input type="checkbox" id="update-tv-credits" name="credits" value="checked">
						  <span class="checkmark"></span>
						</label>
						<label class="container">Images
						  <input type="checkbox" id="update-tv-images" name="images" value="checked">
						  <span class="checkmark"></span>
						</label>
						<label class="container">Videos
						  <input type="checkbox" id="update-tv-videos" name="videos" value="checked">
						  <span class="checkmark"></span>
						</label>
						<label class="container">All Seasons
						  <input type="checkbox" id="update-tv-all-seasons" name="all_seasons" value="checked">
						  <span class="checkmark"></span>
						</label>
						<input type="text" class="season_no" id="update-tv-season-no" name="season_no" placeholder="Add season no">
        			</div>
        			<div class="button" data-type="tv" data-action="update">Update TV Series</div>
        		</div>
        		<div class="processloading"><div class="loading-tv-update processing"></div></div>
        	</div>

        	<div class="item-content" id="delete-tv" style="display: none;">
        		<div class="heading">Delete TV Series</div>
        		<div class="form" id="delete-tv-form">
        			<input type="text" id="delete-tv-post-id" name="post_id" placeholder="Post ID">
        			<input type="text" id="delete-tv-tmdb-id" name="tmdb_id" placeholder="TMDB ID">
        			<div class="checkboxes">
        				<label class="container">Credits
						  <input type="checkbox" id="delete-tv-credits" name="credits" value="checked">
						  <span class="checkmark"></span>
						</label>
						<label class="container">Images
						  <input type="checkbox" id="delete-tv-images" name="images" value="checked">
						  <span class="checkmark"></span>
						</label>
						<label class="container">Videos
						  <input type="checkbox" id="delete-tv-videos" name="videos" value="checked">
						  <span class="checkmark"></span>
						</label>
						<label class="container">All Seasons
						  <input type="checkbox" id="delete-tv-all-seasons" name="all_seasons" value="checked">
						  <span class="checkmark"></span>
						</label>
						<input type="text" class="season_no" id="delete-tv-season-no" name="season_no" placeholder="Add season no">
        			</div>
        			<div class="button" data-type="tv" data-action="delete">Delete TV Series</div>
        		</div>

        		<div class="processloading"><div class="loading-tv-delete processing"></div></div>
        	</div>
        	<?php } ?>

        	<!-- Drama -->
        	<?php if ($options['tmu_dramas']==='on') { ?>
        	<div class="item-content" id="add-drama" style="display: <?= $options['tmu_movies']==='off' && $options['tmu_tv_series']==='off' ? 'block' : 'none' ?>;">
        		<div class="heading">Add Drama</div>
        		<div class="form" id="add-drama-form">
        			<input type="text" id="add-drama-tmdb-id" name="tmdb_id" placeholder="TMDB ID">
        			<div class="button" data-type="drama" data-action="add">Add Drama</div>
        		</div>
        		<div class="processloading"><div class="loading-drama-add processing"></div></div>
        	</div>

        	<div class="item-content" id="update-drama" style="display: none;">
        		<div class="heading">Update Drama</div>
        		<div class="form" id="update-drama-form">
        			<input type="text" id="update-drama-post-id" name="post_id" placeholder="Post ID">
        			<input type="text" id="update-drama-tmdb-id" name="tmdb_id" placeholder="TMDB ID">
        			<div class="checkboxes">
        				<label class="container">Credits
						  <input type="checkbox" id="update-drama-credits" name="credits" value="checked">
						  <span class="checkmark"></span>
						</label>
						<label class="container">Images
						  <input type="checkbox" id="update-drama-images" name="images" value="checked">
						  <span class="checkmark"></span>
						</label>
						<label class="container">Videos
						  <input type="checkbox" id="update-drama-videos" name="videos" value="checked">
						  <span class="checkmark"></span>
						</label>
						<label class="container">Episodes
						  <input type="checkbox" id="update-drama-episodes" name="episodes" value="checked">
						  <span class="checkmark"></span>
						</label>
        			</div>
        			<div class="button" data-type="drama" data-action="update">Update drama</div>
        		</div>
        		<div class="processloading"><div class="loading-drama-update processing"></div></div>
        	</div>

        	<div class="item-content" id="delete-drama" style="display: none;">
        		<div class="heading">Delete Drama</div>
        		<div class="form" id="delete-drama-form">
        			<input type="text" id="delete-drama-post-id" name="post_id" placeholder="Post ID">
        			<input type="text" id="delete-drama-tmdb-id" name="tmdb_id" placeholder="TMDB ID">
        			<div class="checkboxes">
        				<label class="container">Credits
						  <input type="checkbox" id="delete-drama-credits" name="credits" value="checked">
						  <span class="checkmark"></span>
						</label>
						<label class="container">Images
						  <input type="checkbox" id="delete-drama-images" name="images" value="checked">
						  <span class="checkmark"></span>
						</label>
						<label class="container">Videos
						  <input type="checkbox" id="delete-drama-videos" name="videos" value="checked">
						  <span class="checkmark"></span>
						</label>
						<label class="container">Episodes
						  <input type="checkbox" id="delete-drama-episodes" name="episodes" value="checked">
						  <span class="checkmark"></span>
						</label>
        			</div>
        			<div class="button" data-type="drama" data-action="delete">Delete Drama</div>
        		</div>
        		<div class="processloading"><div class="loading-drama-delete processing"></div></div>
        	</div>
        	<?php } ?>

        </div>
    </div>
    <?php
    tmdb_script();
}

function tmdb_page_styles(){
?>
<style type="text/css">
	.processloading {
		position: absolute;
	    text-align: center;
	    background-color: #282e34;
	    margin: 0 20px;
	    height: 130px;
	    color: white;
	    font-size: 13px;
	    font-weight: 600;
	    bottom: 20px;
	    width: calc(100% - 40px);
	    overflow: hidden;
	}

	.processloading a { color: #48aaf9!important; }

	.processing {
		display: flex;
	    position: relative;
	    width: 100%;
	    height: 100%;
	    justify-content: center;
	    align-items: center;
	}
	
	.tmdb-api-updates-container {
		display: flex;
		gap: 20px;
	}

	.api-sidebar {
		position: relative;
	    background: #fff;
	    border: 1px solid #e2e4e7;
	    border-radius: 3px;
	    padding: 20px;
	    box-sizing: border-box;
	    width: 220px;
    	min-width: 220px;
    	height: 550px;
	}

	.api-sidebar .heading {
		background: #282E34;
		font-size: 18px;
		font-weight: 600;
		padding: 8px 10px;
		color: #fff;
		margin: -20px -20px 0 -20px;
		overflow: hidden;
	}

	.api-sidebar .items { margin: 10px 0 50px; }

	.api-sidebar .item {
		font-weight: 600;
		padding: 8px 10px;
    	margin: 1px 0px;
    	cursor:pointer;
    	border: 1px solid transparent;
    	border-radius: 3px;
	}

	.api-sidebar .item:hover { color: #4A89DD; }
	.api-sidebar .item.active { border-color: #4A89DD; background: #e8f1ff; }

	.item-content { position: relative; background: #fff; border: 1px solid #e2e4e7; border-radius: 3px; box-sizing: border-box; width: 700px; height: 550px; overflow: hidden; }
	.item-content .heading { display: flex; align-items: center; font-size: 20px; padding: 20px; font-weight: 700; background: #282E34; border-radius: 3px 3px 0px 0px; color: #fff; overflow: hidden; }
	.form { padding:20px; }
	.form input { width: 100%; padding: 6px 20px; display: inline-block; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; margin: 8px 0; }
	.form .button { text-align: center; width: 100%; background-color: #2271b1; color: white; padding: 6px 20px; margin: 8px 0; border: none; border-radius: 4px; font-weight:600 }
	.form .button:hover { background-color:#282E34; color:white }
	.season_no { width: 150px!important; margin-top: -8px!important; }

	.checkboxes { display: flex; gap: 30px; margin: 20px 0; }

	.container {
	  display: block;
	  position: relative;
	  padding-left: 35px;
	  margin-bottom: 12px;
	  cursor: pointer;
	  font-size: 14px;
	  font-weight: 600;
	  line-height: 1.8;
	  -webkit-user-select: none;
	  -moz-user-select: none;
	  -ms-user-select: none;
	  user-select: none;
	}

	/* Hide the browser's default checkbox */
	.container input {
	  position: absolute;
	  opacity: 0;
	  cursor: pointer;
	  height: 0;
	  width: 0;
	}

	.checkmark {
	  position: absolute;
	  top: 0;
	  left: 0;
	  height: 25px;
	  width: 25px;
	  background-color: #eee;
	}

	/* On mouse-over, add a grey background color */
	.container:hover input ~ .checkmark {
	  background-color: #ccc;
	}

	/* When the checkbox is checked, add a blue background */
	.container input:checked ~ .checkmark {
	  background-color: #2196F3;
	}

	/* Create the checkmark/indicator (hidden when not checked) */
	.checkmark:after {
	  content: "";
	  position: absolute;
	  display: none;
	}

	/* Show the checkmark when checked */
	.container input:checked ~ .checkmark:after {
	  display: block;
	}

	/* Style the checkmark/indicator */
	.container .checkmark:after {
	  left: 9px;
	  top: 5px;
	  width: 5px;
	  height: 10px;
	  border: solid white;
	  border-width: 0 3px 3px 0;
	  -webkit-transform: rotate(45deg);
	  -ms-transform: rotate(45deg);
	  transform: rotate(45deg);
	}

	.loading {
	    position: absolute;
	    top: 0;
	    width: 100%;
	    height: 100%;
	    background: #ddddddb0;
	    z-index: 9;
	}


	.lds-grid,
	.lds-grid div {
	  box-sizing: border-box;
	}
	.lds-grid {
	  display: inline-block;
	  width: 80px;
	  height: 80px;
	  position: absolute;
	  top: 23%;
      left: 43%;
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
</style>
<?php
}

function tmdb_script(){
?>
<script type="text/javascript">
const items = document.querySelectorAll('.item');
const contentItems = document.querySelectorAll('.item-content');

items.forEach(item => {
  item.addEventListener('click', () => {
    // Remove active class from all items
    items.forEach(item => item.classList.remove('active'));

    // Add active class to the clicked item
    item.classList.add('active');

    // Get the target content ID from the clicked item's 'for' attribute
    const targetId = item.dataset.for;

    // Hide all content items
    contentItems.forEach(contentItem => {
      contentItem.style.display = 'none';
    });

    // Show the target content item
    const targetContent = document.getElementById(targetId);
    targetContent.style.display = 'block';
  });
});

</script>
<?php
}

add_action( 'wp_ajax_api_update', 'api_update_handler' );
add_action( 'wp_ajax_nopriv_api_update', 'api_update_handler' );

function api_update_handler(){
  $post_type = $_POST[ 'btn_type' ];
  $post_id = $_POST[ 'post_id' ];
  $tmdb_id = $_POST[ 'tmdb_id' ];
  $action = $_POST[ 'action_type' ];
  $images = $_POST[ 'images' ];
  $videos = $_POST[ 'videos' ];
  $episodes = $_POST[ 'episodes' ];
  $credits = $_POST[ 'credits' ];
  $all_seasons = $_POST['all_seasons'];
  $season_no = $_POST['season_no'];

  global $wpdb;
  $table_name = $wpdb->prefix.($post_type == 'tv' ? 'tmu_tv_series' : ($post_type == 'drama' ? 'tmu_dramas' :'tmu_movies'));
  $where = $post_id ? "WHERE ID = ".$post_id : ($tmdb_id ? "WHERE tmdb_id = ".$tmdb_id : '');
  $where_format = ['%d'];
  $result = $where ? $wpdb->get_row("SELECT ID,tmdb_id,images,release_timestamp FROM $table_name $where") : '';

  $tmdb_id = $tmdb_id ? $tmdb_id : ($result ? $result->tmdb_id : '');

  if ($action === 'add' && $tmdb_id && !$result) {
  	$data = $post_type==='tv' || $post_type==='drama' ? tv_data($tmdb_id) : movie_data($tmdb_id);
  	$title = $post_type==='tv' || $post_type==='drama' ? $data->name : $data->title;
  	$new_post_id = wp_insert_post([ 'post_title' => $title, 'post_content' => $data->overview, 'post_status' => 'publish', 'post_type' => $post_type ]);
  	if ($new_post_id) {
  		wp_update_post( array( 'ID' => $new_post_id, 'post_name' => $new_post_id.'-'.sanitize_title($title) ) );
  		$basic = basic_data($post_type, $new_post_id, $tmdb_id, $title, $data);
  		// echo json_encode($basic);
	  	$wpdb->insert($table_name, $basic['data'], $basic['format']);
	  	?>
	  	<div>
	  		<p><?= ucwords($post_type) ?> Added succesfully</p>
	  		<p><a href="<?= get_permalink($new_post_id)?>"><?=get_the_title($new_post_id) ?></a></p>
	  	</div>
	  	<?php
  	} else { echo 'Failed to Add'.$post_type; }
  	
  } elseif ($result && $action === 'add') {
  	echo ucwords($post_type).' Already Existed: <a href="'.get_permalink($result->ID).'">'.get_the_title($result->ID).'</a>';
  }

  if ($action === 'update' && $result) {
  	if ($credits || $videos || $images || $all_seasons || $season_no || $episodes) {
  		if ($credits) {
  			$all_credits = $post_type === 'drama' ? insert_drama_credits($result->ID, $result->tmdb_id, $result->release_timestamp) : ($post_type === 'tv' ? insert_tv_credits($result->ID, $result->tmdb_id, $result->release_timestamp) : insert_movie_credits($result->ID, $result->tmdb_id, $result->release_timestamp));
  			if ($result->credits) {
  				$main_credits = unserialize($result->credits);
  				if(isset($main_credits['cast']) && $main_credits['cast']) foreach ($main_credits['cast'] as $credit) { process_custom_cast($table_name, $post_id, $credit, $timestamp); }
				if(isset($main_credits['crew']) && $main_credits['crew']) foreach ($main_credits['crew'] as $credit) { process_custom_crew($table_name, $post_id, $credit, $timestamp); }
  			}

  			$star_casts = array_map(function($cast){ return ['person' => $cast['person'], 'character' => $cast['acting_job']]; }, array_slice($all_credits['cast'], 0, 4));
  			$wpdb->update($table_name, [ 'star_cast' => is_array($star_casts) ? serialize($star_casts) : $star_casts], ['ID' => $result->ID], [ '%s' ], ['%d']);
  		}
  		if ($images) {
  			$images_cnd = true;
  			if ($result->images) { $old_images = unserialize($result->images); $total_images = count($old_images); if ($total_images > 0) { $images_cnd = false; } }
  			if($images_cnd) {
  				$get_images_urls = $post_type === 'tv' || $post_type === 'drama' ? tv_images($result->tmdb_id) : movie_images($result->tmdb_id);
				$all_images = upload_images_from_urls($get_images_urls, $result->ID, get_the_title($result->ID));
				$wpdb->update($table_name, [ 'images' => is_array($all_images) ? serialize($all_images) : $all_images], ['ID' => $result->ID], [ '%s' ], ['%d']);
  			}
  		}
  		if ($videos) {
  			$tv = tv_data($result->tmdb_id);
  			$results = $wpdb->get_results("SELECT `ID` FROM {$wpdb->prefix}tmu_videos WHERE post_id = $result->ID");
    		if ($results) {
    			$vids = [];
    			foreach ($results as $row) $vids[] = $row->ID;
    		} else {
    			$vids = ($post_type === 'tv' || $post_type === 'drama') ? tv_videos($result->tmdb_id, $result->ID, $tv->number_of_seasons) : movie_videos($result->tmdb_id, $result->ID);
    		}
    		if ($vids) $wpdb->update($table_name, [ 'videos' => is_array($vids) ? serialize($vids) : $vids], ['ID' => $result->ID], [ '%s' ], ['%d']);
  		}
  		if ($all_seasons || $season_no) {
  			$tv = tv_data($result->tmdb_id);
  			$seasons = $season_no ? insert_seasons($result->ID, $tv->name, $tv->seasons, $result->tmdb_id, $season_no) : insert_seasons($result->ID, $tv->name, $tv->seasons, $result->tmdb_id);
  			$wpdb->update($table_name, [ 'seasons' => is_array($seasons) ? serialize($seasons) : $seasons], ['ID' => $result->ID], [ '%s' ], ['%d']);
  		}
  		if ($episodes) {
  			insert_drama_episodes($result->ID, 1, $result->tmdb_id);
  		}
  	} else {
  		$data = $post_type==='tv' || $post_type === 'drama' ? tv_data($result->tmdb_id) : movie_data($result->tmdb_id);
  		$title = $post_type==='tv' || $post_type === 'drama' ? $data->name : $data->title;
  		$basic = basic_data($post_type, $result->ID, $result->tmdb_id, $title, $data);
	    $wpdb->update($table_name, $basic['data'], ['ID' => $result->ID], $basic['format'], $where_format);
  	}

  	?>
  	<div>
  		<p><?= ucwords($post_type) ?> Updated succesfully</p>
  		<p><a href="<?= get_permalink($result->ID)?>"><?=get_the_title($result->ID) ?></a></p>
  	</div>
  	<?php
  }

  if ($result && $action === 'delete') {
  	if ($credits) {
  		$delete_type = $post_type === 'tv' ? 'tv_series' : ($post_type === 'drama' ? 'dramas' : $post_type);
		$wpdb->query( "DELETE FROM {$table_name}_cast  WHERE $delete_type = $result->ID" );
		$wpdb->query( "DELETE FROM {$table_name}_crew  WHERE $delete_type = $result->ID" );
		$wpdb->update($table_name, [ 'star_cast' => ''], ['ID' => $result->ID], [ '%s' ], ['%d']);
  	}

  	if ($images) {
  		$attachments = get_posts( array(
		    'post_type'      => 'attachment',
		    'posts_per_page' => -1,
		    'post_status'    => 'any',
		    'post_parent'    => $result->ID,
		    'exclude'        => get_post_thumbnail_id($result->ID),
		) );

		if ( $attachments ) {
		    foreach ( $attachments as $attachment ) {
		        delete_post_images( $attachment->ID );
		        wp_delete_attachment( $attachment->ID, true );
		    }
		}
		$wpdb->update($table_name, [ 'images' => ''], ['ID' => $result->ID], [ '%s' ], ['%d']);
  	}

  	if ($videos) {
  		$results = $wpdb->get_col("SELECT `ID` FROM {$wpdb->prefix}tmu_videos WHERE post_id = $result->ID");
  		$wpdb->query( "DELETE FROM {$wpdb->prefix}tmu_videos  WHERE post_id = $result->ID" );
  		$wpdb->update($table_name, [ 'videos' => ''], ['ID' => $result->ID], [ '%s' ], ['%d']);
  		foreach ($results as $video) { if(has_post_thumbnail($video)) { wp_delete_attachment( $video, true ); delete_post_images( get_post_thumbnail_id($video) ); } wp_delete_post($video, true); }
  	}

  	if ($episodes) {
		$episodes = $wpdb->get_col("SELECT ID FROM {$table_name}_episodes WHERE `dramas`=$result->ID");
		foreach ($episodes as $episode) { if(has_post_thumbnail($episode)) { wp_delete_attachment( $episode, true ); delete_post_images( get_post_thumbnail_id($episode) ); } wp_delete_post($episode, true); }
		$wpdb->query( "DELETE FROM {$table_name}_episodes WHERE `dramas`=$result->ID" );
  	}

  	if ($all_seasons) {
  		$seasons = $wpdb->get_results("SELECT ID,poster FROM {$table_name}_seasons WHERE `series_name` = $result->ID");
		$season_episodes = $wpdb->get_col("SELECT ID FROM {$table_name}_episodes WHERE `tv_series`=$result->ID");
  		foreach ($season_episodes as $episode) { if(has_post_thumbnail($episode)) { wp_delete_attachment( $episode, true ); delete_post_images( get_post_thumbnail_id($episode) ); } wp_delete_post($episode, true); }
		$wpdb->query( "DELETE FROM {$table_name}_episodes WHERE `tv_series`=$result->ID" );
		foreach ($seasons as $season) {
			wp_delete_term( $season->ID, 'season' );
			$season_posters = $season->poster ? unserialize($season->poster) : '';
			foreach ($season_posters as $season_poster) delete_post_images( $season_poster );
		}
  	}

  	if ($season_no) {
  		$season = $wpdb->get_row("SELECT ID,poster FROM {$table_name}_seasons WHERE `series_name` = $result->ID AND season_no=$season_no");
		$season_episodes = $wpdb->get_col("SELECT ID FROM {$table_name}_episodes WHERE `season_no`=$season_no AND `tv_series`=$result->ID");
		foreach ($season_episodes as $episode) { if(has_post_thumbnail($episode)) { wp_delete_attachment( $episode, true ); delete_post_images( get_post_thumbnail_id($episode) ); } wp_delete_post($episode, true); }
		$wpdb->query( "DELETE FROM {$table_name}_episodes WHERE `season_no`=$season_no AND `tv_series`=$result->ID" );
		wp_delete_term( $season->ID, 'season' );
		$season_posters = $season->poster ? unserialize($season->poster) : '';
		foreach ($season_posters as $season_poster) delete_post_images( $season_poster );
  	}
  	?>
  	<div>
  		<p><?= ucwords($post_type) ?> Deleted succesfully</p>
  		<p><a href="<?= get_permalink($result->ID)?>"><?=get_the_title($result->ID) ?></a></p>
  	</div>
  	<?php
  }



  die;
}

function basic_data($post_type, $post_id, $tmdb_id, $title, $data) {
	$db_data = [];
  	if(!has_post_thumbnail($post_id)) set_post_feature_image($post_id, $data->poster_path, $title);

  	$db_data['ID'] = $post_id;
  	$db_data['tmdb_id'] = $tmdb_id;
  	$db_data['original_title'] = $data->original_title;
  	$db_data['tagline'] = stripslashes($data->tagline);
  	$db_data['production_house'] = stripslashes(productions($data->production_companies));
  	$db_data['average_rating'] = $data->vote_average;
	$db_data['vote_count'] = $data->vote_count;

  	if ($post_type === 'tv') {
  		$tags = tv_tags($tmdb_id);
  		$db_data['release_timestamp'] = $data->first_air_date ? strtotime($data->first_air_date) : NULL;
		$db_data['release_date'] = $data->first_air_date;
		$release_year = $db_data['release_timestamp'] ? date('Y', $db_data['release_timestamp']) : NULL;

		$db_data['runtime'] = isset($data->episode_run_time[0]) ? $data->episode_run_time[0] : (isset($data->episode_run_time) ? $data->episode_run_time : '');
	    $db_data['certification'] = tv_certification($tmdb_id);

  		$episode = $data->next_episode_to_air ? $data->next_episode_to_air : $data->last_episode_to_air;
	    $db_data['last_season'] = $episode ? $episode->season_number : '';
	    $db_data['last_episode'] = $episode ? $episode->episode_number : '';

	    $db_data['finished'] = $data->status=='Ended' || $data->status=='Canceled' ? 1 : 0;
	    
  		$db_data['popularity'] = $data->popularity;

	    $format = ['%d', '%d', '%s', '%s', '%s', '%f', '%d', '%d', '%s', '%d', '%s', '%d', '%d', '%d', '%f'];

	    $channels = array_map(function($network) { return $network->name; }, $data->networks);
	    if ($channels) wp_set_post_terms( $post_id, $channels, 'network', true );

	    if ($data->networks) {
	    	foreach ($data->networks as $network) {
	    		wp_set_post_terms( $post_id, [$network->name], 'network', true );
	    		$term_id = get_term_by( 'slug', $network->name, 'network' )->term_id;
	    		if(!get_term_meta( $term_id, 'logo', true )) {
	    			$logo_url = 'https://image.tmdb.org/t/p/original'.$network->logo_path;
	    			$logo_id = upload_images_from_urls($logo_url, $term_id, $network->name);
	    			update_term_meta( $term_id, 'logo', $logo_id);
	    		}
	    	}
	    }

  	} elseif ($post_type === 'drama') {
  		$tags = tv_tags($tmdb_id);
  		$db_data['release_timestamp'] = $data->first_air_date ? strtotime($data->first_air_date) : NULL;
		$db_data['release_date'] = $data->first_air_date;
		$release_year = $db_data['release_timestamp'] ? date('Y', $db_data['release_timestamp']) : NULL;

		$db_data['runtime'] = isset($data->episode_run_time[0]) ? $data->episode_run_time[0] : (isset($data->episode_run_time) ? $data->episode_run_time : '');
	    $db_data['certification'] = tv_certification($tmdb_id);

	    $db_data['finished'] = $data->status=='Ended' || $data->status=='Canceled' ? 1 : 0;
	    
  		$db_data['popularity'] = $data->popularity;

	    $format = ['%d', '%d', '%s', '%s', '%s', '%f', '%d', '%d', '%s', '%d', '%s', '%d', '%d', '%d', '%f'];

	    if ($data->networks) {
	    	foreach ($data->networks as $network) {
	    		wp_set_post_terms( $post_id, [$network->name], 'channel', true );
	    		$term_id = get_term_by( 'slug', $network->name, 'channel' )->term_id;
	    		if(!get_term_meta( $term_id, 'logo', true )) {
	    			$logo_url = 'https://image.tmdb.org/t/p/original'.$network->logo_path;
	    			$logo_id = upload_images_from_urls($logo_url, $term_id, $network->name);
	    			update_term_meta( $term_id, 'logo', $logo_id);
	    		}
	    	}
	    }
	    

  	} else {
  		$theatr_release = movie_theater_release($tmdb_id);

  		$tags = movie_tags($tmdb_id);
		$db_data['release_timestamp'] = $theatr_release ? strtotime($theatr_release) : ($data->release_date ? strtotime($data->release_date) : NULL);
		$db_data['release_date'] = $db_data['release_timestamp'] ? date('Y-m-d', $db_data['release_timestamp']) : NULL;
		$release_year = $db_data['release_timestamp'] ? date('Y', $db_data['release_timestamp']) : NULL;
  		
  		$db_data['runtime'] = is_array($data->runtime) ? $data->runtime[0] : $data->runtime;
  		$db_data['certification'] = movie_certification($tmdb_id);
  		
  		$db_data['revenue'] = $data->revenue;
  		$db_data['budget'] = $data->budget;
  		$db_data['popularity'] = $data->popularity;

  		$format = ['%d', '%d', '%s', '%s', '%s', '%f', '%d', '%d', '%s', '%d', '%s', '%d', '%d', '%f'];
  	}

  	$genres = array_map(function($genre) { return $genre->name; }, $data->genres);
  	$countries = array_map(function($country) { return $country->name; }, $data->production_countries);
  	$languages = array_map(function($language) { return $language->english_name; }, $data->spoken_languages);

  	if($genres) wp_set_post_terms( $post_id, $genres, 'genre', true );
  	if($tags) wp_set_post_terms( $post_id, $tags, 'keyword', true );
  	if($db_data['release_date']) wp_set_post_terms( $post_id, [$release_year], 'by-year', true );
  	if($countries) wp_set_post_terms( $post_id, $countries, 'country', true );
  	if($languages) wp_set_post_terms( $post_id, $languages, 'language', true );

  	return ['data' => $db_data, 'format' => $format];
}