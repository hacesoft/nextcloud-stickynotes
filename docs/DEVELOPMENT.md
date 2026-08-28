# Development and repository structure

[English](DEVELOPMENT.md) | [Česky – rozcestník](README_CZ.md) | [← Documentation](README.md)

Sticky Notes 1.0.0 is a native Nextcloud application.

## Repository layout

```text
nextcloud-stickynotes/
├── src/                  # application runtime source
│   ├── appinfo/
│   ├── css/
│   ├── img/
│   ├── js/
│   ├── l10n/
│   ├── lib/
│   └── templates/
├── docs/                 # project and user documentation
├── release/              # current distribution package
├── old/                  # archived distribution packages
├── install.sh
├── CHANGELOG.md
├── LICENSE
├── README.md
└── README_CZ.md
```

Only the runtime content under `src/` belongs in the installed Nextcloud `custom_apps/stickynotes` directory.

## Main runtime areas

- `appinfo/` – Nextcloud application metadata and routes.
- `css/` – application styles.
- `img/` – application graphics.
- `js/` – main application and Dashboard client code.
- `l10n/` – translations.
- `lib/` – PHP application classes, controllers, database layer, migrations, Dashboard integration, and notifications.
- `templates/` – server-rendered templates.

## Versioning

The public application version is stored in `src/appinfo/info.xml`.

Development-only revision labels should not be shown in the public 1.0.0 release.

## Documentation

Keep Czech and English pages paired. Each documentation page should provide a direct language switch to its counterpart.

The two existing GitHub-hosted screenshots used by the project should retain their current URLs so documentation does not create unnecessary duplicate assets.
