<?php
/**
 * Plugin Name: Shelvd for WooCommerce
 * Plugin URI:  https://shelvd.org
 * Description: Transform WooCommerce into a professional bookshop platform with structured book metadata, author archives, ISBN lookup, and smart filtering.
 * Version:     1.0.0
 * Author:      Shelvd
 * Author URI:  https://shelvd.org
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: shelvd
 * Domain Path: /languages
 * Requires Plugins: woocommerce
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * WC requires at least: 7.0
 * WC tested up to: 9.6
 *
 * @package Shelvd
 */

defined( 'ABSPATH' ) || exit;

define( 'SHELVD_VERSION', '1.0.0' );
define( 'SHELVD_PLUGIN_FILE', __FILE__ );
define( 'SHELVD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SHELVD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SHELVD_MINIMUM_WC_VERSION', '7.0' );
define( 'SHELVD_MINIMUM_WP_VERSION', '6.0' );

require_once SHELVD_PLUGIN_DIR . 'includes/class-plugin.php';

register_activation_hook( __FILE__, array( 'Shelvd\\Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Shelvd\\Plugin', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'Shelvd\\Plugin', 'instance' ) );
