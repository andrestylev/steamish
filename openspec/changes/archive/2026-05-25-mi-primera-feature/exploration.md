## Exploration: mi-primera-feature

### Current State

The project is **empty** — no code, no commits, no dependencies declared. This is a greenfield project. The SDD infrastructure is initialized (`openspec/` directory, `openspec/changes/mi-primera-feature/specs/` exists), but no artifacts exist yet.

Key findings from project root:

| Artifact | Signal |
|----------|--------|
| `.AGENTS.md` | "Use clear variable names. Add comments in Spanish." — Spanish comments convention. |
| `.gga` (Gentle Guardian Angel) | `FILE_PATTERNS="*.ts,*.tsx,*.js,*.jsx"` — **strongly suggests TypeScript/JavaScript stack**. Provider: `gemini`, model: `gemini-2.0-flash`. |
| `.atl/skill-registry.md` | SDD skills indexed. No project-specific skills detected. |
| `openspec/` | SDD config directory exists. No `config.yaml` yet (not created by init — the context block would need filling). |
| Git | User: `andres` (`asqg14@gmail.com`). Branch: `master`. No commits, no remotes. |

### Affected Areas

- **Whole project** — no source exists yet; this is the first feature, so everything is affected.
- `openspec/config.yaml` — missing; should be created at init-time to define stack context and rules.
- `openspec/changes/mi-primera-feature/` — active change folder; will receive specs, design, and tasks.

### Approaches

Given an empty project, the question isn't *which approach* but **what is the project's purpose?** The exploration reveals:

1. **TypeScript/JavaScript expected** — based on `.gga` file patterns (`*.ts, *.tsx, *.js, *.jsx`)
2. **Spanish comments** — per `.AGENTS.md`
3. **No framework hints** — no `package.json`, no framework config, not even an `index.ts` or `src/` directory

This means "mi-primera-feature" needs to decide:
- **What does the project do?** (CLI tool? Web app? Library? API?)
- **Which runtime?** (Node.js? Bun? Deno? Browser?)
- **Which framework/library?** (React? Vue? Express? Hono? Vanilla?)
- **Which bundler/toolchain?** (Vite? tsc? esbuild? Webpack?)

### Recommendation

**Elevate to the user for project clarification before proceeding.** The exploration surface is too thin to recommend a technical approach — the project has zero source code and no declared purpose. The orchestrator should ask the user:

1. What kind of project is this (CLI, web app, library, API, …)?
2. Target runtime/environment?
3. Preferred framework (if any)?
4. Testing framework preference?

Once clarified, this exploration can be updated with concrete approaches.

### Risks

- **Unknown domain** — without knowing the project's purpose, any technical recommendation is guesswork.
- **Missing `openspec/config.yaml`** — the SDD pipeline needs a config to enforce rules per phase (e.g., test commands, conventions). Config should be created early.
- **Stack assumption risk** — the `.gga` file patterns suggest TS/JS, but this is a dev-tool config, not a project manifest. The user may have a different stack in mind.

### Ready for Proposal

**No** — insufficient context. The orchestrator should ask the user clarifying questions about project purpose and tech stack before proceeding to `sdd-propose`. Alternatively, if the user confirms TypeScript as the stack and describes the feature intent, this exploration can be treated as done and the pipeline can move forward.
