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
 - IP-Symcon Connect

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

### b. Somfy-Cloud
Es wird ein Account bei Somfy benötigt, den man für die TaHoma Box nutzt.

Um Zugriff auf die TaHoma Box über die Somfy APi zu erhalten muss zunächst IP-Symcon als System authentifiziert werden.
Hierzu wird ein aktives IP-Symcon Connect benötigt und den normalen Somfy Benutzernamen und Passwort.
Zunächst wird beim installieren des Modul gefragt ob eine Discovery Instanz angelegt werden soll, dies beantwortet man mit _ja_, man kann aber auch die Discovery Instanz von Hand selber anlegen

![Discovery](img/discovery.png?raw=true "discovery")

### c. Authentifizierung bei Somfy
Anschließend erscheint ein Fenster Schnittstelle konfigurieren, hier drückt man auf den Knopf _Registrieren_ und hält seinen Somfy Benutzernamen und Passwort bereit.

![Schnittstelle](img/schnittstelle.png?raw=true "Schnittstelle")

Es öffnet sich die Anmeldeseite von Somfy. Hier gibt man in die Maske den Somfy Benutzernamen und das Somfy Passwort an und fährt mit einem Klick auf _Anmelden_ fort.

![Anmeldung](img/somfy_anmeldung.png?raw=true "Anmeldung")

Jetzt wird man von Somfy gefragt ob IP-Symcon als System die persönlichen Geräte auslesen darf, die Somfy Geräte steuern sowie den Status der Geräte auslesen darf.
HIer muss man nun mit _Ja_ bestätigen um IP-Symcon zu erlauben die TaHoma Box zu steuern und damit auch die Somfy Geräte steuern zu können.

![Genehmigung](img/genehmigung.png?raw=true "Genehmigung")

Es erscheint dann eine Bestätigung durch IP-Symcon das die Authentifizierung erfolgreich war,
 
![Success](img/sucess.png?raw=true "Success")
 
anschließend kann das Browser Fenster geschlossen werden und man kehrt zu IP-Symcon zurück.
Zurück beim Fenster Schnittstelle konfigurieren geht man nun auf _Weiter_

Nun öffen wir die Discovery Instanz im Objekt Baum zu finden unter _Discovery Instanzen_. Hier wählen wir den TaHoma Account aus und wählen _Erstellen_.

![Discovery](img/discovery1.png?raw=true "discoverywindow")

### d. Einrichtung des Konfigurator-Moduls

Jetzt wechseln wir im Objektbaum in die Instanz _**TaHoma**_ (Typ TaHoma Configurator) zu finden unter _Konfigurator Instanzen_.

![config](img/config.png?raw=true "config")

Hier werden alle Geräte, die bei Somfy unter dem Account registiert sind und von der Somfy API unterstützt werden aufgeführt.

Ein einzelnes Gerät kann man durch markieren auf das Gerät und ein Druck auf den Button _Erstellen_ erzeugen. Der Konfigurator legt dann eine Geräte Instanz an.

### e. Einrichtung der Geräteinstanz
Eine manuelle Einrichtung eines Gerätemoduls ist nicht erforderlich, das erfolgt über den Konfigurator. In dem Geräte-Modul ist gegebenenfalls nur das Abfrage-Intervall anzupassen, die anderen Felder, insbesondere die Seriennummer (diese ist die Identifikation des Gerätes) und die Geräte-Typ-ID (diese steuert, welche Variablen angelegt werden) sind vom Konfigurator vorgegeben.


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

Position
```php
TAHOMA_Position(integer $InstanceID, integer $Position)
``` 
Parameter _$InstanceID_ ObjektID des TaHoma Geräts
Parameter _$Position_ Position 0 -100

Status und Geräteparameter abfragen
```php
TAHOMA_RequestStatus(integer $InstanceID)
``` 
Parameter _$InstanceID_ ObjektID des TaHoma Geräts

Gibt den Status des Geräts zurück insofern das Gerät über einen Gerätestatus verfügt. Es wird ein Arry mit den Geräteparametern und unterstützten Befehlen für das Gerät zurückgegeben.
  

## 5. Konfiguration:



## 6. Anhang

###  GUIDs und Datenaustausch:

#### TaHoma Cloud:

GUID: `{6F83CEDB-BC40-63BB-C209-88D6B252C9FF}` 


#### TaHoma Device:

GUID: `{4434685E-551F-D887-3163-006833D318E3}` 