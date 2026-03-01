<?php
/**
 * Main plugin class.
 *
 * @package Shelvd
 */

namespace Shelvd;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin orchestrator.
 */
class Plugin {

	use Traits\Singleton;

	/**
	 * Whether dependencies are met.
	 *
	 * @var bool
	 */
	private $dependencies_met = false;

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->dependencies_met = $this->check_dependencies();

		if ( ! $this->dependencies_met ) {
			return;
		}

		$this->load_includes();
		$this->init_hooks();
	}

	/**
	 * Check plugin dependencies.
	 *
	 * @return bool
	 */
	private function check_dependencies() {
		if ( version_compare( get_bloginfo( 'version' ), SHELVD_MINIMUM_WP_VERSION, '<' ) ) {
			add_action( 'admin_notices', function () {
				printf(
					'<div class="notice notice-error"><p><strong>Shelvd for WooCommerce</strong> %s</p></div>',
					/* translators: %s: minimum WordPress version */
					esc_html( sprintf( __( 'requires WordPress %s or higher.', 'shelvd' ), SHELVD_MINIMUM_WP_VERSION ) )
				);
			} );
			return false;
		}

		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action( 'admin_notices', function () {
				echo '<div class="notice notice-error"><p><strong>Shelvd for WooCommerce</strong> ';
				esc_html_e( 'requires WooCommerce to be installed and activated.', 'shelvd' );
				echo '</p></div>';
			} );
			return false;
		}

		if ( version_compare( WC()->version, SHELVD_MINIMUM_WC_VERSION, '<' ) ) {
			add_action( 'admin_notices', function () {
				printf(
					'<div class="notice notice-error"><p><strong>Shelvd for WooCommerce</strong> %s</p></div>',
					/* translators: %s: minimum WooCommerce version */
					esc_html( sprintf( __( 'requires WooCommerce %s or higher.', 'shelvd' ), SHELVD_MINIMUM_WC_VERSION ) )
				);
			} );
			return false;
		}

		return true;
	}

	/**
	 * Load include files.
	 */
	private function load_includes() {
		// Core.
		require_once SHELVD_PLUGIN_DIR . 'includes/database/class-taxonomy-manager.php';
		require_once SHELVD_PLUGIN_DIR . 'includes/database/class-schema.php';
		require_once SHELVD_PLUGIN_DIR . 'includes/lib/class-book-meta.php';
		require_once SHELVD_PLUGIN_DIR . 'includes/lib/class-isbn-validator.php';

		// Admin.
		if ( is_admin() ) {
			require_once SHELVD_PLUGIN_DIR . 'includes/admin/class-product-editor.php';
			require_once SHELVD_PLUGIN_DIR . 'includes/admin/class-isbn-lookup.php';
			require_once SHELVD_PLUGIN_DIR . 'includes/class-settings.php';
		}

		// Frontend.
		if ( ! is_admin() || wp_doing_ajax() ) {
			require_once SHELVD_PLUGIN_DIR . 'includes/frontend/class-product-display.php';
			require_once SHELVD_PLUGIN_DIR . 'includes/frontend/class-filter-widgets.php';
		}

		// REST API.
		require_once SHELVD_PLUGIN_DIR . 'includes/api/class-rest-controller.php';
	}

	/**
	 * Initialize hooks and components.
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'init', array( Database\Taxonomy_Manager::class, 'register_all' ), 5 );
		add_action( 'init', array( Lib\Book_Meta::class, 'register_meta_fields' ) );

		// Admin.
		if ( is_admin() ) {
			Admin\Product_Editor::instance();
			Settings::instance();
		}

		// Frontend.
		if ( ! is_admin() || wp_doing_ajax() ) {
			Frontend\Product_Display::instance();
			Frontend\Filter_Widgets::instance();
		}

		// REST API.
		Api\Rest_Controller::instance();

		// Assets.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		// HPOS compatibility.
		add_action( 'before_woocommerce_init', function () {
			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', SHELVD_PLUGIN_FILE, true );
			}
		} );
	}

	/**
	 * Load translations.
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'shelvd',
			false,
			dirname( plugin_basename( SHELVD_PLUGIN_FILE ) ) . '/languages'
		);
	}

	/**
	 * Enqueue frontend assets.
	 */
	public function enqueue_frontend_assets() {
		if ( ! is_woocommerce() && ! is_product() ) {
			return;
		}

		wp_enqueue_style(
			'shelvd-frontend',
			SHELVD_PLUGIN_URL . 'assets/css/frontend.css',
			array(),
			SHELVD_VERSION
		);

		wp_enqueue_script(
			'shelvd-filters',
			SHELVD_PLUGIN_URL . 'assets/js/filters.js',
			array( 'jquery' ),
			SHELVD_VERSION,
			true
		);

		wp_localize_script( 'shelvd-filters', 'shelvd', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'shelvd-filter' ),
		) );
	}

	/**
	 * Enqueue admin assets.
	 */
	public function enqueue_admin_assets( $hook_suffix ) {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}

		// Product editor.
		if ( in_array( $screen->id, array( 'product', 'edit-product' ), true ) ) {
			wp_enqueue_style(
				'shelvd-admin',
				SHELVD_PLUGIN_URL . 'assets/css/admin.css',
				array(),
				SHELVD_VERSION
			);

			wp_enqueue_script(
				'shelvd-admin',
				SHELVD_PLUGIN_URL . 'assets/js/admin.js',
				array( 'jquery' ),
				SHELVD_VERSION,
				true
			);

			wp_localize_script( 'shelvd-admin', 'shelvdAdmin', array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'shelvd-admin' ),
				'i18n'    => array(
					'lookingUp'   => __( 'Looking up ISBN...', 'shelvd' ),
					'found'       => __( 'Book data loaded.', 'shelvd' ),
					'notFound'    => __( 'No data found for this ISBN.', 'shelvd' ),
					'invalidIsbn' => __( 'Invalid ISBN format.', 'shelvd' ),
					'error'       => __( 'Lookup failed. Try again.', 'shelvd' ),
				),
			) );
		}

	}

	/**
	 * Plugin activation.
	 */
	public static function activate() {
		require_once SHELVD_PLUGIN_DIR . 'includes/traits/trait-singleton.php';
		require_once SHELVD_PLUGIN_DIR . 'includes/database/class-taxonomy-manager.php';
		require_once SHELVD_PLUGIN_DIR . 'includes/database/class-schema.php';

		Database\Taxonomy_Manager::register_all();
		Database\Schema::install();

		flush_rewrite_rules();

		update_option( 'shelvd_version', SHELVD_VERSION );
	}

	/**
	 * Plugin deactivation.
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}
}
