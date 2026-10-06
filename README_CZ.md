[🇨🇿 **Česky**](README_CZ.md) | [🇬🇧 English](README.md)

# Žluté lístečky 2.0.11

Aplikace pro Nextcloud z dílny Hacesoft pro osobní a sdílené poznámky, úkoly s termíny, kategorie, widget Dashboardu a volitelná oznámení. Poznámky lze sdílet s uživateli nebo skupinami s právem prohlížení či úprav.

<img width="1134" height="617" alt="image" src="https://github.com/user-attachments/assets/c9e34307-17b8-4a27-97c3-cbb349a57cfe" />

## Požadavky

- Nextcloud 35 a PHP 8.3 nebo novější.
- **Nainstalované a zapnuté [Hacesoft Core](https://github.com/hacesoft/core), verze 0.18.0-dev.2 nebo novější.** Bez Core aplikace nefunguje.

Core je samostatná aplikace a není přibalené k Lístečkům. Nejprve jej nainstalujte podle návodu v [repozitáři Core](https://github.com/hacesoft/core).

## Instalace a aktualizace

Stáhněte a rozbalte kompletní zdrojový balíček. V kořeni rozbaleného projektu spusťte:

```sh
sudo sh install.sh
```

Instalační skript podporuje zdokumentované nasazení Synology/Docker. Požadavky a postup aktualizace najdete v [instalačním návodu](docs/cz/INSTALL_CZ.md). Existující poznámky se při aktualizaci zachovávají v databázi. Před aktualizací zálohujte databázi a soubory aplikace.

Před odinstalací si přečtěte část o odstranění v instalačním návodu. Z kořene projektu použijte `sudo sh uninstall.sh`; před odstraněním uložených dat zkontrolujte volby skriptu.

## Dokumentace

- [Přehled dokumentace](docs/cz/README_CZ.md)
- [Uživatelská příručka](docs/cz/USER_GUIDE_CZ.md)
- [Sdílení a oprávnění](docs/cz/SHARING_CZ.md)
- [Editor](docs/cz/EDITOR_CZ.md)
- [Widget Dashboardu](docs/cz/DASHBOARD_CZ.md)
- [Nastavení](docs/cz/SETTINGS_CZ.md)
- [Oznámení](docs/cz/NOTIFICATIONS_CZ.md)
- [Mobilní rozložení](docs/cz/MOBILNI_LAYOUT.md)

## Struktura zdrojů

`src/` obsahuje aplikaci, `scripts/` instalační pomocníky a `docs/cz/` a `docs/en/` českou a anglickou dokumentaci. Kompletní zdrojový balíček obsahuje `install.sh` i `uninstall.sh`. `build-release.sh` sestavuje runtime ZIP/tarball z `src/`; generované výstupy patří do `release/`.

Podklady pro vývojáře: [vývoj](docs/cz/DEVELOPMENT_CZ.md), [kontrola Nextcloud 35](docs/cz/NC35_REVIEW_CZ.md) a [přehled změn](docs/cz/CHANGELOG_CZ.md).

Licence: [AGPL-3.0-or-later](LICENSE).
