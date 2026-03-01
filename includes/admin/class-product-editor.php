<?php
/**
 * Product Editor integration.
 *
 * @package WC_Flavor_Books
 */

namespace WC_Flavor_Books\Admin;

defined( 'ABSPATH' ) || exit;

use WC_Flavor_Books\Lib\Book_Meta;
use WC_Flavor_Books\Database\Taxonomy_Manager;
use WC_Flavor_Books\Traits\Singleton;

/**
 * Adds "Book Details" tab to WooCommerce product editor.
 */
class Product_Editor {

	use Singleton;

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'add_book_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( $this, 'render_book_panel' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_book_data' ) );
	}

	/**
	 * Add "Book Details" tab.
	 *
	 * @param array $tabs Product data tabs.
	 * @return array
	 */
	public function add_book_tab( $tabs ) {
		$tabs['book_details'] = array(
			'label'    => __( 'Book Details', 'wc-flavor-books' ),
			'target'   => 'wc_flavor_books_panel',
			'class'    => array( 'show_if_simple', 'show_if_variable', 'show_if_grouped', 'show_if_external' ),
			'priority' => 25,
		);

		return $tabs;
	}

	/**
	 * Render the Book Details panel.
	 */
	public function render_book_panel() {
		global $post;

		$product_id = $post->ID;
		$meta       = Book_Meta::get_all( $product_id );

		echo '<div id="wc_flavor_books_panel" class="panel woocommerce_options_panel">';
		echo '<div class="options_group">';

		// ISBN + lookup button.
		echo '<p class="form-field _book_isbn_field">';
		echo '<label for="_book_isbn">' . esc_html__( 'ISBN', 'wc-flavor-books' ) . '</label>';
		echo '<span class="wrap">';
		echo '<input type="text" id="_book_isbn" name="_book_isbn" value="' . esc_attr( $meta['isbn'] ?? '' ) . '" placeholder="978-..." style="width:60%;" />';
		echo ' <button type="button" class="button" id="wc-flavor-books-isbn-lookup">' . esc_html__( 'Look up', 'wc-flavor-books' ) . '</button>';
		echo ' <span id="wc-flavor-books-isbn-status" style="margin-left:8px;"></span>';
		echo '</span>';
		echo '</p>';

		echo '</div><div class="options_group">';

		// Author (taxonomy — free-text with auto-complete).
		$authors = wp_get_post_terms( $product_id, 'book_author', array( 'fields' => 'names' ) );
		woocommerce_wp_text_input( array(
			'id'          => '_book_author_names',
			'label'       => __( 'Author(s)', 'wc-flavor-books' ),
			'description' => __( 'Comma-separated for multiple authors.', 'wc-flavor-books' ),
			'desc_tip'    => true,
			'value'       => is_wp_error( $authors ) ? '' : implode( ', ', $authors ),
		) );

		// Publisher (taxonomy).
		$publishers = wp_get_post_terms( $product_id, 'book_publisher', array( 'fields' => 'names' ) );
		woocommerce_wp_text_input( array(
			'id'    => '_book_publisher_name',
			'label' => __( 'Publisher', 'wc-flavor-books' ),
			'value' => is_wp_error( $publishers ) ? '' : implode( ', ', $publishers ),
		) );

		// Language (taxonomy).
		$languages = wp_get_post_terms( $product_id, 'book_language', array( 'fields' => 'names' ) );
		woocommerce_wp_text_input( array(
			'id'    => '_book_language_name',
			'label' => __( 'Language', 'wc-flavor-books' ),
			'value' => is_wp_error( $languages ) ? '' : implode( ', ', $languages ),
		) );

		// Original language.
		woocommerce_wp_text_input( array(
			'id'          => '_book_original_language',
			'label'       => __( 'Original Language', 'wc-flavor-books' ),
			'description' => __( 'If this book is a translation.', 'wc-flavor-books' ),
			'desc_tip'    => true,
			'value'       => $meta['original_language'] ?? '',
		) );

		echo '</div><div class="options_group">';

		// Year.
		woocommerce_wp_text_input( array(
			'id'                => '_book_year',
			'label'             => __( 'Publication Year', 'wc-flavor-books' ),
			'type'              => 'number',
			'custom_attributes' => array( 'min' => '1400', 'max' => gmdate( 'Y' ) + 2, 'step' => '1' ),
			'value'             => $meta['year'] ?? '',
		) );

		// Pages.
		woocommerce_wp_text_input( array(
			'id'                => '_book_pages',
			'label'             => __( 'Pages', 'wc-flavor-books' ),
			'type'              => 'number',
			'custom_attributes' => array( 'min' => '1', 'step' => '1' ),
			'value'             => $meta['pages'] ?? '',
		) );

		// Edition.
		woocommerce_wp_text_input( array(
			'id'    => '_book_edition',
			'label' => __( 'Edition', 'wc-flavor-books' ),
			'value' => $meta['edition'] ?? '',
		) );

		echo '</div><div class="options_group">';

		// Condition.
		woocommerce_wp_select( array(
			'id'      => '_book_condition',
			'label'   => __( 'Condition', 'wc-flavor-books' ),
			'value'   => $meta['condition'] ?? '',
			'options' => array(
				''          => __( '— Select —', 'wc-flavor-books' ),
				'new'       => __( 'New', 'wc-flavor-books' ),
				'like-new'  => __( 'Like New', 'wc-flavor-books' ),
				'very-good' => __( 'Very Good', 'wc-flavor-books' ),
				'good'      => __( 'Good', 'wc-flavor-books' ),
				'fair'      => __( 'Fair', 'wc-flavor-books' ),
				'poor'      => __( 'Poor', 'wc-flavor-books' ),
			),
		) );

		// Format.
		woocommerce_wp_select( array(
			'id'      => '_book_format',
			'label'   => __( 'Format', 'wc-flavor-books' ),
			'value'   => $meta['format'] ?? '',
			'options' => array(
				''          => __( '— Select —', 'wc-flavor-books' ),
				'hardcover' => __( 'Hardcover', 'wc-flavor-books' ),
				'paperback' => __( 'Paperback', 'wc-flavor-books' ),
				'pocket'    => __( 'Pocket', 'wc-flavor-books' ),
				'ebook'     => __( 'E-book', 'wc-flavor-books' ),
				'audiobook' => __( 'Audiobook', 'wc-flavor-books' ),
			),
		) );

		echo '</div>';
		echo '</div>';
	}

	/**
	 * Save book data on product save.
	 *
	 * @param int $product_id Product ID.
	 */
	public function save_book_data( $product_id ) {
		if ( ! current_user_can( 'edit_product', $product_id ) ) {
			return;
		}

		// Save meta fields.
		foreach ( Book_Meta::get_keys() as $key ) {
			$post_key = '_book_' . $key;
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce handles nonce.
			$value = isset( $_POST[ $post_key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $post_key ] ) ) : '';
			Book_Meta::set( $product_id, $key, $value );
		}

		// Save taxonomy: authors.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$author_names = isset( $_POST['_book_author_names'] ) ? sanitize_text_field( wp_unslash( $_POST['_book_author_names'] ) ) : '';
		$this->save_taxonomy_from_text( $product_id, 'book_author', $author_names );

		// Save taxonomy: publisher.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$publisher_name = isset( $_POST['_book_publisher_name'] ) ? sanitize_text_field( wp_unslash( $_POST['_book_publisher_name'] ) ) : '';
		$this->save_taxonomy_from_text( $product_id, 'book_publisher', $publisher_name );

		// Save taxonomy: language.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$language_name = isset( $_POST['_book_language_name'] ) ? sanitize_text_field( wp_unslash( $_POST['_book_language_name'] ) ) : '';
		$this->save_taxonomy_from_text( $product_id, 'book_language', $language_name );

		/**
		 * Fires after book data is saved.
		 *
		 * @param int $product_id Product ID.
		 */
		do_action( 'wc_flavor_books_product_saved', $product_id );
	}

	/**
	 * Save taxonomy terms from comma-separated text input.
	 * Creates terms if they don't exist.
	 *
	 * @param int    $product_id Product ID.
	 * @param string $taxonomy   Taxonomy name.
	 * @param string $text       Comma-separated term names.
	 */
	private function save_taxonomy_from_text( $product_id, $taxonomy, $text ) {
		if ( empty( trim( $text ) ) ) {
			wp_set_post_terms( $product_id, array(), $taxonomy );
			return;
		}

		$names    = array_map( 'trim', explode( ',', $text ) );
		$term_ids = array();

		foreach ( $names as $name ) {
			if ( '' === $name ) {
				continue;
			}

			$term = get_term_by( 'name', $name, $taxonomy );

			if ( $term ) {
				$term_ids[] = $term->term_id;
			} else {
				$result = wp_insert_term( $name, $taxonomy );
				if ( ! is_wp_error( $result ) ) {
					$term_ids[] = $result['term_id'];
				}
			}
		}

		wp_set_post_terms( $product_id, $term_ids, $taxonomy );
	}
}
