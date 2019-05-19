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

Mit Hilfe des Logitech Harmony Hub sind Geräte bedienbar, die sonst über IR-Fernbedienungen steuerbar sind oder auch neuere Geräte wie FireTV und AppleTV die über Bluetooth angesteuert werden.
Nähere Informationen zu ansteuerbaren Geräten über den Logitech Harmony Hub unter [Logitech Harmony Elite](https://www.logitech.com/de-de/product/harmony-elite "Logitech Harmony Elite")

Mit Hilfe des Moduls können die Geräte die im Logitech Harmony Hub hinterlegt sind in IP-Symcon importiert werden und dann von IP-Symcon über den Logitech Harmony Hub geschaltet werden.
Harmony Aktivitäten können von IP-Symcon aus gestartet werden. Wenn der Harmony Hub eine Aktivität ausführt wird die aktuelle laufende Aktivität an IP-Symcon übermittelt.

### IR Code Senden:  

 - Senden eines IR Signals über den Logitech Harmony Hub an dem Hub bekannte Geräte  

### FireTV:  (Bluetooth)

 - Senden von Befehlen an einen FireTV    

### Aktivität anzeigen:  

 - Anzeige der momentanen aktiven Harmony Activity des Harmony Hub 
 
### Aktivität starten 
   
- Starten einen Harmony Hub Aktivität

### Tastendrücke auswerten 
   
- Es können bei einem IP-Symcon Geräte (Roku 3 Emulation) die Tastendrücke Up, Down, Left, Right, Select, Back, Play, Reverse, Forward, Search, Info, Home in IP-Symcon ausgewertet werden und Skripte gestartet werden


## 2. Voraussetzungen

 - IPS 5.x
 - Logitech Harmony Hub im gleichen Netzwerk wie IP-Symcon

## 3. Installation

### a. Laden des Moduls

Die Webconsole von IP-Symcon mit _http://<IP-Symcon IP>:3777/console/_ öffnen. 


Anschließend oben rechts auf das Symbol für den Modulstore (IP-Symcon > 5.1) klicken

![Store](img/store_icon.png?raw=true "open store")

Im Suchfeld nun

```
Logitech Harmony
```  

eingeben

![Store](img/module_store_search.png?raw=true "module search")

und schließend das Modul auswählen und auf _Installieren_

![Store](img/install.png?raw=true "install")

drücken.


#### Alternatives Installieren über Modules Instanz

Den Objektbaum _Öffnen_.

![Objektbaum](img/objektbaum.png?raw=true "Objektbaum")	

Die Instanz _'Modules'_ unterhalb von Kerninstanzen im Objektbaum von IP-Symcon (>=Ver. 5.x) mit einem Doppelklick öffnen und das  _Plus_ Zeichen drücken.

![Modules](img/Modules.png?raw=true "Modules")	

![Plus](img/plus.png?raw=true "Plus")	

![ModulURL](img/add_module.png?raw=true "Add Module")
 
Im Feld die folgende URL eintragen und mit _OK_ bestätigen:

```
https://github.com/Wolbolar/IPSymconHarmony 
```  
	
Anschließend erscheint ein Eintrag für das Modul in der Liste der Instanz _Modules_    

Es wird im Standard der Zweig (Branch) _master_ geladen, dieser enthält aktuelle Änderungen und Anpassungen.
Nur der Zweig _master_ wird aktuell gehalten.

![Master](img/master.png?raw=true "master") 

Sollte eine ältere Version von IP-Symcon die kleiner ist als Version 5.1 (min 4.3) eingesetzt werden, ist auf das Zahnrad rechts in der Liste zu klicken.
Es öffnet sich ein weiteres Fenster,

![SelectBranch](img/select_branch.png?raw=true "select branch") 

hier kann man auf einen anderen Zweig wechseln, für ältere Versionen kleiner als 5.1 (min 4.3) ist hier
_Old-Version_ auszuwählen. 

### b. Einrichtung in IP-Symcon

ymcon gesteuert wird, kann so geschaltet werden.

## 4. Funktionsreferenz

### Logitech Harmony Hub:

### Harmony Devices
 Die Harmony Devices sind über den Konfigurator anzulegen
 An jedes Device kann ein Befehl geschickt werden
 
 Liest die verfügbaren Funktionen des Geräts aus und gibt diese als Array aus
```php 
LHD_GetCommands(integer $InstanceID) 
```  
Parameter _$InstanceID_ ObjektID des Harmony Hub Geräts
  
 Sendet einen Befehl an den Logitech Harmony Hub 
```php
LHD_Send(integer $InstanceID, string $Command)
``` 
 Parameter _$InstanceID_ ObjektID des Harmony Hub Geräts
 Parameter _$Command_ Befehl der gesendet werden soll, verfügbare Befehle werden über LHD_GetCommands ausgelesen.
 
### Harmony Hub
 Es können Aktivitäten des Logitech Harmony Hub ausgeführt werden.
 Die aktuelle Akivität des Logitech Harmony Hub wird in der Variable Harmony Activity angezeigt und kann im Webfront geschaltet werden.
 
 Wenn die Aktivität über Funktionen aktualisiert werden soll oder über ein Skript geschaltet sind die folgenden Funktionen zu benutzten:
 Fordert die aktuelle Aktivität des Logitech Harmony Hub an. Der Wert wird in die Variable Harmony Activity gesetzt.
```php
HarmonyHub_getCurrentActivity(integer $InstanceID) 
```   
  Parameter _$InstanceID_ ObjektID des Harmony Hub Splitters

 
 Liest alle verfügbaren Aktivitäten des Logitech Harmony Hub aus und gibt einen Array zurück.
```php
HarmonyHub_GetAvailableAcitivities(integer $InstanceID) 
```   
  Parameter _$InstanceID_ ObjektID des Harmony Hub Splitters
  
  Liest alle verfügbaren Device IDs des Logitech Harmony Hub aus und gibt einen Array zurück.
```php
HarmonyHub_GetHarmonyDeviceIDs(integer $InstanceID) 
```   
   
  Parameter _$InstanceID_ ObjektID des Harmony Hub Splitters 
 
 Schaltet auf die gewünschte Logitech Harmony Hub Aktivität
```php
HarmonyHub_startActivity(integer $InstanceID, integer $activityID)
``` 
  Parameter _$InstanceID_ ObjektID des Harmony Hub Splitters
  Parameter _$activityID_ ID der Harmony Aktivität, verfügbare IDs können über HarmonyHub_GetAvailableAcitivities ausgelesen werden


## 5. Konfiguration:

### Logitech Harmony Hub:

| Eigenschaft      | Typ     | Standardwert | Funktion                                            |
| :--------------: | :-----: | :----------: | :-------------------------------------------------: |
| Open             | boolean | true         | Verbindung zum Logitech Harmony Hub aktiv / deaktiv |
| Host             | string  |              | IP Adresse des Logitech Harmony Hub                 |
| Email            | string  |              | Email Adresse zur Anmeldung MyHarmony               |
| Passwort         | string  |              | Passwort zur Anmeldung MyHarmony                    |
| ImportCategoryID | integer |              | ObjektID der Import Kategorie                       |
| HarmonyVars      | boolean |              | Aktiv legt Variablen pro Controlgroup an            |
| HarmonyScript    | boolean |              | Aktiv legt für jeden Befehl ein Skript an           |


### Logitech Harmony Device:  

| Eigenschaft     | Typ     | Standardwert | Funktion                                                              |
| :-------------: | :-----: | :----------: | :-------------------------------------------------------------------: |
| Name            | string  |              | Name des Geräts                                                       |
| DeviceID        | integer |              | DeviceID des Geräts                                                   |
| BluetoothDevice | boolean |     false    | Bluetooth Gerät                                                       |


## 6. Anhang

###  b. GUIDs und Datenaustausch:

#### Logitech Harmony Hub Splitter:

GUID: `{7E03C651-E5BF-4EC6-B1E8-397234992DB4}` 


#### Logitech Harmony Device:

GUID: `{C45FF6B3-92E9-4930-B722-0A6193C7FFB5}` 

Credits:
[Logitech Harmony Ultimate Smart Control Hub Library](https://www.symcon.de/forum/threads/22682-Logitech-Harmony-Ultimate-Smart-Control-Hub-library "Logitech-Harmony-Ultimate-Smart-Control-Hub-library") _Zapp_ 


