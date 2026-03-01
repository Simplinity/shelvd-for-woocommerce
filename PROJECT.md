# Shelvd for WooCommerce — Project Document

## Wat is Shelvd for WooCommerce?

Shelvd for WooCommerce is een WooCommerce-plugin die een gewone webshop transformeert in een professionele online boekenwinkel. In plaats van boekgegevens los in productbeschrijvingen te stoppen, biedt de plugin gestructureerde velden, eigen taxonomieën, en browsing-features die boeken vindbaar maken en de winkel een professionele uitstraling geven.

De plugin is ontworpen als een commercieel product — verkoopbaar aan elke WooCommerce-boekenwinkel wereldwijd.


## Wat de plugin doet

### Product-editor: Book Details tab
Er komt een nieuw tabblad "Book Details" bij elk WooCommerce-product met velden voor:
- ISBN (met validatie voor ISBN-10 en ISBN-13)
- Aantal pagina's
- Publicatiejaar
- Editie
- Conditie (nieuw, als nieuw, zeer goed, goed, redelijk, slecht)
- Formaat (hardcover, paperback, pocket, e-book, audioboek)
- Oorspronkelijke taal

### ISBN Lookup
Een knop in de product-editor waarmee je een ISBN invoert en automatisch titel, auteur, uitgever, jaar, pagina's, taal en beschrijving ophaalt via Google Books API of Open Library API. Configureerbaar welke service primair is.

### Custom taxonomieën
Auteurs, uitgevers en talen worden echte WordPress-taxonomieën (net als categorieën):
- `book_author` — Elk auteur krijgt een eigen archiefpagina met al zijn/haar boeken
- `book_publisher` — Idem voor uitgevers
- `book_language` — Idem voor talen

Dit levert ook SEO-voordeel op: elke taxonomie-pagina heeft een unieke URL.

### Frontend productpagina
- Metadata-blok onder de prijs met auteur, uitgever, ISBN, jaar, pagina's, formaat en conditie
- Extra "Book Details" tab bij de productbeschrijving
- Schema.org Book JSON-LD markup voor zoekmachines
- Template is overridable via het thema (`yourtheme/shelvd/product/book-metadata.php`)

### Uitgebreid zoeken
Klanten kunnen zoeken op auteursnaam of ISBN — niet alleen op producttitel. Dit werkt binnen de standaard WooCommerce-zoekfunctie.

### Filter widgets
Sidebar-widgets waarmee bezoekers de shop kunnen filteren op auteur, uitgever en taal. AJAX-gebaseerd voor een vlotte gebruikerservaring.

### REST API
- Alle boekvelden beschikbaar op het standaard WooCommerce product-endpoint (`book_data` field)
- Taxonomie-velden (`book_authors`, `book_publishers`, `book_languages`)
- Custom endpoints:
  - `GET /shelvd/v1/products/by-author/{id}` — Boeken per auteur
  - `GET /shelvd/v1/search?s=...&author=...&isbn=...` — Zoeken op meerdere criteria

### Instellingen
Onder WooCommerce > Settings > Products > Books:
- Auteur-/uitgever-/taal-archiefpagina's aan/uit
- Schema.org markup aan/uit
- Uitgebreide zoekfunctie aan/uit
- Keuze ISBN lookup service (Google Books of Open Library)


## Huidige bestandsstructuur

```
shelvd/
├── shelvd.php                                    # Main plugin file, constants, activation hooks
├── LICENSE                                       # GPL v2
├── readme.txt                                    # WordPress.org readme
├── PROJECT.md                                    # Dit bestand
│
├── includes/
│   ├── traits/
│   │   └── trait-singleton.php                   # Singleton pattern trait
│   │
│   ├── class-plugin.php                          # Hoofd-orchestrator: dependency check, loading, hooks, assets
│   ├── class-settings.php                        # WooCommerce settings sectie (Products > Books)
│   │
│   ├── database/
│   │   ├── class-taxonomy-manager.php            # Registreert book_author, book_publisher, book_language
│   │   └── class-schema.php                      # Default options bij activatie
│   │
│   ├── lib/
│   │   ├── class-book-meta.php                   # CRUD helper voor _book_* post meta
│   │   └── class-isbn-validator.php              # ISBN-10/13 validatie, conversie, formattering
│   │
│   ├── admin/
│   │   ├── class-product-editor.php              # "Book Details" tab in WooCommerce product editor
│   │   └── class-isbn-lookup.php                 # AJAX ISBN lookup (Google Books + Open Library)
│   │
│   ├── frontend/
│   │   ├── class-product-display.php             # Metadata op productpagina, Schema.org, zoek-extensie
│   │   └── class-filter-widgets.php              # Sidebar filter widgets (auteur, uitgever, taal)
│   │
│   └── api/
│       └── class-rest-controller.php             # REST API extensie + custom endpoints
│
├── templates/
│   └── product/
│       └── book-metadata.php                     # Frontend template (theme-overridable)
│
└── assets/
    ├── css/
    │   ├── admin.css                             # Admin styles (product editor tab)
    │   └── frontend.css                          # Frontend styles (metadata, filters)
    │
    └── js/
        ├── admin.js                              # ISBN lookup button logic
        └── filters.js                            # AJAX filter interactie
```

## Technische specificaties

- **Minimum WordPress:** 6.0
- **Minimum WooCommerce:** 7.0
- **Minimum PHP:** 7.4
- **HPOS compatibel:** Ja (declaratie in class-plugin.php)
- **Namespace:** `Shelvd`
- **Text domain:** `shelvd`
- **Meta prefix:** `_book_` (isbn, pages, year, edition, condition, format, original_language)
- **Taxonomies:** `book_author`, `book_publisher`, `book_language`
- **Licentie:** GPL v2 or later


## Doelgroep

- Tweedehands boekenhandels
- Onafhankelijke boekwinkels
- Academische boekverkopers
- Antiquariaten
- Elke WooCommerce-winkel die boeken verkoopt


---


## Roadmap: wat moet er nog gebeuren

De plugin heeft een werkende basis. Hieronder staat wat er nog nodig is om er een volwaardig, verkoopbaar professioneel product van te maken.


### Fase 2 — Functionele features

#### 2.1 CSV/Excel Import & Export
Kopers willen hun boekencatalogus kunnen importeren vanuit CSV of Excel, en exporteren. Dit is de meest gevraagde feature bij productdata-plugins.
- Import: CSV upload → mapping UI → preview → batch import
- Export: selectie → CSV/Excel download met alle boekvelden
- Ondersteuning voor WooCommerce's eigen CSV-formaat (uitbreiden met boekvelden)

**Bestanden:** `includes/admin/class-csv-importer.php`, `includes/admin/class-csv-exporter.php`, `assets/js/import.js`, `assets/css/import.css`, `templates/admin/import.php`

#### 2.2 Admin kolommen
Auteur, ISBN en conditie als sorteerbare en filterbare kolommen in het WooCommerce product-overzicht (edit.php?post_type=product). Deels voorbereid in taxonomy manager maar moet completer: quick-edit support, sorteerbaar, filterbaar via dropdown.

**Bestanden:** `includes/admin/class-product-columns.php`

#### 2.3 Bulk editing
Meerdere producten tegelijk een auteur, uitgever, conditie of formaat toewijzen via het WooCommerce bulk-edit scherm.

**Bestanden:** uitbreiding in `class-product-editor.php` of nieuw `includes/admin/class-bulk-editor.php`

#### 2.4 Shortcodes & Gutenberg Blocks
- `[books by_author="..."]` — Toon boeken van een specifieke auteur
- `[books by_publisher="..."]` — Toon boeken van een uitgever
- `[new_books count="8"]` — Nieuwste boeken
- `[books condition="new"]` — Boeken per conditie
- Gutenberg block-equivalenten van bovenstaande
- Book Grid block met filteropties

**Bestanden:** `includes/class-shortcodes.php`, `includes/blocks/` folder, `assets/js/blocks.js`

#### 2.5 Related Books
"Meer van deze auteur" / "Meer van deze uitgever" sectie onder het product, vergelijkbaar met WooCommerce's "Related products" maar dan gebaseerd op boek-taxonomieën.

**Bestanden:** `includes/frontend/class-related-books.php`, `templates/product/related-books.php`

#### 2.6 Boekenlijsten / Curated Collections
Mogelijkheid om handmatige boekenlijsten samen te stellen ("Zomerlezen 2026", "Top 10 thrillers") als een custom post type of taxonomy. Toonbaar via shortcode/block.

**Bestanden:** `includes/class-book-lists.php`, `templates/book-list.php`


### Fase 3 — Kwaliteit & vertrouwen

#### 3.1 Uninstall.php
Nette cleanup bij verwijdering. Optie om alle data (meta, taxonomies, opties) te wissen of te behouden.

**Bestanden:** `uninstall.php`

#### 3.2 POT-bestand
Vertaaltemplate genereren zodat de plugin in elke taal vertaald kan worden.

**Bestanden:** `languages/shelvd.pot`

#### 3.3 Input validatie & sanitization audit
Alle user input in de product editor, REST API, en AJAX handlers moet streng gevalideerd worden. Audit op XSS, SQL injection, CSRF. WordPress coding standards (WPCS) doorlopen met PHP_CodeSniffer.

#### 3.4 Unit tests
PHPUnit tests met WordPress test framework:
- ISBN validator (checksum, conversie, edge cases)
- Book_Meta CRUD (set, get, get_all, has_book_data)
- REST API endpoints (response format, filtering, permissions)
- Taxonomy Manager (registratie, term creation)
- Settings (default values, saving)

**Bestanden:** `tests/` folder, `tests/bootstrap.php`, `tests/test-isbn-validator.php`, `tests/test-book-meta.php`, `tests/test-rest-api.php`, `phpunit.xml`

#### 3.5 Coding standards
- WordPress Coding Standards (WPCS) compliance
- PHPStan / Psalm static analysis
- ESLint voor JavaScript
- Composer + npm scripts voor linting

**Bestanden:** `composer.json`, `package.json`, `.phpcs.xml`, `.eslintrc`

#### 3.6 Documentatie
- Inline PHPDoc voor alle classes en methods (grotendeels al aanwezig)
- Developer hooks documentatie (alle filters en actions)
- Gebruikershandleiding (Getting Started, Features, FAQ)
- Online docs-site of GitHub wiki


### Fase 4 — Commercieel

#### 4.1 Freemium model
Meest bewezen model voor WooCommerce-plugins:
- **Gratis (WordPress.org):** Book Details tab, taxonomieën, ISBN lookup, Schema.org, basis zoeken
- **Pro (betaald):** CSV import/export, Gutenberg blocks, geavanceerde filters, related books, boekenlijsten, premium support

#### 4.2 Licentiesysteem
Voor de Pro-versie is een license key systeem nodig. Opties:
- Freemius SDK (meest gangbaar, handles licensing + updates + analytics)
- EDD Software Licensing (als je Easy Digital Downloads gebruikt)
- WooCommerce API Manager
- Custom (eigen license server)

**Bestanden:** `includes/class-license.php`, `includes/class-updater.php` (of Freemius SDK integratie)

#### 4.3 Automatische updates
Pro-gebruikers moeten updates ontvangen buiten WordPress.org om. Dit wordt typisch afgehandeld door het licentiesysteem (Freemius, EDD, etc.).

#### 4.4 Website & landing page
- Productpagina met feature-overzicht, screenshots, demo
- Prijzentabel (Free vs Pro)
- Documentatie-sectie
- Support/contact

#### 4.5 Support systeem
- Helpdesk (Zendesk, HelpScout, of eigen WordPress-based)
- Knowledge base / FAQ
- WordPress.org support forum (voor gratis versie)

#### 4.6 Marketing assets
- Plugin banner en icoon voor WordPress.org (772x250 banner, 256x256 icon)
- Screenshots voor WordPress.org listing (minimaal 5)
- Demo-site met voorbeelddata
- Video walkthrough


### Fase 5 — Groei

#### 5.1 WooCommerce Marketplace listing
Aanvraag voor opname in de officiële WooCommerce Marketplace (woocommerce.com/products/). Vereist review door WooCommerce team.

#### 5.2 Integraties
- Goodreads import
- Amazon ASIN lookup
- LibraryThing import
- ONIX (standaard boekhandel data-uitwisseling formaat) import/export

#### 5.3 Meertaligheid
- WPML / Polylang compatibiliteit testen en documenteren
- Vertalingen: Nederlands, Frans, Duits, Spaans als eerste

#### 5.4 Analytics dashboard
Admin dashboard widget met:
- Best verkopende auteurs
- Populairste categorieën
- Voorraad per conditie
- ISBN coverage (% producten met ISBN)


## Prioriteitsvolgorde

| Prioriteit | Item | Reden |
|-----------|------|-------|
| 1 | CSV Import/Export | Meest gevraagde feature, dealbreaker voor grotere winkels |
| 2 | Admin kolommen + bulk edit | Dagelijkse workflow verbetering |
| 3 | Unit tests + coding standards | Kwaliteitsgarantie voor verkoop |
| 4 | Uninstall.php + POT-bestand | WordPress.org vereisten |
| 5 | Shortcodes & blocks | Visuele presentatie, marketing feature |
| 6 | Related Books | Verkoopverhoging, upsell feature |
| 7 | Freemium + licentiesysteem | Monetisatie |
| 8 | Website + docs + screenshots | Verkoop enablement |
| 9 | Boekenlijsten | Nice-to-have, differentiator |
| 10 | Integraties (Goodreads, ONIX) | Groei, niche dominantie |
