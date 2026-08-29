# Notifikace

[Česky](NOTIFICATIONS_CZ.md) | [English](NOTIFICATIONS.md) | [← Dokumentace](README_CZ.md)

## Verze 1.0.0

Sticky Notes 1.0.0 používá interní notifikační mechanismus Nextcloudu pro podporované události, například při přiřazení úkolu. Externí ntfy integrace není součástí 1.0.0.

## Návrh pro 1.1.0

Notifikační systém bude navržen jako vícekanálový. Událost vznikne jednou a samostatná notifikační vrstva rozhodne, který uživatel ji má dostat a přes který povolený kanál.

```text
Událost Sticky Notes
        │
        ├── NextcloudNotificationProvider
        │
        └── NtfyNotificationProvider
```

Tím nebude aplikační logika přímo závislá na ntfy a v budoucnu lze přidat další kanál bez přepisování samotných událostí.

## Uživatelské nastavení

Konfigurace ntfy bude **per-user**. Každý uživatel si nastaví vlastní server, topic, případný token a jednotlivé preference. Topic ani autentizační údaje se nesmí zobrazovat ostatním uživatelům.

Předpokládaná nastavení:

- interní Nextcloud notifikace zapnuto/vypnuto,
- ntfy zapnuto/vypnuto,
- URL ntfy serveru,
- topic,
- volitelný access token,
- tlačítko **Odeslat testovací oznámení**.

## Události a kanály

Uživatel má mít možnost samostatně zvolit kanál pro jednotlivé události, například:

| Událost | Nextcloud | ntfy |
| --- | :---: | :---: |
| Lísteček přiřazen přímo mně | volitelné | volitelné |
| Lísteček přiřazen mé skupině | volitelné | volitelné |
| Lísteček se mnou sdílen | volitelné | volitelné |
| Můj zadaný úkol byl dokončen | volitelné | volitelné |
| Dokončený úkol byl znovu otevřen | volitelné | volitelné |
| Blíží se termín | volitelné | volitelné |
| Termín dosažen / překročen | volitelné | volitelné |

## Připomínky termínu

Pro úkoly s termínem bude vhodné nabídnout více okamžiků upozornění, například jeden den předem, jednu hodinu předem, v okamžiku termínu nebo jinou uživatelem zvolenou hodnotu. Přesný model bude uzavřen při implementaci 1.1.0.

## Příklad toku události

Uživatel A vytvoří úkol a přiřadí jej uživateli B. Uživatel B dostane oznámení podle vlastních preferencí. Když B úkol dokončí, autor/zadavatel A může dostat oznámení o dokončení, pokud má tuto událost povolenou.

U přiřazení skupině se vyhodnocují preference každého příjemce samostatně.

## Soukromí

Do externího ntfy kanálu se nemá automaticky posílat celý obsah soukromého lístečku. Oznámení by mělo obsahovat jen nezbytné informace, například typ události, stručný název, termín a odkaz zpět do autentizované aplikace Sticky Notes.

Přihlašovací token a další citlivá nastavení se nesmí logovat ani zpřístupnit jiným uživatelům.
