/**
 * Shelvd for WooCommerce — Frontend Filters JS
 *
 * Handles AJAX-based filtering on shop/archive pages.
 */
(function ($) {
	'use strict';

	var $filterLists = $('.shelvd-filter-list');
	if (!$filterLists.length) {
		return;
	}

	var activeFilters = {};

	$filterLists.on('click', 'a', function (e) {
		e.preventDefault();

		var $link    = $(this);
		var $li      = $link.closest('li');
		var taxonomy = $link.closest('ul').data('taxonomy');
		var termId   = $link.data('term-id');

		// Toggle active state.
		if ($li.hasClass('active')) {
			$li.removeClass('active');
			delete activeFilters[taxonomy];
		} else {
			$link.closest('ul').find('li').removeClass('active');
			$li.addClass('active');
			activeFilters[taxonomy] = termId;
		}

		doFilter();
	});

	function doFilter() {
		var $productsWrap = $('.products');
		if (!$productsWrap.length) {
			return;
		}

		$productsWrap.css('opacity', 0.5);

		var data = {
			action: 'shelvd_filter',
			nonce:  shelvd.nonce
		};

		$.extend(data, activeFilters);

		$.post(shelvd.ajaxUrl, data, function (response) {
			if (response.success && response.data.html) {
				$productsWrap.replaceWith(response.data.html);
			}
		}).always(function () {
			$productsWrap.css('opacity', 1);
		});
	}

})(jQuery);
