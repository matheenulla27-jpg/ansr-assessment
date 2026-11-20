# Decisions and trade-offs

This note records how the MVP resolves unspecified product behavior and where the implementation deliberately stops. The implementation and tests are the source of truth when an older roadmap differs; discrepancies are called out rather than hidden.

## Product and technical assumptions

- **Featured ranking:** Sort by rating rate descending, then rating count descending, then product ID ascending. The home page takes the first five, making ties stable.
- **Category split:** The current service and feature test put the first **two** ranked products in “Top Rated” and the rest in “More in this category”; products do not overlap and a short category can have an empty second group. This differs from [`.github/docs/PLAN.md`](./.github/docs/PLAN.md), which specifies five top-rated products. Treat this as an outstanding plan/implementation discrepancy, not as a silently changed acceptance criterion.
- **Search:** Trim the GET `q` query and match case-insensitively against title, description, and category only. Price and product ID are not searched. Blank and over-100-character queries fail validation; valid searches with no matches show an empty state.
- **Enquiries:** Product ID, name, email, and message are required; phone is optional. Name is capped at 120 characters, email at 255, phone at 40, and message at 2,000. The product is resolved before sending; an unknown ID returns 404. The validated contact and product context is emailed to `SALES_INBOX`.
- **Persistence:** Product and enquiry domain data are not stored in database tables. Product data is fetched through `ProductRepositoryInterface` and cached; enquiries are delivered by the mail abstraction. Laravel's standard framework tables remain available for its configured cache, queue, and session drivers.
- **Cache and outage behavior:** Successful upstream products are cached for `FAKE_STORE_CACHE_TTL` (default 3,600 seconds) and copied as plain arrays to a last-known-good cache for `FAKE_STORE_STALE_TTL` (default 86,400 seconds). On an expired/missing fresh entry the request refreshes synchronously; if that fails, valid stale data is logged and served. This is stale-on-error fallback, not a background stale-while-revalidate worker. If no valid cache is available, `ProductSourceUnavailable` becomes a user-visible HTTP 503 Inertia page; an outage is not represented as an empty catalogue.
- **External API boundary:** `FakeStoreProductRepository` is the only application adapter for Fake Store API. It validates response values into readonly `ProductData` objects and stores/rehydrates plain arrays. Controllers pass DTO-derived data to Inertia; React uses props and does not call external product APIs.
- **Mail:** Laravel's `log` mailer is the safe local default. Mailpit is optional local SMTP; its host and port are configurable without changing application code. Real secrets and credentials are not required or included.

## Simplified or deferred requirements

- No registration, authentication, user accounts, or admin UI: they are not necessary for the catalogue and enquiry PoC.
- No product or enquiry persistence, product editing, or enquiry history: upstream products remain the data source and the first version sends email only.
- No dedicated REST/GraphQL endpoint: Inertia is sufficient for the web MVP; a client API can be added behind the repository/service boundary.
- No production mail provider, paid API plan, or credentials: local logging and public Fake Store API keep setup free of secrets.
- No queue, Redis cache, background refresh, or rate limiting yet: the current traffic and time-box do not require operational infrastructure; these are listed as next steps.
- No filtering by price/ID and no pagination: search scope and catalogue size remain intentionally small for the PoC.

## How the PoC can grow

- Replace the container binding for `ProductRepositoryInterface` with a database- or ERP-backed adapter; catalogue sorting/search use the same domain contract.
- Switch Laravel's mail transport from `log`/local SMTP to AWS SES through configuration; `EnquiryService` and the mailable remain provider-independent.
- Add a REST or GraphQL layer for mobile clients and reuse the repository/catalogue service and typed product representation rather than exposing Fake Store payloads.
- Reskin the modular React catalogue components and layout independently of backend product sourcing and page orchestration.

## If we had another day

1. Persist enquiries with an explicit retention/privacy policy and migration.
2. Move shared product caching to Redis for multi-instance deployments.
3. Queue email delivery and add delivery-failure visibility.
4. Add admin curation for featured products instead of ranking alone.
5. Rate-limit enquiry submissions and cover the limits with feature tests.

