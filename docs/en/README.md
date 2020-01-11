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

Control Somfy devices via Somfy Cloud API. 
	  
## 2. Requirements

- IPS 5.2.
- Logitech Harmony Hub on the same network as IP Symcon

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

## 5. Configuration:




## 6. Annnex

