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


## Nextcloud compatibility policy

The project targets the newest Nextcloud major version actively used and tested by the project maintainer. Backward compatibility with older major versions is intentionally not maintained. For version 1.0.0, the supported target is Nextcloud 34. A later major version is declared supported only after the project itself has moved to it and the app has been tested there.

## Distribution layout planned for 1.1.0

The Git repository may keep development material under `src/`, `docs/`, `release/`, and `old/`. Official distribution packages must contain a top-level `stickynotes/` application directory whose contents are the runtime tree expected by Nextcloud, without repository-only directories.

A release build helper is planned for 1.1.0. It should read the version from `src/appinfo/info.xml`, validate required runtime directories, build a clean `stickynotes/` staging tree, and create distribution archives suitable for manual installation and later App Store signing/publishing.

Signing keys and certificates must never be committed to the repository.
