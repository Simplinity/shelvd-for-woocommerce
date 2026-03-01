<?php
/**
 * Plugin Name: WC Flavor: Books
 * Plugin URI:  https://wcflavor.com/books
 * Description: Transform WooCommerce into a professional bookshop platform with structured book metadata, author archives, ISBN lookup, and smart filtering.
 * Version:     1.0.0
 * Author:      WC Flavor
 * Author URI:  https://wcflavor.com
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wc-flavor-books
 * Domain Path: /languages
 * Requires Plugins: woocommerce
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * WC requires at least: 7.0
 * WC tested up to: 9.6
 *
 * @package WC_Flavor_Books
 */

defined( 'ABSPATH' ) || exit;

define( 'WC_FLAVOR_BOOKS_VERSION', '1.0.0' );
define( 'WC_FLAVOR_BOOKS_PLUGIN_FILE', __FILE__ );
define( 'WC_FLAVOR_BOOKS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WC_FLAVOR_BOOKS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WC_FLAVOR_BOOKS_MINIMUM_WC_VERSION', '7.0' );
define( 'WC_FLAVOR_BOOKS_MINIMUM_WP_VERSION', '6.0' );

require_once WC_FLAVOR_BOOKS_PLUGIN_DIR . 'includes/class-plugin.php';

register_activation_hook( __FILE__, array( 'WC_Flavor_Books\\Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WC_Flavor_Books\\Plugin', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'WC_Flavor_Books\\Plugin', 'instance' ) );
