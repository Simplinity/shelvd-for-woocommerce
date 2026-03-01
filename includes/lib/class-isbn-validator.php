<?php
/**
 * ISBN Validator.
 *
 * @package Shelvd
 */

namespace Shelvd\Lib;

defined( 'ABSPATH' ) || exit;

/**
 * Validates ISBN-10 and ISBN-13 formats.
 */
class ISBN_Validator {

	/**
	 * Clean an ISBN string (remove hyphens, spaces).
	 *
	 * @param string $isbn Raw ISBN.
	 * @return string Cleaned ISBN.
	 */
	public static function clean( $isbn ) {
		return preg_replace( '/[^0-9Xx]/', '', trim( $isbn ) );
	}

	/**
	 * Validate an ISBN (10 or 13).
	 *
	 * @param string $isbn ISBN string.
	 * @return bool
	 */
	public static function is_valid( $isbn ) {
		$isbn = self::clean( $isbn );

		if ( 13 === strlen( $isbn ) ) {
			return self::is_valid_isbn13( $isbn );
		}

		if ( 10 === strlen( $isbn ) ) {
			return self::is_valid_isbn10( $isbn );
		}

		return false;
	}

	/**
	 * Validate ISBN-13 checksum.
	 *
	 * @param string $isbn 13-digit ISBN.
	 * @return bool
	 */
	public static function is_valid_isbn13( $isbn ) {
		if ( ! preg_match( '/^\d{13}$/', $isbn ) ) {
			return false;
		}

		$sum = 0;
		for ( $i = 0; $i < 12; $i++ ) {
			$sum += (int) $isbn[ $i ] * ( 0 === $i % 2 ? 1 : 3 );
		}

		$check = ( 10 - ( $sum % 10 ) ) % 10;

		return $check === (int) $isbn[12];
	}

	/**
	 * Validate ISBN-10 checksum.
	 *
	 * @param string $isbn 10-character ISBN.
	 * @return bool
	 */
	public static function is_valid_isbn10( $isbn ) {
		if ( ! preg_match( '/^\d{9}[\dXx]$/', $isbn ) ) {
			return false;
		}

		$sum = 0;
		for ( $i = 0; $i < 9; $i++ ) {
			$sum += (int) $isbn[ $i ] * ( 10 - $i );
		}

		$last = strtoupper( $isbn[9] );
		$sum += ( 'X' === $last ) ? 10 : (int) $last;

		return 0 === $sum % 11;
	}

	/**
	 * Convert ISBN-10 to ISBN-13.
	 *
	 * @param string $isbn10 ISBN-10 string.
	 * @return string|false ISBN-13 or false if invalid.
	 */
	public static function isbn10_to_isbn13( $isbn10 ) {
		$isbn10 = self::clean( $isbn10 );

		if ( 10 !== strlen( $isbn10 ) || ! self::is_valid_isbn10( $isbn10 ) ) {
			return false;
		}

		$isbn13 = '978' . substr( $isbn10, 0, 9 );

		$sum = 0;
		for ( $i = 0; $i < 12; $i++ ) {
			$sum += (int) $isbn13[ $i ] * ( 0 === $i % 2 ? 1 : 3 );
		}

		$check  = ( 10 - ( $sum % 10 ) ) % 10;
		$isbn13 .= $check;

		return $isbn13;
	}

	/**
	 * Format an ISBN with hyphens.
	 *
	 * @param string $isbn Clean ISBN.
	 * @return string Formatted ISBN.
	 */
	public static function format( $isbn ) {
		$isbn = self::clean( $isbn );

		if ( 13 === strlen( $isbn ) ) {
			return substr( $isbn, 0, 3 ) . '-' . substr( $isbn, 3, 1 ) . '-' .
				   substr( $isbn, 4, 4 ) . '-' . substr( $isbn, 8, 4 ) . '-' . substr( $isbn, 12, 1 );
		}

		return $isbn;
	}
}
