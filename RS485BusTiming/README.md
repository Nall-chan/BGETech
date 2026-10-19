[![SDK](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Module Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FBGETech%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.version&label=Modul%20Version&color=blue)](https://community.symcon.de/t/modul-alle-modbus-zaehler-von-b-g-e-tech/45290)
[![Symcon Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FBGETech%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.compatibility.version&suffix=%3E&label=Symcon%20Version&color=green)](https://www.symcon.de/de/service/dokumentation/installation/migrationen/v81-v90-q1-2026/)  
[![License](https://img.shields.io/badge/License-Custom--NC--SA-green.svg)](#10-lizenz)
[![Check Style](https://github.com/Nall-chan/BGETech/workflows/Check%20Style/badge.svg)](https://github.com/Nall-chan/BGETech/actions)
[![Run Tests](https://github.com/Nall-chan/BGETech/workflows/Run%20Tests/badge.svg)](https://github.com/Nall-chan/BGETech/actions)  
[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](#2-spenden) [![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](#2-spenden)  

# RS485 Bus Zeitsteuerung <!-- omit in toc -->  

## Inhaltsverzeichnis <!-- omit in toc -->

- [1. Funktionsumfang](#1-funktionsumfang)
- [2. Voraussetzungen](#2-voraussetzungen)
- [3. Software-Installation](#3-software-installation)
- [4. Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
- [5. Statusvariablen](#5-statusvariablen)
- [6. Visualisierung](#6-visualisierung)
  - [Kachel Visualisierung](#kachel-visualisierung)
  - [WebFront Visualisierung](#webfront-visualisierung)
- [7. PHP-Befehlsreferenz](#7-php-befehlsreferenz)
- [8. Aktionen](#8-aktionen)
- [9. Anhang](#9-anhang)
  - [1. Changelog](#1-changelog)
  - [2. Spenden](#2-spenden)
- [10. Lizenz](#10-lizenz)

## 1. Funktionsumfang

Sorgt dafür, dass mehrere ModBus-Geräte an einem gemeinsamen RS485-Bus zuverlässig abgefragt werden können.  

Jedes Gerät am Bus bekommt in Symcon ein eigenes `ModBus Gateway` (eine Geräte-ID je Gateway). Ohne diese Instanz senden die Gateways unabhängig voneinander. Manche Zähler (z.B. DRS 210-C) belegen den Bus nach ihrer Antwort noch eine kurze Zeit. Eine Anfrage an das nächste Gerät geht dann verloren und endet erst nach dem Timeout des Gateways (Standard 5 Sekunden).  

Die RS485 Bus Zeitsteuerung sitzt zwischen den ModBus Gateways und der seriellen Schnittstelle:  

```text
ModBus Gerät / Zähler  →  ModBus Gateway (je Geräte-ID)  →  RS485 Bus Zeitsteuerung  →  Serial Port / Client Socket
```

- Es ist immer nur eine Anfrage gleichzeitig auf dem Bus.  
- Nach einer Antwort wartet sie, bis der Bus für die eingestellte Ruhezeit frei war, und sendet erst dann die nächste Anfrage.  
- Antwortet ein Gerät nicht, gibt sie den Bus nach der eingestellten Zeit wieder frei. Ein nicht erreichbares Gerät blockiert die anderen so nicht mehr für die volle Timeout-Zeit des Gateways.  
- Die Daten werden unverändert durchgereicht. Das Modul funktioniert daher mit allen ModBus-Instanzen von Symcon (`ModBus Gerät`, `ModBus Adresse`) und mit den Zähler-Modulen dieser Library.  

Gemessen mit vier DRS 210-C und einem SDM 630 an einem Bus (9600 Baud): Ab 100 ms Ruhezeit antworteten alle Zähler zuverlässig, bei 0–50 ms fielen bis zur Hälfte der Antworten aus.  

## 2. Voraussetzungen

- Symcon 9.0 oder höher  
- ModBus-Geräte mit RS485-Schnittstelle  
- Serielle Schnittstelle (`Serial Port`) oder ein Seriell-auf-LAN-Wandler im Modus `ModBus RTU über TCP` (`Client Socket`)  

## 3. Software-Installation

 Dieses Modul ist Bestandteil der [B+G E-Tech Library](../README.md#3-software-installation).  

## 4. Einrichten der Instanzen in IP-Symcon

Die Instanz wird über `Instanz hinzufügen` mit dem Modul `RS485 Bus Zeitsteuerung` angelegt.  
Die Konsole schlägt als übergeordnete Instanz einen `Serial Port` oder einen `Client Socket` vor. Dort werden Schnittstelle, Baudrate, Datenbits, Parität und Stoppbits passend zu den Geräten eingestellt.  

Danach wird bei **jedem** `ModBus Gateway`, das an diesem Bus hängt, die übergeordnete Instanz auf die RS485 Bus Zeitsteuerung geändert. Der Modus der Gateways bleibt `Modbus RTU` (bzw. `Modbus RTU über TCP` beim Client Socket).  

| Eigenschaft                             | Name            | Typ     | Standardwert | Beschreibung                                                                                        |
| :-------------------------------------- | :-------------- | :------ | :----------: | :-------------------------------------------------------------------------------------------------- |
| Ruhezeit auf dem Bus nach einer Antwort | BusIdleTime     | integer |     100      | Wartezeit in ms nach dem letzten empfangenen Byte, bevor die nächste Anfrage gesendet wird (0–5000) |
| Bus ohne Antwort freigeben nach         | ResponseTimeout | integer |     1000     | Kommt auf eine Anfrage keine Antwort, ist der Bus nach dieser Zeit in ms wieder frei (100–10000)    |

Beispiel:  

```php
IPS_SetProperty(12345, 'BusIdleTime', 150);
IPS_SetProperty(12345, 'ResponseTimeout', 1000);
IPS_ApplyChanges(12345);
```

> [!TIP]
> Antwortet ein Gerät dauerhaft nicht, wartet sein Gateway trotzdem die volle `Wartezeit bis zum Timeout` ab (Expertenoptionen des Gateways, Standard 5 Sekunden). In dieser Zeit fragt Symcon auch die anderen Gateways seltener ab. Bei RS485-Zählern reicht meist 1 Sekunde.

## 5. Statusvariablen

Dieses Modul legt keine Statusvariablen an.  

## 6. Visualisierung

### Kachel Visualisierung

Dieses Modul hat keine Statusvariablen und erscheint nicht in der Visualisierung.  

### WebFront Visualisierung

Dieses Modul hat keine Statusvariablen und erscheint nicht in der Visualisierung.  

## 7. PHP-Befehlsreferenz

Dieses Modul hat keine eigenen PHP-Befehle.  

## 8. Aktionen

**Grundsätzlich können alle bedienbaren Statusvariablen als Ziel einer [`Aktion`](https://www.symcon.de/service/dokumentation/konzepte/automationen/ablaufplaene/aktionen/) mit `Auf Wert schalten` angesteuert werden, so dass hier keine speziellen Aktionen benutzt werden müssen.**

Dieses Modul stellt keine eigenen Aktionen bereit.  

## 9. Anhang

### 1. Changelog

[Changelog der Library](../README.md#2-changelog)

### 2. Spenden

  Die Library ist für die nicht kommerzielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:  

[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](https://paypal.me/Nall4chan)  

[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](https://www.amazon.de/hz/wishlist/ls/YU4AI9AQT9F?ref_=wl_share)  

## 10. Lizenz

  IPS-Modul:  
  [Custom NC-SA](../LICENSE)  
