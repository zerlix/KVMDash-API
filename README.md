# Projektbeschreibung: KVMDash-API
<table style="border-collapse: collapse; width: 100%;">
    <tr>
        <td style="width: 150px; padding: 10px; vertical-align: middle;">
            <img src="https://github.com/zerlix/KVMDash/raw/main/src/assets/kvmdash.svg" alt="KvmDash Logo" style="max-width: 100%;">
        </td>
        <td style="padding: 10px; vertical-align: middle;">
            KVMDash-API ist eine API, die die Verwaltung von Virtual Machines (VMs) auf Linux-Systemen ermöglicht. 
            Es bietet Endpunkte zur Steuerung und Überwachung von Virtualisierungsumgebungen und dient als Rückgrat der KVMDash Webanwendung. 
            <br>
            <a href="https://github.com/zerlix/KVMDash">https://github.com/zerlix/KVMDash</a>
        </td>
    </tr>
</table>

## Features

### API zur VM Verwaltung
* Endpunkte zum Erstellen, Löschen und Konfigurieren von VMs und Containern.
* Unterstützung von Vorlagen für die schnelle und standardisierte Erstellung von VMs und Containern.

### Systemmonitoring
* Endpunkte zur Echtzeitüberwachung von Ressourcen wie CPU, Arbeitsspeicher, Festplattenauslastung und weiteren wichtigen Systemmetriken.
* Bereitstellung von Daten zur Systemleistung für eine optimale Kontrolle und Fehleranalyse.

## API
Die API-Dokumentation mit den Endpunkten und Beispielen finden Sie hier:
[Postman API Doku](https://documenter.getpostman.com/view/36034764/2sAYdZuu5W)


## Videos


https://github.com/user-attachments/assets/44dbd85b-9263-4ad5-aaa6-afdb6e12e2c8





## Voraussetzung
Ein Linux-System mit:
* Installiertem KVM (Kernel-based Virtual Machine).
* Installiertem libvirt für die Verwaltung von Virtualisierungsressourcen.

Eine detaillierte Anleitung zur Installation von KVM und libvirt unter Debian 12 (Bookworm) finden Sie hier: 
* [Installation von KVM unter Debian 12 Bookworm](https://github.com/zerlix/Howtos/blob/main/KVM_Debian.md)

## Installation

TODO ...


Nach erfolgreicher Installation muss User "www-data" der Gruppe libvirt und libvirt-qemu hinzugefügt werden.

```bash
# Füge www-data zur libvirt-Gruppe hinzu
usermod -a -G libvirt www-data
usermod -a -G libvirt-qemu www-data

# Setze Gruppenrechte für das Verzeichnis
chown root:libvirt /var/lib/libvirt/images
chmod g+rwx /var/lib/libvirt/images
```





