# Installation

[English](INSTALL.md) | [Česky](INSTALL_CZ.md) | [← Documentation](README.md)

## Requirements

Sticky Notes 1.0.0 supports **Nextcloud 34 and 35**. Older releases are not supported.

## Repository layout

The application runtime source is stored under `src/`. The `install.sh` deployment script is located in the project root.

```text
nextcloud-stickynotes/
├── src/
│   ├── appinfo/
│   ├── css/
│   ├── img/
│   ├── js/
│   ├── l10n/
│   ├── lib/
│   └── templates/
├── docs/
├── release/
├── old/
└── install.sh
```

## Installation with install.sh

The installer targets the Docker-based Nextcloud deployment supported by this project. From the repository root run:

```sh
sudo sh install.sh
```

The script deploys the runtime tree from `src/` to `custom_apps/stickynotes`, fixes ownership, enables the application, and verifies the deployed version.

For other Nextcloud deployment types, review and adapt the installation script to the target environment before use.

## Verification

After a successful installation, Sticky Notes should be enabled and available in Nextcloud.

The application version is defined in `src/appinfo/info.xml`.
