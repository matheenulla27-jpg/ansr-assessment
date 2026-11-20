# Product Catalogue

A product catalogue built with PHP, Laravel, Inertia.js, React, and TypeScript. Product data comes from Fake Store API through a backend repository; React receives typed page props and never calls the external API directly.

## Why this stack

PHP with Laravel, Inertia.js, and React (TypeScript) was chosen to deliver a complete server-and-browser product experience quickly within the assessment time-box. Laravel provides routing, validation, caching, and mail delivery in one application, while Inertia lets the UI use modular React components without introducing a separate API service. The repository/DTO boundary keeps product-source details out of controllers and the UI, so the data source can later change or an API can be added for mobile clients without rewriting the catalogue screens.

## Local setup (under 10 minutes)

Requirements: PHP 8.3+, Composer 2, Node.js 20.19+ (developed and verified on Node 26.5.0), npm, and [Laravel Herd](https://herd.laravel.com/) (or another local PHP server). No paid account, API key, or mail credentials are needed.

### Quick setup (single command)

From the repository root, run the bundled Composer setup script:

```sh
composer run setup
```

### Step-by-step setup

1. Install PHP dependencies:

```sh
composer install
```

2. Create the local environment and app key:

```sh
cp .env.example .env
php artisan key:generate
```

3. Use SQLite. The default connection is SQLite at `database/database.sqlite`; create the empty file if it is not already present, then apply the framework migrations:

```sh
# Windows PowerShell
if (!(Test-Path database\database.sqlite)) { New-Item -ItemType File database\database.sqlite | Out-Null }
php artisan migrate
```

On macOS/Linux, use `touch database/database.sqlite` instead of the PowerShell file-creation command. There are no product or enquiry tables in this MVP; migrations are for Laravel's framework tables.

4. Install frontend dependencies and compile assets:

```sh
npm install
npm run build
```

5. Open in Laravel Herd:
- If cloned inside a Herd parked directory, visit `http://<folder-name>.test` directly in your browser.
- If cloned elsewhere, run `herd link` in the project directory and open the `.test` URL Herd reports (or run `composer run dev` to serve locally).

6. Run verification and formatting workflows:

```sh
composer run check
composer run format
```

`composer run check` runs Rector (`--dry-run`), Pint (`--test`), and the Pest test suite (`php artisan test`). `composer run format` applies Rector and Pint fixes.

## Email in development

The default `.env.example` uses `MAIL_MAILER=log`; submitting an enquiry writes its message to `storage/logs/laravel.log`, so no external mail service is needed. The destination is configured by `SALES_INBOX` (default: `sales@example.com`, a placeholder).

To inspect email in a local [Mailpit](https://mailpit.axllent.org/) instance, start Mailpit and set these values in `.env`:

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
SALES_INBOX=sales@example.com
```

Mailpit accepts SMTP on `127.0.0.1:1025`; view the inbox at `http://127.0.0.1:8025`. No production credentials belong in this repository.

## Routes

| Route | Purpose |
| --- | --- |
| `GET /` (`home`) | Five top-rated featured products |
| `GET /categories/{category}` (`categories.show`) | Category products, split into top-rated and additional products |
| `GET /products/search?q=...` (`products.search`) | Product search (live debounced + submit button) |
| `GET /products/{id}` (`products.show`) | Product detail and enquiry modal |
| `POST /products/enquiries` (`products.enquiries.store`) | Validated enquiry email |

See [DECISIONS.md](./DECISIONS.md) for product behavior, trade-offs, and follow-up work, and [AI_USAGE.md](./AI_USAGE.md) for how AI tools were directed and verified.

## Time spent

Approximate active time across phases:

| Phase | Approximate time |
| --- | --- |
| Planning | ~20 mins |
| Building | ~60 mins |
| Testing | ~25 mins |
| Reviewing & Polish | ~20 mins |