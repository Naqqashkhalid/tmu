<?php

// Celebrities

add_action( 'wp_enqueue_scripts', 'add_ajax_scripts_2' );
function add_ajax_scripts_2() {
    if ( is_archive() && get_queried_object()->name=='people' ) {
        wp_register_script('ajax_people', plugin_dir_url( __DIR__ ) . 'src/js/ajax_people.js', array( 'jquery' ), 1.1, true);
        wp_enqueue_script( 'ajax_people' );
        wp_localize_script( 'ajax_people', 'ajax_people_params', array('ajaxurl' => admin_url( 'admin-ajax.php' )));
        wp_enqueue_style( 'filter_module_style', plugin_dir_url( __DIR__ ) . 'src/css/people-filter.css', array(), '1.1', 'all' );
    } else if ( is_single() && get_post_type() == 'tv' && current_user_can('administrator') ) {
        wp_enqueue_script('jquery-ui-autocomplete');
        wp_register_script('ajax_credits', plugin_dir_url( __DIR__ ) . 'src/js/ajax-credits.js', array('jquery', 'jquery-ui-autocomplete'), '1.0', true);
        wp_localize_script('ajax_credits', 'ajax_credits_params', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'series_id' => get_the_ID(),
            'nonce' => wp_create_nonce('tv_credits_nonce')
        ));
        wp_enqueue_script('ajax_credits');
    }
}

add_action( 'wp_ajax_people', 'loadmore_ajax_handler_2' );
add_action( 'wp_ajax_nopriv_people', 'loadmore_ajax_handler_2' );

function loadmore_ajax_handler_2(){

    // prepare our arguments for the query
    $page = (int)$_POST[ 'page' ];
    $sort_by = $_POST[ 'sort_by' ];
    $profession = $_POST[ 'profession' ];
    $networth = $_POST[ 'networth' ];
    $country = $_POST[ 'country' ];
    $ppp = (int)$_POST[ 'ppp' ];

    celebrities_template_ajax($page, $sort_by, $profession, $networth, $country, $ppp);

    die;
}

function celebrities_template_ajax($page=1, $sort_by='a_z', $profession=[], $networth=[], $country=[], $ppp=20){
    $count = 0; $offset = ($page-1)*$ppp; $gender = []; $processed_professions = [];
    $options = get_options(["tmu_dramas","tmu_movies","tmu_tv_series"]);

    global $wpdb;
    $table_name = $wpdb->prefix.'tmu_people';

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
            $sort_query = "SUBSTRING(`name`, 1, 1) REGEXP '^[a-zA-Z]+$' ORDER BY `name` ASC";
    }

    $profession_clause = !empty($processed_professions) ? "WHERE profession IN ('" . implode("','", $processed_professions) . "')" : '';
    $net_worth_clause = !empty($networth) ? ($profession_clause ? 'AND ' : 'WHERE ')."(net_worth != 0 AND net_worth IS NOT NULL AND (" . implode(" OR ", $query_networth) . "))" : '';
    $gender_clause = !empty($gender) ? (count($gender) == 2 ? '' : ($profession_clause || $net_worth_clause ? "AND " : 'WHERE ' )."gender IN ('" . implode(",", $gender) . "')" ) : '';
    $country_clause = $country_query ? (($profession_clause || $net_worth_clause || $gender_clause) ? 'AND ' : 'WHERE ').$country_query : '';

    $sort_query = ($sort_by == 'a_z' || $sort_by == 'networth_highest' || $sort_by == 'networth_lowest') ? (($profession_clause || $gender_clause || $net_worth_clause || $country_clause) ? "AND " : "WHERE ").$sort_query : $sort_query;


    $results = $wpdb->get_results("SELECT `ID`,`net_worth`,`no_movies` FROM $table_name $tax_query $profession_clause $gender_clause $net_worth_clause $country_clause $sort_query LIMIT $ppp OFFSET $offset");
    $total_persons = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    $total_pages = ceil($total_persons/$ppp);

    ?><div class="module_flexbox"><?php

    foreach ($results as $result) {
        $title = get_the_title($result->ID);
        $permalink = get_permalink($result->ID);
        ?>
        <a class="actor-box" href="<?= $permalink ?>">
            <div class="actor-poster">
                <img src="<?= has_post_thumbnail($result->ID) ? get_the_post_thumbnail_url($result->ID, 'full') : plugin_dir_url( __DIR__ ) . 'src/images/no-poster.webp' ?>" alt="<?= $title ?>" width="100%" height="100%">
            </div>
            <div class="actor-details">
                <h3><?= $title ?></h3>
                <p class="total-movies"><?= $result->no_movies ? 'Number of Movies: '.$result->no_movies : '' ?></p>
                <?php if ($result->net_worth) { ?> <p class="net-worth">NET WORTH: $<?= nice_numbers($result->net_worth) ?></p> <?php } ?>
            </div>
        </a>
        <?php
    }
    ?></div>
    <?php
    $json_country = $country ? json_encode($country) : '[]';
    $json_networth = $networth ? json_encode($networth) : '[]';
    $json_profession = $profession ? json_encode($profession) : '[]';
    ?>
    <div class="load_more_box" id="loadmore_container" data-sort_by='<?= $sort_by ?>' data-profession='<?= $json_profession ?>' data-networth='<?= $json_networth ?>' data-country='<?= $json_country ?>' data-posts-per-page="<?= $ppp ?>" data-total-pages="<?= $total_pages ?>" data-page="<?= $page ?>"><div class="load_prev button" id="loadprev" style="display:<?= ($page == 1 ? 'none' : 'block') ?>;">< PREV</div><div class="load_next button" id="loadnext" style="display:<?= ($total_pages == $page ? 'none' : 'block') ?>">NEXT ></div></div>
    <?php
}

function nice_numbers($n) {
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

// Add these action hooks at the top with other add_action calls
add_action('wp_ajax_get_tv_credits', 'get_tv_credits_handler');
add_action('wp_ajax_save_tv_credit', 'save_tv_credit_handler');
add_action('wp_ajax_delete_tv_credit', 'delete_tv_credit_handler');
add_action('wp_ajax_search_tv_people', 'search_tv_people_handler');

function get_tv_credits_handler() {
    if (!current_user_can('administrator')) {
        wp_send_json_error('Unauthorized access');
    }

    $series_id = intval($_POST['series_id']);
    $type = sanitize_text_field($_POST['type']);

    global $wpdb;
    $table = $wpdb->prefix . 'tmu_tv_series_' . $type;

    $credits = $wpdb->get_results($wpdb->prepare(
        "SELECT c.*, p.post_title as person_name 
         FROM $table c 
         LEFT JOIN {$wpdb->posts} p ON c.person = p.ID 
         WHERE c.tv_series = %d",
        $series_id
    ));

    wp_send_json_success($credits);
}

function search_tv_people_handler() {
    if (!current_user_can('administrator')) {
        wp_send_json_error('Unauthorized access');
    }

    $search = sanitize_text_field($_POST['search']);

    $args = array(
        'post_type' => 'people',
        'posts_per_page' => 10,
        's' => $search,
        'post_status' => 'publish'
    );

    $query = new WP_Query($args);
    $results = array();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $results[] = array(
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'image' => get_the_post_thumbnail_url(get_the_ID(), 'thumbnail') ?: plugin_dir_url(__DIR__) . 'src/icons/no-image.svg'
            );
        }
    }
    wp_reset_postdata();

    wp_send_json_success($results);
}

function save_tv_credit_handler() {
    if (!current_user_can('administrator')) {
        wp_send_json_error('Unauthorized access');
    }

    $series_id = intval($_POST['series_id']);
    $person_id = intval($_POST['person_id']);
    $type = sanitize_text_field($_POST['type']);
    $role = wp_unslash(sanitize_text_field($_POST['role']));
    $department = isset($_POST['department']) ? sanitize_text_field($_POST['department']) : '';
    $credit_id = isset($_POST['credit_id']) ? intval($_POST['credit_id']) : 0;

    global $wpdb;
    $table = $wpdb->prefix . 'tmu_tv_series_' . $type;

    // Check for existing credit
    if ($type === 'cast') {
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM $table WHERE tv_series = %d AND person = %d AND job = %s AND ID != %d",
            $series_id, $person_id, $role, $credit_id
        ));
    } else {
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM $table WHERE tv_series = %d AND person = %d AND department = %s AND job = %s AND ID != %d",
            $series_id, $person_id, $department, $role, $credit_id
        ));
    }

    if ($exists) {
        wp_send_json_error('This credit already exists');
        return;
    }

    $data = array(
        'tv_series' => $series_id,
        'person' => $person_id,
        'job' => $role
    );

    if ($type === 'crew') {
        $data['department'] = $department;
    }

    if ($credit_id) {
        $wpdb->update($table, $data, array('ID' => $credit_id));
    } else {
        $wpdb->insert($table, $data);
        $credit_id = $wpdb->insert_id;
    }

    wp_send_json_success(array('id' => $credit_id));
}

function delete_tv_credit_handler() {
    if (!current_user_can('administrator')) {
        wp_send_json_error('Unauthorized access');
    }

    $credit_id = intval($_POST['credit_id']);
    $type = sanitize_text_field($_POST['type']);

    global $wpdb;
    $table = $wpdb->prefix . 'tmu_tv_series_' . $type;

    $wpdb->delete($table, array('ID' => $credit_id));
    wp_send_json_success();
}