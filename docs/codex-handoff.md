# Codex Handoff

Use this file to keep Codex context synchronized between machines. Commit and push updates to this file when switching between home and office.

## Current Status

- Branch: `main`
- Last checked: 2026-06-03
- Git status at setup: clean
- Purpose: keep work context, decisions, verification steps, and known environment differences visible across machines.

## Working Notes

- No application code was changed when this handoff file was created.
- Conversation history is not synchronized by git automatically; this file is the shared project memory.
- If something behaves differently between machines, compare `.env`, PHP/Composer/Node/npm versions, database state, cache, `vendor/`, and `node_modules/`.

## Latest Changes

- 2026-06-03: Added project handoff workflow via `AGENTS.md` and this file.

## Verification

- Ran `git status --short --branch`; repository was clean on `main`.

## Open Items

- Add project-specific setup commands once they are confirmed.
- Add any known fragile areas of the application before the next cross-machine handoff.

## Machine Handoff Checklist

Before stopping work:

1. Run `git status --short --branch`.
2. Note any unfinished work in this file.
3. Run relevant tests or manual checks and write the result here.
4. Commit and push code plus this handoff file.

After starting work on another machine:

1. Pull latest changes.
2. Read this file before asking Codex to modify code.
3. Confirm environment-sensitive settings if behavior differs.
