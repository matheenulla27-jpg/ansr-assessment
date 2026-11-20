# Project Rules: Product Catalogue MVP

## Architecture & Core Principles
- This is a Laravel + Inertia.js + React (TypeScript) application using Pest for testing, Laravel Pint for formatting, and Rector for static refactoring.
- **Strict Boundary Rule:** React components MUST NEVER call external APIs (such as Fake Store API) directly. All product data flows through Laravel backend services and Inertia props.
- **Extensibility Rule:** External dependencies (product source, email provider) must be abstracted behind interfaces/services so they can be swapped without modifying controllers or UI components.
- Keep Inertia controllers skinny: delegate business logic to Services/Repositories and pass readonly DTOs or typed arrays to `Inertia::render()`.

## Quality & Testing Mandate
- Every feature or task MUST include Pest feature or unit tests covering the happy path and validation/edge cases.
- All PHP files must include `declare(strict_types=1);`, explicit parameter types, and explicit return types.
- After generating code, ensure it passes `./vendor/bin/rector process`, `./vendor/bin/pint`, and `./vendor/bin/pest`.

## Mandatory Post-Task Workflow (Every Code Change)
1. **Write Tests First or Alongside Code:** Every new feature, service, controller, or bug fix MUST include Pest tests (`tests/Feature` or `tests/Unit`) covering the happy path and edge cases.
2. **Format & Refactor:** After writing code and tests, ALWAYS run `composer run format` (which executes `vendor/bin/rector` and `vendor/bin/pint`) so all PHP files adhere to strict types, Rector rules, and Laravel Pint styling.
3. **Verify Everything Passes:** Run `php artisan test` after `composer run format` to confirm zero regressions before finishing a task.