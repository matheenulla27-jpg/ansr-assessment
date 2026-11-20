---
applyTo: "tests/**/*.php"
---
# Pest Testing Conventions
- Write expressive Pest tests using `it('...', function () { ... })` and `expect()` chains (avoid PHPUnit `$this->assert*` methods).
- Use `Http::fake()` to mock Fake Store API responses—never make live HTTP requests in tests.
- Use `Mail::fake()` and `Mail::assertSent(...)` to verify enquiry emails.
- For Inertia responses, use `assertInertia(fn (Assert $page) => $page->component(...)->has(...))`.
- Test both valid flows and edge cases (empty search results, invalid product ID 404, form validation errors, API timeout fallback).