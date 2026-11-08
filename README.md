# Pylontech für IP-Symcon

Dieses Repository enthält ein IP-Symcon-Modul zur Kommunikation mit Pylontech-Batteriespeichern. Es besteht aus einem Splitter für die Weiterleitung zwischen IP-Symcon-I/O und Geräteinstanzen, einer Instanz für einzelne Batterien sowie einer Stack-Instanz zur Zusammenfassung mehrerer Batterien.

## Funktionen

### Einzelne Pylontech-Batterie

Die Geräteinstanz liest – abhängig von der Konfiguration – folgende Werte aus:

- bis zu 15 Zellspannungen
- bis zu 5 Temperaturen
- aktuelle und nominale Kapazität sowie Ladezustandswerte
- Gesamtspannung und Strom
- Zyklenzahl
- Geräteversion

Für jede Batterie lässt sich eine Adresse von 1 bis 8 einstellen. Zellspannungen, Temperaturen, Kapazitätswerte, Gesamtspannung, Strom und Zyklenzahl können einzeln aktiviert werden. Das Aktualisierungsintervall wird in Minuten angegeben; ein Intervall von 0 deaktiviert den Timer.

### Pylontech-Stack

Die Stack-Instanz aktualisiert die zugeordneten Batterieinstanzen und fasst ihre Werte zusammen. Sie stellt unter anderem Gesamtkapazität, Stromsumme, Durchschnittsspannung, berechneten Ladezustand und Energie bereit. Zusätzlich ermittelt sie die niedrigste und höchste Zellspannung einschließlich Batterie- und Zellnummer sowie die Spannungsdifferenz.

## Bestandteile

- `IPSPylontechSplitter`: leitet Daten zwischen der IP-Symcon-I/O-Instanz und den Geräteinstanzen weiter.
- `IPSPylontech`: kommuniziert mit einer einzelnen Batterie und stellt deren Messwerte bereit.
- `IPSPylonstack`: fasst die Messwerte der zugeordneten Batterien zusammen.

## Installation

1. In IP-Symcon unter **Kerninstanzen → Modules** das Repository hinzufügen:
   `https://github.com/gogo2001-2002/Pylontech`
2. Eine passende I/O-Instanz und den Pylontech-Splitter einrichten und verbinden.
3. Für jede Batterie eine `IPSPylontech`-Instanz anlegen, Adresse und gewünschte Messwerte konfigurieren.
4. Für mehrere Batterien eine `IPSPylonstack`-Instanz anlegen und die Batterieinstanzen dieser Stack-Instanz zuordnen.

Die Modul-Metadaten nennen IP-Symcon 5.0 als Kompatibilitätsbasis. Die tatsächliche Kommunikation hängt von der verwendeten I/O-Hardware und deren Konfiguration ab.

## Hinweise

Das Repository bildet den aktuellen Quellstand aus der Symcon-Installation ab. Die Dateien `module1.php` und `module_ori.php` sind ebenfalls enthalten, werden von IP-Symcon jedoch nicht als Modul-Einstiegspunkt verwendet; dieser ist jeweils `module.php`.
