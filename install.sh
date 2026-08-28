#!/bin/sh
set -eu

APP_ID="stickynotes"
APP_NAME="Sticky Notes"
MIN_NC_MAJOR=34

SCRIPT_DIR="$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)"
APP_DIR="$SCRIPT_DIR"

WEB_CONTAINER=""
CRON_CONTAINER=""

say() { printf '%s\n' "$*"; }
die() { printf 'ERROR: %s\n' "$*" >&2; exit 1; }

if [ "$(id -u)" -ne 0 ]; then
    die "Run this installer with: sudo sh install.sh"
fi

# Once the script is running through "sh", make it executable for convenience.
chmod 755 "$0" 2>/dev/null || true

command -v docker >/dev/null 2>&1 || die "Docker was not found."

RUNTIME_ITEMS="
appinfo
css
img
js
l10n
lib
templates
"

container_has_occ() {
    docker exec "$1" sh -c 'test -f /var/www/html/occ' >/dev/null 2>&1
}

find_containers() {
    if docker ps --format '{{.Names}}' | grep -qx 'nextcloud-app' && container_has_occ 'nextcloud-app'; then
        WEB_CONTAINER='nextcloud-app'
    fi

    if docker ps --format '{{.Names}}' | grep -qx 'nextcloud-cron' && container_has_occ 'nextcloud-cron'; then
        CRON_CONTAINER='nextcloud-cron'
    fi

    if [ -z "$WEB_CONTAINER" ]; then
        for c in $(docker ps --format '{{.Names}}' | grep -Ei 'nextcloud' || true); do
            case "$c" in
                *cron*) continue ;;
            esac
            if container_has_occ "$c"; then
                WEB_CONTAINER="$c"
                break
            fi
        done
    fi

    if [ -z "$CRON_CONTAINER" ]; then
        for c in $(docker ps --format '{{.Names}}' | grep -Ei 'nextcloud.*cron|cron.*nextcloud' || true); do
            if container_has_occ "$c"; then
                CRON_CONTAINER="$c"
                break
            fi
        done
    fi
}

find_containers
[ -n "$WEB_CONTAINER" ] || die "Could not find the Nextcloud web container."

say "Nextcloud web container: $WEB_CONTAINER"
[ -n "$CRON_CONTAINER" ] && say "Nextcloud cron container: $CRON_CONTAINER"

NC_STATUS="$(docker exec -u www-data "$WEB_CONTAINER" php /var/www/html/occ status --output=json 2>/dev/null || true)"
NC_VERSION="$(printf '%s' "$NC_STATUS" | sed -n 's/.*"versionstring":"\([^"]*\)".*/\1/p')"

if [ -n "$NC_VERSION" ]; then
    say "Nextcloud version: $NC_VERSION"
    NC_MAJOR="$(printf '%s' "$NC_VERSION" | cut -d. -f1)"
    case "$NC_MAJOR" in
        ''|*[!0-9]*) ;;
        *)
            [ "$NC_MAJOR" -ge "$MIN_NC_MAJOR" ] || die "$APP_NAME requires Nextcloud $MIN_NC_MAJOR or newer."
            ;;
    esac
fi

for item in $RUNTIME_ITEMS; do
    [ -e "$APP_DIR/$item" ] || die "Required application item is missing: $item"
done
[ -f "$APP_DIR/appinfo/info.xml" ] || die "appinfo/info.xml is missing."

TMP_LOCAL="$(mktemp -d)"
trap 'rm -rf "$TMP_LOCAL"' EXIT HUP INT TERM

STAGE="$TMP_LOCAL/$APP_ID"
mkdir -p "$STAGE"

say "Preparing complete new runtime tree..."
for item in $RUNTIME_ITEMS; do
    cp -R "$APP_DIR/$item" "$STAGE/"
done

TARGET_DIR="/var/www/html/custom_apps/$APP_ID"
NEW_DIR="/var/www/html/custom_apps/$APP_ID.new"
REMOTE_TMP="/tmp/${APP_ID}.install.$$"
STAMP="$(date +%Y%m%d-%H%M%S)"

# Prepare the entire new application before touching the current one.
say "Uploading new application tree to temporary location..."
docker exec "$WEB_CONTAINER" sh -c 'mkdir -p /var/www/html/custom_apps'
docker exec "$WEB_CONTAINER" rm -rf "$REMOTE_TMP" "$NEW_DIR" >/dev/null 2>&1 || true
docker exec "$WEB_CONTAINER" mkdir -p "$REMOTE_TMP"
docker cp "$STAGE/." "$WEB_CONTAINER:$REMOTE_TMP/"
docker exec "$WEB_CONTAINER" sh -c "
    mv '$REMOTE_TMP' '$NEW_DIR' &&
    chown -R www-data:www-data '$NEW_DIR'
"

# Check if an old version is present.
OLD_PRESENT=0
if docker exec "$WEB_CONTAINER" sh -c "test -d '$TARGET_DIR'"; then
    OLD_PRESENT=1
fi

if [ "$OLD_PRESENT" -eq 1 ]; then
    say "Existing Sticky Notes installation detected."

    if docker exec -u www-data "$WEB_CONTAINER" php /var/www/html/occ app:list --enabled 2>/dev/null | grep -q -- "- $APP_ID:"; then
        say "Temporarily disabling $APP_NAME..."
        docker exec -u www-data "$WEB_CONTAINER" php /var/www/html/occ app:disable "$APP_ID"
    fi

    say "Removing old runtime tree..."
    docker exec "$WEB_CONTAINER" rm -rf "$TARGET_DIR"
fi

say "Activating new runtime tree..."
docker exec "$WEB_CONTAINER" mv "$NEW_DIR" "$TARGET_DIR"
docker exec "$WEB_CONTAINER" chown -R www-data:www-data "$TARGET_DIR"

# If cron has a separate custom_apps volume, deploy there as well.
if [ -n "$CRON_CONTAINER" ] && [ "$CRON_CONTAINER" != "$WEB_CONTAINER" ]; then
    if docker exec "$CRON_CONTAINER" sh -c "test -f '$TARGET_DIR/appinfo/info.xml'" >/dev/null 2>&1; then
        say "Shared custom_apps detected: cron container already sees the new application."
    else
        say "Cron container uses a separate custom_apps volume; deploying runtime tree there."
        CRON_TMP="/tmp/${APP_ID}.install.$$"
        CRON_NEW="/var/www/html/custom_apps/$APP_ID.new"

        docker exec "$CRON_CONTAINER" sh -c 'mkdir -p /var/www/html/custom_apps'
        docker exec "$CRON_CONTAINER" rm -rf "$CRON_TMP" "$CRON_NEW" "$TARGET_DIR" >/dev/null 2>&1 || true
        docker exec "$CRON_CONTAINER" mkdir -p "$CRON_TMP"
        docker cp "$STAGE/." "$CRON_CONTAINER:$CRON_TMP/"
        docker exec "$CRON_CONTAINER" sh -c "
            mv '$CRON_TMP' '$CRON_NEW' &&
            chown -R www-data:www-data '$CRON_NEW' &&
            mv '$CRON_NEW' '$TARGET_DIR' &&
            chown -R www-data:www-data '$TARGET_DIR'
        "
    fi
fi

say "Removing stale Sticky Notes backup directories from custom_apps, if any..."
docker exec "$WEB_CONTAINER" sh -c "rm -rf /var/www/html/custom_apps/${APP_ID}.backup-*"

say "Resetting PHP OPcache for fresh application code..."
docker exec "$WEB_CONTAINER" php -r 'if (function_exists("opcache_reset")) { opcache_reset(); }' >/dev/null 2>&1 || true

say "Enabling $APP_NAME..."
docker exec -u www-data "$WEB_CONTAINER" php /var/www/html/occ app:enable "$APP_ID"

say "Running Nextcloud upgrade checks..."
docker exec -u www-data "$WEB_CONTAINER" php /var/www/html/occ upgrade

say "Checking application state..."
docker exec -u www-data "$WEB_CONTAINER" php /var/www/html/occ app:list | grep -A1 -B1 "$APP_ID" || true


say "Verifying deployed Sticky Notes version..."
DEPLOYED_VERSION="$(docker exec "$WEB_CONTAINER" sh -c "grep -o '<version>[^<]*</version>' '$TARGET_DIR/appinfo/info.xml' | sed 's#<version>##;s#</version>##'" 2>/dev/null || true)"
[ "$DEPLOYED_VERSION" = "1.0.0" ] || die "Version verification failed. Expected 1.0.0, found: $DEPLOYED_VERSION"

docker exec "$WEB_CONTAINER" sh -c "test -f '$TARGET_DIR/js/app-1.0.0.js'" || die "Missing deployed JavaScript asset."
docker exec "$WEB_CONTAINER" sh -c "test -f '$TARGET_DIR/css/style-1.0.0.css'" || die "Missing deployed stylesheet."
docker exec "$WEB_CONTAINER" sh -c "test -f '$TARGET_DIR/js/dashboard-1.0.0.js'" || die "Missing deployed Dashboard asset."

printf 'Sticky Notes version: %s\n' "$DEPLOYED_VERSION"
say "Deployment verification: OK"

say
say "$APP_NAME deployment finished successfully."
say "Web container: $WEB_CONTAINER"
[ -n "$CRON_CONTAINER" ] && say "Cron container: $CRON_CONTAINER"
say "Installed path: $TARGET_DIR"
say "Project directory: $APP_DIR"
