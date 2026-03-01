<?php
/**
 * Book metadata template.
 *
 * Override this template by copying it to:
 * yourtheme/shelvd/product/book-metadata.php
 *
 * @package Shelvd
 * @var array  $meta       Book meta values.
 * @var array  $authors    Author terms.
 * @var array  $publishers Publisher terms.
 * @var array  $languages  Language terms.
 */

defined( 'ABSPATH' ) || exit;

use Shelvd\Lib\Book_Meta;
?>

<div class="shelvd-metadata">

	<?php if ( ! is_wp_error( $authors ) && ! empty( $authors ) ) : ?>
		<div class="book-field book-authors">
			<span class="book-field-label"><?php esc_html_e( 'Author:', 'shelvd' ); ?></span>
			<span class="book-field-value">
				<?php
				$links = array();
				foreach ( $authors as $author ) {
					$links[] = '<a href="' . esc_url( get_term_link( $author ) ) . '">' . esc_html( $author->name ) . '</a>';
				}
				echo implode( ', ', $links ); // phpcs:ignore WordPress.Security.EscapeOutput
				?>
			</span>
		</div>
	<?php endif; ?>

	<?php if ( ! is_wp_error( $publishers ) && ! empty( $publishers ) ) : ?>
		<div class="book-field book-publishers">
			<span class="book-field-label"><?php esc_html_e( 'Publisher:', 'shelvd' ); ?></span>
			<span class="book-field-value">
				<?php
				$links = array();
				foreach ( $publishers as $pub ) {
					$links[] = '<a href="' . esc_url( get_term_link( $pub ) ) . '">' . esc_html( $pub->name ) . '</a>';
				}
				echo implode( ', ', $links ); // phpcs:ignore WordPress.Security.EscapeOutput
				?>
			</span>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $meta['isbn'] ) ) : ?>
		<div class="book-field book-isbn">
			<span class="book-field-label"><?php esc_html_e( 'ISBN:', 'shelvd' ); ?></span>
			<span class="book-field-value"><?php echo esc_html( $meta['isbn'] ); ?></span>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $meta['year'] ) ) : ?>
		<div class="book-field book-year">
			<span class="book-field-label"><?php esc_html_e( 'Year:', 'shelvd' ); ?></span>
			<span class="book-field-value"><?php echo esc_html( $meta['year'] ); ?></span>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $meta['pages'] ) ) : ?>
		<div class="book-field book-pages">
			<span class="book-field-label"><?php esc_html_e( 'Pages:', 'shelvd' ); ?></span>
			<span class="book-field-value"><?php echo esc_html( $meta['pages'] ); ?></span>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $meta['format'] ) ) : ?>
		<div class="book-field book-format">
			<span class="book-field-label"><?php esc_html_e( 'Format:', 'shelvd' ); ?></span>
			<span class="book-field-value"><?php echo esc_html( Book_Meta::get_format_label( $meta['format'] ) ); ?></span>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $meta['condition'] ) ) : ?>
		<div class="book-field book-condition">
			<span class="book-field-label"><?php esc_html_e( 'Condition:', 'shelvd' ); ?></span>
			<span class="book-field-value"><?php echo esc_html( Book_Meta::get_condition_label( $meta['condition'] ) ); ?></span>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $meta['edition'] ) ) : ?>
		<div class="book-field book-edition">
			<span class="book-field-label"><?php esc_html_e( 'Edition:', 'shelvd' ); ?></span>
			<span class="book-field-value"><?php echo esc_html( $meta['edition'] ); ?></span>
		</div>
	<?php endif; ?>

	<?php if ( ! is_wp_error( $languages ) && ! empty( $languages ) ) : ?>
		<div class="book-field book-languages">
			<span class="book-field-label"><?php esc_html_e( 'Language:', 'shelvd' ); ?></span>
			<span class="book-field-value">
				<?php
				$links = array();
				foreach ( $languages as $lang ) {
					$links[] = '<a href="' . esc_url( get_term_link( $lang ) ) . '">' . esc_html( $lang->name ) . '</a>';
				}
				echo implode( ', ', $links ); // phpcs:ignore WordPress.Security.EscapeOutput
				?>
			</span>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $meta['original_language'] ) ) : ?>
		<div class="book-field book-original-language">
			<span class="book-field-label"><?php esc_html_e( 'Original language:', 'shelvd' ); ?></span>
			<span class="book-field-value"><?php echo esc_html( $meta['original_language'] ); ?></span>
		</div>
	<?php endif; ?>

</div>
