# Codex Project Instructions

This repository is edited from more than one machine. Before changing code, Codex should:

1. Read `docs/codex-handoff.md`.
2. Check `git status --short --branch`.
3. Avoid overwriting uncommitted user changes.
4. Prefer small, focused commits or clearly documented working changes.
5. After any meaningful change, update `docs/codex-handoff.md` with:
   - what changed,
   - what was verified,
   - what still needs attention,
   - any environment assumptions.

Environment files such as `.env` should not be committed. If behavior depends on environment values, document the key names only, not secrets.
