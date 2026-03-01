<?php
/**
 * Singleton trait.
 *
 * @package WC_Flavor_Books
 */

namespace WC_Flavor_Books\Traits;

defined( 'ABSPATH' ) || exit;

trait Singleton {

	/**
	 * Instance holder.
	 *
	 * @var static|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return static
	 */
	public static function instance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * Prevent cloning.
	 */
	private function __clone() {}

	/**
	 * Prevent unserialization.
	 */
	public function __wakeup() {
		throw new \Exception( 'Cannot unserialize singleton' );
	}
}
