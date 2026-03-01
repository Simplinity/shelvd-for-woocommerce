<?php
/**
 * Taxonomy Manager.
 *
 * @package WC_Flavor_Books
 */

namespace WC_Flavor_Books\Database;

defined( 'ABSPATH' ) || exit;

/**
 * Registers custom taxonomies for book metadata.
 */
class Taxonomy_Manager {

	/**
	 * Register all book taxonomies.
	 */
	public static function register_all() {
		self::register_book_author();
		self::register_book_publisher();
		self::register_book_language();
	}

	/**
	 * Register book_author taxonomy.
	 */
	private static function register_book_author() {
		register_taxonomy( 'book_author', 'product', array(
			'labels'            => array(
				'name'          => _x( 'Book Authors', 'taxonomy general name', 'wc-flavor-books' ),
				'singular_name' => _x( 'Book Author', 'taxonomy singular name', 'wc-flavor-books' ),
				'search_items'  => __( 'Search Authors', 'wc-flavor-books' ),
				'all_items'     => __( 'All Authors', 'wc-flavor-books' ),
				'edit_item'     => __( 'Edit Author', 'wc-flavor-books' ),
				'update_item'   => __( 'Update Author', 'wc-flavor-books' ),
				'add_new_item'  => __( 'Add New Author', 'wc-flavor-books' ),
				'new_item_name' => __( 'New Author Name', 'wc-flavor-books' ),
				'menu_name'     => __( 'Authors', 'wc-flavor-books' ),
				'not_found'     => __( 'No authors found.', 'wc-flavor-books' ),
			),
			'public'            => true,
			'hierarchical'      => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_in_rest'      => true,
			'query_var'         => true,
			'rewrite'           => array(
				'slug'       => 'book-author',
				'with_front' => false,
			),
			'capabilities'      => array(
				'manage_terms' => 'manage_product_terms',
				'edit_terms'   => 'edit_product_terms',
				'delete_terms' => 'delete_product_terms',
				'assign_terms' => 'assign_product_terms',
			),
		) );
	}

	/**
	 * Register book_publisher taxonomy.
	 */
	private static function register_book_publisher() {
		register_taxonomy( 'book_publisher', 'product', array(
			'labels'            => array(
				'name'          => _x( 'Publishers', 'taxonomy general name', 'wc-flavor-books' ),
				'singular_name' => _x( 'Publisher', 'taxonomy singular name', 'wc-flavor-books' ),
				'search_items'  => __( 'Search Publishers', 'wc-flavor-books' ),
				'all_items'     => __( 'All Publishers', 'wc-flavor-books' ),
				'edit_item'     => __( 'Edit Publisher', 'wc-flavor-books' ),
				'update_item'   => __( 'Update Publisher', 'wc-flavor-books' ),
				'add_new_item'  => __( 'Add New Publisher', 'wc-flavor-books' ),
				'new_item_name' => __( 'New Publisher Name', 'wc-flavor-books' ),
				'menu_name'     => __( 'Publishers', 'wc-flavor-books' ),
				'not_found'     => __( 'No publishers found.', 'wc-flavor-books' ),
			),
			'public'            => true,
			'hierarchical'      => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_in_rest'      => true,
			'query_var'         => true,
			'rewrite'           => array(
				'slug'       => 'book-publisher',
				'with_front' => false,
			),
			'capabilities'      => array(
				'manage_terms' => 'manage_product_terms',
				'edit_terms'   => 'edit_product_terms',
				'delete_terms' => 'delete_product_terms',
				'assign_terms' => 'assign_product_terms',
			),
		) );
	}

	/**
	 * Register book_language taxonomy.
	 */
	private static function register_book_language() {
		register_taxonomy( 'book_language', 'product', array(
			'labels'            => array(
				'name'          => _x( 'Languages', 'taxonomy general name', 'wc-flavor-books' ),
				'singular_name' => _x( 'Language', 'taxonomy singular name', 'wc-flavor-books' ),
				'search_items'  => __( 'Search Languages', 'wc-flavor-books' ),
				'all_items'     => __( 'All Languages', 'wc-flavor-books' ),
				'edit_item'     => __( 'Edit Language', 'wc-flavor-books' ),
				'update_item'   => __( 'Update Language', 'wc-flavor-books' ),
				'add_new_item'  => __( 'Add New Language', 'wc-flavor-books' ),
				'new_item_name' => __( 'New Language Name', 'wc-flavor-books' ),
				'menu_name'     => __( 'Languages', 'wc-flavor-books' ),
				'not_found'     => __( 'No languages found.', 'wc-flavor-books' ),
			),
			'public'            => true,
			'hierarchical'      => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_in_rest'      => true,
			'query_var'         => true,
			'rewrite'           => array(
				'slug'       => 'book-language',
				'with_front' => false,
			),
			'capabilities'      => array(
				'manage_terms' => 'manage_product_terms',
				'edit_terms'   => 'edit_product_terms',
				'delete_terms' => 'delete_product_terms',
				'assign_terms' => 'assign_product_terms',
			),
		) );
	}

	/**
	 * Get all registered book taxonomy names.
	 *
	 * @return array
	 */
	public static function get_taxonomy_names() {
		return array( 'book_author', 'book_publisher', 'book_language' );
	}
}
