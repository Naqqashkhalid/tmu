 <?php
/**
 * Latest Celebrities with Filters eg; Sorting, Profession, Net Worth
 *
 * @package green_entertainment
 */

function celebrities_with_filters(){
    $posts = celebrities_template();
    $options = get_options(["tmu_dramas","tmu_movies","tmu_tv_series"]);
    $is_drama = $options['tmu_dramas'] === 'on' && $options['tmu_movies'] === 'off' && $options['tmu_tv_series'] === 'off';
    $is_tv = $options['tmu_dramas'] === 'off' && $options['tmu_movies'] === 'off' && $options['tmu_tv_series'] === 'on';
    $typeText = $is_drama ? 'Dramas' : ($is_tv ? 'TV Shows' : 'Movies');
    $data = '<div class="full_buttons_modals_multi_flex">
        <div><button class="myBtn_multi sort_by_button"  id="sort_byHighlightActive" onclick="select_filters(\'sort_by\');">
            
            <div style="padding: 0px 7px 0px 0px; width: 23px; height: 19px;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="11.673" viewBox="0 0 16 11.673"><defs><style>.cls-1 {fill: #0c0c0f!important;}</style></defs><path id="Union_2" data-name="Union 2" class="cls-1" d="M-1191.827-1.741l.893-.893,2.885,2.858V-8.957h1.273V.222l2.885-2.885.891.923-.072.072-4.342,4.342Zm-3.4,4.371V-6.551l-2.884,2.858-.893-.892.073-.074L-1194.587-9l4.412,4.412-.89.923-2.886-2.885v9.18Z" transform="translate(1199 9)"/></svg></div>

            <span id="sort_bySelectCnt">Sort by: NO. OF '.$typeText.' (HIGHEST)</span>
            <div class="dropdown"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="5" viewBox="0 0 10 5"><defs></defs><path id="Icon_material-arrow-drop-down" data-name="Icon material-arrow-drop-down" class="cls-1" d="M10.5,15l5,5,5-5Z" style="fill: #0c0c0f;" transform="translate(-10.5 -15)"/></svg></div>
        </button></div>

        <div class="buttons_modals_multi">
            <!-- ---------------------------------------------------------- -->
            <div id="clear_all_flex">
                    <div class="line_drop_down"></div>
                    <div id="clear_all_button" class="clearAllButton" style="cursor: pointer;">clear all</div>
            </div>
            <!-- Country button  -->
            <button class="myBtn_multi" id="countryHighlightActive" onclick="select_filters(\'country\');">Country
                <span id="countrySelectCnt"></span>
                <div class="dropdown"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="5" viewBox="0 0 10 5"><path id="Icon_material-arrow-drop-down" data-name="Icon material-arrow-drop-down" class="cls-1" d="M10.5,15l5,5,5-5Z" transform="translate(-10.5 -15)"/></svg></div>
            </button>
            <!-- networth button  -->
            <button class="myBtn_multi" id="networthHighlightActive" onclick="select_filters(\'networth\');">Net Worth
                <span id="networthSelectCnt"></span>
                <div class="dropdown"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="5" viewBox="0 0 10 5"><defs></defs><path id="Icon_material-arrow-drop-down" data-name="Icon material-arrow-drop-down" class="cls-1" d="M10.5,15l5,5,5-5Z" style="fill: #0c0c0f;" transform="translate(-10.5 -15)"/></svg></div>
            </button>
            <!-- profession button  -->
            <button class="myBtn_multi" id="professionHighlightActive" onclick="select_filters(\'profession\');">Profession
                <span id="professionSelectCnt"></span>
                <div class="dropdown"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="5" viewBox="0 0 10 5"><defs></defs><path id="Icon_material-arrow-drop-down" data-name="Icon material-arrow-drop-down" class="cls-1" d="M10.5,15l5,5,5-5Z" style="fill: #0c0c0f;" transform="translate(-10.5 -15)"/></svg></div>
            </button>
        </div>
    </div>
    
<div id="module_1">'.$posts['data'].'</div>


<div class="modal_asdf modal_multi_asdf" id="sort_by_button_modals" style="display: none;">
    <div class="modal-content_asdf">
        <div class="modal_drop_check_flex_header">
            <h3>Sort by</h3>
            <span class="close close_multi" data-close="sort_by"><svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 17 17"><defs></defs><path id="Icon_ionic-md-close" data-name="Icon ionic-md-close" style="fill:#fff;" d="M24.523,9.223l-1.7-1.7-6.8,6.8-6.8-6.8-1.7,1.7,6.8,6.8-6.8,6.8,1.7,1.7,6.8-6.8,6.8,6.8,1.7-1.7-6.8-6.8Z" transform="translate(-7.523 -7.523)"/></svg></span>
        </div>
        <div class="new">
            <form>
                <div class="form-group">
                    <input type="radio" class="sort_by_filter" name="sort_by_filter[]" value="networth_highest" id="networth_highest">
                    <label for="networth_highest">NETWORTH HIGHEST</label>
                </div>
                <div class="form-group">
                    <input type="radio" class="sort_by_filter" name="sort_by_filter[]" value="networth_lowest" id="networth_lowest">
                    <label for="networth_lowest">NETWORTH LOWEST</label>
                </div>
                <div class="form-group">
                    <input type="radio" class="sort_by_filter" name="sort_by_filter[]" value="a_z" id="a_z">
                    <label for="a_z">Alphabetical</label>
                </div>
                <div class="form-group">
                    <input type="radio" class="sort_by_filter" name="sort_by_filter[]" value="movies_highest" id="movies_highest">
                    <label for="movies_highest">NO. OF '.$typeText.' (HIGHEST)</label>
                </div>
                <div class="form-group">
                    <input type="radio" class="sort_by_filter" name="sort_by_filter[]" value="movies_lowest" id="movies_lowest">
                    <label for="movies_lowest">NO. OF '.$typeText.' (LOWEST)</label>
                </div>
            </form>
        </div>
        <div class="button_flex_modals">
            <button id="clear_sort_by" class="clear-button-modals" data-element="sort_by">Clear Filter</button>
            <button id="apply_sort_by" class="applly-button-modals" data-element="sort_by">apply filter</button>
        </div>
    </div>
</div>

<div class="modal_asdf modal_multi_asdf" id="networth_button_modals" style="display: none;">
    <div class="modal-content_asdf">
        <div class="modal_drop_check_flex_header">
            <h3>Net Worth</h3>
            <span class="close close_multi" data-close="networth"><svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 17 17"><defs></defs><path id="Icon_ionic-md-close" data-name="Icon ionic-md-close" style="fill:#fff;" d="M24.523,9.223l-1.7-1.7-6.8,6.8-6.8-6.8-1.7,1.7,6.8,6.8-6.8,6.8,1.7,1.7,6.8-6.8,6.8,6.8,1.7-1.7-6.8-6.8Z" transform="translate(-7.523 -7.523)"/></svg></span>
        </div>
        <div class="new">
            <form>
                <div class="form-group">
                    <input type="checkbox" class="networth_filter" name="networth_filter[]" value="1" id="1">
                    <label for="1">Uptill $1M</label>
                </div>
                <div class="form-group">
                    <input type="checkbox" class="networth_filter" name="networth_filter[]" value="2" id="2">
                    <label for="2">Uptill $2M</label>
                </div>
                <div class="form-group">
                    <input type="checkbox" class="networth_filter" name="networth_filter[]" value="3" id="3">
                    <label for="3">Uptill $3M</label>
                </div>
                <div class="form-group">
                    <input type="checkbox" class="networth_filter" name="networth_filter[]" value="4" id="4">
                    <label for="4">Uptill $4M</label>
                </div>
                <div class="form-group">
                    <input type="checkbox" class="networth_filter" name="networth_filter[]" value="5" id="5">
                    <label for="5">Uptill $5M</label>
                </div>
                <div class="form-group">
                    <input type="checkbox" class="networth_filter" name="networth_filter[]" value="6" id="6">
                    <label for="6">More Than $5M</label>
                </div>
            </form>
        </div>
        <div class="button_flex_modals">
            <button id="clear_networth" class="clear-button-modals" data-element="networth">Clear Filter</button>
            <button id="apply_networth" class="applly-button-modals" data-element="networth">apply filter</button>
        </div>
    </div>
</div>
<!-- 5000000 -->

<div class="modal_asdf modal_multi_asdf" id="profession_button_modals" style="display: none;">
    <div class="modal-content_asdf">
        <div class="modal_drop_check_flex_header">
            <h3>By Profession</h3>
            <span class="close close_multi" data-close="profession"><svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 17 17"><defs></defs><path id="Icon_ionic-md-close" data-name="Icon ionic-md-close" style="fill:#fff;" d="M24.523,9.223l-1.7-1.7-6.8,6.8-6.8-6.8-1.7,1.7,6.8,6.8-6.8,6.8,1.7,1.7,6.8-6.8,6.8,6.8,1.7-1.7-6.8-6.8Z" transform="translate(-7.523 -7.523)"/></svg></span>
        </div>
        <div class="new">
            <form>
                <div class="form-group">
                    <input type="checkbox" class="profession_filter" name="profession_filter[]" value="actor" id="actor">
                    <label for="actor">Actor</label>
                </div>
                <div class="form-group">
                    <input type="checkbox" class="profession_filter" name="profession_filter[]" value="actress" id="actress">
                    <label for="actress">Actress</label>
                </div>
                <div class="form-group">
                    <input type="checkbox" class="profession_filter" name="profession_filter[]" value="producer" id="producer">
                    <label for="producer">Producer</label>
                </div>
                <div class="form-group">
                    <input type="checkbox" class="profession_filter" name="profession_filter[]" value="director" id="director">
                    <label for="director">Director</label>
                </div>
                <div class="form-group">
                    <input type="checkbox" class="profession_filter" name="profession_filter[]" value="writer" id="writer">
                    <label for="writer">Writer</label>
                </div>
                <div class="form-group">
                    <input type="checkbox" class="profession_filter" name="profession_filter[]" value="crew" id="crew">
                    <label for="crew">Crew</label>
                </div>
            </form>
        </div>
        <div class="button_flex_modals">
            <button id="clear_profession" class="clear-button-modals" data-element="profession">Clear Filter</button>
            <button id="apply_profession" class="applly-button-modals" data-element="profession">apply filter</button>
        </div>
    </div>
</div>



<div class="modal_asdf modal_multi_asdf" id="country_button_modals" style="display: none;">
    <div class="modal-content_asdf">
        <div class="modal_drop_check_flex_header">
            <h3>COUNTRY</h3>
            <span class="close close_multi" data-close="country"><svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 17 17"><path id="Icon_ionic-md-close" data-name="Icon ionic-md-close" style="fill:#fff;" d="M24.523,9.223l-1.7-1.7-6.8,6.8-6.8-6.8-1.7,1.7,6.8,6.8-6.8,6.8,1.7,1.7,6.8-6.8,6.8,6.8,1.7-1.7-6.8-6.8Z" transform="translate(-7.523 -7.523)"/></svg></span>
        </div>
        <div class="new">
            <form>'.get_countries().'</form>
        </div>
        <div class="button_flex_modals">
            <button id="clear_country" class="clear-button-modals" data-element="country">Clear Filter</button>
            <button id="apply_country" class="applly-button-modals" data-element="country">apply filter</button>
        </div>
    </div>
</div>';

    return ['data' => $data, 'schema' => $posts['schema']];
}

function celebrities_template($page=1, $sort_by='movies_highest', $profession=[], $networth=[], $country=[], $ppp=20){
    $count = 0; $offset = ($page-1)*$ppp; $gender = []; $processed_professions = [];
    global $wpdb;
    $table_name = $wpdb->prefix.'tmu_people';

    $options = get_options(["tmu_dramas","tmu_movies","tmu_tv_series"]);

    if (!empty($profession)) {
        foreach ($profession as $item) {
            switch ($item) {
              case "actor":
                $gender[] = 'Male';
                $processed_professions[] = 'Acting';
                break;
              case "actress":
                $gender[] = 'Female';
                $processed_professions[] = 'Acting';
                break;
              case "producer":
                $processed_professions[] = 'Production';
                break;
              case "director":
                $processed_professions[] = 'Directing';
                break;
              case "writer":
                $processed_professions[] = 'Writing';
                break;
              case "crew":
                $processed_professions[] = 'Crew';
                break;
              default:
            }
        }
    }

    $query_networth = [];
    if (!empty($networth)) {
        foreach ($networth as $item) {
            if ($item==1) $query_networth[] = "net_worth <= 1000000";
            if ($item==2) $query_networth[] = "net_worth <= 2000000";
            if ($item==3) $query_networth[] = "net_worth <= 3000000";
            if ($item==4) $query_networth[] = "net_worth <= 4000000";
            if ($item==5) $query_networth[] = "net_worth <= 5000000";
            if ($item==6) $query_networth[] = "net_worth > 5000000";
        }
    }

    $country_query = ""; $tax_query = "";
    if ($country) {
        $term_ids = implode(",", array_map(function($term) { return get_term_by( 'slug', $term, 'nationality' )->term_id; }, $country));
        $country_query = " tt1.term_taxonomy_id IN (".$term_ids.")";
        $tax_query = " LEFT JOIN wp_term_relationships AS tt1 ON ($table_name.ID = tt1.object_id)";
    }

    switch ($sort_by) {
      case "networth_highest":
        $sort_query = 'net_worth != 0 AND net_worth IS NOT NULL ORDER BY CAST(net_worth AS SIGNED) DESC';
        break;
      case "networth_lowest":
        $sort_query = 'net_worth != 0 AND net_worth IS NOT NULL ORDER BY CAST(net_worth AS SIGNED) ASC';
        break;
      case "a_z":
        $sort_query = "SUBSTRING(`name`, 1, 1) REGEXP '^[a-zA-Z]+$' ORDER BY `name` ASC";
        break;
      case "z_a":
        $sort_query = 'ORDER BY name DESC';
        break;
      case "movies_highest":
        $sort_query = $options['tmu_dramas'] === 'on' ? 'ORDER BY no_dramas DESC' : ($options['tmu_movies'] === 'on' ? 'ORDER BY no_movies DESC' : 'ORDER BY no_tv_series DESC');
        break;
      case "movies_lowest":
        $sort_query = $options['tmu_dramas'] === 'on' ? 'ORDER BY no_dramas ASC' : ($options['tmu_movies'] === 'on' ? 'ORDER BY no_movies ASC' : 'ORDER BY no_tv_series ASC');
        break;
      default:
        $sort_query = "ORDER BY no_movies DESC";
    }

    $profession_clause = !empty($processed_professions) ? "WHERE profession IN ('" . implode("','", $processed_professions) . "')" : '';
    $net_worth_clause = !empty($networth) ? ($profession_clause ? 'AND ' : 'WHERE ')."(net_worth != 0 AND net_worth IS NOT NULL AND (" . implode(" OR ", $query_networth) . "))" : '';
    $gender_clause = !empty($gender) ? (count($gender) == 2 ? '' : ($profession_clause || $net_worth_clause ? "AND " : 'WHERE ' )."gender IN ('" . implode(",", $gender) . "')" ) : '';
    $country_clause = $country_query ? (($profession_clause || $net_worth_clause || $gender_clause) ? 'AND ' : 'WHERE ').$country_query : '';

    $sort_query = ($sort_by == 'a_z' || $sort_by == 'networth_highest' || $sort_by == 'networth_lowest') ? (($profession_clause || $gender_clause || $net_worth_clause || $country_clause) ? "AND " : "WHERE ").$sort_query : $sort_query;

    $results = $wpdb->get_results("SELECT $table_name.`ID`,$table_name.`net_worth`,$table_name.`no_movies` FROM $table_name JOIN {$wpdb->prefix}posts AS posts ON ($table_name.ID = posts.ID) $tax_query $profession_clause $gender_clause $net_worth_clause $country_clause AND posts.post_status = 'publish' $sort_query LIMIT $ppp OFFSET $offset");
    $total_persons = $wpdb->get_var("SELECT COUNT(*) FROM $table_name JOIN {$wpdb->prefix}posts AS posts ON ($table_name.ID = posts.ID) WHERE posts.post_status = 'publish'");
    $total_pages = ceil($total_persons/$ppp);

    $schema = '';
    $data = '<div class="module_flexbox">';

    if ($results) {
        $schema .= '<script type="application/ld+json">{ "@context": "https://schema.org", "@type": "ItemList", "itemListElement": [';
        $count = 0;
        foreach ($results as $result) {
            $title = get_the_title($result->ID);
            $permalink = get_permalink($result->ID);
            $schema .= ($count !== 0 ? ',' : '' ).'{ "@type": "ListItem", "position": "'.(++$count).'", "url": "'.$permalink.'" }';

            $data .= '<a class="actor-box" href="'.$permalink.'">';
                $data .= '<div class="actor-poster">';
                    $data .= '<img '.(has_post_thumbnail($result->ID) ? ('src="'.plugin_dir_url( __DIR__ ) . 'src/icons/no-image.svg" data-src="'.get_the_post_thumbnail_url($result->ID, 'full').'" class="lazyload"') : ('src="'.plugin_dir_url( __DIR__ ) . 'src/icons/no-image.svg"') ).' alt="'.$title.'" width="100%" height="100%">';
                $data .= '</div>';
                $data .= '<div class="actor-details">';
                    $data .= '<h3>'.$title.'</h3>';
                    $data .= '<p class="total-movies">'.($result->no_movies ? 'Number of '.$typeText.': '.$result->no_movies : '').'</p>';
                    $data .= $result->net_worth ? '<p class="net-worth">NET WORTH: $'.nice_number($result->net_worth).'</p>' : '';
                $data .= '</div>';
            $data .= '</a>';
        }
        $schema .= ']}</script>';
    }
    $data .= '</div>';
    $json_country = $country ? json_encode($country) : '[]';
    $json_networth = $networth ? json_encode($networth) : '[]';
    $json_profession = $profession ? json_encode($profession) : '[]';

    $data .= '<div class="load_more_box" id="loadmore_container" data-sort_by="'.$sort_by.'" data-profession="'.$json_profession.'" data-networth="'.$json_networth.'" data-country="'.$json_country.'" data-posts-per-page="'.$ppp.'" data-total-pages="'.$total_pages.'" data-page="'.$page.'"><div class="load_prev button" id="loadprev" style="display:'.($page == 1 ? 'none' : 'block').'">< PREV</div><div class="load_next button" id="loadnext" style="display:'.($total_pages == $page ? 'none' : 'block').'">NEXT ></div></div>';

    return ['data' => $data, 'schema' => $schema];
}

function nice_number($n) {
    // first strip any formatting;
    $n = (0+str_replace(",", "", $n));

    // is this a number?
    if (!is_numeric($n)) return false;

    // now filter it;
    elseif ($n >= 1000000000) return round(($n/1000000000), 2).' B';
    elseif ($n >= 1000000) return round(($n/1000000), 2).' M';
    elseif ($n >= 1000) return round(($n/1000), 2).' K';

    return number_format($n);
}

function get_countries(){
    $countries = get_terms( 'nationality', array( 'orderby' => 'count', 'order' => 'DESC', 'hide_empty' => 0, 'number' => 10,) );
    $data = '';
    foreach ($countries as $country) {
        $data .= '
                <div class="form-group">
                    <input type="checkbox" class="country_filter" name="country_filter[]" value="'.$country->slug.'" id="'.$country->slug.'">
                    <label for="'.$country->slug.'">'.$country->name.'</label>
                </div>';
    }
    return $data;
}