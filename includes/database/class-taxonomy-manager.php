<?php
/**
 * Taxonomy Manager.
 *
 * @package Shelvd
 */

namespace Shelvd\Database;

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
				'name'          => _x( 'Book Authors', 'taxonomy general name', 'shelvd' ),
				'singular_name' => _x( 'Book Author', 'taxonomy singular name', 'shelvd' ),
				'search_items'  => __( 'Search Authors', 'shelvd' ),
				'all_items'     => __( 'All Authors', 'shelvd' ),
				'edit_item'     => __( 'Edit Author', 'shelvd' ),
				'update_item'   => __( 'Update Author', 'shelvd' ),
				'add_new_item'  => __( 'Add New Author', 'shelvd' ),
				'new_item_name' => __( 'New Author Name', 'shelvd' ),
				'menu_name'     => __( 'Authors', 'shelvd' ),
				'not_found'     => __( 'No authors found.', 'shelvd' ),
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
				'name'          => _x( 'Publishers', 'taxonomy general name', 'shelvd' ),
				'singular_name' => _x( 'Publisher', 'taxonomy singular name', 'shelvd' ),
				'search_items'  => __( 'Search Publishers', 'shelvd' ),
				'all_items'     => __( 'All Publishers', 'shelvd' ),
				'edit_item'     => __( 'Edit Publisher', 'shelvd' ),
				'update_item'   => __( 'Update Publisher', 'shelvd' ),
				'add_new_item'  => __( 'Add New Publisher', 'shelvd' ),
				'new_item_name' => __( 'New Publisher Name', 'shelvd' ),
				'menu_name'     => __( 'Publishers', 'shelvd' ),
				'not_found'     => __( 'No publishers found.', 'shelvd' ),
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
				'name'          => _x( 'Languages', 'taxonomy general name', 'shelvd' ),
				'singular_name' => _x( 'Language', 'taxonomy singular name', 'shelvd' ),
				'search_items'  => __( 'Search Languages', 'shelvd' ),
				'all_items'     => __( 'All Languages', 'shelvd' ),
				'edit_item'     => __( 'Edit Language', 'shelvd' ),
				'update_item'   => __( 'Update Language', 'shelvd' ),
				'add_new_item'  => __( 'Add New Language', 'shelvd' ),
				'new_item_name' => __( 'New Language Name', 'shelvd' ),
				'menu_name'     => __( 'Languages', 'shelvd' ),
				'not_found'     => __( 'No languages found.', 'shelvd' ),
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
