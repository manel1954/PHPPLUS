#!/bin/bash
# crear_fabrica.sh
# Empaqueta el contenido de la carpeta ./fabrica/ en fabrica.zip
# Pon en ./fabrica/ los ficheros que quieras usar como estado de fábrica

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
FABRICA_DIR="$SCRIPT_DIR/fabrica"
ZIPFILE="$SCRIPT_DIR/fabrica.zip"

if [ ! -d "$FABRICA_DIR" ]; then
  echo "ERROR: No existe la carpeta $FABRICA_DIR"
  echo "       Créala y coloca dentro los ficheros de fábrica."
  exit 1
fi

FICHEROS=$(find "$FABRICA_DIR" -maxdepth 1 -type f | wc -l)
if [ "$FICHEROS" -eq 0 ]; then
  echo "ERROR: La carpeta $FABRICA_DIR está vacía."
  exit 1
fi

echo "=== Empaquetando carpeta fabrica/ ==="
find "$FABRICA_DIR" -maxdepth 1 -type f -exec basename {} \; | sort | while read -r f; do
  echo "  [+] $f"
done

# Incluir subcarpeta logs/ si existe
if [ -d "$FABRICA_DIR/logs" ]; then
  echo "  [+] logs/ (subcarpeta)"
fi

[ -f "$ZIPFILE" ] && rm -f "$ZIPFILE"

cd "$FABRICA_DIR" || exit 1
zip -q -r "$ZIPFILE" . 2>/dev/null
ZIP_OK=$?
cd - > /dev/null

if [ $ZIP_OK -ne 0 ]; then
  echo ""
  echo "ERROR: No se pudo crear fabrica.zip"
  exit 1
fi

echo ""
echo "=== fabrica.zip creado en: $ZIPFILE ==="
echo "    Usa restaurar_de_fabrica.sh para restaurar este estado."