# Projektbeschreibung: KVMDash
KVMDash ist eine moderne Webanwendung, die die Verwaltung von Virtual Machines (VMs) und Linux-Containern (LXC) auf Linux-Systemen ermöglicht. Mit einer benutzerfreundlichen Oberfläche erleichtert KVMDash die Administration und Überwachung von Virtualisierungsumgebungen.


## Features

### VM- und LXC-Verwaltung
* Erstellen, Löschen und Konfigurieren von VMs und Containern über die Weboberfläche.
* Nutzung von Vorlagen für die schnelle und standardisierte Erstellung von VMs und Containern.

### Systemmonitoring
* Echtzeitüberwachung von Ressourcen wie CPU, Arbeitsspeicher, Festplattenauslastung und weiteren wichtigen Systemmetriken.
* Übersichtliche Darstellung der Systemleistung für eine optimale Kontrolle und Fehleranalyse.

### Benutzer-Authentifizierung
* Sichere Login-Mechanismen zum Schutz vor unberechtigtem Zugriff.

## Voraussetzung
Ein Linux-System mit:
* Installiertem KVM (Kernel-based Virtual Machine).
* Installiertem libvirt für die Verwaltung von Virtualisierungsressourcen



Eine detaillierte Anleitung zur Installation von KVM und libvirt unter Debian 12 (Bookworm) finden Sie hier: 
* [Installation von KVM unter Debian 12 Bookworm](https://themm.curiosum.eu/howto/installation-von-kvm-unter-debian-12-bookworm)
* [libvirt-howto](https://themm.curiosum.eu/howto/libvirt-howto)

Nach erfolgreicher Installation muss User "www-data" der Gruppe libvirt hinzugefügt werden.
```bash
usermod -aG libvirt www-data
```

## Ziel
Das Hauptziel von KVMDash ist es, die Komplexität bei der Administration von Virtualisierungsumgebungen zu verringern. Dabei werden Verwaltungs- und Überwachungsfunktionen bereitgestellt, die gleichzeitig leicht zugänglich und intuitiv bedienbar sind.



