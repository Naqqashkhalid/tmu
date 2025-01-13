<?php
/**
 * Latest Posts with Filters eg; genre, language, year, country
 *
 * @package green_entertainment
 */

function module_1($post_type){
    $posts = module_template($post_type);

    $data = '<div class="full_buttons_modals_multi_flex">
    <div>
        <button class="myBtn_multi sort_by_button"  id="sort_byHighlightActive" onclick="select_filters(\'sort_by\');">
            <div class="sort-arrow-m" style="padding: 0px 7px 0px 0px; width: 23px; height: 19px;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="11.673" viewBox="0 0 16 11.673"><path id="Union_2" data-name="Union 2" class="cls-1" d="M-1191.827-1.741l.893-.893,2.885,2.858V-8.957h1.273V.222l2.885-2.885.891.923-.072.072-4.342,4.342Zm-3.4,4.371V-6.551l-2.884,2.858-.893-.892.073-.074L-1194.587-9l4.412,4.412-.89.923-2.886-2.885v9.18Z" transform="translate(1199 9)"/></svg></div>
            <span id="sort_bySelectCnt">Sort by: Release Date</span>
            <div class="dropdown"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="5" viewBox="0 0 10 5"><path id="Icon_material-arrow-drop-down" data-name="Icon material-arrow-drop-down" class="cls-1" d="M10.5,15l5,5,5-5Z" transform="translate(-10.5 -15)"/></svg></div>
        </button>
    </div>

    <div class="buttons_modals_multi">
        <!-- ---------------------------------------------------------- -->
        <div id="clear_all_flex">
                <div class="line_drop_down"></div>
                <div id="clear_all_button" class="clearAllButton" style="cursor: pointer;">clear all</div>
        </div>
        <!-- YEAR button  -->
        <button class="myBtn_multi" id="yearHighlightActive" onclick="select_filters(\'year\');">YEAR
            <span id="yearSelectCnt"></span>
            <div class="dropdown"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="5" viewBox="0 0 10 5"><path id="Icon_material-arrow-drop-down" data-name="Icon material-arrow-drop-down" class="cls-1" d="M10.5,15l5,5,5-5Z" transform="translate(-10.5 -15)"/></svg></div>
        </button>';

    if($post_type==='drama') {
        $data .= '<!-- CHANNEL button  -->
    <button class="myBtn_multi" id="channelHighlightActive" onclick="select_filters(\'channel\');">CHANNEL
        <span id="channelSelectCnt"></span>
        <div class="dropdown"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="5" viewBox="0 0 10 5"><path id="Icon_material-arrow-drop-down" data-name="Icon material-arrow-drop-down" class="cls-1" d="M10.5,15l5,5,5-5Z" transform="translate(-10.5 -15)"/></svg></div>
    </button>';
    }

    if($post_type!=='drama') {
        $args = array(
            'taxonomy'   => 'by-year', // Replace 'category' with your desired taxonomy
            'orderby'    => 'count',
            'order'      => 'DESC', // Order by descending count (most items first)
            'hide_empty' => false, // Include terms with no associated items
        );

        $sorted_terms = get_terms( $args );
        $year_data = '';
        foreach ( $sorted_terms as $term ) {
            $year_data .= '<div class="form-group">
                        <input type="checkbox" class="year_filter" name="year_filter[]" value="'.$term->slug.'" id="'.$term->slug.'">
                        <label for="'.$term->slug.'">'.$term->name.'</label>
                    </div>';
        }
        $data .= '<!-- LANGUAGE button  -->
        <button class="myBtn_multi" id="languageHighlightActive" onclick="select_filters(\'language\');">LANGUAGE
            <span id="languageSelectCnt"></span>
            <div class="dropdown"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="5" viewBox="0 0 10 5"><path id="Icon_material-arrow-drop-down" data-name="Icon material-arrow-drop-down" class="cls-1" d="M10.5,15l5,5,5-5Z" transform="translate(-10.5 -15)"/></svg></div>
        </button>
        <!-- Genre button  -->
        <button class="myBtn_multi" id="genreHighlightActive" onclick="select_filters(\'genre\');">Genre
            <span id="genreSelectCnt"></span>
            <div class="dropdown"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="5" viewBox="0 0 10 5"><path id="Icon_material-arrow-drop-down" data-name="Icon material-arrow-drop-down" class="cls-1" d="M10.5,15l5,5,5-5Z" transform="translate(-10.5 -15)"/></svg></div>
        </button>
        <!-- Country button  -->
        <button class="myBtn_multi" id="countryHighlightActive" onclick="select_filters(\'country\');">Country
            <span id="countrySelectCnt"></span>
            <div class="dropdown"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="5" viewBox="0 0 10 5"><path id="Icon_material-arrow-drop-down" data-name="Icon material-arrow-drop-down" class="cls-1" d="M10.5,15l5,5,5-5Z" transform="translate(-10.5 -15)"/></svg></div>
        </button>';
    }

    $data .= '</div></div><div id="module_1">'.$posts['data'].'</div>';

    $data .= '<div class="modal_asdf modal_multi_asdf" id="sort_by_button_modals" style="display: none;">
    <div class="modal-content_asdf">
        <div class="modal_drop_check_flex_header">
            <h3>Sort by</h3>
            <span class="close close_multi" data-close="sort_by"><svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 17 17"><path id="Icon_ionic-md-close" data-name="Icon ionic-md-close" style="fill:#fff;" d="M24.523,9.223l-1.7-1.7-6.8,6.8-6.8-6.8-1.7,1.7,6.8,6.8-6.8,6.8,1.7,1.7,6.8-6.8,6.8,6.8,1.7-1.7-6.8-6.8Z" transform="translate(-7.523 -7.523)"/></svg></span>
        </div>
        <div class="new">
            <form>
                <div class="form-group">
                    <input type="radio" class="sort_by_filter" name="sort_by_filter[]" value="release_date" id="release_date">
                    <label for="release_date">Release Date</label>
                </div>
                <div class="form-group">
                    <input type="radio" class="sort_by_filter" name="sort_by_filter[]" value="a_z" id="a_z">
                    <label for="a_z">Alphabetical</label>
                </div>
                <div class="form-group">
                    <input type="radio" class="sort_by_filter" name="sort_by_filter[]" value="runtime" id="runtime">
                    <label for="runtime">Runtime</label>
                </div>
                <div class="form-group">
                    <input type="radio" class="sort_by_filter" name="sort_by_filter[]" value="rating" id="rating">
                    <label for="rating">Rating</label>
                </div>
            </form>
        </div>
        <div class="button_flex_modals">
            <button id="clear_sort_by" class="clear-button-modals" data-element="sort_by">Clear Filter</button>
            <button id="apply_sort_by" class="applly-button-modals" data-element="sort_by">apply filter</button>
        </div>
    </div>
</div>

<div class="modal_asdf modal_multi_asdf" id="year_button_modals" style="display: none;">
    <div class="modal-content_asdf">
        <div class="modal_drop_check_flex_header">
            <h3>YEAR</h3>
            <span class="close close_multi" data-close="year"><svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 17 17"><path id="Icon_ionic-md-close" data-name="Icon ionic-md-close" style="fill:#fff;" d="M24.523,9.223l-1.7-1.7-6.8,6.8-6.8-6.8-1.7,1.7,6.8,6.8-6.8,6.8,1.7,1.7,6.8-6.8,6.8,6.8,1.7-1.7-6.8-6.8Z" transform="translate(-7.523 -7.523)"/></svg></span>
        </div>
        <div class="new">
            <form>
                '.$year_data.'
            </form>
        </div>
        <div class="button_flex_modals">
            <button id="clear_year" class="clear-button-modals" data-element="year">Clear Filter</button>
            <button id="apply_year" class="applly-button-modals" data-element="year">apply filter</button>
        </div>
    </div>
</div>';

    if($post_type==='drama') {
        $args = array(
            'taxonomy'   => 'channel', // Replace 'category' with your desired taxonomy
            'orderby'    => 'count',
            'order'      => 'DESC', // Order by descending count (most items first)
            'hide_empty' => false, // Include terms with no associated items
        );

        $sorted_terms = get_terms( $args );
        $channel_data = '';
        foreach ( $sorted_terms as $term ) {
            $channel_data .= '<div class="form-group">
                        <input type="checkbox" class="channel_filter" name="channel_filter[]" value="'.$term->slug.'" id="'.$term->slug.'">
                        <label for="'.$term->slug.'">'.$term->name.'</label>
                    </div>';
        }
        $data .= '<div class="modal_asdf modal_multi_asdf" id="channel_button_modals" style="display: none;">
        <div class="modal-content_asdf">
            <div class="modal_drop_check_flex_header">
                <h3>CHANNEL</h3>
                <span class="close close_multi" data-close="channel"><svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 17 17"><path id="Icon_ionic-md-close" data-name="Icon ionic-md-close" style="fill:#fff;" d="M24.523,9.223l-1.7-1.7-6.8,6.8-6.8-6.8-1.7,1.7,6.8,6.8-6.8,6.8,1.7,1.7,6.8-6.8,6.8,6.8,1.7-1.7-6.8-6.8Z" transform="translate(-7.523 -7.523)"/></svg></span>
            </div>
            <div class="new">
                <form>
                    '.$channel_data.'
                </form>
            </div>
            <div class="button_flex_modals">
                <button id="clear_channel" class="clear-button-modals" data-element="channel">Clear Filter</button>
                <button id="apply_channel" class="applly-button-modals" data-element="channel">apply filter</button>
            </div>
        </div>
    </div>';
    }

    if($post_type!=='drama') {
        $taxonomies = ['network','country','genre','by-year','language'];
        $term_data = '';
        foreach ($taxonomies as $taxonomy) {
            $args = array(
                'taxonomy'   => $taxonomy, // Replace 'category' with your desired taxonomy
                'orderby'    => 'count',
                'order'      => 'DESC', // Order by descending count (most items first)
                'hide_empty' => false, // Include terms with no associated items
            );

            $sorted_terms = get_terms( $args );
            foreach ( $sorted_terms as $term ) {
                $term_data .= '<div class="form-group">
                        <input type="checkbox" class="'.$term->slug.'_filter" name="'.$term->slug.'_filter[]" value="'.$term->slug.'" id="'.$term->slug.'">
                        <label for="'.$term->slug.'">'.$term->name.'</label>
                    </div>';
            }
            $data .= '<div class="modal_asdf modal_multi_asdf" id="'.$taxonomy.'_button_modals" style="display: none;">
                <div class="modal-content_asdf">
                    <div class="modal_drop_check_flex_header">
                        <h3>'.strtoupper($taxonomy).'</h3>
                        <span class="close close_multi" data-close="'.$taxonomy.'"><svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 17 17"><path id="Icon_ionic-md-close" data-name="Icon ionic-md-close" style="fill:#fff;" d="M24.523,9.223l-1.7-1.7-6.8,6.8-6.8-6.8-1.7,1.7,6.8,6.8-6.8,6.8,1.7,1.7,6.8-6.8,6.8,6.8,1.7-1.7-6.8-6.8Z" transform="translate(-7.523 -7.523)"/></svg></span>
                    </div>
                    <div class="new">
                        <form>
                            '.$term_data.'
                        </form>
                    </div>
                    <div class="button_flex_modals">
                        <button id="clear_'.$taxonomy.'" class="clear-button-modals" data-element="'.$taxonomy.'">Clear Filter</button>
                        <button id="apply_'.$taxonomy.'" class="applly-button-modals" data-element="'.$taxonomy.'">apply filter</button>
                    </div>
                </div>
            </div>';
            $term_data = '';
        }
    }

    return ['data' => $data, 'schema' => $posts['schema']];
}

function module_template($post_type, $page=1, $sort_by='release_date', $lang=[], $genre=[], $country=[], $year=[], $channel=[], $ppp=20){
	$count = 0; // $tax_terms = []; $tax_query = [];
    $tax_query = ''; $term_query = ''; $term_ids = ""; $offset = ($page-1)*$ppp;

    global $wpdb;
    $table_name = $wpdb->prefix.($post_type === 'movie' ? 'tmu_movies' : ($post_type === 'tv' ? 'tmu_tv_series' : 'tmu_dramas'));

    if ($lang && $term_ids = implode(",", array_map(function($term) { return get_term_by( 'slug', $term, 'language' )->term_id; }, $lang))) {
        $term_query .= " AND tt1.term_taxonomy_id IN (".$term_ids.")";
        $tax_query .= " LEFT JOIN {$wpdb->prefix}term_relationships AS tt1 ON ({$table_name}.ID = tt1.object_id)";
        ++$count; 
    }
    if ($genre && $term_ids = implode(",", array_map(function($term) { return get_term_by( 'slug', $term, 'genre' )->term_id; }, $genre))) {
        $term_query .= " AND tt2.term_taxonomy_id IN (".$term_ids.")";
        $tax_query .= " LEFT JOIN {$wpdb->prefix}term_relationships AS tt2 ON ({$table_name}.ID = tt2.object_id)";
        ++$count;
    }
    if ($country && $term_ids = implode(",", array_map(function($term) { return get_term_by( 'slug', $term, 'country' )->term_id; }, $country))) {
        $term_query .= " AND tt3.term_taxonomy_id IN (".$term_ids.")";
        $tax_query .= " LEFT JOIN {$wpdb->prefix}term_relationships AS tt3 ON ({$table_name}.ID = tt3.object_id)";
        ++$count;
    }
    if ($year && $term_ids = implode(",", array_map(function($term) { return get_term_by( 'slug', $term, 'by-year' )->term_id; }, $year))) {
        $term_query .= " AND tt4.term_taxonomy_id IN (".$term_ids.")";
        $tax_query .= " LEFT JOIN {$wpdb->prefix}term_relationships AS tt4 ON ({$table_name}.ID = tt4.object_id)";
        ++$count;
    }
    if ($channel) {
        $term_ids = implode(",", array_map(function($term) { return get_term_by( 'slug', $term, 'channel' )->term_id; }, $channel));
        $term_query .= " AND tt5.term_taxonomy_id IN (".$term_ids.")";
        $tax_query .= " LEFT JOIN {$wpdb->prefix}term_relationships AS tt5 ON ({$table_name}.ID = tt5.object_id)";
        ++$count;
    }


// SELECT wp_tv_series.ID,wp_tv_series.star_cast FROM wp_tv_series LEFT JOIN ( SELECT wp_tv_series_episodes.`tv_series`, wp_tv_series_episodes.`air_date`, unix_timestamp(wp_tv_series_episodes.`air_date`) AS last_air_date FROM wp_tv_series_episodes WHERE unix_timestamp(wp_tv_series_episodes.`air_date`)<=unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) GROUP BY tv_series ) AS last_aired ON wp_tv_series.ID = last_aired.`tv_series` LEFT JOIN wp_posts AS posts ON (wp_tv_series.ID = posts.ID) LEFT JOIN wp_comments AS comments ON (wp_tv_series.ID = comments.comment_post_ID) WHERE 1=1 AND last_aired.last_air_date<=unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) AND posts.post_status = 'publish' GROUP BY wp_tv_series.ID ORDER BY last_aired.last_air_date DESC LIMIT 15 OFFSET 0;

    switch ($sort_by) {
      case "release_date":
        if ($post_type == 'movie') {
            $sort_query = "ORDER BY $table_name.release_timestamp DESC";
        } elseif ($post_type == 'tv') {
            $tax_query .= "LEFT JOIN ( SELECT `tv_series`, MAX(`air_date`) AS last_air_date FROM {$table_name}_episodes WHERE unix_timestamp(`air_date`)<=unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) GROUP BY tv_series ) AS last_aired ON {$table_name}.ID = last_aired.`tv_series`";
            $sort_query = "ORDER BY last_aired.last_air_date DESC";
        } else {
            $tax_query .= "LEFT JOIN ( SELECT `dramas`, MAX(`air_date`) AS last_air_date FROM {$table_name}_episodes WHERE unix_timestamp(`air_date`)<=unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) GROUP BY dramas ) AS last_aired ON {$table_name}.ID = last_aired.`dramas`";
            $sort_query = "ORDER BY last_aired.last_air_date DESC";
        }
        break;
      case "a_z":
        $sort_query = "ORDER BY posts.post_title ASC";
        break;
      case "runtime":
        $sort_query = "ORDER BY CAST({$table_name}.runtime AS UNSIGNED) ASC";
        break;
      case "rating":
        $sort_query = 'ORDER BY total_average_rating DESC, total_vote_count DESC';
        break;
      default:
        if ($post_type == 'movie') {
            $sort_query = "ORDER BY {$table_name}.release_timestamp DESC";
        } elseif ($post_type == 'tv') {
            $tax_query .= "LEFT JOIN ( SELECT `tv_series`, MAX(`air_date`) AS last_air_date FROM {$table_name}_episodes WHERE unix_timestamp(`air_date`)<=unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) GROUP BY tv_series ) AS last_aired ON {$table_name}.ID = last_aired.`tv_series`";
            $sort_query = "ORDER BY last_aired.last_air_date DESC";
        } else {
            $tax_query .= "LEFT JOIN ( SELECT `dramas`, MAX(`air_date`) AS last_air_date FROM {$table_name}_episodes WHERE unix_timestamp(`air_date`)<=unix_timestamp(DATE_ADD(NOW(),interval 3 hour)) GROUP BY dramas ) AS last_aired ON {$table_name}.ID = last_aired.`dramas`";
            $sort_query = "ORDER BY last_aired.last_air_date DESC";
        }
    }

    $release_query = '';
    if ($sort_by == 'release_date') {
        $release_query = $post_type === 'tv' || $post_type === 'drama' ? "" : "AND release_timestamp<unix_timestamp(DATE_ADD(NOW(),interval 3 hour))";
    }

    $posts = $wpdb->get_results("SELECT {$table_name}.ID,{$table_name}.star_cast FROM $table_name $tax_query JOIN {$wpdb->prefix}posts AS posts ON ({$table_name}.ID = posts.ID) WHERE 1=1 $release_query $term_query AND posts.post_status = 'publish' GROUP BY {$table_name}.ID $sort_query LIMIT 20 OFFSET $offset");

    $total_count = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} $tax_query JOIN {$wpdb->prefix}posts AS posts ON ({$table_name}.ID = posts.ID) WHERE 1=1 $term_query AND posts.post_status = 'publish'");
    $total_pages = ceil($total_count/$ppp);

	$data = '<div class="module_flexbox">'; $schema = '';

	if($posts):
      $schema .= '<script type="application/ld+json">{ "@context": "https://schema.org", "@type": "ItemList", "itemListElement": [';
      $count = 0;
      $schemType = $post_type === 'movie' ? 'Movie' : 'TVSeries';
      $search_type = $post_type === 'tv' ? 'tv_series' : ($post_type === 'drama' ? 'dramas' : $post_type);
      global $wpdb;
      $table_single = $wpdb->prefix.($post_type === 'tv' ? 'tmu_tv_series' : ($post_type === 'drama' ? 'tmu_dramas' : 'tmu_movies'));

      foreach ($posts as $post) {
        $post_id = $post->ID;
        $post_title = get_the_title($post_id);
        $image_url = has_post_thumbnail($post_id) ? get_the_post_thumbnail_url($post_id, 'full') : plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp';
        $permalink = get_permalink($post_id);

        // $tv = $wpdb->get_row($wpdb->prepare("SELECT average_rating,vote_count FROM $table_single WHERE `ID` = %d", $post_id), ARRAY_A);
        // $tmdb_rating['average'] = $tv ? $tv['average_rating'] : 0;
        // $tmdb_rating['count'] = $tv ? $tv['vote_count'] : 0;

        // $comments = get_comments(array('post_id' => $post_id, 'status' => 'approve'));
        // $average_ratings = get_average_ratings($comments, $tmdb_rating);
        $average_ratings = $wpdb->get_row($wpdb->prepare("SELECT total_average_rating,total_vote_count FROM $table_single WHERE `ID` = %d", $post_id), ARRAY_A);

        $publish_date = get_the_date('Y-m-d', $post_id);

        $director = $wpdb->get_var($wpdb->prepare("SELECT person FROM {$table_single}_crew WHERE $search_type = %d AND job='Director'", $post_id));

        $schema .= ($count !== 0 ? ',' : '' ).'{"@type": "ListItem", "position": "'.(++$count).'", "item": { "@type": "'.$schemType.'", "url": "'.$permalink.'", "name": "'.$post_title.'", "image": "'.$image_url.'", "dateCreated": "'.$publish_date.'", '.($director ? '"director": [ { "@type": "Person", "url": "'.get_permalink($director).'", "name": "'.get_the_title($director).'" } ],' : '').' "aggregateRating": { "@type": "AggregateRating", "bestRating": "10", "worstRating": "1", "ratingValue": "'.($average_ratings['total_average_rating'] ?? 5).'", "ratingCount": "'.($average_ratings['total_vote_count'] ?? 1).'" } } }';

        $data .= '<a class="module_post" href="'.$permalink.'">';
            $data .= '<div class="poster"><img '.(has_post_thumbnail($post_id) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/preloader.gif" data-src="'.$image_url.'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp"') ).' alt="'.$post_title.'" width="100%" height="100%"></div>';
            $data .= '<div class="details"><h3>'.$post_title.'</h3></div>';
        $data .= '</a>';
	  }
      wp_reset_postdata();

      $schema .= '] } </script>';

    endif;
    $data .= '</div>';

    $json_lang = $lang ? json_encode($lang) : '[]';
    $json_genre = $genre ? json_encode($genre) : '[]';
    $json_country = $country ? json_encode($country) : '[]';
    $json_channel = $channel ? json_encode($channel) : '[]';
    $json_year = $year ? json_encode($year) : '[]';
    
    $data .= "<div class='load_more_box' id='loadmore_container' data-lang='".$json_lang."' data-genre='".$json_genre."' data-country='".$json_country."' data-channel='".$json_channel."' data-year='".$json_year."' data-sort_by='".$sort_by."' data-posts-per-page='".$ppp."' data-total-pages='".$total_pages."' data-page='".$page."' data-post-type='".$post_type."' ><div class='load_prev button' id='loadprev' style='display:".($page == 1 ? "none" : "block")."'>< PREV</div><div class='load_next button' id='loadnext' style='display:".($total_pages == $page ? "none" : "block")."'>NEXT ></div></div>";

    return ['data' => $data, 'schema' => $schema];
}

add_action( 'wp_enqueue_scripts', 'add_ajax_scripts' );
function add_ajax_scripts() {
    if ( is_archive() && (get_queried_object()->name=='movie' || get_queried_object()->name=='tv' || get_queried_object()->name=='drama') ) {
        wp_register_script('ajax_loadmore', plugin_dir_url( __DIR__ ) . 'src/js/ajax.js', array( 'jquery' ), 1.1, true);
        wp_enqueue_script( 'ajax_loadmore' );
        wp_localize_script( 'ajax_loadmore', 'ajax_loadmore_params', array('ajaxurl' => admin_url( 'admin-ajax.php' )));
	    wp_enqueue_script( 'filter_module_script', plugin_dir_url( __DIR__ ) . 'src/js/filter-module.js', array(), '1.1', true); 
	    wp_enqueue_style( 'filter_module_style', plugin_dir_url( __DIR__ ) . 'src/css/filter-model.css', array(), '1.1', 'all' );
	}
}

add_action( 'wp_ajax_loadmore', 'loadmore_ajax_handler' );
add_action( 'wp_ajax_nopriv_loadmore', 'loadmore_ajax_handler' );

function loadmore_ajax_handler(){
 
	// prepare our arguments for the query
	$post_type = $_POST[ 'post_type' ];
	$page = $_POST[ 'page' ];
    $sort_by = $_POST[ 'sort_by' ];
	$lang = $_POST[ 'language' ];
	$genre = $_POST[ 'genre' ];
	$country = $_POST[ 'country' ];
	$year = $_POST[ 'year' ];
    $channel = $_POST[ 'channel' ];
	$ppp = $_POST[ 'ppp' ];
	
	$result = module_template($post_type, $page, $sort_by, $lang, $genre, $country, $year, $channel, $ppp);
    echo $result['data'];
	die;
}