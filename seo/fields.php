<?php

function seo_options(){
    wp_enqueue_style( 'seo_styles', plugin_dir_url( __DIR__ ) . 'seo/src/styles.css', array(), '1.1', 'all' );
    wp_register_script('seo_options', plugin_dir_url( __DIR__ ) . 'seo/src/script.js', array( 'jquery' ), 1.1, true);
    wp_enqueue_script( 'seo_options' );
    wp_localize_script( 'seo_options', 'seo_options_params', array('ajaxurl' => admin_url( 'admin-ajax.php' )));

    $post_types = seo_options_get_post_types();
    $taxonomies = get_taxonomies(array('public' => true), 'objects');

    global $wpdb;
    $table_name = $wpdb->prefix.'tmu_seo_options';
    ?>
    <div class="main-container wrap">
        <div class="seo-sidebar">
            <div class="heading">Archives</div>
            <div class="items">
                <div class="item active" data-for="homepage">Homepage</div>
                <?php foreach ($post_types as $post_type) { if($post_type !== 'attachment' && $post_type !== 'page' && $post_type !== 'season'){ ?>
                <div class="item" data-for="archive-<?= $post_type ?>"><?= ucfirst($post_type) ?></div>
                <?php } }
                if(get_option( 'tmu_dramas' ) === 'on') { ?>
                    <div class="item" data-for="drama-episodes">Drama All Episodes</div>
                <?php }
                if(get_option( 'tmu_tv_series' ) === 'on') { ?>
                    <div class="item" data-for="tv-series-seasons">TV Series All Seasons</div>
                <?php }?>
            </div>

            <div class="heading">Single Post</div>
            <div class="items">
                <?php foreach ($post_types as $post_type) { if($post_type !== 'attachment'){ ?>
                <div class="item" data-for="single-<?= $post_type ?>"><?= ucfirst($post_type) ?></div>
                <?php } } ?>
            </div>

            <div class="heading">Taxonomies Archive</div>
            <div class="items">
                <?php foreach ($taxonomies as $tax => $taxonomy) { if(!in_array($tax, ['post_format', 'post_tag', 'category'])){ ?>
                <div class="item" data-for="tax-<?= $tax ?>"><?= ucfirst($tax) ?></div>
                <?php } } ?>
            </div>

            <div class="heading">Taxonomy Terms</div>
            <div class="items">
                <?php foreach ($taxonomies as $tax => $taxonomy) { if($tax != 'post_format'){ ?>
                <div class="item" data-for="<?= $tax ?>"><?= ucfirst($tax) ?></div>
                <?php } } ?>
            </div>
        </div>

        <div class="seo-content">
            <div class="item-content" id="homepage" style="display: block;">
                <div class="heading">Homepage SEO</div>
                <div class="form" id="homepage-form">
                    <input type="text" id="homepage-title" name="homepage-title" placeholder="Homepage Title" value="<?= $wpdb->get_var("SELECT title FROM {$table_name} WHERE name = 'homepage' AND post_type = 'homepage' AND section = 'archive'") ?>">
                    <textarea rows="4" cols="50" id="homepage-description" name="homepage-description" placeholder="Homepage Description"><?= $wpdb->get_var("SELECT description FROM {$table_name} WHERE name = 'homepage' AND post_type = 'homepage' AND section = 'archive'") ?></textarea>
                    <input type="text" id="homepage-keywords" name="homepage-keywords" placeholder="Homepage Keywords" value="<?= $wpdb->get_var("SELECT keywords FROM {$table_name} WHERE name = 'homepage' AND post_type = 'homepage' AND section = 'archive'") ?>">

                    <input type="text" id="support-email" name="support-email" placeholder="Support Email" value="<?= get_option( 'tmu_email' ) ?>">

                    <?php $robots = $wpdb->get_var("SELECT robots FROM {$table_name} WHERE name = 'homepage' AND post_type = 'homepage' AND section = 'archive'"); $robots = !$robots || $robots === 'index, follow' ? true : false; ?>
                    <div class="radio-container">
                      <label class="container">Index
                        <input type="radio" <?= $robots ? 'checked="checked"' : '' ?> name="homepage-robots" value="index, follow">
                        <span class="checkmark"></span>
                      </label>
                      <label class="container">NoIndex
                        <input type="radio" name="homepage-robots" <?= !$robots ? 'checked="checked"' : '' ?> value="noindex, nofollow">
                        <span class="checkmark"></span>
                      </label>
                    </div>

                    <div class="button" data-type="homepage" data-section="archive" data-selector="homepage">Save</div>
                    <p><code>%current_month%</code> <code>%current_year%</code> <code>%sitename%</code></p>
                </div>
            </div>

            <?php foreach ($post_types as $post_type) { if($post_type !== 'attachment' && $post_type !== 'page'){ ?>
            <div class="item-content" id="archive-<?= $post_type ?>" style="display: none;">
                <div class="heading"><?= ucfirst($post_type) ?> Archive SEO</div>
                <div class="form" id="<?= $post_type ?>-form">
                    <input type="text" id="archive-<?= $post_type ?>-title" name="archive-<?= $post_type ?>-title" placeholder="Archive <?= ucfirst($post_type) ?> Title" value="<?= $wpdb->get_var("SELECT title FROM {$table_name} WHERE name = 'archive-{$post_type}' AND post_type = '{$post_type}' AND section = 'archive'") ?>">
                    <textarea rows="4" cols="50" id="archive-<?= $post_type ?>-description" name="archive-<?= $post_type ?>-description" placeholder="Archive <?= ucfirst($post_type) ?> Description"><?= $wpdb->get_var("SELECT description FROM {$table_name} WHERE name = 'archive-{$post_type}' AND post_type = '{$post_type}' AND section = 'archive'") ?></textarea>
                    <input type="text" id="archive-<?= $post_type ?>-keywords" name="archive-<?= $post_type ?>-keywords" placeholder="Archive <?= $post_type ?> Keywords" value="<?= $wpdb->get_var("SELECT keywords FROM {$table_name} WHERE name = 'archive-{$post_type}' AND post_type = '{$post_type}' AND section = 'archive'") ?>">

                    <?php $robots = $wpdb->get_var("SELECT robots FROM {$table_name} WHERE name = 'archive-{$post_type}' AND post_type = '{$post_type}' AND section = 'archive'"); $robots = !$robots || $robots === 'index, follow' ? true : false; ?>
                    <div class="radio-container">
                      <label class="container">Index
                        <input type="radio" <?= $robots ? 'checked="checked"' : '' ?> name="archive-<?= $post_type ?>-robots" value="index, follow">
                        <span class="checkmark"></span>
                      </label>
                      <label class="container">NoIndex
                        <input type="radio" name="archive-<?= $post_type ?>-robots" <?= !$robots ? 'checked="checked"' : '' ?> value="noindex, nofollow">
                        <span class="checkmark"></span>
                      </label>
                    </div>

                    <div class="button" data-type="<?= $post_type ?>" data-section="archive" data-selector="archive-<?= $post_type ?>">Save</div>
                    <p><code>%title%</code> <code>%current_month%</code> <code>%current_year%</code> <code>%sitename%</code></p>
                </div>
            </div>
            <?php } } ?>
            <div class="item-content" id="tv-series-seasons" style="display: none;">
                <div class="heading">TV Series Seasons Archive SEO</div>
                <div class="form" id="tv-series-seasons-form">
                    <input type="text" id="tv-series-seasons-title" name="tv-series-seasons-title" placeholder="TV Series Seasons Title" value="<?= $wpdb->get_var("SELECT title FROM {$table_name} WHERE name = 'tv-series-seasons' AND post_type = 'custom' AND section = 'archive'") ?>">
                    <textarea rows="4" cols="50" id="tv-series-seasons-description" name="tv-series-seasons-description" placeholder="TV Series Seasons Description"><?= $wpdb->get_var("SELECT description FROM {$table_name} WHERE name = 'tv-series-seasons' AND post_type = 'custom' AND section = 'archive'") ?></textarea>
                    <input type="text" id="tv-series-seasons-keywords" name="tv-series-seasons-keywords" placeholder="Season Keywords" value="<?= $wpdb->get_var("SELECT keywords FROM {$table_name} WHERE name = 'tv-series-seasons' AND post_type = 'custom' AND section = 'archive'") ?>">

                    <?php $robots = $wpdb->get_var("SELECT robots FROM {$table_name} WHERE name = 'tv-series-seasons' AND post_type = 'custom' AND section = 'archive'"); $robots = !$robots || $robots === 'index, follow' ? true : false; ?>
                    <div class="radio-container">
                      <label class="container">Index
                        <input type="radio" <?= $robots ? 'checked="checked"' : '' ?> name="tv-series-seasons-robots" value="index, follow">
                        <span class="checkmark"></span>
                      </label>
                      <label class="container">NoIndex
                        <input type="radio" name="tv-series-seasons-robots" <?= !$robots ? 'checked="checked"' : '' ?> value="noindex, nofollow">
                        <span class="checkmark"></span>
                      </label>
                    </div>

                    <div class="button" data-type="custom" data-section="archive" data-selector="tv-series-seasons">Save</div>
                    <p><code>%title%</code> <code>%current_month%</code> <code>%current_year%</code> <code>%sitename%</code></p>
                </div>
            </div>

            <div class="item-content" id="drama-episodes" style="display: none;">
                <div class="heading">Single Drama Episodes Archive SEO</div>
                <div class="form" id="drama-episodes-form">
                    <input type="text" id="drama-episodes-title" name="drama-episodes-title" placeholder="Drama Episodes Title" value="<?= $wpdb->get_var("SELECT title FROM {$table_name} WHERE name = 'drama-episodes' AND post_type = 'custom' AND section = 'archive'") ?>">
                    <textarea rows="4" cols="50" id="drama-episodes-description" name="drama-episodes-description" placeholder="Drama Episodes Description"><?= $wpdb->get_var("SELECT description FROM {$table_name} WHERE name = 'drama-episodes' AND post_type = 'custom' AND section = 'archive'") ?></textarea>
                    <input type="text" id="drama-episodes-keywords" name="drama-episodes-keywords" placeholder="Episodes Page Keywords" value="<?= $wpdb->get_var("SELECT keywords FROM {$table_name} WHERE name = 'drama-episodes' AND post_type = 'custom' AND section = 'archive'") ?>">

                    <?php $robots = $wpdb->get_var("SELECT robots FROM {$table_name} WHERE name = 'drama-episodes' AND post_type = 'custom' AND section = 'archive'"); $robots = !$robots || $robots === 'index, follow' ? true : false; ?>
                    <div class="radio-container">
                      <label class="container">Index
                        <input type="radio" <?= $robots ? 'checked="checked"' : '' ?> name="drama-episodes-robots" value="index, follow">
                        <span class="checkmark"></span>
                      </label>
                      <label class="container">NoIndex
                        <input type="radio" name="drama-episodes-robots" <?= !$robots ? 'checked="checked"' : '' ?> value="noindex, nofollow">
                        <span class="checkmark"></span>
                      </label>
                    </div>

                    <div class="button" data-type="custom" data-section="archive" data-selector="drama-episodes">Save</div>
                    <p><code>%title%</code> <code>%current_month%</code> <code>%current_year%</code> <code>%sitename%</code></p>
                </div>
            </div>


            <?php foreach ($post_types as $post_type) { if($post_type !== 'attachment'){ ?>
            <div class="item-content" id="single-<?= $post_type ?>" style="display: none;">
                <div class="heading"><?= ucfirst($post_type) ?> Single SEO</div>
                <div class="form" id="<?= $post_type ?>-form">
                    <input type="text" id="single-<?= $post_type ?>-title" name="single-<?= $post_type ?>-title" placeholder="Single <?= ucfirst($post_type) ?> Title" value="<?= $wpdb->get_var("SELECT title FROM {$table_name} WHERE name = 'single-{$post_type}' AND post_type = '{$post_type}' AND section = 'single'") ?>">
                    <?php if ($post_type === 'movie' || $post_type === 'tv' || $post_type === 'drama') {
                        $description = $wpdb->get_var("SELECT description FROM {$table_name} WHERE name = 'single-{$post_type}' AND post_type = '{$post_type}' AND section = 'single'");
                        $description = $description ? unserialize($description) : '';
                        $released = isset($description['released']) ? $description['released'] : '';
                        $upcoming = isset($description['upcoming']) ? $description['upcoming'] : ''; ?>
                        <label><span class="title">Released Description</span>
                        <textarea rows="4" cols="50" id="single-<?= $post_type ?>-released-description" name="single-<?= $post_type ?>-released-description" placeholder="Single <?= ucfirst($post_type) ?> Released Description"><?= $released ?></textarea></label>
                        <label><span class="title">Upcoming Description</span>
                        <textarea rows="4" cols="50" id="single-<?= $post_type ?>-upcoming-description" name="single-<?= $post_type ?>-upcoming-description" placeholder="Single <?= ucfirst($post_type) ?> Upcoming Description"><?= $upcoming ?></textarea></label>
                    <?php } else { ?>
                        <textarea rows="4" cols="50" id="single-<?= $post_type ?>-description" name="single-<?= $post_type ?>-description" placeholder="Single <?= ucfirst($post_type) ?> Description"><?= $wpdb->get_var("SELECT description FROM {$table_name} WHERE name = 'single-{$post_type}' AND post_type = '{$post_type}' AND section = 'single'") ?></textarea>
                    <?php } ?>

                    <?php $robots = $wpdb->get_var("SELECT robots FROM {$table_name} WHERE name = 'single-{$post_type}' AND post_type = '{$post_type}' AND section = 'single'"); $robots = !$robots || $robots === 'index, follow' ? true : false; ?>
                    <div class="radio-container">
                      <label class="container">Index
                        <input type="radio" <?= $robots ? 'checked="checked"' : '' ?> name="single-<?= $post_type ?>-robots" value="index, follow">
                        <span class="checkmark"></span>
                      </label>
                      <label class="container">NoIndex
                        <input type="radio" name="single-<?= $post_type ?>-robots" <?= !$robots ? 'checked="checked"' : '' ?> value="noindex, nofollow">
                        <span class="checkmark"></span>
                      </label>
                    </div>

                    <div class="button" data-type="<?= $post_type ?>" data-section="single" data-selector="single-<?= $post_type ?>">Save</div>
                    <p><code>%title%</code> <code>%excerpt%</code> <?= implode(' ', array_map(function($tax){ return '<code>%'.$tax.'%</code>'; }, get_object_taxonomies( $post_type ))); ?> <code>%current_month%</code> <code>%current_year%</code> <?= additional_tags($post_type) ?> <code>%sitename%</code></p>
                </div>
            </div>
            <?php } } ?>

            <?php foreach ($taxonomies as $tax => $taxonomy) { if(!in_array($tax, ['post_format', 'post_tag', 'category'])){ ?>
            <div class="item-content" id="tax-<?= $tax ?>" style="display: none;">
                <div class="heading"><?= ucfirst($tax) ?> Archive SEO</div>
                <div class="form" id="tax-<?= $tax ?>-form">
                    <input type="text" id="tax-<?= $tax ?>-title" name="tax-<?= $tax ?>-title" placeholder="<?= ucfirst($tax) ?> Title" value="<?= $wpdb->get_var("SELECT title FROM {$table_name} WHERE name = 'tax-{$tax}'") ?>">
                    <textarea rows="4" cols="50" id="tax-<?= $tax ?>-description" name="tax-<?= $tax ?>-description" placeholder="<?= ucfirst($tax) ?> Description"><?= $wpdb->get_var("SELECT description FROM {$table_name} WHERE name = 'tax-{$tax}'") ?></textarea>

                    <?php $robots = $wpdb->get_var("SELECT robots FROM {$table_name} WHERE name = 'tax-{$tax}'"); $robots = $robots === 'index, follow' ? true : false; ?>
                    <div class="radio-container">
                      <label class="container">Index
                        <input type="radio" <?= $robots ? 'checked="checked"' : '' ?> name="tax-<?= $tax ?>-robots" value="index, follow">
                        <span class="checkmark"></span>
                      </label>
                      <label class="container">NoIndex
                        <input type="radio" <?= !$robots ? 'checked="checked"' : '' ?> name="tax-<?= $tax ?>-robots" value="noindex, nofollow">
                        <span class="checkmark"></span>
                      </label>
                    </div>

                    <div class="button" data-type="taxonomy" data-section="archive" data-selector="tax-<?= $tax ?>">Save</div>
                </div>
            </div>
            <?php } } ?>

            <?php foreach ($taxonomies as $tax => $taxonomy) { if($tax != 'post_format'){ ?>
            <div class="item-content" id="<?= $tax ?>" style="display: none;">
                <div class="heading"><?= ucfirst($tax) ?> SEO</div>
                <div class="form" id="<?= $tax ?>-form">
                    <input type="text" id="<?= $tax ?>-title" name="<?= $tax ?>-title" placeholder="<?= ucfirst($tax) ?> Title" value="<?= $wpdb->get_var("SELECT title FROM {$table_name} WHERE name = '{$tax}' AND post_type = '{$tax}' AND section = 'taxonomy'") ?>">
                    <textarea rows="4" cols="50" id="<?= $tax ?>-description" name="<?= $tax ?>-description" placeholder="<?= ucfirst($tax) ?> Description"><?= $wpdb->get_var("SELECT description FROM {$table_name} WHERE name = '{$tax}' AND post_type = '{$tax}' AND section = 'taxonomy'") ?></textarea>

                    <?php $robots = $wpdb->get_var("SELECT robots FROM {$table_name} WHERE name = '{$tax}' AND post_type = '{$tax}' AND section = 'taxonomy'"); $robots = $robots === 'index, follow' ? true : false; ?>
                    <div class="radio-container">
                      <label class="container">Index
                        <input type="radio" <?= $robots ? 'checked="checked"' : '' ?> name="<?= $tax ?>-robots" value="index, follow">
                        <span class="checkmark"></span>
                      </label>
                      <label class="container">NoIndex
                        <input type="radio" name="<?= $tax ?>-robots" <?= !$robots ? 'checked="checked"' : '' ?> value="noindex, nofollow">
                        <span class="checkmark"></span>
                      </label>
                    </div>

                    <div class="button" data-type="<?= $tax ?>" data-section="taxonomy" data-selector="<?= $tax ?>">Save</div>
                    <p><code>%title%</code> <code>%current_month%</code> <code>%current_year%</code> <code>%sitename%</code></p>
                </div>
            </div>
            <?php } } ?>

        </div>
    </div>
    <?php
}

function seo_options_get_post_types() {
    $post_types = get_post_types( array( 'public' => true ) );
    return $post_types;
}

function additional_tags($post_type) {
    $tags = '';
    if ($post_type === 'drama' || $post_type === 'tv' || $post_type === 'movie') $tags .= '<code>%release_date%</code> <code>%release_month%</code> <code>%star_cast%</code> <code>%runtime%</code> <code>%tagline%</code> <code>%producer%</code> <code>%director%</code> <code>%writer%</code> <code>%production_house%</code>';

    if ($post_type === 'tv') $tags .= '<code>%total_seasons%</code>';
    if ($post_type === 'season') $tags .= '<code>%tv_series%</code> <code>%season_no%</code> <code>%total_episodes%</code> <code>%season_release_year%</code> <code>%season_release_month%</code>';
    if ($post_type === 'episode') $tags .= '<code>%tv_series%</code> <code>%season_no%</code> <code>%episode_no%</code> <code>%tv_series_year%</code> <code>%tv_series_month%</code> <code>%episode_release_date%</code>';
    if ($post_type === 'drama-episode') $tags .= '<code>%drama%</code> <code>%drama_year%</code> <code>%drama_month%</code> <code>%drama_channel%</code> <code>%drama_episode_release_date%</code> <code>%drama_episode_no%</code>';
    if ($post_type === 'drama') $tags .= '<code>%drama_total_episodes%</code>';
    if ($post_type === 'people') $tags .= '<code>%known_for%</code> <code>%He/She%</code> <code>%His/Her%</code> <code>%movie_or_drama_list%</code> <code>%all_jobs%</code>';
    if ($post_type === 'video') $tags .= '<code>%parent_title%</code> <code>%parent_description%</code>';

    return $tags;
}

add_action( 'add_meta_boxes', function ($post_type) { add_meta_box( 'seo_post_metabox', 'SEO Options', 'display_seo_options_single', $post_type, 'normal', 'low' ); } );

function display_seo_options_single( $post ) {
    global $wpdb;
    $result = $wpdb->get_row("SELECT seo_title,seo_description,meta_keywords FROM {$wpdb->prefix}posts WHERE `ID` = {$post->ID}"); ?>
    <div class="seo-options">
        <label for="seo_title">Seo Title</label>
        <input placeholder="Seo Title" type="text" id="seo_title" name="seo_title" aria-labelledby="seo_title-label" value="<?= $result->seo_title ?>">
        <label for="seo_description">Seo Description</label>
        <textarea rows="3" placeholder="Seo Description" id="seo_description" name="seo_description"><?= $result->seo_description ?></textarea>
        <p id="seo_description-description" class="description"><code>%title%</code> <code>%excerpt%</code> <code>%current_month%</code> <code>%current_year%</code> <code>%sitename%</code></p>
        <label for="meta_keywords">Meta Keywords</label>
        <input placeholder="Seo Meta Keywords" type="text" id="meta_keywords" name="meta_keywords" aria-labelledby="meta_keywords-label" value="<?= $result->meta_keywords ?>">

        <?php if ($post->post_type === 'post') { $article_type = get_post_meta($post->ID, 'article_type', true); ?>
            <div class="radio-container">
              <label class="container">Article
                <input type="radio" <?= $article_type === 'Article' || $article_type !== 'NewsArticle' ? 'checked="checked"' : '' ?> name="article_type" value="Article">
                <span class="checkmark"></span>
              </label>
              <label class="container">NewsArticle
                <input type="radio" name="article_type" <?= $article_type === 'NewsArticle' ? 'checked="checked"' : '' ?> value="NewsArticle">
                <span class="checkmark"></span>
              </label>
            </div>
        <?php } ?>
    </div>
<?php }


function save_seo_input_field($post_id) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( !current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    global $wpdb;
    if ( isset( $_POST['seo_title'] ) ) {
        $wpdb->update($wpdb->prefix.'posts', [ 'seo_title' => sanitize_text_field( $_POST['seo_title'] ) ], ['ID' => $post_id], ['%s'], ['%d']);
    }
    if ( isset( $_POST['seo_description'] ) ) {
        $wpdb->update($wpdb->prefix.'posts', [ 'seo_description' => sanitize_text_field( $_POST['seo_description'] ) ], ['ID' => $post_id], ['%s'], ['%d']);
    }
    if ( isset( $_POST['meta_keywords'] ) ) {
        $wpdb->update($wpdb->prefix.'posts', [ 'meta_keywords' => sanitize_text_field( $_POST['meta_keywords'] ) ], ['ID' => $post_id], ['%s'], ['%d']);
    }
    if (get_post_type($post_id) === 'post' && isset($_POST['article_type'])) {
        update_post_meta($post_id, 'article_type', $_POST['article_type']);
    }
}
add_action( 'save_post', 'save_seo_input_field' );

// function save_seo_input_field_taxonomy($term_id) {
//     if ( !current_user_can( 'edit_term', $term_id ) ) {
//         return;
//     }
//     global $wpdb;
//     if ( isset( $_POST['seo_title'] ) ) {
//         $wpdb->update($wpdb->prefix.'terms', [ 'seo_title' => sanitize_text_field( $_POST['seo_title'] ) ], ['ID' => $post_id], ['%s'], ['%d']);
//     }
//     if ( isset( $_POST['seo_description'] ) ) {
//         $wpdb->update($wpdb->prefix.'terms', [ 'seo_description' => sanitize_text_field( $_POST['seo_description'] ) ], ['ID' => $post_id], ['%s'], ['%d']);
//     }
//     if ( isset( $_POST['meta_keywords'] ) ) {
//         $wpdb->update($wpdb->prefix.'posts', [ 'meta_keywords' => sanitize_text_field( $_POST['meta_keywords'] ) ], ['ID' => $post_id], ['%s'], ['%d']);
//     }
// }
// add_action( 'edit_term', 'save_seo_input_field_taxonomy' );

























function your_metabox_callback($post) {
    // Retrieve the current value of the metabox field
    $meta_value = get_term_meta($post->term_id, 'your_metabox_field_name', true);

    ?>
    <p>
        <label for="your_metabox_field_name">Your Field Label:</label>
        <input type="text" id="your_metabox_field_name" name="your_metabox_field_name" value="<?php echo esc_attr($meta_value); ?>">
    </p>
    <?php
}

add_action('admin_menu', 'your_metabox_init');

function your_metabox_init() {
    $metabox = array(
        'id' => 'your_metabox_id', // Unique ID for the metabox
        'title' => 'Your Metabox Title', // Title displayed in the metabox
        'post_type' => 'your_taxonomy_name', // Taxonomy name
        'context' => 'normal', // Placement of the metabox (normal, side, advanced)
        'priority' => 'high', // Priority of the metabox (high, low, default)
        'callback' => 'your_metabox_callback', // Callback function to render the metabox
    );
    add_meta_box($metabox['id'], $metabox['title'], $metabox['callback'], $metabox['post_type'], $metabox['context'], $metabox['priority']);
}

add_action('edit_term', 'your_metabox_save');

function your_metabox_save($term_id) {
    if (isset($_POST['your_metabox_field_name'])) {
        $meta_value = sanitize_text_field($_POST['your_metabox_field_name']);
        update_term_meta($term_id, 'your_metabox_field_name', $meta_value);
    }
}