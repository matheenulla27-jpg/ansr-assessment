# Product Catalogue MVP — Technical Implementation Plan

## Scope and objective

This plan describes a maintainable Laravel + Inertia.js + React + TypeScript Product Catalogue MVP backed by the public Fake Store API. It is an implementation roadmap only; no application code is included here.

The existing repository is a Laravel starter. Its Composer manifest currently has Laravel 13 and no Inertia adapter; its JavaScript manifest has Tailwind CSS 4 and Vite but no React or TypeScript dependencies, and there is no application `resources/js` implementation yet. The roadmap includes that integration work rather than assuming it already exists.

## Product and technical assumptions

These decisions resolve behavior not fully specified in the acceptance criteria:

1. **Top-rated ranking:** Sort by `rating.rate` descending, then `rating.count` descending, then product ID ascending as a deterministic tie-breaker. The home page displays exactly the first five products.
2. **Category split:** For the selected category, display the first five products under **Top rated** and all remaining products under **More in this category**. Use the same ranking for both groups; do not duplicate products between the groups. A category with fewer than five products simply has no additional group items.
3. **Search:** Trim the submitted query and match case-insensitively against `title`, `description`, and `category`. Do not search price or product ID. Use a GET query parameter so the search is bookmarkable. A blank query is a validation error, a query longer than 100 characters is rejected, and a valid query with no matches shows an empty-results state.
4. **Enquiry fields:** Require name, email, and message; phone is optional. Validate email format and reasonable lengths (name up to 120 characters, email up to 255, phone up to 40, and message up to 2,000). Include the product's ID/title and the submitted contact details/message in the email.
5. **Local mail:** Keep Laravel's `log` mailer as the default so a new developer needs no account, credentials, or running mail service. Document optional Mailpit SMTP at `127.0.0.1:1025` and its web inbox at `127.0.0.1:8025`. Configure the destination through `SALES_INBOX` with a safe example address in `.env.example`; do not commit real credentials.
6. **Persistence:** Do not create product or enquiry tables. Fetch products through the repository and cache them; send validated enquiries by email without retaining them in the database.
7. **Product-source errors:** Cache successful product data for a configurable fresh TTL (proposed default: one hour) and retain a last-known-good copy for a longer configurable TTL (proposed default: 24 hours). On an upstream transport/status failure, log the degraded condition and serve stale data if available. If no valid cache is available, raise a domain-specific exception and render a user-visible 503 state; never disguise an outage as a successful empty catalogue.
8. **Local setup:** Fake Store API requires no key. The app should run under Laravel Herd with Composer and the existing SQLite setup; optional Mailpit use must not block setup. No paid external account or committed secret is required.

## Extensible architecture

### Backend boundaries

- **`ProductData` (`app/DTOs/ProductData.php`):** A readonly, typed representation of one product: ID, title, description, category, price, image URL, rating rate, and rating count. Convert/validate the upstream payload at the adapter boundary; controllers and React must not consume untyped Fake Store arrays.
- **`ProductRepositoryInterface` (`app/Contracts/ProductRepositoryInterface.php`):** Define the app-facing operations to retrieve all products and find one by ID. Derive categories, ranking, category partitions, and search results from the repository data so those behaviors are independent of Fake Store-specific endpoints.
- **`FakeStoreProductRepository` (`app/Repositories/FakeStoreProductRepository.php`):** The only application class that calls `https://fakestoreapi.com`. Use Laravel's HTTP client with the project convention `timeout(5)->retry(2, 100)`, `Cache::remember()` for fresh data, and a separate last-known-good cache. Validate the response shape before mapping DTOs. Catch only expected HTTP/connection failures, log when stale data is served, and throw `ProductSourceUnavailable` when there is no usable cache; do not return a success-shaped empty array on errors.
- **Container binding:** Bind `ProductRepositoryInterface` to `FakeStoreProductRepository` in `AppServiceProvider`. Keep the API base URL and cache lifetimes in configuration (under `services.fake_store`) and `.env.example`.
- **`ProductCatalogueService` (`app/Services/ProductCatalogueService.php`):** Own featured ranking, category names and partitions, case-insensitive search, and product lookup orchestration. Use one shared deterministic comparator for all catalogue ordering. Keep these decisions out of controllers and UI.
- **Inertia controllers:** Use small controllers for home, category, search, product detail, and enquiry submission. Inject a service/repository, select the page use case, and pass readonly DTO-derived arrays or typed props to `Inertia::render()`. Map product-source unavailability centrally to an explicit 503 Inertia error page.
- **Enquiry flow:** `StoreEnquiryRequest` owns server-side validation. `EnquiryService` coordinates delivery through Laravel's mail abstraction (`Mail` facade and configured mail transport), and `EnquiryMailable` formats the product and contact context. Keep the inbox address in configuration (`SALES_INBOX`); changing Laravel's mail transport/provider must not require controller or React changes.
- **No API calls in React:** All product/category/search data arrives in Inertia props. Never use `fetch()` or `axios` to access Fake Store API from the browser.

### Frontend boundaries

- Set up the Laravel Inertia adapter, React, TypeScript, and Vite integration while preserving the existing Tailwind 4 toolchain.
- Create a shared `AppLayout` for the common header, category navigation, search entry point, content area, and accessible navigation.
- Define product, category, page-prop, and enquiry types under `resources/js/types/`; keep strict TypeScript and do not use `any`.
- Keep reusable UI in modular catalogue components: `ProductCard`, `ProductGrid`, `CategoryNavigation`, `SearchBar`, and `EnquiryForm`. Use semantic HTML, accessible labels, and clean Tailwind utilities/design tokens without baking in a brand identity, so a later visual redesign can replace styling without rewriting page behavior.
- Use Inertia `<Link>` for navigation, `router.get()` for GET search, and `useForm()` for enquiry submission. The product detail page's Enquire action reveals the enquiry form; a successful submission returns to the detail page with a “Thank you” message.

## Sequential six-task implementation roadmap

For each code task, add the listed Pest coverage before running `composer run format`, then run `php artisan test`. The repository's `composer run format` script runs Rector and Pint. Do not begin the next task until the current task's tests and workflow pass.

### Task 1 — Product DTO, repository contract, and Fake Store adapter

**Files to create or modify**

- Create `app/DTOs/ProductData.php`
- Create `app/Contracts/ProductRepositoryInterface.php`
- Create `app/Repositories/FakeStoreProductRepository.php`
- Create `app/Exceptions/ProductSourceUnavailable.php`
- Modify `app/Providers/AppServiceProvider.php`
- Modify `config/services.php`
- Modify `.env.example`
- Create `tests/Unit/ProductDataTest.php`
- Create `tests/Unit/FakeStoreProductRepositoryTest.php`

**Required Pest scenarios**

- Map a representative `/products` payload, including nested `rating`, to a readonly DTO with the expected typed values.
- Retrieve the product collection from the expected Fake Store endpoint; a second read within the fresh-cache TTL does not make another HTTP request.
- Find an existing ID and return `null` for a missing ID.
- A transient upstream failure is retried and a later successful response is returned.
- When refresh fails, return a valid stale last-known-good collection; verify the degraded path is logged.
- With no stale cache, an exhausted timeout/connection or non-success response raises `ProductSourceUnavailable`, not an empty collection.
- An invalid/malformed response is rejected rather than being mapped as valid catalogue data.

**Gate:** `composer run format`, then `php artisan test`.

### Task 2 — Inertia foundation, home page, and five featured products

**Files to create or modify**

- Modify `composer.json` and `composer.lock` to add `inertiajs/inertia-laravel`.
- Modify `package.json` and `package-lock.json` to add the Inertia React adapter, React, TypeScript, React types, and the Vite React integration; preserve the current Tailwind 4 dependencies.
- Modify `vite.config.js` and `resources/css/app.css`; create `tsconfig.json`.
- Modify `bootstrap/app.php`; create `app/Http/Middleware/HandleInertiaRequests.php`.
- Create `resources/views/app.blade.php`, `resources/js/app.tsx`, `resources/js/layouts/AppLayout.tsx`, and `resources/js/types/catalogue.ts`.
- Create `resources/js/components/catalogue/ProductCard.tsx`, `resources/js/components/catalogue/ProductGrid.tsx`, and `resources/js/pages/Home.tsx`.
- Create `app/Services/ProductCatalogueService.php` and `app/Http/Controllers/HomeController.php`; modify `routes/web.php`.
- Create `resources/js/pages/Errors/Unavailable.tsx`; configure `bootstrap/app.php` to render it with HTTP 503 for `ProductSourceUnavailable`.
- Create `tests/Feature/HomePageTest.php`.

**Required Pest scenarios**

- `GET /` renders the Home Inertia component and exactly five featured products.
- Use a controlled repository fixture to assert descending rate, descending count for rate ties, ascending ID for complete ties, and exclusion of products after rank five.
- A product-source failure with no usable cache returns an explicit 503/error response rather than a successful empty page.
- Bind a fixture/fake repository for feature tests; do not make a live API request.

**Gate:** `composer run format`, then `php artisan test`.

### Task 3 — Category navigation and category page

**Files to create or modify**

- Create `app/Http/Controllers/CategoryController.php`.
- Modify `app/Services/ProductCatalogueService.php`, `routes/web.php`, `resources/js/layouts/AppLayout.tsx`, and `resources/js/types/catalogue.ts`.
- Create `resources/js/components/catalogue/CategoryNavigation.tsx` and `resources/js/pages/Categories/Show.tsx`.
- Create `tests/Feature/CategoryPageTest.php`.

**Required Pest scenarios**

- Category navigation is populated from product data and links to the named category route.
- A category response contains its five highest-ranked products and the remaining products in separate props/sections; assert order and no overlap.
- Products belonging to another category are excluded, and a category with fewer than five products has an empty additional-products group.
- An unknown category returns 404.
- An upstream outage is not silently represented as an empty category page.

**Gate:** `composer run format`, then `php artisan test`.

### Task 4 — Product search

**Files to create or modify**

- Create `app/Http/Controllers/ProductSearchController.php` and `app/Http/Requests/SearchProductsRequest.php`.
- Modify `app/Services/ProductCatalogueService.php`, `routes/web.php`, `resources/js/layouts/AppLayout.tsx`, and `resources/js/types/catalogue.ts`.
- Create `resources/js/components/catalogue/SearchBar.tsx` and `resources/js/pages/Products/Search.tsx`.
- Create `tests/Feature/ProductSearchTest.php`.

**Required Pest scenarios**

- Search finds matches in title, description, and category case-insensitively and after trimming whitespace.
- Every returned result matches the query and non-matching products are excluded.
- A valid query with no matches renders the explicit empty-results state.
- Blank and over-100-character queries fail validation.
- A term present only in price or product ID does not match.
- Product-source unavailability is surfaced rather than converted to an empty search result.

**Gate:** `composer run format`, then `php artisan test`.

### Task 5 — Product detail, enquiry form, and email

**Files to create or modify**

- Create `app/Http/Controllers/ProductController.php`, `app/Http/Controllers/EnquiryController.php`, and `app/Http/Requests/StoreEnquiryRequest.php`.
- Create `app/Services/EnquiryService.php`, `app/Mail/EnquiryMailable.php`, and `resources/views/mail/enquiry.blade.php`.
- Modify `config/services.php` and `routes/web.php`.
- Modify `resources/js/types/catalogue.ts` and `resources/js/components/catalogue/ProductCard.tsx`.
- Create `resources/js/pages/Products/Show.tsx` and `resources/js/components/catalogue/EnquiryForm.tsx`.
- Create `tests/Feature/ProductDetailTest.php` and `tests/Feature/ProductEnquiryTest.php`.
- Add `SALES_INBOX` to `.env.example` here if it was not already introduced in Task 1.

**Required Pest scenarios**

- A product card links to its detail route; the detail Inertia response has the correct product props.
- An unknown product ID returns 404 and cannot be used to send an enquiry.
- A valid submission is accepted with and without optional phone; `Mail::fake()`/`Mail::assertSent()` verify exactly one `EnquiryMailable` reaches the configured sales inbox with product and contact context.
- A successful submission redirects back with the “Thank you” flash message.
- Missing/invalid name, malformed email, missing message, invalid optional phone, and overlong values fail validation and send no email.
- Assert mail is not sent when the referenced product cannot be resolved.

**Gate:** `composer run format`, then `php artisan test`.

### Task 6 — Reviewer documentation and handoff

**Files to create or modify**

- Modify `README.md` with the quickstart, technical overview, and verification commands.
- Create `DECISIONS.md` with the explicit product, cache/error, persistence, and provider-boundary choices.
- Create `AI_USAGE.md` with a truthful summary of AI assistance and human review/verification.
- Modify `.env.example` to show only safe local defaults and all required configurable values (`FAKE_STORE_API_URL`, cache TTLs if configurable, `SALES_INBOX`, and log/Mailpit mail settings).
- Create `docs/PR_DESCRIPTION.md` with a reviewer-ready change summary, implementation highlights, test/format results, and known limitations.

**Required verification**

- Documentation-only edits do not need new Pest cases. Cross-check all documented routes, environment variables, setup commands, architecture decisions, and test results against the implementation.
- README setup must cover Laravel Herd/Composer, the existing SQLite/migration flow, required PHP and Node versions, frontend build/dev commands, and a setup path under ten minutes without paid accounts or secrets.
- Explain that `MAIL_MAILER=log` works without a mail service and how to opt into local Mailpit SMTP; document `SALES_INBOX` without a real address.
- Run the repository workflow after the documentation is finalized: `composer run format`, then `php artisan test`.

## Review and implementation notes

- This roadmap is intentionally sequential: the Inertia home page depends on the product contract, category/search pages reuse the shared catalogue service and layout, and product enquiry depends on the product detail route.
- Fake Store HTTP is isolated in the backend adapter. All tests use HTTP fakes or a bound repository fixture; no test depends on external availability.
- Keep failures visible and typed at the boundary. Do not add broad exception catches, silent empty-data fallbacks, external browser calls, product/enquiry tables, or provider-specific credentials.
- The present worktree already contains changes in `.github/copilot-instructions.md`, `composer.json`, `composer.lock`, `rector.php`, and `feature/`. Preserve those changes while implementing future tasks.
- The requested deliverable for this planning step is this file only; it does not implement any application feature.
