#!/bin/sh
set -eu

ROOT="$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)"
SRC="$ROOT/src"
INFO="$SRC/appinfo/info.xml"

[ -f "$INFO" ] || { echo "ERROR: $INFO not found" >&2; exit 1; }
VERSION="$(sed -n 's:.*<version>\([^<]*\)</version>.*:\1:p' "$INFO" | head -n 1)"
[ -n "$VERSION" ] || { echo "ERROR: version not found in info.xml" >&2; exit 1; }

for item in appinfo css img js l10n lib templates; do
    [ -e "$SRC/$item" ] || { echo "ERROR: missing src/$item" >&2; exit 1; }
done

BUILD="$ROOT/.build"
APP="$BUILD/stickynotes"
RELEASE="$ROOT/release"
rm -rf "$BUILD"
mkdir -p "$APP" "$RELEASE"

for item in appinfo css img js l10n lib templates; do
    cp -R "$SRC/$item" "$APP/$item"
done
cp "$ROOT/LICENSE" "$APP/LICENSE"

# The top-level directory in both archives must be exactly the app id: stickynotes/
(
    cd "$BUILD"
    tar -czf "$RELEASE/stickynotes-$VERSION.tar.gz" stickynotes
    if command -v zip >/dev/null 2>&1; then
        zip -qr "$RELEASE/stickynotes-$VERSION.zip" stickynotes
    fi
)

rm -rf "$BUILD"
echo "Built release/stickynotes-$VERSION.tar.gz"
[ -f "$RELEASE/stickynotes-$VERSION.zip" ] && echo "Built release/stickynotes-$VERSION.zip"
