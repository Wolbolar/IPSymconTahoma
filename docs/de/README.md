# IPSymconTahoma
[![Version](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Version](https://img.shields.io/badge/Symcon%20Version-5.0%20%3E-green.svg)](https://www.symcon.de/forum/threads/38222-IP-Symcon-5-0-verf%C3%BCgbar)

Modul für IP-Symcon ab Version 5. Ermöglicht die Kommunikation mit einem Somfy TaHoma und das Senden von Befehlen.

## Dokumentation

**Inhaltsverzeichnis**

1. [Funktionsumfang](#1-funktionsumfang)  
2. [Voraussetzungen](#2-voraussetzungen)  
3. [Installation](#3-installation)  
4. [Funktionsreferenz](#4-funktionsreferenz)  
5. [Anhang](#5-anhang)  

## 1. Funktionsumfang

Steuerung von Somfy Geräten über die Somfy API.

## 2. Voraussetzungen

 - IPS 5.2
 - Somfy TaHoma

## 3. Installation

### a. Laden des Moduls

Die Webconsole von IP-Symcon mit _http://{IP-Symcon IP}:3777/console/_ öffnen. 


Anschließend oben rechts auf das Symbol für den Modulstore (IP-Symcon > 5.2) klicken

![Store](img/store_icon.png?raw=true "open store")

Im Suchfeld nun

```
TaHoma
```  

eingeben

![Store](img/module_store_search.png?raw=true "module search")

und schließend das Modul auswählen und auf _Installieren_

![Store](img/install.png?raw=true "install")

drücken.

## 4. Funktionsreferenz

Öffnen
```php
TAHOMA_Open(integer $InstanceID)
``` 
Parameter _$InstanceID_ ObjektID des TaHoma Geräts

Schließen
```php
TAHOMA_Close(integer $InstanceID)
``` 
Parameter _$InstanceID_ ObjektID des TaHoma Geräts

Stop
```php
TAHOMA_Stop(integer $InstanceID)
``` 
Parameter _$InstanceID_ ObjektID des TaHoma Geräts
  

## 5. Konfiguration:



## 6. Anhang
