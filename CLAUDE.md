@.claude-rules/symcon-module-rules.md
@STATUS.md

# Diese Library: BGETech  

- Diese Library dient zur Ergänzung der in Symcon vorhandenen ModBus-Geräte an die Software IP-Symcon. Sie stellt die notwendigen Module und Funktionen bereit, um spezielle ModBus Geräte in IP-Symcon zu integrieren und zu steuern.
- Die Library ist so konzipiert, dass sie leicht erweiterbar ist, um fehlende Geräte zu ergänzen. Dabei ist der Datenfluss des ModBus-Gateways von Symcon zu berücksichtigen, um die Kommunikation zwischen den Geräten und IP-Symcon zu gewährleisten.
- Ziel ist die Einbindung von Energie-Zählern der Firma B+G E-Tech ohne mehrere einzelne ModBus-Instanzen pro Adresse in IPS.  
- Zusätzlich gibt es totzeiten beim Abfragen von mehreren Zähler auf einem physikalischen RS485-Bus, um Kommunikationsstörungen zu vermeiden.
- Quellen der Adfessen:
  - PDF und XLSX Dokumente unter  @docs/