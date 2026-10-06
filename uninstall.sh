#!/bin/sh
set -eu

PROJECT_DIR="$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)"
APP_DIR="$PROJECT_DIR/src"
INFO="$APP_DIR/appinfo/info.xml"

[ -f "$INFO" ] || { echo "ERROR: $INFO not found" >&2; exit 1; }
APP_ID="$(sed -n 's:.*<id>\([^<]*\)</id>.*:\1:p' "$INFO" | head -n 1)"
APP_NAME="$(sed -n 's:.*<name>\([^<]*\)</name>.*:\1:p' "$INFO" | head -n 1)"
[ -n "$APP_ID" ] || { echo "ERROR: app id not found" >&2; exit 1; }
case "$APP_ID" in
    ""|*[!A-Za-z0-9_-]*) echo "ERROR: Invalid application id: $APP_ID" >&2; exit 1 ;;
esac
[ -n "$APP_NAME" ] || APP_NAME="$APP_ID"

say(){ printf '%s\n' "$*"; }
die(){ printf 'ERROR: %s\n' "$*" >&2; exit 1; }

[ "$(id -u)" -eq 0 ] || die "Run this uninstaller with: sudo sh uninstall.sh"
chmod 755 "$0" 2>/dev/null || true
command -v docker >/dev/null 2>&1 || die "Docker was not found."

WEB_CONTAINER=""
CRON_CONTAINER=""
container_has_occ(){ docker exec "$1" sh -c 'test -f /var/www/html/occ' >/dev/null 2>&1; }

if docker ps --format '{{.Names}}' | grep -qx 'nextcloud-app' && container_has_occ nextcloud-app; then
    WEB_CONTAINER=nextcloud-app
fi
if docker ps --format '{{.Names}}' | grep -qx 'nextcloud-cron' && container_has_occ nextcloud-cron; then
    CRON_CONTAINER=nextcloud-cron
fi
if [ -z "$WEB_CONTAINER" ]; then
    for c in $(docker ps --format '{{.Names}}' | grep -Ei 'nextcloud' || true); do
        case "$c" in *cron*) continue;; esac
        if container_has_occ "$c"; then WEB_CONTAINER="$c"; break; fi
    done
fi
if [ -z "$CRON_CONTAINER" ]; then
    for c in $(docker ps --format '{{.Names}}' | grep -Ei 'nextcloud.*cron|cron.*nextcloud' || true); do
        if container_has_occ "$c"; then CRON_CONTAINER="$c"; break; fi
    done
fi

[ -n "$WEB_CONTAINER" ] || die "Could not find the Nextcloud web container."
TARGET_DIR="/var/www/html/custom_apps/$APP_ID"

say "Nextcloud web container: $WEB_CONTAINER"
[ -n "$CRON_CONTAINER" ] && say "Nextcloud cron container: $CRON_CONTAINER"
say "Removing $APP_NAME ($APP_ID) application code..."

# Disable through Nextcloud first. If the app is already disabled/not registered,
# continue with removal of the custom_apps directory.
if docker exec "$WEB_CONTAINER" sh -c "test -d '$TARGET_DIR'" >/dev/null 2>&1; then
    docker exec -u www-data "$WEB_CONTAINER" php /var/www/html/occ app:disable "$APP_ID" >/dev/null 2>&1 || true
fi

# Remove only this app's registered class while its source is still present.
docker exec -i -u www-data "$WEB_CONTAINER" php < "$PROJECT_DIR/scripts/remove-own-job.php" || die "Could not remove the registered reminder job; application code was retained."

docker exec "$WEB_CONTAINER" rm -rf "$TARGET_DIR" "${TARGET_DIR}.new" >/dev/null 2>&1 || true

# custom_apps is normally shared between web and cron. If it is not shared,
# clean the cron container too.
if [ -n "$CRON_CONTAINER" ] && [ "$CRON_CONTAINER" != "$WEB_CONTAINER" ]; then
    docker exec "$CRON_CONTAINER" rm -rf "$TARGET_DIR" "${TARGET_DIR}.new" >/dev/null 2>&1 || true
fi

docker exec "$WEB_CONTAINER" php -r 'if (function_exists("opcache_reset")) { opcache_reset(); }' >/dev/null 2>&1 || true

if docker exec "$WEB_CONTAINER" sh -c "test -e '$TARGET_DIR'" >/dev/null 2>&1; then
    die "Application directory still exists: $TARGET_DIR"
fi

say "$APP_NAME was disabled and removed from custom_apps."
say "Application data stored in the Nextcloud database/user configuration was intentionally preserved."
say "This makes a later reinstall/update safe and avoids destructive data loss."

say "Shared App Core was intentionally left installed because other applications may depend on it."
