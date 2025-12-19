#!/usr/bin/env bash
set -euo pipefail

# Create a distributable archive of the Nefertiti salon information system.
# Usage: bash scripts/package.sh

PROJECT_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
OUTPUT="$PROJECT_ROOT/nefertiti_site.zip"

cd "$PROJECT_ROOT"
rm -f "$OUTPUT"
zip -r "$OUTPUT" \
  index.php \
  config.php \
  README.md \
  admin \
  api \
  assets \
  db \
  gitopisanie >/dev/null

echo "Archive created: $OUTPUT"
