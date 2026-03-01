<?php
/**
 * Filter Widgets.
 *
 * @package WC_Flavor_Books
 */

namespace WC_Flavor_Books\Frontend;

defined( 'ABSPATH' ) || exit;

use WC_Flavor_Books\Traits\Singleton;

/**
 * Registers sidebar filter widgets for book taxonomies.
 */
class Filter_Widgets {

	use Singleton;

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );
		add_action( 'wp_ajax_wc_flavor_books_filter', array( $this, 'ajax_filter' ) );
		add_action( 'wp_ajax_nopriv_wc_flavor_books_filter', array( $this, 'ajax_filter' ) );
	}

	/**
	 * Register filter widgets.
	 */
	public function register_widgets() {
		register_widget( Book_Author_Widget::class );
		register_widget( Book_Publisher_Widget::class );
		register_widget( Book_Language_Widget::class );
	}

	/**
	 * Handle AJAX filtering.
	 */
	public function ajax_filter() {
		check_ajax_referer( 'wc-flavor-books-filter', 'nonce' );

		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => absint( isset( $_POST['per_page'] ) ? $_POST['per_page'] : 12 ),
			'paged'          => absint( isset( $_POST['paged'] ) ? $_POST['paged'] : 1 ),
			'tax_query'      => array( 'relation' => 'AND' ),
		);

		$taxonomies = array( 'book_author', 'book_publisher', 'book_language' );

		foreach ( $taxonomies as $tax ) {
			if ( ! empty( $_POST[ $tax ] ) ) {
				$args['tax_query'][] = array(
					'taxonomy' => $tax,
					'field'    => 'term_id',
					'terms'    => array_map( 'absint', (array) $_POST[ $tax ] ),
				);
			}
		}

		// Condition meta filter.
		if ( ! empty( $_POST['book_condition'] ) ) {
			$args['meta_query'][] = array(
				'key'   => '_book_condition',
				'value' => sanitize_text_field( wp_unslash( $_POST['book_condition'] ) ),
			);
		}

		// Format meta filter.
		if ( ! empty( $_POST['book_format'] ) ) {
			$args['meta_query'][] = array(
				'key'   => '_book_format',
				'value' => sanitize_text_field( wp_unslash( $_POST['book_format'] ) ),
			);
		}

		$query = new \WP_Query( $args );

		ob_start();
		if ( $query->have_posts() ) {
			woocommerce_product_loop_start();
			while ( $query->have_posts() ) {
				$query->the_post();
				wc_get_template_part( 'content', 'product' );
			}
			woocommerce_product_loop_end();
		} else {
			echo '<p class="woocommerce-info">' . esc_html__( 'No books found matching your criteria.', 'wc-flavor-books' ) . '</p>';
		}
		$html = ob_get_clean();
		wp_reset_postdata();

		wp_send_json_success( array(
			'html'       => $html,
			'found'      => $query->found_posts,
			'max_pages'  => $query->max_num_pages,
		) );
	}
}

/**
 * Book Author filter widget.
 */
class Book_Author_Widget extends \WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'wc_flavor_books_author',
			__( 'Book Authors Filter', 'wc-flavor-books' ),
			array( 'description' => __( 'Filter products by book author.', 'wc-flavor-books' ) )
		);
	}

	/**
	 * Widget output.
	 *
	 * @param array $args     Widget args.
	 * @param array $instance Widget instance.
	 */
	public function widget( $args, $instance ) {
		if ( ! is_woocommerce() && ! is_product() ) {
			return;
		}

		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Authors', 'wc-flavor-books' );
		$count = ! empty( $instance['count'] ) ? (int) $instance['count'] : 20;

		$terms = get_terms( array(
			'taxonomy'   => 'book_author',
			'hide_empty' => true,
			'number'     => $count,
			'orderby'    => 'count',
			'order'      => 'DESC',
		) );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return;
		}

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput
		echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput

		echo '<ul class="wc-flavor-books-filter-list" data-taxonomy="book_author">';
		foreach ( $terms as $term ) {
			$active = is_tax( 'book_author', $term->term_id ) ? ' class="active"' : '';
			printf(
				'<li%s><a href="%s" data-term-id="%d">%s <span class="count">(%d)</span></a></li>',
				$active,
				esc_url( get_term_link( $term ) ),
				esc_attr( $term->term_id ),
				esc_html( $term->name ),
				esc_html( $term->count )
			);
		}
		echo '</ul>';

		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput
	}

	/**
	 * Widget form.
	 *
	 * @param array $instance Instance.
	 */
	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : __( 'Authors', 'wc-flavor-books' );
		$count = isset( $instance['count'] ) ? (int) $instance['count'] : 20;
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'wc-flavor-books' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>"><?php esc_html_e( 'Number to show:', 'wc-flavor-books' ); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>" type="number" value="<?php echo esc_attr( $count ); ?>" min="1" max="200" />
		</p>
		<?php
	}

	/**
	 * Update widget.
	 *
	 * @param array $new_instance New instance.
	 * @param array $old_instance Old instance.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		return array(
			'title' => sanitize_text_field( $new_instance['title'] ),
			'count' => absint( $new_instance['count'] ),
		);
	}
}

/**
 * Book Publisher filter widget.
 */
class Book_Publisher_Widget extends \WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'wc_flavor_books_publisher',
			__( 'Book Publishers Filter', 'wc-flavor-books' ),
			array( 'description' => __( 'Filter products by publisher.', 'wc-flavor-books' ) )
		);
	}

	/**
	 * Widget output.
	 *
	 * @param array $args     Widget args.
	 * @param array $instance Widget instance.
	 */
	public function widget( $args, $instance ) {
		if ( ! is_woocommerce() && ! is_product() ) {
			return;
		}

		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Publishers', 'wc-flavor-books' );
		$count = ! empty( $instance['count'] ) ? (int) $instance['count'] : 20;

		$terms = get_terms( array(
			'taxonomy'   => 'book_publisher',
			'hide_empty' => true,
			'number'     => $count,
			'orderby'    => 'count',
			'order'      => 'DESC',
		) );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return;
		}

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput
		echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput

		echo '<ul class="wc-flavor-books-filter-list" data-taxonomy="book_publisher">';
		foreach ( $terms as $term ) {
			$active = is_tax( 'book_publisher', $term->term_id ) ? ' class="active"' : '';
			printf(
				'<li%s><a href="%s" data-term-id="%d">%s <span class="count">(%d)</span></a></li>',
				$active,
				esc_url( get_term_link( $term ) ),
				esc_attr( $term->term_id ),
				esc_html( $term->name ),
				esc_html( $term->count )
			);
		}
		echo '</ul>';

		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput
	}

	/**
	 * Widget form.
	 *
	 * @param array $instance Instance.
	 */
	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : __( 'Publishers', 'wc-flavor-books' );
		$count = isset( $instance['count'] ) ? (int) $instance['count'] : 20;
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'wc-flavor-books' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>"><?php esc_html_e( 'Number to show:', 'wc-flavor-books' ); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>" type="number" value="<?php echo esc_attr( $count ); ?>" min="1" max="200" />
		</p>
		<?php
	}

	/**
	 * Update widget.
	 *
	 * @param array $new_instance New instance.
	 * @param array $old_instance Old instance.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		return array(
			'title' => sanitize_text_field( $new_instance['title'] ),
			'count' => absint( $new_instance['count'] ),
		);
	}
}

/**
 * Book Language filter widget.
 */
class Book_Language_Widget extends \WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'wc_flavor_books_language',
			__( 'Book Languages Filter', 'wc-flavor-books' ),
			array( 'description' => __( 'Filter products by language.', 'wc-flavor-books' ) )
		);
	}

	/**
	 * Widget output.
	 *
	 * @param array $args     Widget args.
	 * @param array $instance Widget instance.
	 */
	public function widget( $args, $instance ) {
		if ( ! is_woocommerce() && ! is_product() ) {
			return;
		}

		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Languages', 'wc-flavor-books' );

		$terms = get_terms( array(
			'taxonomy'   => 'book_language',
			'hide_empty' => true,
			'orderby'    => 'count',
			'order'      => 'DESC',
		) );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return;
		}

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput
		echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput

		echo '<ul class="wc-flavor-books-filter-list" data-taxonomy="book_language">';
		foreach ( $terms as $term ) {
			$active = is_tax( 'book_language', $term->term_id ) ? ' class="active"' : '';
			printf(
				'<li%s><a href="%s" data-term-id="%d">%s <span class="count">(%d)</span></a></li>',
				$active,
				esc_url( get_term_link( $term ) ),
				esc_attr( $term->term_id ),
				esc_html( $term->name ),
				esc_html( $term->count )
			);
		}
		echo '</ul>';

		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput
	}

	/**
	 * Widget form.
	 *
	 * @param array $instance Instance.
	 */
	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : __( 'Languages', 'wc-flavor-books' );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'wc-flavor-books' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php
	}

	/**
	 * Update widget.
	 *
	 * @param array $new_instance New instance.
	 * @param array $old_instance Old instance.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		return array(
			'title' => sanitize_text_field( $new_instance['title'] ),
		);
	}
}
