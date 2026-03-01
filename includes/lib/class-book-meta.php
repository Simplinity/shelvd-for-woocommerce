<?php
/**
 * Book Meta helper.
 *
 * @package WC_Flavor_Books
 */

namespace WC_Flavor_Books\Lib;

defined( 'ABSPATH' ) || exit;

/**
 * Centralized CRUD for book product meta fields.
 */
class Book_Meta {

	/**
	 * Meta prefix.
	 */
	const PREFIX = '_book_';

	/**
	 * Field definitions.
	 *
	 * @var array
	 */
	private static $fields = array(
		'isbn' => array(
			'type'     => 'string',
			'label'    => 'ISBN',
			'sanitize' => 'sanitize_text_field',
		),
		'pages' => array(
			'type'     => 'integer',
			'label'    => 'Pages',
			'sanitize' => 'absint',
		),
		'year' => array(
			'type'     => 'integer',
			'label'    => 'Publication Year',
			'sanitize' => 'absint',
		),
		'edition' => array(
			'type'     => 'string',
			'label'    => 'Edition',
			'sanitize' => 'sanitize_text_field',
		),
		'condition' => array(
			'type'     => 'string',
			'label'    => 'Condition',
			'sanitize' => 'sanitize_text_field',
			'options'  => array( 'new', 'like-new', 'very-good', 'good', 'fair', 'poor' ),
		),
		'format' => array(
			'type'     => 'string',
			'label'    => 'Format',
			'sanitize' => 'sanitize_text_field',
			'options'  => array( 'hardcover', 'paperback', 'pocket', 'ebook', 'audiobook' ),
		),
		'original_language' => array(
			'type'     => 'string',
			'label'    => 'Original Language',
			'sanitize' => 'sanitize_text_field',
		),
	);

	/**
	 * Register meta fields with WordPress.
	 */
	public static function register_meta_fields() {
		foreach ( self::$fields as $key => $config ) {
			register_post_meta( 'product', self::PREFIX . $key, array(
				'type'              => $config['type'],
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => $config['sanitize'],
				'auth_callback'     => function () {
					return current_user_can( 'edit_products' );
				},
			) );
		}
	}

	/**
	 * Get all field definitions.
	 *
	 * @return array
	 */
	public static function get_fields() {
		return self::$fields;
	}

	/**
	 * Get field keys.
	 *
	 * @return array
	 */
	public static function get_keys() {
		return array_keys( self::$fields );
	}

	/**
	 * Get a single meta value.
	 *
	 * @param int    $product_id Product ID.
	 * @param string $field      Field name without prefix.
	 * @return mixed
	 */
	public static function get( $product_id, $field ) {
		if ( ! isset( self::$fields[ $field ] ) ) {
			return null;
		}

		$value = get_post_meta( $product_id, self::PREFIX . $field, true );

		if ( 'integer' === self::$fields[ $field ]['type'] && '' !== $value ) {
			return (int) $value;
		}

		return $value;
	}

	/**
	 * Set a single meta value.
	 *
	 * @param int    $product_id Product ID.
	 * @param string $field      Field name without prefix.
	 * @param mixed  $value      Value.
	 * @return bool|int
	 */
	public static function set( $product_id, $field, $value ) {
		if ( ! isset( self::$fields[ $field ] ) ) {
			return false;
		}

		$sanitize = self::$fields[ $field ]['sanitize'];
		$value    = call_user_func( $sanitize, $value );

		if ( '' === $value || null === $value ) {
			return delete_post_meta( $product_id, self::PREFIX . $field );
		}

		return update_post_meta( $product_id, self::PREFIX . $field, $value );
	}

	/**
	 * Get all meta values for a product.
	 *
	 * @param int $product_id Product ID.
	 * @return array Non-empty values only.
	 */
	public static function get_all( $product_id ) {
		$meta = array();

		foreach ( self::get_keys() as $key ) {
			$value = self::get( $product_id, $key );
			if ( '' !== $value && null !== $value && 0 !== $value ) {
				$meta[ $key ] = $value;
			}
		}

		return $meta;
	}

	/**
	 * Set multiple meta values.
	 *
	 * @param int   $product_id Product ID.
	 * @param array $data       Key-value pairs.
	 */
	public static function set_all( $product_id, $data ) {
		foreach ( $data as $key => $value ) {
			self::set( $product_id, $key, $value );
		}
	}

	/**
	 * Check if product has any book meta.
	 *
	 * @param int $product_id Product ID.
	 * @return bool
	 */
	public static function has_book_data( $product_id ) {
		return ! empty( self::get_all( $product_id ) );
	}

	/**
	 * Get human-readable condition label.
	 *
	 * @param string $condition Condition slug.
	 * @return string
	 */
	public static function get_condition_label( $condition ) {
		$labels = array(
			'new'       => __( 'New', 'wc-flavor-books' ),
			'like-new'  => __( 'Like New', 'wc-flavor-books' ),
			'very-good' => __( 'Very Good', 'wc-flavor-books' ),
			'good'      => __( 'Good', 'wc-flavor-books' ),
			'fair'      => __( 'Fair', 'wc-flavor-books' ),
			'poor'      => __( 'Poor', 'wc-flavor-books' ),
		);

		return $labels[ $condition ] ?? ucfirst( str_replace( '-', ' ', $condition ) );
	}

	/**
	 * Get human-readable format label.
	 *
	 * @param string $format Format slug.
	 * @return string
	 */
	public static function get_format_label( $format ) {
		$labels = array(
			'hardcover' => __( 'Hardcover', 'wc-flavor-books' ),
			'paperback' => __( 'Paperback', 'wc-flavor-books' ),
			'pocket'    => __( 'Pocket', 'wc-flavor-books' ),
			'ebook'     => __( 'E-book', 'wc-flavor-books' ),
			'audiobook' => __( 'Audiobook', 'wc-flavor-books' ),
		);

		return $labels[ $format ] ?? ucfirst( $format );
	}
}
