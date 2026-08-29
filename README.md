# Sticky Notes for Nextcloud

[English](README.md) | [Česky](README_CZ.md)

<img width="1181" height="579" alt="Sticky Notes – main application view" src="https://github.com/user-attachments/assets/71fd81a9-5828-4c0e-9160-fc8b3ec76c65" />

**Version 1.0.0**

Sticky Notes is a native Nextcloud application for quick notes, personal sticky notes, and lightweight family or team tasks. It runs directly inside Nextcloud and does not require a separate application container.

## Main features

- colored sticky notes,
- notes and tasks with priority and due dates,
- assignment to individual Nextcloud users or groups,
- user and group sharing,
- completed task state,
- pinning and manual note ordering,
- search, filters, sorting, statistics, and pagination,
- rich-text editor with headings, formatting, lists, links, and tables,
- automatic text contrast based on the note background,
- system and personal categories with icons and custom styles,
- optional natural note tilt and shadows,
- Dashboard widget,
- assignment notifications,
- multiple UI languages.

## Dashboard widget

The widget displays Sticky Notes directly on the Nextcloud Dashboard in a compact layout.

<img width="351" height="569" alt="Sticky Notes – Dashboard widget" src="https://github.com/user-attachments/assets/e11025bc-9568-43ec-abec-431529528306" />

## Documentation

Complete English documentation: **[docs/README.md](docs/README.md)**

It contains the user guide, editor, categories, sharing, Dashboard widget, settings, installation, and update instructions.

## Compatibility

Sticky Notes 1.0.0 is developed and tested for **Nextcloud 34**. The project intentionally does not maintain backward compatibility with earlier Nextcloud major releases. Support for a later major release will be added only after the project moves to that release and the app is tested there.

## Quick links

- [Installation](docs/INSTALL.md)
- [Updating](docs/UPDATE.md)
- [User guide](docs/USER_GUIDE.md)
- [Settings](docs/SETTINGS.md)
- [Dashboard widget](docs/DASHBOARD.md)

## Data and privacy

The application uses Nextcloud's local users, groups, database, and notification system. Normal operation does not require an external cloud service.

## Privacy and external services

Version 1.0.0 uses Nextcloud users, groups, database storage, and the built-in Nextcloud notification system. It does not require an external cloud service for normal operation. Optional ntfy integration is planned for version 1.1.0.

## License

AGPL-3.0-or-later
