<?php
/**
 * Frontend product display.
 *
 * @package Shelvd
 */

namespace Shelvd\Frontend;

defined( 'ABSPATH' ) || exit;

use Shelvd\Lib\Book_Meta;
use Shelvd\Traits\Singleton;

/**
 * Displays book metadata on product pages and adds Schema.org markup.
 */
class Product_Display {

	use Singleton;

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'woocommerce_single_product_summary', array( $this, 'display_book_info' ), 25 );
		add_filter( 'woocommerce_product_tabs', array( $this, 'add_book_tab' ) );
		add_action( 'wp_head', array( $this, 'schema_markup' ) );

		// Extend search.
		if ( get_option( 'shelvd_enable_search_extension', 1 ) ) {
			add_filter( 'posts_search', array( $this, 'extend_search' ), 10, 2 );
		}
	}

	/**
	 * Display book info in product summary.
	 */
	public function display_book_info() {
		global $product;

		if ( ! $product ) {
			return;
		}

		$product_id = $product->get_id();
		$meta       = Book_Meta::get_all( $product_id );
		$authors    = wp_get_post_terms( $product_id, 'book_author' );
		$publishers = wp_get_post_terms( $product_id, 'book_publisher' );
		$languages  = wp_get_post_terms( $product_id, 'book_language' );

		if ( empty( $meta ) && ( is_wp_error( $authors ) || empty( $authors ) ) ) {
			return;
		}

		$template = SHELVD_PLUGIN_DIR . 'templates/product/book-metadata.php';

		// Allow theme override.
		$theme_template = locate_template( 'shelvd/product/book-metadata.php' );
		if ( $theme_template ) {
			$template = $theme_template;
		}

		include $template;
	}

	/**
	 * Add "Book Details" tab on product page.
	 *
	 * @param array $tabs Tabs.
	 * @return array
	 */
	public function add_book_tab( $tabs ) {
		global $product;

		if ( ! $product || ! Book_Meta::has_book_data( $product->get_id() ) ) {
			return $tabs;
		}

		$tabs['book_details'] = array(
			'title'    => __( 'Book Details', 'shelvd' ),
			'priority' => 15,
			'callback' => array( $this, 'render_book_tab' ),
		);

		return $tabs;
	}

	/**
	 * Render book details tab content.
	 */
	public function render_book_tab() {
		global $product;

		$product_id = $product->get_id();
		$meta       = Book_Meta::get_all( $product_id );
		$authors    = wp_get_post_terms( $product_id, 'book_author' );
		$publishers = wp_get_post_terms( $product_id, 'book_publisher' );
		$languages  = wp_get_post_terms( $product_id, 'book_language' );

		$template = SHELVD_PLUGIN_DIR . 'templates/product/book-metadata.php';
		$theme_template = locate_template( 'shelvd/product/book-metadata.php' );
		if ( $theme_template ) {
			$template = $theme_template;
		}

		include $template;
	}

	/**
	 * Output Schema.org Book markup.
	 */
	public function schema_markup() {
		if ( ! is_product() || ! get_option( 'shelvd_enable_schema_markup', 1 ) ) {
			return;
		}

		global $product;
		if ( ! $product ) {
			return;
		}

		$product_id = $product->get_id();
		$meta       = Book_Meta::get_all( $product_id );

		if ( empty( $meta ) ) {
			return;
		}

		$authors = wp_get_post_terms( $product_id, 'book_author' );

		$schema = array(
			'@context' => 'https://schema.org',
			'@type'    => 'Book',
			'name'     => $product->get_title(),
			'url'      => get_permalink( $product_id ),
		);

		if ( ! is_wp_error( $authors ) && ! empty( $authors ) ) {
			$schema['author'] = array_map( function ( $a ) {
				return array( '@type' => 'Person', 'name' => $a->name );
			}, $authors );
			if ( 1 === count( $schema['author'] ) ) {
				$schema['author'] = $schema['author'][0];
			}
		}

		if ( ! empty( $meta['isbn'] ) ) {
			$schema['isbn'] = $meta['isbn'];
		}
		if ( ! empty( $meta['pages'] ) ) {
			$schema['numberOfPages'] = $meta['pages'];
		}
		if ( ! empty( $meta['year'] ) ) {
			$schema['datePublished'] = (string) $meta['year'];
		}

		$image_id = $product->get_image_id();
		if ( $image_id ) {
			$schema['image'] = wp_get_attachment_url( $image_id );
		}

		$schema['offers'] = array(
			'@type'         => 'Offer',
			'price'         => $product->get_price(),
			'priceCurrency' => get_woocommerce_currency(),
			'availability'  => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
			'url'           => get_permalink( $product_id ),
		);

		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
	}

	/**
	 * Extend product search to include author names and ISBN.
	 *
	 * @param string    $search Search SQL.
	 * @param \WP_Query $query  WP Query.
	 * @return string
	 */
	public function extend_search( $search, $query ) {
		if ( ! $query->is_search() || ! $query->is_main_query() || is_admin() ) {
			return $search;
		}

		if ( 'product' !== $query->get( 'post_type' ) && ! $query->get( 'wc_query' ) ) {
			return $search;
		}

		global $wpdb;

		$term = $query->get( 's' );
		if ( empty( $term ) ) {
			return $search;
		}

		$like = '%' . $wpdb->esc_like( $term ) . '%';

		// Search by author taxonomy.
		$author_sql = $wpdb->prepare(
			"SELECT tr.object_id FROM {$wpdb->term_relationships} tr
			 INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
			 INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
			 WHERE tt.taxonomy = 'book_author' AND t.name LIKE %s",
			$like
		);

		// Search by ISBN meta.
		$isbn_sql = $wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_book_isbn' AND meta_value LIKE %s",
			$like
		);

		$search .= " OR ({$wpdb->posts}.ID IN ({$author_sql})) OR ({$wpdb->posts}.ID IN ({$isbn_sql}))";

		return $search;
	}
}
