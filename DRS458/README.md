[![Version](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Version](https://img.shields.io/badge/Modul%20Version-3.00-blue.svg)]()
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)  
[![Version](https://img.shields.io/badge/Symcon%20Version-5.1%20%3E-green.svg)](https://www.symcon.de/forum/threads/30857-IP-Symcon-5-1-%28Stable%29-Changelog)
[![Check Style](https://github.com/Nall-chan/BGETech/workflows/Check%20Style/badge.svg)](https://github.com/Nall-chan/BGETech/actions) 
[![Run Tests](https://github.com/Nall-chan/BGETech/workflows/Run%20Tests/badge.svg)](https://github.com/Nall-chan/BGETech/actions)  


# DRS 458

## Inhaltsverzeichnis <!-- omit in toc -->

- [1. Funktionsumfang](#1-funktionsumfang)
- [2. Voraussetzungen](#2-voraussetzungen)
- [3. Software-Installation](#3-software-installation)
- [4. Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
- [5. Statusvariablen und Profile](#5-statusvariablen-und-profile)
- [6. PHP-Befehlsreferenz](#6-php-befehlsreferenz)
- [7. Anhang](#7-anhang)
  - [1. Changelog](#1-changelog)
  - [2. Spenden](#2-spenden)
- [8. Lizenz](#8-lizenz)

## 1. Funktionsumfang

Ermöglicht die einfache Einbindung von Energie-Zählern des Typs DRS 458 der Firma B+G E-Tech. 
Zusätzlich können mehrere Zähler auf einem physikalischen RS485-Bus betrieben werden.  

## 2. Voraussetzungen

 - IPS 5.1 oder höher  
 - DRS 458 Zähler mit 'ModBus-Interface 
 - physikalisches RS485 Interface für die Zähler  

## 3. Software-Installation

Dieses Modul ist Bestandteil der B+G E-Tech Library.

**IPS 5.1:**  
   Bei privater Nutzung:
     Über den 'Module-Store' in IPS.  
   **Bei kommerzieller Nutzung (z.B. als Errichter oder Integrator) wenden Sie sich bitte an den Autor.**  

## 4. Einrichten der Instanzen in IP-Symcon

Das Modul ist im Dialog 'Instanz hinzufügen' unter dem Hersteller 'B+G E-Tech' zu finden.  
![Instanz hinzufügen](../imgs/add1.png)  

Es wird automatisch eine 'ModBus Gateway' als Splitter-Instanz, sowie ein 'Client Socket' als dessen I/O-Instanz erzeugt.  
Werden in dem sich öffnenden Konfigurationsformular muss der Abfrage-Zyklus eingestellt werden.  
Über den Button 'Gateway konfigurieren' oder das Zahnrad hinter der Übergeordneten Instanz wird das Konfigurationsformular des 'ModBus Gateway' geöffnet.  
Hier muss jetzt der Modus passend zur Hardwareanbindung (TCP /RTU) sowie die Geräte-ID des Zählers eingestellt und übernommen werden.  
Anschließend über den Button 'Schnittstelle konfigurieren' oder wieder über das Zahnrad hinter der Übergeordneten Instanz, das Konfigurationsformular der I/O-Instanz öffnen.  
Je nach Hardwareanbindung müssen hier die RS485 Parameter oder die IP-Adresse des ModBus-Umsetzers eingetragen werden.  
Details hierzu sind dem Handbuch des Zählers (RS485) und dem eventuell verwendeten Umsetzer zu entnehmen.  

## 5. Statusvariablen und Profile

Folgende Statusvariablen werden automatisch angelegt.  

| Name                                              | Typ   | Ident                                      | Profil       |
| :-----------------------------------------------: | :---: | :----------------------------------------: | :----------: |
| Gesamte kumulierte Wirkleistung                   | float | Total active energy                        | Electricity  |

Folgende Profile werden automatisch angelegt.  

| Name        | Typ   |
| :---------: | :---: |
| PhaseAngle  | float |
| VA          | float |
| VaR         | float |
| Intensity.F | float |
| kVArh       | float |

Darstellung in der Console.  
![Instanz](../imgs/DRS458.png) 

## 6. PHP-Befehlsreferenz

```php
bool DRS458_RequestRead(int $InstanzID);
```
Ließt alle Werte vom Zähler.  
Bei Erfolg wird `true` und im Fehlerfall wird `false` zurückgegeben und eine Warnung erzeugt.  


## 7. Anhang

### 1. Changelog

Version 3.0:  
 - Release für IPS 5.1 und den Module-Store  

Version 2.0:  
 - DRS 458 ergänzt  
 - SDM 72D ergänzt  
 - SDM 120C ergänzt  
 - SDM 220 ergänzt  
 - SDM 230 ergänzt  
 - SDM 630 fehlende Werte ergänzt und kleiner Bugfixes  

Version 1.1:  
 - Profile ergänzt  
 - Doku ergänzt  

Version 1.0:  
 - Erstes offizielles Release  

### 2. Spenden  
  
  Die Library ist für die nicht kommerzielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:  

<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=G2SLW2MEMQZH2" target="_blank"><img src="https://www.paypalobjects.com/de_DE/DE/i/btn/btn_donate_LG.gif" border="0" /></a>

## 8. Lizenz

  IPS-Modul:  
  [CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)  
 
