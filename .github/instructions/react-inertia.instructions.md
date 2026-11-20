---
applyTo: "resources/js/**/*.{ts,tsx}"
---
# React, TypeScript & Inertia Conventions
- Use functional components with strict TypeScript interfaces (`resources/js/types/`). Never use `any`.
- Use Inertia's `<Link>`, `router.get()`, and `useForm()` for navigation and form submissions.
- Keep UI styling clean, semantic, and modular with Tailwind CSS so the upcoming creative redesign can easily reskin components.
- Never fetch data via `fetch()` or `axios` to external domains.