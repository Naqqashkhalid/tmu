<?php
namespace MBAIO;

use MetaBox\Updater\Option;

class Settings {
	private $option_name = 'meta_box_aio';

	public function __construct() {
		$this->migrate_settings();
		$this->auto_activate_extensions();

		add_action( 'init', [ $this, 'init' ], 0 );
	}

	/**
	 * Migrate the settings from the previous options to the new one.
	 * Do not save settings in the form of 'extension' => true.
	 * Instead save an array of active extensions.
	 */
	private function migrate_settings() {
		$option = get_option( $this->option_name );
		if ( empty( $option ) || isset( $option['extensions'] ) ) {
			return;
		}

		$dir        = dirname( __DIR__ ) . '/vendor/meta-box';
		$extensions = glob( "$dir/*", GLOB_ONLYDIR );
		$extensions = array_map( 'basename', $extensions );

		$option     = array_filter( $option );
		$extensions = array_intersect( $extensions, array_keys( $option ) );
		$option     = [
			'extensions' => $extensions,
		];
		update_option( $this->option_name, $option );
	}

	private function auto_activate_extensions() {
		$option = get_option( $this->option_name );
		if ( isset( $option['extensions'] ) ) {
			return;
		}
		$option['extensions'] = wp_list_pluck( $this->get_extensions(), 'slug' );
		update_option( $this->option_name, $option );
	}

	public function init() {
		// Allows developers to bypass the settings page by filter.
		if ( false === apply_filters( 'mb_aio_show_settings', true ) ) {
			return;
		}

		// Show Meta Box admin menu.
		add_filter( 'rwmb_admin_menu', '__return_true' );
		add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
	}

	public function add_settings_page() {
		$page_hook = add_submenu_page(
			'meta-box',
			esc_html__( 'Extensions', 'meta-box-aio' ),
			esc_html__( 'Extensions', 'meta-box-aio' ),
			'manage_options',
			'meta-box-aio',
			[ $this, 'render' ]
		);
		add_action( "load-{$page_hook}", [ $this, 'save' ] );
		add_action( "admin_print_styles-{$page_hook}", [ $this, 'enqueue' ] );
	}

	public function render() {
		if ( ! $this->is_license_active() ) {
			$this->show_license_warning();
			return;
		}

		$extensions        = $this->get_extensions();
		$option            = get_option( $this->option_name );
		$active_extensions = isset( $option['extensions'] ) ? $option['extensions'] : [];

		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		?>
		<div class="wrap">
			<header class="mbaio-header">
				<div class="mbaio-container">
					<a class="mbaio-title" target="_blank" href="https://elu.to/aiosm"><?php include dirname( __DIR__ ) . '/assets/meta-box.svg'; ?></a>
					<nav>
						<a target="_blank" href="https://elu.to/aiosd"><?php esc_html_e( 'Documentation', 'meta-box-aio' ); ?></a>
						<a target="_blank" href="https://elu.to/aioss"><?php esc_html_e( 'Support Forum', 'meta-box-aio' ); ?></a>
						<a target="_blank" href="https://elu.to/aiosa"><?php esc_html_e( 'My Account', 'meta-box-aio' ); ?></a>
					</nav>
				</div>
			</header>

			<div class="mbaio-container"><h1 class="screen-reader-text">Meta Box</h1></div><!-- For displaying admin notices -->

			<form action="" method="post" class="mbaio-container">
				<div id="poststuff">
					<div class="mbaio-filter">
						<h4><?php esc_html_e( 'Filter:', 'meta-box-aio' ) ?></h4>
						<ul>
							<li><a href="#" data-filter=""><?php esc_html_e( 'All', 'meta-box-aio' ); ?></a></li>
							<li><a href="#" data-filter="premium"><?php esc_html_e( 'Premium', 'meta-box-aio' ); ?></a></li>
							<li><a href="#" data-filter="free"><?php esc_html_e( 'Free', 'meta-box-aio' ); ?></a></li>
							<li><a href="#" data-filter="popular"><?php esc_html_e( 'Popular', 'meta-box-aio' ); ?></a></li>
							<li><a href="#" data-filter="data"><?php esc_html_e( 'Data', 'meta-box-aio' ); ?></a></li>
							<li><a href="#" data-filter="ui"><?php esc_html_e( 'UI', 'meta-box-aio' ); ?></a></li>
							<li><a href="#" data-filter="integration"><?php esc_html_e( 'Integration', 'meta-box-aio' ); ?></a></li>
							<li><a href="#" data-filter="admin"><?php esc_html_e( 'Admin', 'meta-box-aio' ); ?></a></li>
							<li><a href="#" data-filter="frontend"><?php esc_html_e( 'Frontend', 'meta-box-aio' ); ?></a></li>
						</ul>
					</div>
					<div id="post-body" class="columns-2">
						<div id="post-body-content">
							<table class="widefat mbaio-list">
								<thead>
									<tr>
										<td class="check-column"><input type="checkbox"></td>
										<th><strong><?php esc_html_e( 'Available Extensions', 'meta-box-aio' ); ?></strong></th>
										<th></th>
										<th></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ( $extensions as $extension ) : ?>
										<?php
										$info  = "https://metabox.io/plugins/{$extension['slug']}/?utm_source=settings_page&utm_medium=link&utm_campaign=aio";
										$docs  = "https://docs.metabox.io/extensions/{$extension['slug']}/?utm_source=settings_page&utm_medium=link&utm_campaign=aio";
										$forum = "https://metabox.io/support/forum/{$extension['slug']}/?utm_source=settings_page&utm_medium=link&utm_campaign=aio";

										if ( isset( $extension['info'] ) ) {
											$info = $extension['info'];
										}
										if ( isset( $extension['docs'] ) ) {
											$docs = $extension['docs'];
										}
										if ( isset( $extension['forum'] ) ) {
											$forum = $extension['forum'];
										}
										$is_active = in_array( $extension['slug'], $active_extensions );
										$class     = $is_active ? 'mbaio-active' : '';

										$require = ! isset( $extension['require'] ) || is_plugin_active( $extension['require'] );
										?>
										<tr class="<?php echo esc_attr( $class ) ?>">
											<th class="check-column">
												<?php if ( $require ) : ?>
													<input type="checkbox" name="meta_box_aio[extensions][]" value="<?php echo esc_attr( $extension['slug'] ) ?>" <?php checked( $is_active ) ?>>
												<?php else : ?>
													<input type="checkbox" disabled value="<?php echo esc_attr( $extension['slug'] ) ?>">
												<?php endif; ?>
											</th>
											<td>
												<a target="_blank" class="mbaio-tooltip" data-tippy-content="<?php echo esc_attr( $extension['desc'] ) ?>" href="<?php echo esc_url( $info ) ?>"><?php echo esc_html( $extension['title'] ) ?></a>
												<?php if ( ! $require ) : ?>
													<?php // Translators: %s - Plugin name ?>
													<?php $this->tooltip( sprintf( __( 'This extension requires plugin %s to be installed and activated.', 'meta-box-aio' ), $extension['plugin'] ), 'warning' ) ?>
												<?php endif; ?>
											</td>
											<td class="mbaio-link">
												<?php if ( $docs ) : ?>
													<a target="_blank" href="<?php echo esc_url( $docs ) ?>"><?php esc_html_e( 'Docs', 'meta-box-aio' ) ?></a>
												<?php endif; ?>
											</td>
											<td class="mbaio-link">
												<?php if ( $forum ) : ?>
													<a target="_blank" href="<?php echo esc_url( $forum ) ?>"><?php esc_html_e( 'Forum', 'meta-box-aio' ) ?></a>
												<?php endif; ?>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>

							<?php submit_button( __( 'Save Changes', 'meta-box-aio' ) ) ?>
						</div>
						<div id="postbox-container-1" class="postbox-container">
							<div class="postbox">
								<h2 class="hndle"><span class="dashicons dashicons-admin-links"></span> <?php esc_html_e( 'Quick Links', 'meta-box-aio' ) ?></h2>
								<div class="inside">
									<ul>
										<li><a target="_blank" href="https://elu.to/aiosa"><?php esc_html_e( 'My Account', 'meta-box-aio' ) ?></a></li>
										<li><a target="_blank" href="https://elu.to/aiosd"><?php esc_html_e( 'Documentation', 'meta-box-aio' ) ?></a></li>
										<li><a target="_blank" href="https://elu.to/aioss"><?php esc_html_e( 'Support Forum', 'meta-box-aio' ) ?></a></li>
									</ul>
								</div>
							</div>
							<div class="postbox">
								<h2 class="hndle"><span class="dashicons dashicons-groups"></span> <?php esc_html_e( 'Meta Box Community', 'meta-box-aio' ) ?></h2>
								<div class="inside">
									<p><?php esc_html_e( 'Join the community of super helpful Meta Box users. Say hello, ask questions, give feedback, help each other and get faster update information!', 'meta-box-aio' ); ?></p>
									<p><a target="_blank" href="https://elu.to/aiosfb"><?php esc_html_e( 'Join Our Facebook Group →', 'meta-box-aio' ) ?></a></p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
		<?php
	}

	public function show_license_warning() {
		$settings_page = $this->get_updater()->is_network_activated() ? network_admin_url( 'settings.php?page=meta-box-updater' ) : admin_url( 'admin.php?page=meta-box-updater' );

		$status   = $this->get_updater()->get_license_status();
		$messages = [
			// Translators: %1$s - URL to the settings page.
			'no_key'  => __( 'You have not set your Meta Box license key yet. Please <a href="%1$s">enter your license key</a> to continue.', 'meta-box-aio' ),
			// Translators: %1$s - URL to the settings page.
			'invalid' => __( 'Your license key for Meta Box is <b>invalid</b>. Please <a href="%1$s">update your license key</a> to continue.', 'meta-box-aio' ),
			// Translators: %1$s - URL to the settings page.
			'error'   => __( 'Your license key for Meta Box is <b>invalid</b>. Please <a href="%1$s">update your license key</a> to continue.', 'meta-box-aio' ),
			// Translators: %2$s - URL to the My Account page.
			'expired' => __( 'Your license key for Meta Box is <b>expired</b>. Please <a href="%2$s" target="_blank">renew your license</a> to continue.', 'meta-box-aio' ),
		];

		?>
		<div class="wrap">
			<header class="mbaio-header">
				<div class="mbaio-container">
					<a class="mbaio-title" target="_blank" href="https://elu.to/aiosm"><?php include dirname( __DIR__ ) . '/assets/meta-box.svg'; ?></a>
					<nav>
						<a target="_blank" href="https://elu.to/aiosd"><?php esc_html_e( 'Documentation', 'meta-box-aio' ); ?></a>
						<a target="_blank" href="https://elu.to/aioss"><?php esc_html_e( 'Support Forum', 'meta-box-aio' ); ?></a>
						<a target="_blank" href="https://elu.to/aiosa"><?php esc_html_e( 'My Account', 'meta-box-aio' ); ?></a>
					</nav>
				</div>
			</header>

			<div class="mbaio-container"><h1 class="screen-reader-text">Meta Box</h1></div><!-- For displaying admin notices -->

			<form action="" method="post" class="mbaio-container">
				<div id="poststuff">
					<div class="mbaio-license-warning">
						<h2>
							<span class="dashicons dashicons-warning"></span>
							<?php esc_html_e( 'License Warning', 'meta-box-aio' ) ?>
						</h2>
						<?php echo wp_kses_post( sprintf( $messages[ $status ], $settings_page, 'https://elu.to/aiosa' ) ); ?>
					</div>
				</div>
			</form>
		</div>
		<?php
	}


	public function save() {
		if ( ! isset( $_POST['submit'] ) || empty( $_POST['meta_box_aio'] ) ) {
			return;
		}
		$data = $_POST['meta_box_aio'];
		update_option( 'meta_box_aio', $data );

		add_settings_error( null, 'meta-box-aio', __( 'Settings saved.', 'meta-box-aio' ), 'updated' );
		add_action( 'admin_notices', 'settings_errors' );
	}

	public function enqueue() {
		wp_enqueue_style( 'meta-box-aio', plugin_dir_url( __DIR__ ) . 'assets/aio.css', [], '1.23.0' );
		wp_register_script( 'tippy', 'https://cdn.jsdelivr.net/combine/npm/@popperjs/core@2.11.2/dist/umd/popper.min.js,npm/tippy.js@6.3.7/dist/tippy-bundle.umd.min.js', [], '6.3.7', true );
		wp_enqueue_script( 'meta-box-aio', plugin_dir_url( __DIR__ ) . 'assets/aio.js', [ 'tippy' ], '1.23.0', true );
	}

	private function get_extensions() {
		$extensions = [
			[
				'slug'  => 'mb-acf-migration',
				'title' => 'MB ACF Migration',
				'desc'  => __( 'Migrate field groups and custom fields from Advanced Custom Fields to Meta Box', 'meta-box-aio' ),
			],
			[
				'slug'  => 'mb-admin-columns',
				'title' => 'MB Admin Columns',
				'desc'  => __( 'Display custom fields in table columns in admin screens for All Posts (types).', 'meta-box-aio' ),
			],
			[
				'slug'  => 'mb-blocks',
				'title' => 'MB Blocks',
				'desc'  => __( 'Creating custom Gutenberg blocks with PHP. No React, Webpack or Babel. Beautiful syntax, powerful features.', 'meta-box-aio' ),
			],
			[
				'slug'  => 'mb-comment-meta',
				'title' => 'MB Comment Meta',
				'docs'  => false,
				'desc'  => __( 'Add custom fields to comments in WordPress. Support all field types and options.', 'meta-box-aio' ),
			],
			[
				'slug'  => 'mb-custom-post-type',
				'title' => 'MB Custom Post Type',
				'info'  => 'https://metabox.io/plugins/custom-post-type/',
				'desc'  => __( 'Create and manage custom post types easily in WordPress with an easy-to-use interface.', 'meta-box-aio' ),
			],
			[
				'slug'  => 'mb-custom-table',
				'title' => 'MB Custom Table',
				'desc'  => __( 'Save custom fields data to custom table instead of the default meta tables. Reduce database size and increase performance.', 'meta-box-aio' ),
			],
			[
				'slug'  => 'mb-divi-integrator',
				'title' => 'MB Divi Integrator',
				'docs'  => false,
				'desc'  => __( 'Connect and display custom fields created by the Meta Box plugin in the Divi.', 'meta-box-aio' ),
			],
			[
				'slug'  => 'mb-frontend-submission',
				'title' => 'MB Frontend Submission',
				'desc'  => __( 'Create editorial forms so users can submit blog posts on the front end.', 'meta-box-aio' ),
			],
			[
				'slug'  => 'mb-rank-math',
				'title' => 'MB Rank Math',
				'desc'  => __( 'Add content of custom fields to Rank Math Content Analysis to have better/correct SEO score.', 'meta-box-aio' ),
			],
			[
				'slug'  => 'mb-relationships',
				'title' => 'MB Relationships',
				'desc'  => __( 'A lightweight WordPress plugin for creating many-to-many relationships between posts, terms and users.', 'meta-box-aio' ),
			],
			[
				'slug'  => 'mb-rest-api',
				'title' => 'MB REST API',
				'desc'  => __( 'Pull all meta value from posts, terms into the WP REST API responses.', 'meta-box-aio' ),
			],
			[
				'slug'  => 'mb-revision',
				'title' => 'MB Revision',
				'desc'  => __( 'Track changes of custom fields with WordPress revision. Save, compare, restore the changes easily.', 'meta-box-aio' ),
			],
			[
				'slug'  => 'mb-settings-page',
				'title' => 'MB Settings Page',
				'desc'  => __( 'Create impressive and robust custom settings pages in a few clicks.', 'meta-box-aio' ),
			],
			[
				'slug'  => 'mb-term-meta',
				'title' => 'MB Term Meta',
				'desc'  => __( 'Easily add custom fields to categories, tags or any custom taxonomy.', 'meta-box-aio' ),
			],
			[
				'slug'  => 'mb-toolset-migration',
				'title' => 'MB Toolset Migration',
				'desc'  => __( 'Migrate post types, field groups, custom fields and relationships from Toolset to Meta Box', 'meta-box-aio' ),
			],
			[
				'slug'  => 'mb-user-meta',
				'title' => 'MB User Meta',
				'desc'  => __( 'Add custom fields to user profile (user meta) quickly with simple syntax.', 'meta-box-aio' ),
			],
			[
				'slug'  => 'mb-user-profile',
				'title' => 'MB User Profile',
				'desc'  => __( 'Create register, login and edit user profile forms in the frontend. Embed everywhere with shortcodes.', 'meta-box-aio' ),
			],
			[
				'slug'  => 'mb-views',
				'title' => 'MB Views',
				'desc'  => __( 'Build front-end templates for WordPress without touching theme files. Support Twig and all field types.', 'meta-box-aio' ),
			],
			[
				'slug'    => 'meta-box-beaver-themer-integrator',
				'title'   => 'Meta Box - Beaver Themer Integrator',
				'docs'    => false,
				'desc'    => __( 'Select and show custom fields created by the Meta Box plugin in the Beaver Themer field connection.', 'meta-box-aio' ),
				'require' => 'bb-theme-builder/bb-theme-builder.php',
				'plugin'  => 'Beaver Themer',
			],
			[
				'slug'  => 'meta-box-builder',
				'title' => 'Meta Box Builder',
				'desc'  => __( 'Drag and drop your custom fields into place without a single line of code.', 'meta-box-aio' ),
			],
			[
				'slug'  => 'meta-box-columns',
				'title' => 'Meta Box Columns',
				'desc'  => __( 'Display fields more beautiful by putting them into 12-columns grid.', 'meta-box-aio' ),
			],
			[
				'slug'  => 'meta-box-conditional-logic',
				'title' => 'Meta Box Conditional Logic',
				'desc'  => __( 'Control when and where meta boxes, fields and HTML elements appear.', 'meta-box-aio' ),
			],
			[
				'slug'    => 'mb-elementor-integrator',
				'title'   => 'Meta Box - Elementor Integrator',
				'docs'    => false,
				'desc'    => __( 'Connect and display custom fields created by the Meta Box plugin in the Elementor\'s dynamic tags.', 'meta-box-aio' ),
				'require' => 'elementor-pro/elementor-pro.php',
				'plugin'  => 'Elementor Pro',
			],
			[
				'slug'    => 'meta-box-facetwp-integrator',
				'title'   => 'Meta Box - FacetWP Integrator',
				'docs'    => false,
				'desc'    => __( 'Integrates Meta Box and FacetWP, makes custom fields searchable and filterable in the frontend.', 'meta-box-aio' ),
				'require' => 'facetwp/index.php',
				'plugin'  => 'FacetWP',
			],
			[
				'slug'  => 'meta-box-geolocation',
				'title' => 'Meta Box Geolocation',
				'desc'  => __( 'Automatically and instantly populate location data with the power of Google Maps Geolocation API.', 'meta-box-aio' ),
			],
			[
				'slug'  => 'meta-box-group',
				'title' => 'Meta Box Group',
				'desc'  => __( 'Organize custom fields into robust and intensely user-friendly groups.', 'meta-box-aio' ),
			],
			[
				'slug'  => 'meta-box-include-exclude',
				'title' => 'Meta Box Include Exclude',
				'desc'  => __( 'Show or hide meta boxes whenever and for whomever you choose.', 'meta-box-aio' ),
			],
			[
				'slug'  => 'meta-box-show-hide',
				'title' => 'Meta Box Show Hide',
				'desc'  => __( 'Toggle meta boxes by page template, post format or taxonomy using JS.', 'meta-box-aio' ),
			],
			[
				'slug'  => 'meta-box-tabs',
				'title' => 'Meta Box Tabs',
				'desc'  => __( 'Add as many custom fields as you want and organize them into tabs.', 'meta-box-aio' ),
			],
			[
				'slug'  => 'meta-box-template',
				'title' => 'Meta Box Template',
				'desc'  => __( 'Define custom meta boxes and custom fields easier with templates.', 'meta-box-aio' ),
			],
			[
				'slug'  => 'meta-box-text-limiter',
				'title' => 'Meta Box Text Limiter',
				'docs'  => false,
				'desc'  => __( 'Limit the number of characters or words entered for text and textarea fields.', 'meta-box-aio' ),
			],
			[
				'slug'  => 'meta-box-tooltip',
				'title' => 'Meta Box Tooltip',
				'desc'  => __( 'Display help information for fields using beautiful tooltips.', 'meta-box-aio' ),
			],
			[
				'slug'    => 'meta-box-yoast-seo',
				'title'   => 'Meta Box For Yoast SEO',
				'docs'    => false,
				'forum'   => 'https://metabox.io/support/forum/meta-box-for-yoast-seo/',
				'desc'    => __( 'Add content of custom fields to Yoast SEO Content Analysis to have better/correct SEO score.', 'meta-box-aio' ),
				'require' => 'wordpress-seo/wp-seo.php',
				'plugin'  => 'Yoast SEO',
			],
		];

		$slugs = wp_list_pluck( $extensions, 'slug' );
		$slugs = apply_filters( 'mb_aio_extensions', $slugs );
		$slugs = array_unique( $slugs );

		$extensions = array_filter(
			$extensions, function ( $extension ) use ( $slugs ) {
				return in_array( $extension['slug'], $slugs );
			}
		);

		return $extensions;
	}

	private function tooltip( $content, $icon = 'info' ) {
		if ( 'info' === $icon ) {
			echo '<button type="button" class="mbaio-tooltip" data-tippy-content="' . esc_attr( $content ) . '"><span class="dashicons dashicons-editor-help"></span></button>';
			return;
		}
		echo '<button type="button" class="mbaio-tooltip" data-tippy-content="' . esc_attr( $content ) . '">';
		include dirname( __DIR__ ) . '/assets/warning.svg';
		echo '</button>';
	}

	private function is_license_active(): bool {
		return $this->get_updater()->get_license_status() === 'active';
	}

	private function get_updater(): Option {
		static $updater;

		if ( ! $updater ) {
			$updater = new Option();
		}

		return $updater;
	}
}