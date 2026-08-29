# Changelog

## 1.1.1

- Renamed JavaScript and CSS assets to version 1.1.1 so browsers and Nextcloud cannot reuse cached 1.1.0 files.
- Includes the full emoji picker in the note editor.
- Includes per-note deep links and the explicit **Open note** notification action.
- Keeps ntfy event-only privacy behavior.

## 1.1.0

### Fixed
- Desktop Nextcloud notifications now include an explicit **Open note** action and per-note deep links.
- Direct self-assignment notifications are no longer suppressed.
- Group assignment notifications include the owner when the owner is a member of the assigned group.

### Added
- Per-user notification preferences.
- Optional ntfy notification channel with per-user server, topic and access token.
- Test ntfy notification button.
- Notification event selection for direct assignments, group assignments, shares, task completion/reopening and due-time reminders.
- Due-time reminder background job (24 hours, 1 hour and due-now thresholds).
- About section with version, author, project URL and license.
- Categorized local Unicode emoji picker with recent emoji support and cursor-position insertion.
- App Store-oriented repository and release layout with `src/`, `docs/`, `release/` and `old/`.
- `build-release.sh` for generating a clean Nextcloud app package.

### Changed
- Target compatibility is Nextcloud 34 only. Backward compatibility with older major releases is not maintained.
- Main page, settings, category editor, sharing dialog and other application views have expanded mobile responsive rules.
- Notification routing is based on the recipient's own preferences rather than only the note owner's assignment setting.

### Notes
- Due reminders depend on Nextcloud background jobs/cron running regularly.
- ntfy remains optional; normal operation requires no external service.
- ntfy payloads intentionally contain only the title and event type; note bodies and actor identities are not sent.
