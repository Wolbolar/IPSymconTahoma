# IPSymconTahoma
[![Version](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Version](https://img.shields.io/badge/Symcon%20Version-5.0%20%3E-green.svg)](https://www.symcon.de/forum/threads/37412-IP-Symcon-5-0-%28Testing%29)

Module for IP Symcon Version 5 and higher. Enables communication with a Logitech Harmony Hub and sending commands through the Logitech Harmony Hub.

## Documentation

**Table of Contents**

1. [Features](#1-features)
2. [Requirements](#2-requirements)
3. [Installation](#3-installation)
4. [Function reference](#4-functionreference)
5. [Configuration](#5-configuration)
6. [Annex](#6-annex)

## 1. Features

With the help of the Logitech Harmony Hub devices can be operated, which are otherwise controllable via IR remote controls or even newer devices such as FireTV and AppleTV which are controlled via Bluetooth.
For more information about controllable devices through the Logitech Harmony Hub, see [Logitech Harmony Elite](https://www.logitech.com/de-de/product/harmony-elite "Logitech Harmony Elite")

Using the module, the devices stored in the Logitech Harmony Hub can be imported into IP-Symcon and then switched from IP-Symcon via the Logitech Harmony Hub.
Harmony activities can be started from IP-Symcon. When the Harmony Hub performs an activity, the current running activity is submitted to IP-Symcon.

### send IR Code :  

 - Send an IR signal through the Logitech Harmony Hub to the hub known devices 

### FireTV:  (Bluetooth)

 - Sending commands to a FireTV   

### Show current activity:  

 - Shows the current active Harmony Hub activity 
 
### Start an activity:  

 - Starts an activity of the Harmony Hub  
	  
## 2. Requirements

- IPS 5.x.
- Logitech Harmony Hub on the same network as IP Symcon

## 3. Installation

### a. Loading the module

Open the IP Console's web console with _http://<IP-Symcon IP>:3777/console/_.

Then click on the module store (IP-Symcon > 5.1) icon in the upper right corner.

![Store](img/store_icon.png?raw=true "open store")

In the search field type

```
Logitech Harmony
```  


![Store](img/module_store_search_en.png?raw=true "module search")

Then select the module and click _Install_

![Store](img/install_en.png?raw=true "install")


#### Install alternative via Modules instance

_Open_ the object tree.

![Objektbaum](img/object_tree.png?raw=true "object tree")	

Open the instance _'Modules'_ below core instances in the object tree of IP-Symcon (>= Ver 5.x) with a double-click and press the _Plus_ button.

![Modules](img/modules.png?raw=true "modules")	

![Plus](img/plus.png?raw=true "Plus")	

![ModulURL](img/add_module.png?raw=true "Add Module")
 
Enter the following URL in the field and confirm with _OK_:


```	
https://github.com/Wolbolar/IPSymconHarmony 
```
    
and confirm with _OK_.    
    
Then an entry for the module appears in the list of the instance _Modules_

By default, the branch _master_ is loaded, which contains current changes and adjustments.
Only the _master_ branch is kept current.

![Master](img/master.png?raw=true "master") 

If an older version of IP-Symcon smaller than version 5.1 (min 4.3) is used, click on the gear on the right side of the list.
It opens another window,

![SelectBranch](img/select_branch_en.png?raw=true "select branch") 

here you can switch to another branch, for older versions smaller than 5.1 (min 4.3) select _Old-Version_ .

### b. Configuration in IPS

If smony Hub. For each created variable also the description field is used, here stands the actual command inside which is sent to the Harmony Hub. Therefore, the description field of the variable must not be changed. The name of the variable as well as the command names that are stored in the variable profile of the variable can be customized by the user. However, the order in the variable profile should not change


## 4. Function reference

### Logitech Harmony Hub:

### Harmony Devices
The Harmony Devices are to be created via the configurator
A command can be sent to each device
 
Reads out the available functions of the device and outputs them as an array
```php 
LHD_GetCommands(integer $InstanceID) 
```  
Parameter _$InstanceID_ ObjectID of the Harmony Hub device

  
 Sends a command to the Logitech Harmony Hub 
```php
LHD_Send(integer $InstanceID, string $Command)
``` 
 Parameter _$InstanceID_ ObjectID of the Harmony Hub device
 Parameter _$Command_ Command to be sent, available commands are read out via LHD_GetCommands.
 
### Harmony Hub
Activities of the Logitech Harmony Hub can be performed.
The current activity of the Logitech Harmony Hub is displayed in the variable Harmony Activity and can be switched on the web front.
 
If the activity is to be updated via functions or switched via a script, the following functions are to be used:
Requests the current activity of the Logitech Harmony Hub. The value is set in the variable Harmony Activity.
```php
HarmonyHub_getCurrentActivity(integer $InstanceID) 
```   
Parameter _$InstanceID_ ObjektID of the Harmony Hub Splitter

 
Reads all available activities of the Logitech Harmony Hub and returns an array.
```php
HarmonyHub_GetAvailableAcitivities(integer $InstanceID) 
```   
Parameter _$InstanceID_ ObjektID of the Harmony Hub Splitter
  
Reads all available Device IDs of the Logitech Harmony Hub and returns an array.
```php
HarmonyHub_GetHarmonyDeviceIDs(integer $InstanceID) 
```   
   
Parameter _$InstanceID_ of the Harmony Hub Splitter
 
Switches to the desired Logitech Harmony Hub activity
```php
HarmonyHub_startActivity(integer $InstanceID, integer $activityID)
``` 
Parameter _$InstanceID_ ObjektID of the Harmony Hub Splitter
Parameter _$activityID_ ID of the Harmony Activity, available IDs can be read out via HarmonyHub_GetAvailableAcitivities

## 5. Configuration:

### Logitech Harmony Hub:

| Property         | Type    | Standard Value | Function                                            |
| :--------------: | :-----: | :------------: | :-------------------------------------------------: |
| Open             | boolean | true           | Connect to Logitech Harmony Hub Active / Disable    |
| Host             | string  |                | IP address of the Logitech Harmony Hub              |
| Email            | string  |                | Email address to login MyHarmony                    |
| Passwort         | string  |                | Password to log in MyHarmony                        |
| ImportCategoryID | integer |                | ObjectID of the import category                     |
| HarmonyVars      | boolean |                | Active creates variables per control group          |
| HarmonyScript    | boolean |                | Active creates a script for each command            |


### Logitech Harmony Device:  

| Property        | Type    | Standard Value | Function                                                              |
| :-------------: | :-----: | :------------: | :-------------------------------------------------------------------: |
| Name            | string  |                | Name of the device                                                    |
| DeviceID        | integer |                | DeviceID of the device                                                |
| BluetoothDevice | boolean |     false      | Bluetooth Device                                                      |


## 6. Annnex

###  b. GUIDs und Data Flow:

#### Logitech Harmony Hub Splitter:

GUID: `{7E03C651-E5BF-4EC6-B1E8-397234992DB4}` 


#### Logitech Harmony Device:

GUID: `{C45FF6B3-92E9-4930-B722-0A6193C7FFB5}` 

Credits:
[Logitech Harmony Ultimate Smart Control Hub Library](https://www.symcon.de/forum/threads/22682-Logitech-Harmony-Ultimate-Smart-Control-Hub-library "Logitech-Harmony-Ultimate-Smart-Control-Hub-library") _Zapp_ 