# Notifikace

[Česky](NOTIFICATIONS_CZ.md) | [English](NOTIFICATIONS.md) | [← Dokumentace](README_CZ.md)

## Verze 1.1.0

Sticky Notes 1.1.0 používá dva nezávislé notifikační kanály: interní notifikace Nextcloudu a volitelně ntfy. Nastavení je **pro každého uživatele samostatné**.

Každý uživatel může zapnout nebo vypnout Nextcloud notifikace, zapnout ntfy a zadat vlastní URL serveru, topic a volitelný access token. Token je uložen zašifrovaně pomocí kryptografické služby Nextcloudu a není vracen zpět do webového rozhraní.

## Události

Uživatel může samostatně povolit nebo zakázat upozornění pro:

- přímé přiřazení lístečku nebo úkolu,
- přiřazení jeho skupině,
- sdílení lístečku,
- dokončení úkolu, který vytvořil,
- opětovné otevření takového úkolu,
- termín do 24 hodin,
- termín do 1 hodiny,
- dosažení termínu.

Při přiřazení skupině se vyhodnocuje nastavení každého člena skupiny samostatně.

## ntfy

ntfy je volitelné. Uživatel může použít veřejnou službu nebo vlastní ntfy server. V nastavení je tlačítko **Odeslat testovací notifikaci**, kterým lze konfiguraci okamžitě ověřit.

Externí zpráva obsahuje pouze stručný typ události, název lístečku nebo úkolu a odkaz zpět do autentizované aplikace. **Obsah lístečku ani identita uživatele, který akci provedl, se do ntfy neposílají.** Díky tomu může ntfy sloužit jako jednoduchý společný signalizační kanál pro upozornění z různých aplikací a systémů, aniž by do něj Sticky Notes posílaly detailní obsah.

Interní Nextcloud notifikace mohou obsahovat podrobnější kontext. Notifikace má odkaz na konkrétní lísteček a také akci **Otevřít lísteček**, aby šel cílový lísteček otevřít přímo i z desktopového panelu oznámení.

## Upozornění na termín

Kontrolu termínů provádí background job Sticky Notes každých přibližně 5 minut. Skutečný okamžik doručení proto závisí na tom, jak často Nextcloud spouští své úlohy na pozadí. Pro spolehlivé připomínky je doporučen systémový cron Nextcloudu.

Stejná připomínka se pro stejný termín neposílá opakovaně. Pokud se termín úkolu změní, nový termín může vytvořit novou sadu upozornění.
