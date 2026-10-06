[🇨🇿 **Česky**](DEVELOPMENT_CZ.md) | [🇬🇧 English](../en/DEVELOPMENT.md)

# Vývoj a balíčky

`src/` obsahuje nasazovanou aplikaci; instalátor již nepřevádí původní záznamy, `scripts/remove-own-job.php` uklízí vlastní úlohu při odinstalaci. `scripts/nas-migration-check.sh` je volitelná kontrola databáze pouze pro čtení. Příkaz `sh build-release.sh` sestaví čistý runtime ZIP do `release/`; NAS instalace používá celý zdrojový balíček. Vývojové testy, staré binární archivy `old/` a gitové konfigurační soubory nejsou součástí balíčku. Ověření PHP, databáze a cronu se provádí na NASu.
