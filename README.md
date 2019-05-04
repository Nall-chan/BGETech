[![Version](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Version](https://img.shields.io/badge/Modul%20Version-3.00-blue.svg)]()
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)  
[![Version](https://img.shields.io/badge/Symcon%20Version-5.1%20%3E-green.svg)](https://www.symcon.de/forum/threads/30857-IP-Symcon-5-1-%28Stable%29-Changelog)
[![StyleCI](https://styleci.io/repos/107579755/shield?style=flat)](https://styleci.io/repos/107579755)  

# Symcon-Modul: B+G E-Tech

## Dokumentation

**Inhaltsverzeichnis**

1. [Funktionsumfang](#1-funktionsumfang)  
2. [Voraussetzungen](#2-voraussetzungen)  
3. [Software-Installation](#3-software-installation) 
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Anhang](#5-anhang)  
    1. [GUID der Module](#1-guid-der-module)
    2. [Changlog](#2-changlog)
    3. [Spenden](#3-spenden)
6. [Lizenz](#6-lizenz)

## 1. Funktionsumfang

Ermöglich die Einbindung von Energie-Zählern der Firma B+G E-Tech
ohne mehrere ModBus-Instanzen in IPS.  
Zusätzlich können mehrere Zähler auf einem physikalischen RS485-Bus
betrieben werden.  

Folgende Module beinhaltet das IPSBGETEch Repository:

- __DRS210C__  
	Zähler vom Typ DRS 210C  

- __DRS458__  
	Zähler vom Typ DRS 458  

- __DRT710M__  
	Zähler vom Typ DRT 710M  

- __SDM72D__   
	Zähler vom Typ SDM 72D

- __SDM120C__   
	Zähler vom Typ SDM 120C

- __SDM220__   
	Zähler vom Typ SDM 220  

- __SDM230__   
	Zähler vom Typ SDM 230  

- __SDM530__   
	Zähler vom Typ SDM 530 

- __SDM630__   
	Zähler vom Typ SDM 630  

## 2. Voraussetzungen

 - IPS 5.1 oder höher  
 - Unterstützte Zähler  
 - physikalisches RS485 Interface für die Zähler  

## 3. Software-Installation

**IPS 5.1:**  
   Bei privater Nutzung:
     Über den 'Module-Store' in IPS.  
   **Bei kommerzieller Nutzung (z.B. als Errichter oder Integrator) wenden Sie sich bitte an den Autor.**  

## 4. Einrichten der Instanzen in IP-Symcon

Ist direkt in der Dokumentation der jeweiligen Module beschrieben.  

## 5. Anhang

###  1. GUID der Module

 
| Modul   | Typ    | Prefix  | GUID                                   |
| :-----: | :----: | :-----: | :------------------------------------: |
| DRS210C | Device | DRS210C | {2CA41C9F-355C-4231-90A5-6D83A90B65BD} |
| DRS458  | Device | DRS458  | {8CA96C98-3014-44E4-8D15-4EC6B524F1F4} |
| DRT710M | Device | DRT710M | {187BB86B-A52F-4ADD-A233-92108BD71767} |
| SDM72D  | Device | SDM72D  | {08371372-5993-4BAF-A6EC-D70759709CD9} |
| SDM210C | Device | SDM210C | {32DCCC5C-78D3-475E-885A-652F56DB4D18} |
| SDM220  | Device | SDM220  | {93668601-F92A-46FC-AE5B-E44451F022EE} |
| SDM230  | Device | SDM230  | {10D08FCD-D1AC-4CF3-8B19-54B92209DA07} |
| SDM530  | Device | SDM530  | {9A65E88A-21DC-439F-8602-CA14EE9FDF27} |
| SDM630  | Device | SDM630  | {BBCA5E14-505E-4394-B653-8CD33AD52037} |


### 2. Changlog

Version 3.0:  
 - Release für IPS 5.1 und den Module-Store  

Version 2.06:  
 - IntervallBox und caption ersetzt  

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

### 3. Spenden  
  
  Die Library ist für die nicht kommzerielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:  

<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=G2SLW2MEMQZH2" target="_blank"><img src="https://www.paypalobjects.com/de_DE/DE/i/btn/btn_donate_LG.gif" border="0" /></a>

## 6. Lizenz

  IPS-Modul:  
  [CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)  
 
