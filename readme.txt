=== Shelvd for WooCommerce ===
Contributors: simplinity
Tags: woocommerce, books, bookshop, isbn, book metadata
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Transform your WooCommerce store into a professional online bookshop with structured book metadata, ISBN lookup, author archives, and rich Schema.org markup.

== Description ==

**Shelvd for WooCommerce** adds everything a bookshop needs to WooCommerce. Instead of cramming book details into product descriptions, this plugin provides dedicated fields, taxonomies, and browsing features that make your books discoverable and your store professional.

= Key Features =

* **Book Details Panel** — A dedicated "Book Details" tab in the WooCommerce product editor with fields for ISBN, pages, year, edition, condition, format, and original language.
* **Custom Taxonomies** — Author, Publisher, and Language as proper WordPress taxonomies with archive pages and admin columns.
* **ISBN Lookup** — Automatic book data retrieval from Google Books and Open Library APIs. Enter an ISBN and auto-fill title, author, publisher, year, pages, and description.
* **Schema.org Markup** — Structured `Book` data output as JSON-LD for better search engine visibility.
* **Extended Search** — Customers can search by author name or ISBN in the WooCommerce product search.
* **Filter Widgets** — Sidebar widgets to filter by author, publisher, and language.
* **REST API** — Full REST API support for book metadata, plus custom endpoints for searching by author or ISBN.
* **Theme Override** — Templates can be overridden by your theme for full design control.

= Perfect For =

* Second-hand bookshops
* Independent bookstores
* Academic book retailers
* Antiquarian booksellers
* Any WooCommerce store that sells books

= Requirements =

* WordPress 6.0 or higher
* WooCommerce 7.0 or higher
* PHP 7.4 or higher

== Installation ==

1. Upload the `shelvd` folder to `/wp-content/plugins/`.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Go to WooCommerce > Settings > Products > Books to configure.
4. Edit any product and use the "Book Details" tab to add book metadata.

== Frequently Asked Questions ==

= Can I override the book metadata template? =

Yes. Copy `templates/product/book-metadata.php` from the plugin to `yourtheme/shelvd/product/book-metadata.php` and customize it.

= Does it work with WooCommerce HPOS? =

Yes. The plugin declares full compatibility with WooCommerce High-Performance Order Storage.

= Which ISBN lookup services are supported? =

Google Books API (default) and Open Library API. You can choose your preferred service in Settings.

== Screenshots ==

1. Book Details tab in the product editor.
2. ISBN lookup auto-fills product data.
3. Book metadata displayed on the product page.
4. Author archive page with all books by that author.
5. Settings page under WooCommerce > Products > Books.

== Changelog ==

= 1.0.0 =
* Initial release.
* Book Details product editor tab with ISBN, pages, year, edition, condition, format fields.
* Custom taxonomies: book_author, book_publisher, book_language.
* ISBN lookup via Google Books and Open Library.
* Schema.org Book JSON-LD markup.
* Extended product search (author + ISBN).
* Filter widgets for sidebar.
* REST API extension and custom endpoints.
* Settings page under WooCommerce > Products > Books.

== Upgrade Notice ==

= 1.0.0 =
Initial release. Install and activate to start using book metadata features.
