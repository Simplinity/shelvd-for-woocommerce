<?php
/**
 * ISBN Lookup service.
 *
 * @package Shelvd
 */

namespace Shelvd\Admin;

defined( 'ABSPATH' ) || exit;

use Shelvd\Lib\ISBN_Validator;

/**
 * Looks up book data from Google Books and Open Library APIs.
 */
class ISBN_Lookup {

	/**
	 * Constructor — registers AJAX handler.
	 */
	public function __construct() {
		add_action( 'wp_ajax_shelvd_isbn_lookup', array( $this, 'ajax_lookup' ) );
	}

	/**
	 * Handle AJAX ISBN lookup.
	 */
	public function ajax_lookup() {
		check_ajax_referer( 'shelvd-admin' );

		if ( ! current_user_can( 'edit_products' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'shelvd' ) ) );
		}

		$isbn = ISBN_Validator::clean( sanitize_text_field( wp_unslash( $_POST['isbn'] ?? '' ) ) );

		if ( empty( $isbn ) ) {
			wp_send_json_error( array( 'message' => __( 'ISBN is required.', 'shelvd' ) ) );
		}

		if ( ! ISBN_Validator::is_valid( $isbn ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid ISBN format.', 'shelvd' ) ) );
		}

		// Try Google Books first.
		$data = $this->lookup_google_books( $isbn );

		// Fallback to Open Library.
		if ( empty( $data ) ) {
			$data = $this->lookup_open_library( $isbn );
		}

		if ( empty( $data ) ) {
			wp_send_json_error( array( 'message' => __( 'No data found for this ISBN.', 'shelvd' ) ) );
		}

		$data['isbn']   = $isbn;
		$data['source'] = $data['source'] ?? 'unknown';

		wp_send_json_success( $data );
	}

	/**
	 * Look up ISBN via Google Books API.
	 *
	 * @param string $isbn ISBN.
	 * @return array|null
	 */
	private function lookup_google_books( $isbn ) {
		$url      = 'https://www.googleapis.com/books/v1/volumes?q=isbn:' . $isbn;
		$response = wp_remote_get( $url, array( 'timeout' => 10 ) );

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $body['items'][0]['volumeInfo'] ) ) {
			return null;
		}

		$book    = $body['items'][0]['volumeInfo'];
		$sale    = $body['items'][0]['saleInfo'] ?? array();
		$lang_map = array(
			'nl' => 'Nederlands', 'en' => 'English', 'fr' => 'Français',
			'de' => 'Deutsch', 'es' => 'Español', 'it' => 'Italiano',
			'pt' => 'Português', 'ru' => 'Русский', 'ja' => '日本語',
		);

		$year = $book['publishedDate'] ?? '';
		if ( strlen( $year ) > 4 ) {
			$year = substr( $year, 0, 4 );
		}

		$cover = '';
		if ( ! empty( $book['imageLinks'] ) ) {
			$cover = $book['imageLinks']['extraLarge']
				?? $book['imageLinks']['large']
				?? $book['imageLinks']['medium']
				?? $book['imageLinks']['thumbnail']
				?? '';
			$cover = str_replace( array( 'http:', 'zoom=1', '&edge=curl' ), array( 'https:', 'zoom=2', '' ), $cover );
		}

		$lang = $lang_map[ $book['language'] ?? '' ] ?? ( $book['language'] ?? '' );

		return array(
			'title'     => $book['title'] . ( ! empty( $book['subtitle'] ) ? ' - ' . $book['subtitle'] : '' ),
			'authors'   => $book['authors'] ?? array(),
			'publisher' => $book['publisher'] ?? '',
			'year'      => $year,
			'pages'     => $book['pageCount'] ?? '',
			'language'  => $lang,
			'cover'     => $cover,
			'description' => wp_strip_all_tags( $book['description'] ?? '' ),
			'source'    => 'google',
		);
	}

	/**
	 * Look up ISBN via Open Library API.
	 *
	 * @param string $isbn ISBN.
	 * @return array|null
	 */
	private function lookup_open_library( $isbn ) {
		$url      = 'https://openlibrary.org/api/books?bibkeys=ISBN:' . $isbn . '&format=json&jscmd=data';
		$response = wp_remote_get( $url, array( 'timeout' => 10 ) );

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		$book = $body[ 'ISBN:' . $isbn ] ?? null;

		if ( ! $book ) {
			return null;
		}

		$cover = '';
		if ( ! empty( $book['cover'] ) ) {
			$cover = $book['cover']['large'] ?? $book['cover']['medium'] ?? '';
		}

		return array(
			'title'     => $book['title'] ?? '',
			'authors'   => ! empty( $book['authors'] ) ? array_column( $book['authors'], 'name' ) : array(),
			'publisher' => ! empty( $book['publishers'] ) ? $book['publishers'][0]['name'] : '',
			'year'      => $book['publish_date'] ?? '',
			'pages'     => $book['number_of_pages'] ?? '',
			'language'  => '',
			'cover'     => $cover,
			'description' => '',
			'source'    => 'openlibrary',
		);
	}
}
