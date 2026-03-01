<?php
/**
 * Plugin Settings.
 *
 * @package WC_Flavor_Books
 */

namespace WC_Flavor_Books;

defined( 'ABSPATH' ) || exit;

use WC_Flavor_Books\Traits\Singleton;

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
		$sections['wc_flavor_books'] = __( 'Books', 'wc-flavor-books' );
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
		if ( 'wc_flavor_books' !== $current_section ) {
			return $settings;
		}

		return array(
			// Section title.
			array(
				'title' => __( 'WC Flavor: Books', 'wc-flavor-books' ),
				'type'  => 'title',
				'desc'  => __( 'Configure book-specific features for your WooCommerce store.', 'wc-flavor-books' ),
				'id'    => 'wc_flavor_books_options',
			),

			// Archives.
			array(
				'title'   => __( 'Author Archives', 'wc-flavor-books' ),
				'desc'    => __( 'Enable browsable author archive pages.', 'wc-flavor-books' ),
				'id'      => 'wc_flavor_books_enable_author_archives',
				'default' => '1',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Publisher Archives', 'wc-flavor-books' ),
				'desc'    => __( 'Enable browsable publisher archive pages.', 'wc-flavor-books' ),
				'id'      => 'wc_flavor_books_enable_publisher_archives',
				'default' => '1',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Language Archives', 'wc-flavor-books' ),
				'desc'    => __( 'Enable browsable language archive pages.', 'wc-flavor-books' ),
				'id'      => 'wc_flavor_books_enable_language_archives',
				'default' => '1',
				'type'    => 'checkbox',
			),

			// SEO.
			array(
				'title'   => __( 'Schema.org Markup', 'wc-flavor-books' ),
				'desc'    => __( 'Output structured Book data for search engines.', 'wc-flavor-books' ),
				'id'      => 'wc_flavor_books_enable_schema_markup',
				'default' => '1',
				'type'    => 'checkbox',
			),

			// Search.
			array(
				'title'   => __( 'Extended Search', 'wc-flavor-books' ),
				'desc'    => __( 'Include author names and ISBN in product search results.', 'wc-flavor-books' ),
				'id'      => 'wc_flavor_books_enable_search_extension',
				'default' => '1',
				'type'    => 'checkbox',
			),

			// ISBN Lookup.
			array(
				'title'   => __( 'ISBN Lookup Service', 'wc-flavor-books' ),
				'desc'    => __( 'Primary service for ISBN lookups in the product editor.', 'wc-flavor-books' ),
				'id'      => 'wc_flavor_books_isbn_lookup_service',
				'default' => 'google',
				'type'    => 'select',
				'options' => array(
					'google'       => __( 'Google Books (recommended)', 'wc-flavor-books' ),
					'open_library' => __( 'Open Library', 'wc-flavor-books' ),
				),
			),

			// Section end.
			array(
				'type' => 'sectionend',
				'id'   => 'wc_flavor_books_options',
			),
		);
	}
}
