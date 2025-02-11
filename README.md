# Projektbeschreibung: KVMDash-API
Das KVMDash-API ist eine API, die die Verwaltung von Virtual Machines (VMs) auf Linux-Systemen ermöglicht. Es bietet Endpunkte zur Steuerung und Überwachung von Virtualisierungsumgebungen und dient als Rückgrat der KVMDash Webanwendung. 
https://github.com/zerlix/KVMDash

## Features

### API zur VM Verwaltung
* Endpunkte zum Erstellen, Löschen und Konfigurieren von VMs und Containern.
* Unterstützung von Vorlagen für die schnelle und standardisierte Erstellung von VMs und Containern.

### Systemmonitoring
* Endpunkte zur Echtzeitüberwachung von Ressourcen wie CPU, Arbeitsspeicher, Festplattenauslastung und weiteren wichtigen Systemmetriken.
* Bereitstellung von Daten zur Systemleistung für eine optimale Kontrolle und Fehleranalyse.

## Videos
[![YouTube Video](https://img.youtube.com/vi/bIJdHC3julM/0.jpg)](https://www.youtube.com/watch?v=bIJdHC3julM)

## Voraussetzung
Ein Linux-System mit:
* Installiertem KVM (Kernel-based Virtual Machine).
* Installiertem libvirt für die Verwaltung von Virtualisierungsressourcen.

Eine detaillierte Anleitung zur Installation von KVM und libvirt unter Debian 12 (Bookworm) finden Sie hier: 
* [Installation von KVM unter Debian 12 Bookworm](https://github.com/zerlix/Howtos/blob/main/KVM_Debian.md)

## Installation

TODO ...

Nach erfolgreicher Installation muss User "www-data" der Gruppe libvirt und kvm hinzugefügt werden.
```bash
usermod -aG libvirt,kvm www-data
```





