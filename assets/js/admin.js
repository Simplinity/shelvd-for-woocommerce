/**
 * Shelvd for WooCommerce — Admin JS
 *
 * Handles ISBN lookup button in the product editor.
 */
(function ($) {
	'use strict';

	var $isbnField   = $('#_book_isbn');
	var $lookupBtn   = $('#shelvd-isbn-lookup');
	var $statusEl    = $('#shelvd-isbn-status');

	if (!$isbnField.length || !$lookupBtn.length) {
		return;
	}

	$lookupBtn.on('click', function (e) {
		e.preventDefault();

		var isbn = $.trim($isbnField.val());
		if (!isbn) {
			$statusEl.text(shelvdAdmin.i18n.invalidIsbn).removeClass('success').addClass('error');
			return;
		}

		$lookupBtn.prop('disabled', true);
		$statusEl.text(shelvdAdmin.i18n.lookingUp).removeClass('success error');

		$.post(shelvdAdmin.ajaxUrl, {
			action: 'shelvd_isbn_lookup',
			nonce:  shelvdAdmin.nonce,
			isbn:   isbn
		}, function (response) {
			$lookupBtn.prop('disabled', false);

			if (!response.success || !response.data) {
				$statusEl.text(shelvdAdmin.i18n.notFound).removeClass('success').addClass('error');
				return;
			}

			var data = response.data;

			// Fill in fields.
			if (data.title)     { $('#title').val(data.title); }
			if (data.authors)   { $('#_book_author_input').val(data.authors.join(', ')); }
			if (data.publisher) { $('#_book_publisher_input').val(data.publisher); }
			if (data.year)      { $('#_book_year').val(data.year); }
			if (data.pages)     { $('#_book_pages').val(data.pages); }
			if (data.language)  { $('#_book_language_input').val(data.language); }

			// Description.
			if (data.description) {
				var $descField = $('#excerpt');
				if ($descField.length) {
					$descField.val(data.description);
				}
				// Also try TinyMCE editor.
				if (typeof tinyMCE !== 'undefined') {
					var editor = tinyMCE.get('excerpt');
					if (editor) {
						editor.setContent(data.description);
					}
				}
			}

			$statusEl.text(shelvdAdmin.i18n.found).removeClass('error').addClass('success');

		}).fail(function () {
			$lookupBtn.prop('disabled', false);
			$statusEl.text(shelvdAdmin.i18n.error).removeClass('success').addClass('error');
		});
	});

})(jQuery);
