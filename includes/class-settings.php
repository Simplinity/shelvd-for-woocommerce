<?php
/**
 * Plugin Settings.
 *
 * @package Shelvd
 */

namespace Shelvd;

defined( 'ABSPATH' ) || exit;

use Shelvd\Traits\Singleton;

/**
 * Adds a settings section under WooCommerce > Settings > Products.
 */
class Settings {

	use Singleton;

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_filter( 'woocommerce_get_sections_products', array( $this, 'add_section' ) );
		add_filter( 'woocommerce_get_settings_products', array( $this, 'get_settings' ), 10, 2 );
	}

	/**
	 * Add "Books" section under Products tab.
	 *
	 * @param array $sections Sections.
	 * @return array
	 */
	public function add_section( $sections ) {
		$sections['shelvd'] = __( 'Books', 'shelvd' );
		return $sections;
	}

	/**
	 * Get settings for our section.
	 *
	 * @param array  $settings        Existing settings.
	 * @param string $current_section Current section ID.
	 * @return array
	 */
	public function get_settings( $settings, $current_section ) {
		if ( 'shelvd' !== $current_section ) {
			return $settings;
		}

		return array(
			// Section title.
			array(
				'title' => __( 'Shelvd for WooCommerce', 'shelvd' ),
				'type'  => 'title',
				'desc'  => __( 'Configure book-specific features for your WooCommerce store.', 'shelvd' ),
				'id'    => 'shelvd_options',
			),

			// Archives.
			array(
				'title'   => __( 'Author Archives', 'shelvd' ),
				'desc'    => __( 'Enable browsable author archive pages.', 'shelvd' ),
				'id'      => 'shelvd_enable_author_archives',
				'default' => '1',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Publisher Archives', 'shelvd' ),
				'desc'    => __( 'Enable browsable publisher archive pages.', 'shelvd' ),
				'id'      => 'shelvd_enable_publisher_archives',
				'default' => '1',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Language Archives', 'shelvd' ),
				'desc'    => __( 'Enable browsable language archive pages.', 'shelvd' ),
				'id'      => 'shelvd_enable_language_archives',
				'default' => '1',
				'type'    => 'checkbox',
			),

			// SEO.
			array(
				'title'   => __( 'Schema.org Markup', 'shelvd' ),
				'desc'    => __( 'Output structured Book data for search engines.', 'shelvd' ),
				'id'      => 'shelvd_enable_schema_markup',
				'default' => '1',
				'type'    => 'checkbox',
			),

			// Search.
			array(
				'title'   => __( 'Extended Search', 'shelvd' ),
				'desc'    => __( 'Include author names and ISBN in product search results.', 'shelvd' ),
				'id'      => 'shelvd_enable_search_extension',
				'default' => '1',
				'type'    => 'checkbox',
			),

			// ISBN Lookup.
			array(
				'title'   => __( 'ISBN Lookup Service', 'shelvd' ),
				'desc'    => __( 'Primary service for ISBN lookups in the product editor.', 'shelvd' ),
				'id'      => 'shelvd_isbn_lookup_service',
				'default' => 'google',
				'type'    => 'select',
				'options' => array(
					'google'       => __( 'Google Books (recommended)', 'shelvd' ),
					'open_library' => __( 'Open Library', 'shelvd' ),
				),
			),

			// Section end.
			array(
				'type' => 'sectionend',
				'id'   => 'shelvd_options',
			),
		);
	}
}
