#!/bin/bash
# crear_fabrica.sh
# Genera fabrica.zip con los ficheros actuales del sistema como "estado de fábrica"
# Coloca el ZIP junto a este script en: /home/pi/.local/fabrica.zip

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ZIPFILE="$SCRIPT_DIR/fabrica.zip"
TMPDIR=$(mktemp -d)

declare -A SRCMAP=(
  ["MMDVMHost.ini"]="/home/pi/MMDVMHost/MMDVMHost.ini"
  ["MMDVMYSF.ini"]="/home/pi/MMDVMHost/MMDVMYSF.ini"
  ["MMDVMDSTAR.ini"]="/home/pi/MMDVMHost/MMDVMDSTAR.ini"
  ["MMDVMNXDN.ini"]="/home/pi/MMDVMHost/MMDVMNXDN.ini"
  ["DisplayDriver.ini"]="/home/pi/Display-Driver/DisplayDriver.ini"
  ["YSFGateway.ini"]="/home/pi/YSFClients/YSFGateway/YSFGateway.ini"
  ["DMRGateway.ini"]="/home/pi/DMRGateway/DMRGateway.ini"
  ["DStarGateway.ini"]="/home/pi/DStarGateway/DStarGateway.ini"
  ["NXDNGateway.ini"]="/home/pi/NXDNClients/NXDNGateway/NXDNGateway.ini"
  ["station.cfg"]="/home/pi/radiosonde_auto_rx/auto_rx/station.cfg"
  ["rbfeeder.ini"]="/etc/rbfeeder.ini"
  ["fr24feed.ini"]="/etc/fr24feed.ini"
  ["ModuleEchoLink.conf"]="/usr/local/etc/svxlink/svxlink.d/ModuleEchoLink.conf"
  ["svxlink.conf"]="/usr/local/etc/svxlink/svxlink.conf"
  ["enlaces.json"]="/home/pi/.local/enlaces.json"
  ["AMBEserver.ini"]="/home/pi/AMBE_SERVER/AMBEserver.ini"
  ["dump1090.args"]="/home/pi/dump1090-fa/dump1090.args"
  ["bluetooth.sh"]="/home/pi/.local/bluetooth.sh"
)

INCLUDED=()
SKIPPED=()

echo "=== Creando fabrica.zip ==="

for name in "${!SRCMAP[@]}"; do
  src="${SRCMAP[$name]}"
  if [ -f "$src" ]; then
    cp "$src" "$TMPDIR/$name"
    INCLUDED+=("$name")
    echo "  [+] $name"
  else
    SKIPPED+=("$name")
    echo "  [-] $name (no encontrado en $src)"
  fi
done

# Incluir carpeta logs/ de radiosonde si existe
LOGSBASE="/home/pi/radiosonde_auto_rx/auto_rx/logs"
if [ -d "$LOGSBASE" ]; then
  cp -r "$LOGSBASE" "$TMPDIR/logs"
  echo "  [+] logs/ (radiosonde)"
fi

# Eliminar ZIP anterior si existe
[ -f "$ZIPFILE" ] && rm -f "$ZIPFILE"

cd "$TMPDIR" || exit 1
zip -q -r "$ZIPFILE" . 2>/dev/null
ZIP_OK=$?
cd - > /dev/null

rm -rf "$TMPDIR"

if [ $ZIP_OK -ne 0 ]; then
  echo ""
  echo "ERROR: No se pudo crear el ZIP"
  exit 1
fi

echo ""
echo "=== fabrica.zip creado en: $ZIPFILE ==="
echo "    Ficheros incluidos: ${#INCLUDED[@]}"
if [ ${#SKIPPED[@]} -gt 0 ]; then
  echo "    Omitidos (no existen): $(IFS=', '; echo "${SKIPPED[*]}")"
fi
echo ""
echo "Ahora puedes usar restaurar_de_fabrica.sh para volver a este estado."
