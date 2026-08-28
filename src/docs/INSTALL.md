# Installing Sticky Notes 1.0.0 on Nextcloud AIO

Unpack the release so you have a `stickynotes` directory containing `appinfo/`.

```bash
sudo docker cp stickynotes nextcloud-aio-nextcloud:/var/www/html/custom_apps/
sudo docker exec -u root nextcloud-aio-nextcloud chown -R www-data:www-data /var/www/html/custom_apps/stickynotes
sudo docker exec --user www-data nextcloud-aio-nextcloud php occ app:enable stickynotes
```

Verify with:

```bash
sudo docker exec --user www-data nextcloud-aio-nextcloud php occ app:list | grep -A2 stickynotes
```
