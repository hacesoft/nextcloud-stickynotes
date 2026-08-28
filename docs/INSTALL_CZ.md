# Instalace

[Česky](INSTALL_CZ.md) | [English](INSTALL.md) | [← Dokumentace](README_CZ.md)

## Požadavky

Sticky Notes 1.0.0 podporuje **Nextcloud 34 a 35**. Starší verze nejsou podporovány.

## Struktura repozitáře

Zdrojový runtime aplikace je uložen v adresáři `src/`. Instalační skript `install.sh` je v kořeni projektu.

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

## Instalace pomocí install.sh

Instalátor je určen pro Docker instalaci Nextcloudu odpovídající podporovanému nasazení projektu. Z kořenového adresáře repozitáře spusťte:

```sh
sudo sh install.sh
```

Skript pracuje se zdrojovým stromem `src/`, nasadí aplikaci do `custom_apps/stickynotes`, nastaví vlastnictví souborů, aplikaci aktivuje a provede kontrolu nasazené verze.

Před použitím na jiném typu instalace Nextcloudu zkontrolujte instalační skript a přizpůsobte jej danému prostředí.

## Ověření

Po úspěšné instalaci musí být aplikace Sticky Notes aktivní v Nextcloudu a dostupná mezi aplikacemi.

Verzi aplikace určuje `src/appinfo/info.xml`.
