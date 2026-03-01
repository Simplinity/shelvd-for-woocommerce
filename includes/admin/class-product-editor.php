<?php
/**
 * Product Editor integration.
 *
 * @package Shelvd
 */

namespace Shelvd\Admin;

defined( 'ABSPATH' ) || exit;

use Shelvd\Lib\Book_Meta;
use Shelvd\Database\Taxonomy_Manager;
use Shelvd\Traits\Singleton;

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
			'label'    => __( 'Book Details', 'shelvd' ),
			'target'   => 'shelvd_panel',
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

		echo '<div id="shelvd_panel" class="panel woocommerce_options_panel">';
		echo '<div class="options_group">';

		// ISBN + lookup button.
		echo '<p class="form-field _book_isbn_field">';
		echo '<label for="_book_isbn">' . esc_html__( 'ISBN', 'shelvd' ) . '</label>';
		echo '<span class="wrap">';
		echo '<input type="text" id="_book_isbn" name="_book_isbn" value="' . esc_attr( $meta['isbn'] ?? '' ) . '" placeholder="978-..." style="width:60%;" />';
		echo ' <button type="button" class="button" id="shelvd-isbn-lookup">' . esc_html__( 'Look up', 'shelvd' ) . '</button>';
		echo ' <span id="shelvd-isbn-status" style="margin-left:8px;"></span>';
		echo '</span>';
		echo '</p>';

		echo '</div><div class="options_group">';

		// Author (taxonomy — free-text with auto-complete).
		$authors = wp_get_post_terms( $product_id, 'book_author', array( 'fields' => 'names' ) );
		woocommerce_wp_text_input( array(
			'id'          => '_book_author_names',
			'label'       => __( 'Author(s)', 'shelvd' ),
			'description' => __( 'Comma-separated for multiple authors.', 'shelvd' ),
			'desc_tip'    => true,
			'value'       => is_wp_error( $authors ) ? '' : implode( ', ', $authors ),
		) );

		// Publisher (taxonomy).
		$publishers = wp_get_post_terms( $product_id, 'book_publisher', array( 'fields' => 'names' ) );
		woocommerce_wp_text_input( array(
			'id'    => '_book_publisher_name',
			'label' => __( 'Publisher', 'shelvd' ),
			'value' => is_wp_error( $publishers ) ? '' : implode( ', ', $publishers ),
		) );

		// Language (taxonomy).
		$languages = wp_get_post_terms( $product_id, 'book_language', array( 'fields' => 'names' ) );
		woocommerce_wp_text_input( array(
			'id'    => '_book_language_name',
			'label' => __( 'Language', 'shelvd' ),
			'value' => is_wp_error( $languages ) ? '' : implode( ', ', $languages ),
		) );

		// Original language.
		woocommerce_wp_text_input( array(
			'id'          => '_book_original_language',
			'label'       => __( 'Original Language', 'shelvd' ),
			'description' => __( 'If this book is a translation.', 'shelvd' ),
			'desc_tip'    => true,
			'value'       => $meta['original_language'] ?? '',
		) );

		echo '</div><div class="options_group">';

		// Year.
		woocommerce_wp_text_input( array(
			'id'                => '_book_year',
			'label'             => __( 'Publication Year', 'shelvd' ),
			'type'              => 'number',
			'custom_attributes' => array( 'min' => '1400', 'max' => gmdate( 'Y' ) + 2, 'step' => '1' ),
			'value'             => $meta['year'] ?? '',
		) );

		// Pages.
		woocommerce_wp_text_input( array(
			'id'                => '_book_pages',
			'label'             => __( 'Pages', 'shelvd' ),
			'type'              => 'number',
			'custom_attributes' => array( 'min' => '1', 'step' => '1' ),
			'value'             => $meta['pages'] ?? '',
		) );

		// Edition.
		woocommerce_wp_text_input( array(
			'id'    => '_book_edition',
			'label' => __( 'Edition', 'shelvd' ),
			'value' => $meta['edition'] ?? '',
		) );

		echo '</div><div class="options_group">';

		// Condition.
		woocommerce_wp_select( array(
			'id'      => '_book_condition',
			'label'   => __( 'Condition', 'shelvd' ),
			'value'   => $meta['condition'] ?? '',
			'options' => array(
				''          => __( '— Select —', 'shelvd' ),
				'new'       => __( 'New', 'shelvd' ),
				'like-new'  => __( 'Like New', 'shelvd' ),
				'very-good' => __( 'Very Good', 'shelvd' ),
				'good'      => __( 'Good', 'shelvd' ),
				'fair'      => __( 'Fair', 'shelvd' ),
				'poor'      => __( 'Poor', 'shelvd' ),
			),
		) );

		// Format.
		woocommerce_wp_select( array(
			'id'      => '_book_format',
			'label'   => __( 'Format', 'shelvd' ),
			'value'   => $meta['format'] ?? '',
			'options' => array(
				''          => __( '— Select —', 'shelvd' ),
				'hardcover' => __( 'Hardcover', 'shelvd' ),
				'paperback' => __( 'Paperback', 'shelvd' ),
				'pocket'    => __( 'Pocket', 'shelvd' ),
				'ebook'     => __( 'E-book', 'shelvd' ),
				'audiobook' => __( 'Audiobook', 'shelvd' ),
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
		do_action( 'shelvd_product_saved', $product_id );
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
