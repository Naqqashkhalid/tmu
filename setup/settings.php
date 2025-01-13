<?php

function tmu_settings_menu() {
    add_menu_page(
        'TMU Setting Options',
        'TMU Settings',
        'manage_options',
        'tmu-settings',
        'tmu_settings_page',
        'dashicons-feedback'
    );

    add_submenu_page(
        'tmu-settings',
        'API Updates Options',
        'API Updates',
        'manage_options',
        'tmdb-api-update',
        'tmdb_api_update_page'
    );

    add_submenu_page(
        'tmu-settings',
        'SEO Options',
        'SEO Options',
        'manage_options',
        'seo-options',
        'seo_options'
    );
}
add_action('admin_menu', 'tmu_settings_menu');

function tmu_settings_page() {
    wp_register_script('tmu_settings', plugin_dir_url( __FILE__ ) . 'src/js/ajax.js', array( 'jquery' ), 1.1, true);
    wp_enqueue_script( 'tmu_settings' );
    wp_localize_script( 'tmu_settings', 'tmu_settings_params', array('ajaxurl' => admin_url( 'admin-ajax.php' )));
    wp_enqueue_script('tmu-settings-js', plugin_dir_url(__FILE__) . 'src/js/settings.js', array(), '1.0', true);
    wp_enqueue_style('tmu-settings-css', plugin_dir_url(__FILE__) . 'src/css/styles.css', array(), '1.0');
    
    $options = get_options( ['tmu_movies', 'tmu_tv_series', 'tmu_dramas'] );
    ?>
    <div class="settings-container wrap">
        <div class="settings-content">
            <div class="item-content">
                <div class="heading">Setting Options</div>
                
                <div class="form" id="update-movies-form">
                    <div class="label">Movies (Enable/Disable)</div>
                    <label class="switch">
                      <input type="checkbox" name="movies" id="tmu_movies" <?= $options['tmu_movies']==='on' ? 'checked' : '' ?>>
                      <span class="slider round"></span>
                    </label>
                </div>

                <div class="form" id="update-tv-series-form">
                    <div class="label">TV Series (Enable/Disable)</div>
                    <label class="switch">
                      <input type="checkbox" name="tv_series" id="tmu_tv_series" <?= $options['tmu_tv_series']==='on' ? 'checked' : '' ?>>
                      <span class="slider round"></span>
                    </label>
                </div>

                <div class="form" id="update-dramas-form">
                    <div class="label">Drama (Enable/Disable)</div>
                    <label class="switch">
                      <input type="checkbox" name="dramas" id="tmu_dramas" <?= $options['tmu_dramas']==='on' ? 'checked' : '' ?>>
                      <span class="slider round"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>
<?php
}