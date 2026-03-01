<?php
/**
 * Database Schema.
 *
 * @package WC_Flavor_Books
 */

namespace WC_Flavor_Books\Database;

defined( 'ABSPATH' ) || exit;

/**
 * Handles database setup on activation.
 */
class Schema {

	/**
	 * Install schema.
	 */
	public static function install() {
		self::set_default_options();
	}

	/**
	 * Set default plugin options.
	 */
	private static function set_default_options() {
		$defaults = array(
			'wc_flavor_books_enable_author_archives'    => 1,
			'wc_flavor_books_enable_publisher_archives'  => 1,
			'wc_flavor_books_enable_language_archives'   => 1,
			'wc_flavor_books_enable_schema_markup'       => 1,
			'wc_flavor_books_enable_search_extension'    => 1,
			'wc_flavor_books_isbn_lookup_service'        => 'google',
		);

		foreach ( $defaults as $key => $value ) {
			if ( false === get_option( $key ) ) {
				update_option( $key, $value );
			}
		}
	}
}
