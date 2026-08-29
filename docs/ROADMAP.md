# Sticky Notes 1.1.0 roadmap

[English](ROADMAP.md) | [Česky](ROADMAP_CZ.md) | [← Documentation](README.md)

This document records the goals for **version 1.1.0** and their current implementation status. The features still require practical validation on Nextcloud 34 before the final release.

## Goal of 1.1.0

Version 1.1.0 will focus on complete mobile usability, expanded notifications, and a cleaner repository/distribution workflow suitable for later official Nextcloud publishing.

## 1. Complete mobile responsiveness

In 1.1.0 the new-note editor is usable on mobile, but the main page, overview areas, and some settings can be clipped or unable to scroll correctly.

Version 1.1.0 implements a one-column note board on narrow screens, viewport-safe widths, responsive toolbars/filters/statistics/pagination, mobile-safe settings and category management, responsive sharing/assignment/edit dialogs, and verification on phone, tablet, and desktop. The already working mobile editor must not regress.

## 2. About section

Settings will gain an **About** section showing at least the app name, current version, author **hacesoft**, AGPL-3.0-or-later license, and a clickable project URL:

`https://github.com/hacesoft/nextcloud-stickynotes`

## 3. Notifications: Nextcloud + optional ntfy

Built-in Nextcloud notifications will remain available. Version 1.1.0 adds an optional per-user **ntfy** delivery channel.

Each user should be able to configure Nextcloud notifications, ntfy enablement, ntfy server URL, personal topic, optional authentication token, event preferences per channel, and deadline reminders.

Implemented events include direct assignment, group assignment, sharing, completion of a task created by the user, reopening, approaching deadline, and deadline reached/overdue. Group notifications should respect each member's own preferences. A test-notification button is included.

External notifications should avoid sending the full private note body. Prefer a short title/event/deadline plus a link back into Nextcloud.

See [Notifications](NOTIFICATIONS.md).

## 4. Nextcloud compatibility policy

The project intentionally targets the newest Nextcloud major version actively used and tested by the project maintainer instead of maintaining backward compatibility across older majors.

Sticky Notes 1.1.0 and the initial 1.1.0 development target **Nextcloud 34**. Nextcloud 35 support will be declared only after the project itself moves to Nextcloud 35 and the app is tested there. The older major will then no longer be actively tested.

## 5. Repository and distribution

The development repository may keep `src/`, `docs/`, `release/`, and `old/`. `build-release.sh` creates a clean Nextcloud package whose top-level directory is `stickynotes/` and contains only runtime application content.

Repository-only content, documentation, archived releases, helper files, and Git metadata do not belong in the installed application package.

## 6. Release build

The release helper should read the version from `src/appinfo/info.xml`, validate required runtime directories, build a clean `stickynotes/` staging tree, create distribution archives, and prepare the project for later signing and App Store publishing.

Private signing keys must never be committed to Git.

## Status

This document remains as the design record for the 1.1.0 development cycle. The implementation must still be tested before the final release is tagged.
