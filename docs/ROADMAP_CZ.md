# Roadmap Sticky Notes 1.1.0

[Česky](ROADMAP_CZ.md) | [English](ROADMAP.md) | [← Dokumentace](README_CZ.md)

Tento dokument zachycuje cíle vývoje **verze 1.1.0** a jejich současný stav implementace. Funkce musí být před finálním vydáním ověřeny v reálném Nextcloudu 34.

## Cíl verze 1.1.0

Verze 1.1.0 bude zaměřena na dokončení mobilního rozhraní, rozšíření notifikací a přípravu projektu pro čistší distribuci a pozdější publikování prostřednictvím oficiálních mechanismů Nextcloudu.

## 1. Kompletní mobilní responzivita

Na mobilním zařízení je ve verzi 1.1.0 použitelný editor vytvoření lístečku, ale u hlavní stránky, přehledů a některých nastavení může být zobrazena jen část rozhraní a stránka nemusí umožnit správný posun.

V 1.1.0 je implementováno a určeno k otestování:

- hlavní stránka s lístečky v jednom sloupci na úzkých displejích,
- žádný hlavní prvek širší než viewport,
- správné vertikální i horizontální chování bez zablokovaného posunu,
- responzivní toolbar, filtry, statistiky a stránkování,
- responzivní nastavení a správa kategorií,
- responzivní dialogy pro sdílení, přiřazení a editaci,
- bezpečné použití `100dvh` a vlastního scrollování pouze tam, kde je to nutné,
- kontrola všech obrazovek na telefonu, tabletu a desktopu.

Funkční mobilní editor z 1.1.0 se při těchto změnách nesmí regresně poškodit.

## 2. O aplikaci / About

Do nastavení bude přidána samostatná informační sekce s minimálně těmito údaji:

- název aplikace Sticky Notes,
- aktuální verze,
- autor: **hacesoft**,
- licence: **AGPL-3.0-or-later**,
- klikací odkaz na projekt a zdrojové kódy:
  `https://github.com/hacesoft/nextcloud-stickynotes`.

## 3. Notifikace: Nextcloud + volitelné ntfy

Interní notifikace Nextcloudu zůstanou podporovaným kanálem. Verze 1.1.0 má přidat volitelný **ntfy** kanál konfigurovaný samostatně každým uživatelem.

Každý uživatel bude moci podle návrhu nastavit:

- zda chce interní Nextcloud notifikace,
- zda chce ntfy,
- URL ntfy serveru,
- vlastní topic,
- volitelný autentizační token,
- které události chce přijímat přes jednotlivé kanály,
- vlastní upozornění před termínem.

Implementované typy událostí zahrnují:

- lísteček přiřazen přímo uživateli,
- lísteček přiřazen skupině uživatele,
- lísteček sdílen s uživatelem nebo skupinou,
- úkol zadaný uživatelem byl dokončen,
- dokončený úkol byl znovu otevřen,
- blížící se termín,
- dosažení nebo překročení termínu.

U skupinových úkolů má být respektována individuální preference každého člena skupiny.

Nastavení ntfy bude obsahovat tlačítko pro odeslání testovací notifikace. Do externí notifikace se nemá posílat celý soukromý obsah lístečku; vhodnější je stručný název, typ události, termín a odkaz zpět do Nextcloudu.

Technický návrh je popsán v [Notifikace](NOTIFICATIONS_CZ.md).

## 4. Kompatibilita Nextcloudu

Projekt nebude cíleně udržovat zpětnou kompatibilitu s předchozími hlavními verzemi Nextcloudu.

Pro Sticky Notes 1.1.0 a začátek vývoje 1.1.0 je cílovou a testovanou verzí **Nextcloud 34**. Podpora Nextcloud 35 bude deklarována teprve poté, co projekt přejde na Nextcloud 35 a aplikace na něm bude prakticky otestována. V takovém okamžiku nebude starší hlavní verze nadále aktivně testována.

## 5. Repozitář a distribuce

Vývojový Git repozitář může zůstat přehledně rozdělen:

```text
nextcloud-stickynotes/
├── src/                  # runtime zdroj aplikace
├── docs/                 # dokumentace
├── release/              # aktuální distribuční balíčky
├── old/                  # archiv starších balíčků
├── build-release.sh      # součást 1.1.0
├── install.sh
├── CHANGELOG.md
├── LICENSE
├── README.md
└── README_CZ.md
```

Distribuční balíček pro Nextcloud ale musí obsahovat čistou aplikaci s kořenovým adresářem `stickynotes/`, například:

```text
stickynotes/
├── appinfo/
├── css/
├── img/
├── js/
├── l10n/
├── lib/
└── templates/
```

`docs/`, `release/`, `old/`, instalační pomocné soubory a Git metadata nejsou součástí runtime balíčku.

## 6. Release build

Verze 1.1.0 obsahuje skript `build-release.sh`, který:

1. načíst verzi z `src/appinfo/info.xml`,
2. ověřit povinné runtime adresáře,
3. sestavit čistý adresář `stickynotes/`,
4. vytvořit distribuční archiv,
5. zkontrolovat, že archiv nemá navíc vývojové soubory,
6. připravit projekt na pozdější podpis a publikování v Nextcloud App Store.

Privátní podpisový klíč nikdy nesmí být uložen v Git repozitáři.

## Stav

Tato roadmapa je návrh pro následující vývojový cyklus. Funkce budou z dokumentu přesouvány do běžné uživatelské dokumentace až po jejich implementaci a otestování.
