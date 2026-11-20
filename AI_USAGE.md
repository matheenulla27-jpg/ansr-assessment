# AI assistance and review

## Tools and model

- **Tool:** GitHub Copilot in VS Code.
- **Underlying model:** Copilot Fusion (as identified for this implementation).
- The repository also contains committed agent guidance in [`.github/copilot-instructions.md`](./.github/copilot-instructions.md) and [`.github/instructions/`](./.github/instructions/). The implementation roadmap is [`.github/docs/PLAN.md`](./.github/docs/PLAN.md); the plan is in `.github/docs/`, not `docs/`.
- The work followed a plan-first sequence: define the repository/DTO and UI boundaries, deliver the catalogue features in increments, add Pest coverage, and run formatter/test gates before handoff.

## Prompt themes and review moments

These are concise paraphrases of the work-driving prompts and review themes, not verbatim prompt transcripts:

1. **Plan before coding:** define the six-task sequence, product assumptions, architecture boundaries, and a test/format gate for each feature.
2. **Repository and DTO boundary:** keep Fake Store HTTP in a Laravel adapter, validate upstream data into a readonly DTO, and pass typed Inertia props to React.
3. **Determinism and tests:** define tie-breaking, category partitioning, and search semantics explicitly, then cover ordering, validation, and empty/error states with Pest.
4. **Resilient API and cache:** use configurable API URL/TTLs, retry upstream calls, preserve last-known-good data on refresh failure, and report a 503 when neither fresh nor valid stale data is available.
5. **Critical review and local verification:** inspect route/config/test reality, verify mail defaults and local Herd behavior, and run the prescribed build, formatting, and test commands before writing the reviewer handoff.

## Bugs and issues caught during human review/testing

- **`__PHP_Incomplete_Class` from caching DTO objects:** object instances in the cache can be unserializable under the application's safe cache configuration. The adapter now caches plain arrays (`ProductData::toArray()`) and hydrates DTOs after reads; the file-cache test checks the round trip and that the second read is served from cache.
- **Local Herd API configuration/request behavior:** review focused on ensuring `FAKE_STORE_API_URL` has a usable configured fallback and that the Laravel HTTP client sends the expected JSON request headers in the local Herd setup. The adapter uses the configured base URL and its `/products` endpoint; the repository test verifies the resolved endpoint. No Herd credentials or API key are needed.

This summary documents the AI-assisted workflow and validation performed; it does not claim that a model replaces human review.

