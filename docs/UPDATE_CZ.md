# Aktualizace

[Česky](UPDATE_CZ.md) | [English](UPDATE.md) | [← Dokumentace](README_CZ.md)

## Před aktualizací

Před nasazením nové verze je vhodné mít aktuální zálohu Nextcloudu a databáze.

## Aktualizace ze zdrojového stromu

Pokud je nová verze projektu připravena v adresáři `src/`, spusťte z kořene repozitáře:

```sh
sudo sh install.sh
```

Instalační skript nasazuje kompletní nový runtime strom aplikace namísto ručního přepisování jednotlivých souborů.

## Verze

Verze aplikace je definována v:

```text
src/appinfo/info.xml
```

Při budoucích vydáních je vhodné současně aktualizovat `CHANGELOG.md` a vytvořit odpovídající release balíček.

## release a old

Doporučená organizace repozitáře:

- `release/` – aktuální distribuční balíček,
- `old/` – archiv starších distribučních balíčků,
- `src/` – aktuální zdrojový strom aplikace.
