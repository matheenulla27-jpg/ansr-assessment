# PR: Product Catalogue MVP

## Summary

Build a Laravel + Inertia.js + React (TypeScript) product catalogue backed by Fake Store API, with category browsing, search, product detail, and enquiry email delivery.

## Architecture

- `ProductRepositoryInterface` separates catalogue use cases from the Fake Store adapter; `ProductData` validates and types upstream products.
- `ProductCatalogueService` owns ranking, categories, and search. Controllers return Inertia props; React does not call the external API.
- Fresh and last-known-good product caches reduce upstream requests and allow stale data on refresh errors. An unavailable source with no valid cache renders an explicit 503 page.
- `StoreEnquiryRequest`, `EnquiryService`, and `EnquiryMailable` validate and deliver enquiries through Laravel's configurable mail transport.
- No product or enquiry database tables are added; local setup uses SQLite and does not require paid services or secrets.

## Acceptance coverage

- Five featured products with deterministic ordering: rating rate descending, rating count descending, product ID ascending.
- Category navigation and category results use the same deterministic order.
- Search is a trimmed, case-insensitive GET query over title, description, and category, with validation and an empty-results state.
- Product detail and validated enquiry submission include unknown-product and invalid-input handling.
- Fresh/stale cache, upstream failure, and 503 behavior are covered.

**Known discrepancy:** the implementation and category feature test currently split the first **two** products into “Top Rated”; [`.github/docs/PLAN.md`](../.github/docs/PLAN.md) specifies five. This is surfaced for reviewer resolution rather than misrepresented as matching the plan.

## Verification

Run from the repository root:

```sh
npm run build
composer run format
php artisan test
```

Results for this checkout:

- `npm run build` — passed (Vite production build; emitted an informational warning that optional `fontaine` is not installed).
- `composer run format` — passed (Rector made no changes; Pint passed).
- `php artisan test` — passed: **43 tests, 258 assertions**.

## Trade-offs

- Uses the public Fake Store API and configurable cache instead of a product database.
- Logs mail by default; local Mailpit SMTP is optional.
- Enquiries are emailed, not persisted; authentication, admin curation, queueing, Redis, rate limiting, and a mobile API are deferred.

