<?php
/**
 * REST API Controller.
 *
 * @package WC_Flavor_Books
 */

namespace WC_Flavor_Books\Api;

defined( 'ABSPATH' ) || exit;

use WC_Flavor_Books\Lib\Book_Meta;
use WC_Flavor_Books\Traits\Singleton;

/**
 * Extends WooCommerce REST API with book fields and adds custom endpoints.
 */
class Rest_Controller {

	use Singleton;

	/**
	 * Namespace.
	 *
	 * @var string
	 */
	const NAMESPACE_V1 = 'wc-flavor-books/v1';

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_fields' ) );
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register book_data field on product endpoint.
	 */
	public function register_fields() {
		register_rest_field( 'product', 'book_data', array(
			'get_callback'    => array( $this, 'get_book_data' ),
			'update_callback' => array( $this, 'update_book_data' ),
			'schema'          => $this->get_book_data_schema(),
		) );

		// Taxonomy fields.
		foreach ( array( 'book_author', 'book_publisher', 'book_language' ) as $taxonomy ) {
			register_rest_field( 'product', $taxonomy . 's', array(
				'get_callback' => function ( $object ) use ( $taxonomy ) {
					$terms = wp_get_post_terms( $object['id'], $taxonomy );
					if ( is_wp_error( $terms ) ) {
						return array();
					}
					return array_map( function ( $term ) {
						return array(
							'id'   => $term->term_id,
							'name' => $term->name,
							'slug' => $term->slug,
							'link' => get_term_link( $term ),
						);
					}, $terms );
				},
				'schema'       => array(
					'description' => sprintf( 'Book %s terms.', $taxonomy ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit' ),
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'id'   => array( 'type' => 'integer' ),
							'name' => array( 'type' => 'string' ),
							'slug' => array( 'type' => 'string' ),
							'link' => array( 'type' => 'string', 'format' => 'uri' ),
						),
					),
				),
			) );
		}
	}

	/**
	 * Get book data for REST response.
	 *
	 * @param array $object Product data.
	 * @return array
	 */
	public function get_book_data( $object ) {
		$meta = Book_Meta::get_all( $object['id'] );

		return array(
			'isbn'              => isset( $meta['isbn'] ) ? $meta['isbn'] : '',
			'pages'             => isset( $meta['pages'] ) ? (int) $meta['pages'] : null,
			'year'              => isset( $meta['year'] ) ? (int) $meta['year'] : null,
			'edition'           => isset( $meta['edition'] ) ? $meta['edition'] : '',
			'condition'         => isset( $meta['condition'] ) ? $meta['condition'] : '',
			'condition_label'   => isset( $meta['condition'] ) ? Book_Meta::get_condition_label( $meta['condition'] ) : '',
			'format'            => isset( $meta['format'] ) ? $meta['format'] : '',
			'format_label'      => isset( $meta['format'] ) ? Book_Meta::get_format_label( $meta['format'] ) : '',
			'original_language' => isset( $meta['original_language'] ) ? $meta['original_language'] : '',
		);
	}

	/**
	 * Update book data from REST request.
	 *
	 * @param array    $value   Book data values.
	 * @param \WP_Post $object  Product post object.
	 * @return bool
	 */
	public function update_book_data( $value, $object ) {
		if ( ! is_array( $value ) ) {
			return false;
		}

		$product_id = $object->ID;
		$fields     = Book_Meta::get_field_definitions();

		foreach ( $value as $key => $val ) {
			if ( array_key_exists( $key, $fields ) ) {
				Book_Meta::set( $product_id, $key, $val );
			}
		}

		return true;
	}

	/**
	 * Get book data schema.
	 *
	 * @return array
	 */
	private function get_book_data_schema() {
		return array(
			'description' => 'Book metadata.',
			'type'        => 'object',
			'context'     => array( 'view', 'edit' ),
			'properties'  => array(
				'isbn'              => array( 'type' => 'string', 'description' => 'ISBN-10 or ISBN-13.' ),
				'pages'             => array( 'type' => array( 'integer', 'null' ), 'description' => 'Number of pages.' ),
				'year'              => array( 'type' => array( 'integer', 'null' ), 'description' => 'Publication year.' ),
				'edition'           => array( 'type' => 'string', 'description' => 'Edition.' ),
				'condition'         => array( 'type' => 'string', 'description' => 'Book condition key.' ),
				'condition_label'   => array( 'type' => 'string', 'description' => 'Human-readable condition.', 'readonly' => true ),
				'format'            => array( 'type' => 'string', 'description' => 'Book format key.' ),
				'format_label'      => array( 'type' => 'string', 'description' => 'Human-readable format.', 'readonly' => true ),
				'original_language' => array( 'type' => 'string', 'description' => 'Original language.' ),
			),
		);
	}

	/**
	 * Register custom routes.
	 */
	public function register_routes() {
		register_rest_route( self::NAMESPACE_V1, '/products/by-author/(?P<id>\d+)', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_products_by_author' ),
			'permission_callback' => '__return_true',
			'args'                => array(
				'id'       => array(
					'required'          => true,
					'validate_callback' => function ( $param ) {
						return is_numeric( $param );
					},
				),
				'per_page' => array(
					'default'           => 10,
					'sanitize_callback' => 'absint',
				),
				'page'     => array(
					'default'           => 1,
					'sanitize_callback' => 'absint',
				),
			),
		) );

		register_rest_route( self::NAMESPACE_V1, '/search', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'search_products' ),
			'permission_callback' => '__return_true',
			'args'                => array(
				's'        => array( 'type' => 'string', 'default' => '' ),
				'author'   => array( 'type' => 'integer' ),
				'isbn'     => array( 'type' => 'string' ),
				'per_page' => array( 'default' => 10, 'sanitize_callback' => 'absint' ),
				'page'     => array( 'default' => 1, 'sanitize_callback' => 'absint' ),
			),
		) );
	}

	/**
	 * Get products by author term ID.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 */
	public function get_products_by_author( $request ) {
		$query = new \WP_Query( array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => $request->get_param( 'per_page' ),
			'paged'          => $request->get_param( 'page' ),
			'tax_query'      => array(
				array(
					'taxonomy' => 'book_author',
					'field'    => 'term_id',
					'terms'    => $request->get_param( 'id' ),
				),
			),
		) );

		return $this->format_product_response( $query, $request );
	}

	/**
	 * Search products by various criteria.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 */
	public function search_products( $request ) {
		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => $request->get_param( 'per_page' ),
			'paged'          => $request->get_param( 'page' ),
		);

		$search = $request->get_param( 's' );
		if ( ! empty( $search ) ) {
			$args['s'] = $search;
		}

		$author = $request->get_param( 'author' );
		if ( ! empty( $author ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'book_author',
				'field'    => 'term_id',
				'terms'    => absint( $author ),
			);
		}

		$isbn = $request->get_param( 'isbn' );
		if ( ! empty( $isbn ) ) {
			$args['meta_query'][] = array(
				'key'     => '_book_isbn',
				'value'   => sanitize_text_field( $isbn ),
				'compare' => 'LIKE',
			);
		}

		$query = new \WP_Query( $args );

		return $this->format_product_response( $query, $request );
	}

	/**
	 * Format query results into REST response.
	 *
	 * @param \WP_Query        $query   Query.
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 */
	private function format_product_response( $query, $request ) {
		$products = array();

		foreach ( $query->posts as $post ) {
			$product = wc_get_product( $post->ID );
			if ( ! $product ) {
				continue;
			}

			$meta    = Book_Meta::get_all( $post->ID );
			$authors = wp_get_post_terms( $post->ID, 'book_author' );

			$products[] = array(
				'id'        => $post->ID,
				'name'      => $product->get_name(),
				'slug'      => $product->get_slug(),
				'permalink' => get_permalink( $post->ID ),
				'price'     => $product->get_price(),
				'image'     => wp_get_attachment_url( $product->get_image_id() ),
				'book_data' => $this->get_book_data( array( 'id' => $post->ID ) ),
				'authors'   => ! is_wp_error( $authors ) ? array_map( function ( $t ) {
					return array( 'id' => $t->term_id, 'name' => $t->name );
				}, $authors ) : array(),
			);
		}

		$response = new \WP_REST_Response( $products, 200 );
		$response->header( 'X-WP-Total', $query->found_posts );
		$response->header( 'X-WP-TotalPages', $query->max_num_pages );

		return $response;
	}
}
