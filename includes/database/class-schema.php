<?php
/**
 * Database Schema.
 *
 * @package Shelvd
 */

namespace Shelvd\Database;

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
			'shelvd_enable_author_archives'    => 1,
			'shelvd_enable_publisher_archives'  => 1,
			'shelvd_enable_language_archives'   => 1,
			'shelvd_enable_schema_markup'       => 1,
			'shelvd_enable_search_extension'    => 1,
			'shelvd_isbn_lookup_service'        => 'google',
		);

		foreach ( $defaults as $key => $value ) {
			if ( false === get_option( $key ) ) {
				update_option( $key, $value );
			}
		}
	}
}
