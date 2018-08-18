[![Version](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Version](https://img.shields.io/badge/Modul%20Version-2.05-blue.svg)]()
[![Version](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)  
[![Version](https://img.shields.io/badge/Symcon%20Version-4.3%20%3E-green.svg)](https://www.symcon.de/forum/threads/30857-IP-Symcon-4-3-%28Stable%29-Changelog)

# DRT 710M

## Dokumentation

**Inhaltsverzeichnis**

1. [Funktionsumfang](#1-funktionsumfang)  
2. [Voraussetzungen](#2-voraussetzungen)  
3. [Software-Installation](#3-software-installation) 
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)  
6. [PHP-Befehlsreferenz](#6-php-befehlsreferenz)   
7. [Anhang](#7-anhang)  
    1. [Changlog](#1-changlog)
    2. [Spenden](#2-spenden)
8. [Lizenz](#8-lizenz)

## 1. Funktionsumfang

Ermöglich die einfache Einbindung von Energie-Zählern des Typs DRT 710M der Firma B+G E-Tech.  
Zusätzlich können mehrere Zähler auf einem physikalischen RS485-Bus betrieben werden.  

## 2. Voraussetzungen

 - IPS 4.3 oder höher  
 - DRT 710M Zähler mit 'ModBus-Interface 
 - physikalisches RS485 Interface für die Zähler  

## 3. Software-Installation

Dieses Modul ist Bestandteil der IPSBGETEch-Library.

**IPS 4.3:**  
   Bei privater Nutzung: Über das 'Module-Control' in IPS folgende URL hinzufügen.  
    `git://github.com/Nall-chan/IPSBGETEch.git`  

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

| Name                                              | Typ     | Ident                                      | Profil       |
| :-----------------------------------------------: | :-----: | :----------------------------------------: | :----------: |
| Spannung L1                                       | integer | Voltage L1                                 | Volt.230     |
| Spannung L2                                       | integer | Voltage L2                                 | Volt.230     |
| Spannung L3                                       | integer | Voltage L3                                 | Volt.230     |
| Strom L1                                          | integer | Current L1                                 | Ampere       |
| Strom L2                                          | integer | Current L2                                 | Ampere       |
| Strom L3                                          | integer | Current L3                                 | Ampere       |
| Wirkleistung L1                                   | integer | Active power L1                            | Watt.14490   |
| Wirkleistung L2                                   | integer | Active power L2                            | Watt.14490   |
| Wirkleistung L3                                   | integer | Active power L3                            | Watt.14490   |
| Scheinleistung L1                                 | integer | Apparent power L1                          | VA           |
| Scheinleistung L2                                 | integer | Apparent power L2                          | VA           |
| Scheinleistung L3                                 | integer | Apparent power L3                          | VA           |
| Blindleistung L1                                  | integer | Reactive power L1                          | VaR          |
| Blindleistung L2                                  | integer | Reactive power L2                          | VaR          |
| Blindleistung L3                                  | integer | Reactive power L3                          | VaR          |
| Leistungsfaktor L1                                | integer | Power factor L1                            |              |
| Leistungsfaktor L2                                | integer | Power factor L2                            |              |
| Leistungsfaktor L3                                | integer | Power factor L3                            |              |
| Kumulierte System Wirkleistung                    | integer | Total system power                         | Watt.14490   |
| Kumulierte System Scheinleistung                  | integer | Total system apparent power                | VA           |
| Kumulierte System Blindleistung                   | integer | Total system reactive power                | VaR          |
| Kumulierte System Leistungsfaktor                 | integer | Total system power factor                  |              |
| Frequenz                                          | integer | Frequency                                  | Hertz.50     |
| Neutral Strom                                     | integer | Neutral current                            | Ampere       |
| Gesamte Import Wirkleistung                       | integer | Total import energy                        | Electricity  |
| Gesamte Export Wirkleistung                       | integer | Total export energy                        | Electricity  |

Folgende Profile werden automatisch angelegt.  

| Name          | Typ     |
| :-----------: | :-----: |
| Ampere.I      | integer |
| Electricity.I | integer |
| Hertz.I       | integer |
| VA.I          | integer |
| VaR.I         | integer |
| Volt.I        | integer |
| Watt.I        | integer |


Darstellung in der Console.  
![Instanz](../imgs/DRT710M.png) 

## 6. PHP-Befehlsreferenz

```php
bool DRT710M_RequestRead(int $InstanzID);
```
Ließt alle Werte vom Zähler.  
Bei Erfolg wird `true` und im Fehlerfall wird `false` zurückgegeben und eine Warnung erzeugt.  


## 7. Anhang

### 1. Changlog

Version 2.05:  
 - Bugfix für DRT 710M  

Version 2.04:  
 - DRT 710M ergänzt  

Version 2.03:  
 - SDM 530 ergänzt  

Version 2.2:  
 - Intern werden jetzt auch Integer, Boolean und String Variablen unterstützt  
 - Fehlende Übersetzungen ergänzt  

Version 2.1:  
 - Abzufragende Werte können deaktivert werden  

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
  
  Die Library ist für die nicht kommzerielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:  

<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=G2SLW2MEMQZH2" target="_blank"><img src="https://www.paypalobjects.com/de_DE/DE/i/btn/btn_donate_LG.gif" border="0" /></a>

## 8. Lizenz

  IPS-Modul:  
  [CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)  
 
