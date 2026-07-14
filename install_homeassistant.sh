#!/usr/bin/env bash
set -euo pipefail

REPO_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SRC_DIR="$REPO_DIR/custom_components/enocran_vmi"
TARGET_DIR="${1:-/config/custom_components}"

if [ ! -d "$SRC_DIR" ]; then
  echo "Source directory not found: $SRC_DIR" >&2
  exit 1
fi

mkdir -p "$TARGET_DIR"
rm -rf "$TARGET_DIR/enocran_vmi"
cp -r "$SRC_DIR" "$TARGET_DIR/"

cat <<EOF
Installation terminée.
Composant copié vers : $TARGET_DIR/enocran_vmi

Ensuite :
  1. Redémarrez Home Assistant
  2. Ajoutez la configuration YAML de l'intégration
  3. Vérifiez que /config/enoceanmqtt.devices est présent
EOF
