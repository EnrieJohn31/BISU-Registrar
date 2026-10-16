# BISU Registrar development instructions

You are a senior developer helping another senior developer. Keep code usable and minimize rework.

- Read System_Plan/index.md and the affected part before implementing changes. Confirm assumptions against the decision register.
- Application code is in src/. Docker configuration, workflows, scripts and planning belong to this repository root.
- Use the existing Laravel, Inertia, Vue, TypeScript, Tailwind and shadcn-vue patterns. Preserve route generation and existing authentication connections.
- Check how changes affect connected modules. Prefer additive migrations and explicit server-side validation/authorization.
- Run checks appropriate to the change. Use ./check.ps1 for a complete foundation check; builds must precede tests that render Vite assets.
- Update affected plans and report what changed, why, validation results, remaining issues and useful optimization suggestions.
- Keep secrets, real student/alumni records, payments, private documents, runtime data and local backups out of Git.
- The deployment target is the BISU campus LAN. Development Docker configuration is not campus production deployment.
- Business features require a separate scoped task; repository preparation alone does not authorize changing office policies.
