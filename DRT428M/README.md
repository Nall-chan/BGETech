[![SDK](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Version](https://img.shields.io/badge/Modul%20Version-3.50-blue.svg)](https://community.symcon.de/t/modul-alle-modbus-zaehler-von-b-g-e-tech/45290)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)  
[![Version](https://img.shields.io/badge/Symcon%20Version-5.1%20%3E-green.svg)](https://www.symcon.de/service/dokumentation/installation/migrationen/v50-v51-q2-2019/)
[![Check Style](https://github.com/Nall-chan/BGETech/workflows/Check%20Style/badge.svg)](https://github.com/Nall-chan/BGETech/actions) 
[![Run Tests](https://github.com/Nall-chan/BGETech/workflows/Run%20Tests/badge.svg)](https://github.com/Nall-chan/BGETech/actions)  
[![Spenden](https://www.paypalobjects.com/de_DE/DE/i/btn/btn_donate_SM.gif)](#2-spenden)    


# DRT 428M <!-- omit in toc -->  

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

Ermöglicht die einfache Einbindung von Energie-Zählern des Typs DRT 428M der Firma B+G E-Tech.  
Zusätzlich können mehrere Zähler auf einem physikalischen RS485-Bus betrieben werden.  

## 2. Voraussetzungen

 - IPS 5.1 oder höher  
 - DRT 428M Zähler mit **ModBus-Interface**  
 - physikalisches RS485 Interface für die Zähler  

## 3. Software-Installation

 Dieses Modul ist Bestandteil der [B+G E-Tech Library](../README.md#3-software-installation).  

## 4. Einrichten der Instanzen in IP-Symcon

Das Modul ist im Dialog 'Instanz hinzufügen' unter dem Hersteller 'B+G E-Tech' zu finden.  
![Instanz hinzufügen](../imgs/add1.png)  

Es wird automatisch ein 'ModBus Gateway' als Splitter-Instanz, sowie ein 'Client Socket' als dessen I/O-Instanz erzeugt.  
In dem sich öffnenden Konfigurationsformular muss der Abfrage-Zyklus eingestellt werden.  
 Über den Button 'Gateway konfigurieren' wird das Konfigurationsformular des 'ModBus Gateway' geöffnet.  
![Instanz konfigurieren](../imgs/config.png)    
Hier muss jetzt der Modus passend zur Hardwareanbindung (TCP /RTU) sowie die Geräte-ID des Zählers eingestellt und übernommen werden.  
Anschließend über den Button 'Schnittstelle konfigurieren' das Konfigurationsformular der I/O-Instanz öffnen.  
Je nach Hardwareanbindung müssen hier die RS485 Parameter oder die IP-Adresse des ModBus-Umsetzers eingetragen werden.  
Details hierzu sind dem Handbuch des Zählers (RS485) und dem eventuell verwendeten Umsetzer zu entnehmen.  

## 5. Statusvariablen und Profile

Folgende Statusvariablen werden automatisch angelegt.  

|                   Name                   |  Typ  |         Ident          |   Profil    |
| :--------------------------------------: | :---: | :--------------------: | :---------: |
|               Spannung L1                | float |       VoltageL1        |  Volt.230   |
|               Spannung L2                | float |       VoltageL2        |  Volt.230   |
|               Spannung L3                | float |       VoltageL3        |  Volt.230   |
|                 Frequenz                 | float |       Frequency        |  Hertz.50   |
|                 Strom L1                 | float |       CurrentL1        |   Ampere    |
|                 Strom L2                 | float |       CurrentL2        |   Ampere    |
|                 Strom L3                 | float |       CurrentL3        |   Ampere    |
|         Kumulierte Wirkleistung          | float |    Totalactivepower    | Watt.14490  |
|             Wirkleistung L1              | float |     ActivepowerL1      | Watt.14490  |
|             Wirkleistung L2              | float |     ActivepowerL2      | Watt.14490  |
|             Wirkleistung L3              | float |     ActivepowerL3      | Watt.14490  |
|         Kumulierte Blindleistung         | float |   Totalreactivepower   |     VaR     |
|             Blindleistung L1             | float |    ReactivepowerL1     |     VaR     |
|             Blindleistung L2             | float |    ReactivepowerL2     |     VaR     |
|             Blindleistung L3             | float |    ReactivepowerL3     |     VaR     |
|        Kumulierte Scheinleistung         | float |   Totalapparentpower   |     VA      |
|            Scheinleistung L1             | float |    ApparentpowerL1     |     VA      |
|            Scheinleistung L2             | float |    ApparentpowerL2     |     VA      |
|            Scheinleistung L3             | float |    ApparentpowerL3     |     VA      |
|       Kumulierter Leistungsfaktor        | float |    Totalpowerfactor    |             |
|            Leistungsfaktor L1            | float |     PowerfactorL1      |             |
|            Leistungsfaktor L2            | float |     PowerfactorL2      |             |
|            Leistungsfaktor L3            | float |     PowerfactorL3      |             |
|     Gesamte kumulierte Wirkleistung      | float |   Totalactiveenergy    | Electricity |
|    Gesamte kumulierte Wirkleistung L1    | float |  TotalactiveenergyL1   | Electricity |
|    Gesamte kumulierte Wirkleistung L2    | float |  TotalactiveenergyL2   | Electricity |
|    Gesamte kumulierte Wirkleistung L3    | float |  TotalactiveenergyL3   | Electricity |
|   aufgenommene kumulierte Wirkleistung   | float |   Importactiveenergy   | Electricity |
| aufgenommene kumulierte Wirkleistung L1  | float |  ImportactiveenergyL1  | Electricity |
| aufgenommene kumulierte Wirkleistung L2  | float |  ImportactiveenergyL2  | Electricity |
| aufgenommene kumulierte Wirkleistung L3  | float |  ImportactiveenergyL3  | Electricity |
|    abgegebene kumulierte Wirkleistung    | float |   Exportactiveenergy   | Electricity |
|  abgegebene kumulierte Wirkleistung L1   | float |  ExportactiveenergyL1  | Electricity |
|  abgegebene kumulierte Wirkleistung L2   | float |  ExportactiveenergyL2  | Electricity |
|  abgegebene kumulierte Wirkleistung L3   | float |  ExportactiveenergyL3  | Electricity |
|     Gesamte kumulierte Blindleistung     | float |  Totalreactiveenergy   |    kVArh    |
|   Gesamte kumulierte Blindleistung L1    | float | TotalreactiveenergyL1  |    kVArh    |
|   Gesamte kumulierte Blindleistung L2    | float | TotalreactiveenergyL2  |    kVArh    |
|   Gesamte kumulierte Blindleistung L3    | float | TotalreactiveenergyL3  |    kVArh    |
|  aufgenommene kumulierte Blindleistung   | float |  Importreactiveenergy  |    kVArh    |
| aufgenommene kumulierte Blindleistung L1 | float | ImportreactiveenergyL1 |    kVArh    |
| aufgenommene kumulierte Blindleistung L2 | float | ImportreactiveenergyL2 |    kVArh    |
| aufgenommene kumulierte Blindleistung L3 | float | ImportreactiveenergyL3 |    kVArh    |
|   abgegebene kumulierte Blindleistung    | float |  Exportreactiveenergy  |    kVArh    |
|  abgegebene kumulierte Blindleistung L1  | float | ExportreactiveenergyL1 |    kVArh    |
|  abgegebene kumulierte Blindleistung L2  | float | ExportreactiveenergyL2 |    kVArh    |
|  abgegebene kumulierte Blindleistung L3  | float | ExportreactiveenergyL3 |    kVArh    |


Folgende Profile werden automatisch angelegt.  

|    Name     |  Typ  |
| :---------: | :---: |
| PhaseAngle  | float |
|     VA      | float |
|     VaR     | float |
| Intensity.F | float |
|    kVArh    | float |

Darstellung in der Console.  
![Instanz](../imgs/DRT428M.png) 

## 6. PHP-Befehlsreferenz

```php
bool DRT428M_RequestRead(int $InstanzID);
```
Ließt alle Werte vom Zähler.  
Bei Erfolg wird `true` und im Fehlerfall wird `false` zurückgegeben und eine Warnung erzeugt.  


## 7. Anhang

### 1. Changelog

[Changelog der Library](../README.md#2-changelog)

### 2. Spenden

Die Library ist für die nicht kommerzielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:  

  PayPal:  
<a href="https://www.paypal.com/donate?hosted_button_id=G2SLW2MEMQZH2" target="_blank"><img src="https://www.paypalobjects.com/de_DE/DE/i/btn/btn_donate_LG.gif" border="0" /></a>  

  Wunschliste:  
<a href="https://www.amazon.de/hz/wishlist/ls/YU4AI9AQT9F?ref_=wl_share" target="_blank"><img src="https://upload.wikimedia.org/wikipedia/commons/4/4a/Amazon_icon.svg" border="0" width="100"/></a>  

## 8. Lizenz

  IPS-Modul:  
  [CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)  
 
