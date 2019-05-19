<?php

class Tahoma extends IPSModule
{

	public function Create()
	{
//Never delete this line!
		parent::Create();

		//These lines are parsed on Symcon Startup or Instance creation
		//You cannot use variables here. Just static values.

		$this->RegisterPropertyString("device_id", "");
		$this->RegisterPropertyInteger("type", 0);
		$this->RegisterPropertyString("typename", "");
		$this->RegisterPropertyString("label", "");
		$this->RegisterPropertyString("uiClass", "");
		$this->RegisterPropertyString("oid", "");
		$this->RegisterPropertyString("enocean_id", "");
		$this->RegisterPropertyString("deviceURL", "");
	}

	/*
	 *
	 */

	public function ApplyChanges()
	{
		//Never delete this line!
		parent::ApplyChanges();


		$this->RegisterVariableInteger("STATE", $this->Translate("State"), "~Switch", 1);
		$this->EnableAction("STATE");

		$this->RegisterVariableInteger("LEVEL", $this->Translate("Position"), "~Shutter.Position.100", 2);
		$this->EnableAction("LEVEL");

		$this->RegisterVariableInteger("SHUTTERCONTROL", $this->Translate("Control"), "ShutterControl.Tahoma", 3);
		$this->EnableAction("SHUTTERCONTROL");

		$this->ValidateConfiguration();

	}

	/**
	 * Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
	 * Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wiefolgt zur Verfügung gestellt:
	 *
	 *
	 */

	private function ValidateConfiguration()
	{

		// Status Aktiv
		$this->SetStatus(IS_ACTIVE);

	}

	public function RequestAction($Ident, $Value)
	{
		switch ($Ident) {
			case "STATE":
				$this->PowerToggle($Value);
				break;
			case "LEVEL":
				$this->SetLevel($Value);
				break;
			case "SHUTTERCONTROL":
				if ($Value == 0) {
					$this->Down();
				} elseif ($Value == 1) {
					$this->Stop();
				} elseif ($Value == 2) {
					$this->Up();
				}
				break;
			default:
				$this->SendDebug("Tahoma", "Invalid ident", 0);
		}
	}

	/** Send a request to the splitter and get the response.
	 * @param string $Method
	 * @param string $command
	 * @return string
	 */
	private function SendData(string $Method, string $command, string $parameters = null)
	{
		if (empty($parameters)) {
			$parameters = "";
		}
		$Data['DataID'] = '{338DCDF5-2F2C-0199-4588-2390F2DF8A77}';
		$Data['Buffer'] = ['Method' => $Method, 'commandName' => $command, 'deviceURL' => $this->ReadPropertyString("deviceURL"), 'parameters' => $parameters];
		$this->SendDebug('Method:', $Method, 0);
		$this->SendDebug('Command:', $command, 0);
		$this->SendDebug('Send:', json_encode($Data), 0);
		$this->SendDebug('Form:', json_last_error_msg(), 0);
		$ResultString = @$this->SendDataToParent(json_encode($Data));
		return $ResultString;
	}

	public function PowerToggle($value)
	{
		if ($value) {
			$this->PowerOn();
		} else {
			$this->PowerOff();
		}
	}

	public function PowerOn()
	{
		$this->SendData('SET', 'on');
	}

	public function PowerOff()
	{
		$this->SendData('SET', 'off');
	}

	public function Up()
	{
		$this->SendData('SET', 'ON');
	}

	public function Down()
	{
		$this->SendData('SET', 'ON');
	}

	public function Stop()
	{
		$this->SendData('SET', 'ON');
	}

	public function SetLevel($level)
	{
		$level = $level/100;
		$this->SendData('SET', 'ON', $level);
	}

	public function Close()
	{
		$this->SendData('SET', 'ON');
	}

	public function Open()
	{
		$this->SendData('SET', 'ON');
	}

	public function My()
	{
		$this->SendData('SET', 'ON');
	}

	/*
	 *


eingebundenes Zubehör
- schalten
- Status
- abhängige Szenarien

Gruppenbildung/Szenarien
- zeitliches bedienen/schalten von Gruppen
- Bedienung manuell oder nach Kalender
- Timer bis max. 2h (bis Szenario ausgeführt wird)

manuelle Bedienung von Gruppen z.B.:
- Timer bis max. 2h (bis die
- bei Zu-> Timer für ZU setzen (bis max. 2h)
	 */


	public function ReceiveData($JSONString)
	{
		$data = json_decode($JSONString);
		$this->SendDebug("Tahoma Recieve:", $data, 0);
		/*
		$actionparameter = NULL;
		$payload = $data->Buffer;
		$action = "";
		$device = "";
		$room = "";
		$actionparameter = "";
		$recipe = "";
		if (property_exists($payload, 'action')) {
			$action = $payload->action;
			$this->SendDebug("NEEO Recieve:", "Action: " . $action, 0);
		}
		if (property_exists($payload, 'device')) {
			$device = $payload->device;
			$this->SendDebug("NEEO Recieve:", "Device: " . $device, 0);
		}
		if (property_exists($payload, 'room')) {
			$room = $payload->room;
			$this->SendDebug("NEEO Recieve:", "Room: " . $room, 0);
		}
		if (property_exists($payload, 'actionparameter')) {
			$actionparameter = $payload->actionparameter;
			$this->SendDebug("NEEO Recieve:", "Action parameter: " . json_encode($actionparameter), 0);
		}
		if (property_exists($payload, 'recipe')) {
			$recipe = $payload->recipe;
			$this->SendDebug("NEEO Recieve:", "Recipe: " . $recipe, 0);
		}

		$this->WriteValues($action, $actionparameter, $device, $recipe);
		*/
	}

	protected function WriteValues($action, $actionparameter = NULL, $device = NULL, $recipe = NULL)
	{
		$recipes = $this->ReadPropertyString("recipes");
		if ($recipes != "") {

			$uid = $this->GetRecipeUID($recipe);
			$recipe_ident = $this->CreateIdent($uid);

			if ($action == "launch") {
				$this->SetValue("LaunchRecipe_" . $recipe_ident, true);
				$this->SendDebug("NEEO Recieve:", "Recipe " . $recipe . " started", 0);
			}
			if ($action == "poweroff") {
				$this->SetValue("LaunchRecipe_" . $recipe_ident, false);
				$this->SendDebug("NEEO Recieve:", "Recipe " . $recipe . " stopped", 0);
			}
		}
	}


	//Profile
	protected function RegisterProfile($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits, $Vartype)
	{

		if (!IPS_VariableProfileExists($Name)) {
			IPS_CreateVariableProfile($Name, $Vartype); // 0 boolean, 1 int, 2 float, 3 string,
		} else {
			$profile = IPS_GetVariableProfile($Name);
			if ($profile['ProfileType'] != $Vartype)
				$this->SendDebug("BMW:", "Variable profile type does not match for profile " . $Name, 0);
		}

		IPS_SetVariableProfileIcon($Name, $Icon);
		IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
		IPS_SetVariableProfileDigits($Name, $Digits); //  Nachkommastellen
		IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize); // string $ProfilName, float $Minimalwert, float $Maximalwert, float $Schrittweite
	}

	protected function RegisterProfileAssociation($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $Stepsize, $Digits, $Vartype, $Associations)
	{
		if (sizeof($Associations) === 0) {
			$MinValue = 0;
			$MaxValue = 0;
		}

		$this->RegisterProfile($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $Stepsize, $Digits, $Vartype);

		//boolean IPS_SetVariableProfileAssociation ( string $ProfilName, float $Wert, string $Name, string $Icon, integer $Farbe )
		foreach ($Associations as $Association) {
			IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
		}

	}

	/***********************************************************
	 * Configuration Form
	 ***********************************************************/

	/**
	 * build configuration form
	 * @return string
	 */
	public function GetConfigurationForm()
	{
		// return current form
		return json_encode([
			'elements' => $this->FormHead(),
			'actions' => $this->FormActions(),
			'status' => $this->FormStatus()
		]);
	}

	/**
	 * return form configurations on configuration step
	 * @return array
	 */
	protected function FormHead()
	{
		$form = [
			[
				'name' => 'devicetype',
				'type' => 'Select',
				'caption' => 'device type',
				'options' => [
					[
						'label' => 'Please choose',
						'value' => -1
					],
					[
						'label' => 'Type 1',
						'value' => 0
					],
					[
						'label' => 'Type 2',
						'value' => 1
					]
				]
			],
			[
				'type' => 'Label',
				'label' => 'IP adress'
			],
			[
				'name' => 'ip',
				'type' => 'ValidationTextBox',
				'caption' => 'IP adress'
			]
		];
		return $form;
	}

	/**
	 * return form actions
	 * @return array
	 */
	protected function FormActions()
	{
		$form = [
			[
				'type' => 'Label',
				'label' => 'Update'
			],
			[
				'type' => 'Button',
				'label' => 'Update State',
				'onClick' => 'Tahoma_Update($id);'
			]
		];

		return $form;
	}

	/**
	 * return from status
	 * @return array
	 */
	protected function FormStatus()
	{
		$form = [
			[
				'code' => 101,
				'icon' => 'inactive',
				'caption' => 'Creating instance.'
			],
			[
				'code' => 102,
				'icon' => 'active',
				'caption' => 'Device created.'
			],
			[
				'code' => 104,
				'icon' => 'inactive',
				'caption' => 'interface closed.'
			],
			[
				'code' => 201,
				'icon' => 'error',
				'caption' => 'special errorcode'
			]
		];

		return $form;
	}

	//Add this Polyfill for IP-Symcon 4.4 and older
	protected function SetValue($Ident, $Value)
	{

		if (IPS_GetKernelVersion() >= 5) {
			parent::SetValue($Ident, $Value);
		} else {
			SetValue($this->GetIDForIdent($Ident), $Value);
		}
	}
}
