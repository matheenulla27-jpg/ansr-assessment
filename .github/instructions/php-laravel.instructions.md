---
applyTo: "app/**/*.php,routes/**/*.php"
---
# PHP & Laravel Conventions
- Use `declare(strict_types=1);` at the top of every file.
- Use PHP 8.3+ constructor property promotion and `readonly` DTO classes (`app/DTOs/`) for structured data instead of passing raw external API arrays.
- Bind repository interfaces (`app/Contracts/`) to concrete implementations in `AppServiceProvider`.
- Validate all incoming requests with dedicated `FormRequest` classes (`app/Http/Requests/`).
- Wrap external HTTP calls (Fake Store API) in `Http::timeout(5)->retry(2, 100)` with Laravel `Cache::remember()` and throw domain-specific exceptions on failure.
- Use Laravel's `Mail` facade with `Mailable` classes (`app/Mail/`) so mail drivers remain swappable via `.env`.