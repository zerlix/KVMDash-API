#!/bin/bash
echo "Starte WebSocket-Dienste..."

for vm in $(virsh -c qemu:///system list --name); do
    # SPICE-Port Extraktion
    SPICE_PORT=$(virsh -c qemu:///system dumpxml "$vm" | xmllint --xpath "string(//graphics[@type='spice']/@port)" -)
    
    # Validierung
    if [ -z "$SPICE_PORT" ] || [ "$SPICE_PORT" = "0" ]; then
        echo "Warnung: Kein gültiger SPICE-Port für VM $vm gefunden"
        continue
    fi
    
    echo "Debug: SPICE_PORT=$SPICE_PORT für VM $vm"
    
    # Neue vereinfachte Port-Berechnung: SPICE_PORT + 1000
    WS_PORT=$((SPICE_PORT + 1000))
    echo "Starte Websockify für $vm: $WS_PORT → $SPICE_PORT"
    
    nohup websockify $WS_PORT localhost:$SPICE_PORT > /dev/null 2>&1 &
    
    if ps aux | grep -v grep | grep -q "websockify $WS_PORT"; then
        echo "✓ Websockify läuft für $vm auf Port $WS_PORT"
    else
        echo "✗ Fehler beim Starten von Websockify für $vm"
    fi
done

