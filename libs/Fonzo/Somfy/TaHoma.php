<?php

namespace Fonzo\TaHoma;

class TaHoma
{

    public $DeviceInstanceID;

	function __construct($DeviceInstanceID)
	{

		$this->DeviceInstanceID = $DeviceInstanceID;
	}

	public function GetParent()
	{
		$instance = IPS_GetInstance($this->DeviceInstanceID);//array
		return ($instance['ConnectionID'] > 0) ? $instance['ConnectionID'] : false;//ConnectionID
	}

}