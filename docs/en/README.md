# IPSymconTahoma
[![Version](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Version](https://img.shields.io/badge/Symcon%20Version-5.0%20%3E-green.svg)](https://www.symcon.de/forum/threads/37412-IP-Symcon-5-0-%28Testing%29)

Module for IP-Symcon from version 5. Allows communication with a Somfy TaHoma and sending commands.

## Documentation

**Table of Contents**

1. [Features](#1-features)
2. [Requirements](#2-requirements)
3. [Installation](#3-installation)
4. [Function reference](#4-functionreference)
5. [Configuration](#5-configuration)
6. [Annex](#6-annex)

## 1. Features

Control Somfy devices via Somfy Cloud API. 
	  
## 2. Requirements

 - IPS 5.2
 - Somfy TaHoma
 - IP-Symcon Connect

## 3. Installation

### a. Loading the module

Open the IP Console's web console with _http://{IP-Symcon IP}:3777/console/_.

Then click on the module store (IP-Symcon > 5.2) icon in the upper right corner.

![Store](img/store_icon.png?raw=true "open store")

In the search field type

```
TaHoma
```  


![Store](img/module_store_search_en.png?raw=true "module search")

Then select the module and click _Install_

![Store](img/install_en.png?raw=true "install")

### b. Somfy Cloud
An account with Somfy is required, which is used for the TaHoma Box.

To get access to the TaHoma Box via the Somfy API, IP-Symcon must first be authenticated as a system.
This requires an active IP-Symcon Connect and the normal Somfy user name and password.
First, when installing the module, you are asked whether you want to create a discovery instance, you answer this with _yes_, but you can also create the discovery instance yourself

![Discovery](img/discovery_en.png?raw=true "discovery")

### c. Authentication to Somfy
Then a Configure Interface window appears, here you press the _Register_ button and have your Somfy user name and password ready.

![Interface](img/interface.png?raw=true "interface")

Somfy's login page opens. Here you enter the Somfy user name and the Somfy password in the mask and continue by clicking on _Login_.

![Login](img/somfy_login.png?raw=true "Login")

Somfy now asks if IP-Symcon as a system can read out personal devices, control Somfy devices and read out the status of the devices.
Here you have to confirm with _Yes_ to allow IP-Symcon to control the TaHoma Box and thus also to control the Somfy devices.

![Approval](img/genehmigung.png?raw=true "approval")

A confirmation by IP-Symcon appears that the authentication was successful,
 
![Success](img/sucess.png?raw=true "Success")

then the browser window can be closed and you return to IP-Symcon.
Back at the Configure Interface window, go to _Next_

Now we open the discovery instance in the object tree under _Discovery instances_. Here we select the TaHoma account and choose _Create_.

![Discovery](img/discovery1_en.png?raw=true "discoverywindow")


### d. Setup of the configurator module

Now we switch to the instance _**TaHoma**_ (type TaHoma Configurator) in the object tree under _Configurator Instances_.

![config](img/config_en.png?raw=true "config")

All devices that are registered with Somfy under the account and supported by the Somfy API are listed here.

A single device can be created by marking the device and pressing the _Create_ button. The configurator then creates a device instance.

### e. Device instance setup
A manual setup of a device module is not necessary, this is done via the configurator. If necessary, only the query interval has to be adjusted in the device module; the other fields, in particular the serial number (this is the identification of the device) and the device type ID (which controls which variables are created) are specified by the configurator.


## 4. Function reference

Öpen
```php
TAHOMA_Open(integer $InstanceID)
``` 
Parameter _$InstanceID_ ObjektID TaHoma device

Close
```php
TAHOMA_Close(integer $InstanceID)
``` 
Parameter _$InstanceID_ ObjektID TaHoma device

Stop
```php
TAHOMA_Stop(integer $InstanceID)
``` 
Parameter _$InstanceID_ ObjektID TaHoma device

Position
```php
TAHOMA_Position(integer $InstanceID, integer $Position)
``` 
Parameter _$InstanceID_ ObjektID des TaHoma Geräts
Parameter _$Position_ Position 0 -100

Query status and device parameters
```php
TAHOMA_RequestStatus(integer $InstanceID)
``` 
Parameter _$InstanceID_ ObjektID des TaHoma Geräts

Returns the status of the device insofar as the device has a device status. An array is returned with the device parameters and supported commands for the device.

## 5. Configuration:




## 6. Annnex

###  GUIDs und Data Flow:

#### TaHoma Cloud:

GUID: `{6F83CEDB-BC40-63BB-C209-88D6B252C9FF}` 


#### TaHoma Device:

GUID: `{4434685E-551F-D887-3163-006833D318E3}` 
