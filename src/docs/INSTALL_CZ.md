# Instalace Sticky Notes 1.0.0 do Nextcloud AIO

## 1. Rozbalení balíčku

Rozbalte `stickynotes-1.0.0.zip`. Výsledkem musí být složka `stickynotes` s podsložkou `appinfo`.

## 2. Zkopírování do AIO kontejneru

Na Synology se přihlaste přes SSH, přejděte do adresáře, kde je rozbalená složka `stickynotes`, a spusťte:

```bash
sudo docker cp stickynotes nextcloud-aio-nextcloud:/var/www/html/custom_apps/
```

Nastavte správného vlastníka:

```bash
sudo docker exec -u root nextcloud-aio-nextcloud chown -R www-data:www-data /var/www/html/custom_apps/stickynotes
```

## 3. Zapnutí aplikace

```bash
sudo docker exec --user www-data nextcloud-aio-nextcloud php occ app:enable stickynotes
```

Ověření:

```bash
sudo docker exec --user www-data nextcloud-aio-nextcloud php occ app:list | grep -A2 stickynotes
```

## 4. Použití

Po obnovení stránky se v horní navigaci Nextcloudu objeví **Sticky Notes / Samolepicí lístečky**. Na Dashboardu lze přidat widget Sticky Notes.

## Odinstalace

Nejdříve aplikaci zakažte:

```bash
sudo docker exec --user www-data nextcloud-aio-nextcloud php occ app:disable stickynotes
```

Pokud chcete smazat i soubory aplikace:

```bash
sudo docker exec -u root nextcloud-aio-nextcloud rm -rf /var/www/html/custom_apps/stickynotes
```

Poznámka: zakázání nebo odstranění souborů samo o sobě nemaže databázové tabulky aplikace.
