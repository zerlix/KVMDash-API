#!/bin/bash

# Debug-Modus nur wenn DEBUG=1 gesetzt ist
[[ "${DEBUG:-0}" == "1" ]] && set -x

# Basis-Logging-Funktion
log() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $*"
}

# Aufräumen nur wenn CLEANUP=1 gesetzt ist
if [[ "${CLEANUP:-0}" == "1" ]]; then
    cleanup() {
        log "Beende WebSocket-Dienste..."
        pkill -u $(whoami) websockify 2>/dev/null || true
    }
    trap cleanup EXIT
fi

# Prüfe websockify Installation
which websockify >/dev/null || {
    log "Fehler: websockify nicht gefunden"
    exit 1
}

# Liste alle laufenden VMs
log "Starte WebSocket-Dienste"

for vm in $(virsh -c qemu:///system list --name); do
    # Hole SPICE-Port
    SPICE_PORT=$(virsh -c qemu:///system dumpxml "$vm" | xmllint --xpath "string(//graphics[@type='spice']/@port)" - 2>/dev/null)
    
    if [ -n "$SPICE_PORT" ] && [ "$SPICE_PORT" != "0" ]; then
        WS_PORT=$((SPICE_PORT + 1000))
        log "Konfiguriere WebSocket für $vm: Port $WS_PORT -> $SPICE_PORT"
        
        # Test ob Port frei ist
        if netstat -tuln 2>/dev/null | grep -q ":$WS_PORT "; then
            log "WARNUNG: Port $WS_PORT ist bereits belegt"
            continue
        fi
        
        # Starte websockify
        websockify -D $WS_PORT localhost:$SPICE_PORT --log-file /tmp/websockify_${vm}.log 2>/dev/null
        
        # Prüfe ob Start erfolgreich war
        sleep 1
        if pgrep -f "websockify.*$WS_PORT" >/dev/null; then
            log "✓ WebSocket für $vm erfolgreich gestartet"
        else
            log "✗ WebSocket für $vm konnte nicht gestartet werden"
        fi
    fi
done