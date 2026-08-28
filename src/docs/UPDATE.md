# Updating Sticky Notes

1. Disable Sticky Notes.
2. Back up the current `custom_apps/stickynotes` directory and Nextcloud database.
3. Replace the app directory with the new release.
4. Restore ownership to `www-data:www-data`.
5. Run `php occ upgrade` if Nextcloud requests it.
6. Enable Sticky Notes and verify the version shown in the app.

For AIO, execute `occ` through the `nextcloud-aio-nextcloud` container as user `www-data`.
