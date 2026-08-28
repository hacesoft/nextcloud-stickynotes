# Updating

[English](UPDATE.md) | [Česky](UPDATE_CZ.md) | [← Documentation](README.md)

## Before updating

Keep a current backup of Nextcloud and its database before deploying a new application version.

## Updating from the source tree

When the new application version is prepared under `src/`, run from the repository root:

```sh
sudo sh install.sh
```

The deployment script installs a complete new runtime tree instead of manually replacing individual files.

## Version

The application version is defined in:

```text
src/appinfo/info.xml
```

For future releases, update `CHANGELOG.md` and create the corresponding release package.

## release and old

Recommended repository organization:

- `release/` – current distribution package,
- `old/` – archive of older distribution packages,
- `src/` – current application source tree.
