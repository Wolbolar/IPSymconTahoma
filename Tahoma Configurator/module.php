<?
declare(strict_types=1);

require_once __DIR__ . '/../libs/ConstHelper.php';
require_once __DIR__ . '/../libs/TahomaBufferHelper.php';
require_once __DIR__ . '/../libs/TahomaDebugHelper.php';

class TahomaConfigurator extends IPSModule
{
	use TahomaBufferHelper,
		TahomaDebugHelper;

	public function Create()
	{
		//Never delete this line!
		parent::Create();

		// 1. connect Tahoma IO
		$this->ConnectParent("{469C3076-0077-7214-C7EE-72F8694AADE7}");
		$this->RegisterAttributeBoolean("ExtendedDebug", false);
	}

	/**
	 * Interne Funktion des SDK.
	 */
	public function ApplyChanges()
	{
		//Never delete this line!
		parent::ApplyChanges();
		$this->ValidateConfiguration();
	}

	private function ValidateConfiguration()
	{
		$this->SetStatus(IS_ACTIVE);
	}

	public function ExtendedDebug(bool $debug)
	{
		$this->WriteAttributeBoolean("ExtendedDebug", $debug);
		$this->LogMessage("Set Debug Mode to Extended", KL_DEBUG);
	}

	private function GetExtendedDebugMode()
	{
		$debug = $this->ReadAttributeBoolean("ExtendedDebug");
		return $debug;
	}


	/** Get Config Tahoma
	 *
	 * @return array
	 */
	private function Get_ListConfiguration()
	{
		$room_ips_id = 1;
		$config_list = [];
		$extended_debug = $this->GetExtendedDebugMode();
		$TahomaInstanceIDList = IPS_GetInstanceListByModuleID('{2C226E59-C0AF-C417-7185-C27D3DDE4060}'); // Tahoma Devices
		$devices = $this->SendData('GET', 'devices');
		// $this->SendDebug('Devices', $devices, 0);

		if ($devices != "Not authenticated") {
			$data = json_decode($devices);
			foreach ($data as $key => $device) {
				$instanceID = 0;
				$label = "";
				if (property_exists($device, "label")) {
					$label = $device->label;
					if ($extended_debug) {
						$this->SendDebug('Label', $label, 0);
					}
				}
				$deviceid = "";
				$deviceURL = "";
				if (property_exists($device, "deviceURL")) {
					$deviceURL = $device->deviceURL;
					if ($extended_debug) {
						$deviceid = explode("/", $deviceURL)[3];
						$this->SendDebug('Device ID', $deviceid, 0);
					}
				}

				if (property_exists($device, "states")) {
					$states = $device->states;
					foreach ($states as $key_state => $state) {
						$name = $state->name;
						$value = $state->value;
						if ($extended_debug) {
							$this->SendDebug('State', $name . ": " . $value, 0);
						}
					}
				}
				$oid = "";
				if (property_exists($device, "oid")) {
					$oid = $device->oid;
					if ($extended_debug) {
						$this->SendDebug('Device OID', $oid, 0);
					}
				}
				$type = 0;
				if (property_exists($device, "type")) {
					$type = $device->type;
					if ($extended_debug) {
						$this->SendDebug('Type', $type, 0);
					}
				}
				$typename = "";
				if (property_exists($device, "definition")) {
					$typename = $device->definition->type;
					if ($extended_debug) {
						$this->SendDebug('Typename', $typename, 0);
					}
				}
				$uiClass = "";
				if (property_exists($device, "uiClass")) {
					$uiClass = $device->uiClass;
					if ($extended_debug) {
						$this->SendDebug('Class', $uiClass, 0);
					}
				}
				foreach ($TahomaInstanceIDList as $TahomaInstanceID) {
					if ($oid == IPS_GetProperty($TahomaInstanceID, 'oid')) {
						$instanceID = $TahomaInstanceID;
					}
				}
				$config_list[] = [
					"instanceID" => $instanceID,
					"id" => $deviceid,
					"type" => $this->Translate($typename),
					"label" => $label,
					"device" => $this->Translate($uiClass),
					"oid" => $oid,
					"enocean_id" => $deviceid,
					"location" => [
						$this->Translate('devices'), "Tahoma", $this->Translate('Tahoma Devices'), $label . " (" . $deviceid . ")"
					],
					"create" => [
						[
							"moduleID" => "{67252707-E627-4DFC-07D3-438452F20B23}",
							"configuration" => [
								"device_id" => $deviceid,
								"type" => $type,
								"typename" => $typename,
								"label" => $label,
								"uiClass" => $uiClass,
								"oid" => $oid,
								"enocean_id" => $deviceid,
								"deviceURL" => $deviceURL
							]
						]
					]
				];
			}



		}
		return $config_list;
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
				'type' => 'Image',
				'image' => 'data:image/png;base64, iVBORw0KGgoAAAANSUhEUgAAAJYAAABkCAYAAABkW8nwAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA3BpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMDY3IDc5LjE1Nzc0NywgMjAxNS8wMy8zMC0yMzo0MDo0MiAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDphYjNhMzU4My1kNGIyLTRkMTItODAwYy00OTA5MjQyZWU5MmMiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MURFNUE3RDg3QTRCMTFFOTk0NUJFNzBBNDgxNTdGODgiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MURFNUE3RDc3QTRCMTFFOTk0NUJFNzBBNDgxNTdGODgiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTQgKE1hY2ludG9zaCkiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDpGOUVCQUMwNDlBMzkxMUU0QTQzQ0IyMTI5M0Y4NjVERCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpGOUVCQUMwNTlBMzkxMUU0QTQzQ0IyMTI5M0Y4NjVERCIvPiA8L3Jk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'
			]
		];
		return $form;
	}

	/**
	 * return form actions by token
	 * @return array
	 */
	protected function FormActions()
	{
		$form = [
			[
				'type' => 'Configurator',
				'name' => 'TahomaConfiguration',
				'caption' => 'Tahoma configuration',
				'rowCount' => 20,
				'add' => false,
				'delete' => false,
				'sort' => [
					'column' => 'label',
					'direction' => 'ascending'
				],
				'columns' => [
					[
						'caption' => 'ID',
						'name' => 'id',
						'width' => '200px',
						'visible' => false
					],
					[
						'caption' => 'Label',
						'name' => 'label',
						'width' => 'auto'
					],
					[
						'caption' => 'Type',
						'name' => 'type',
						'width' => '200px'
					],
					[
						'caption' => 'Device',
						'name' => 'device',
						'width' => '200px'
					],
					[
						'caption' => 'OID',
						'name' => 'oid',
						'width' => '350px'
					],
					[
						'caption' => 'ID',
						'name' => 'enocean_id',
						'width' => '250px'
					]
				],
				'values' => $this->Get_ListConfiguration()
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
				'caption' => 'Tahoma configurator created.'
			],
			[
				'code' => 104,
				'icon' => 'inactive',
				'caption' => 'interface closed.'
			],
			[
				'code' => 201,
				'icon' => 'inactive',
				'caption' => 'Please follow the instructions.'
			]
		];

		return $form;
	}

	/** Sendet Eine Anfrage an den IO und liefert die Antwort.
	 * @param string $Method
	 * @param string $command
	 * @return string
	 */
	private function SendData(string $Method, string $command)
	{
		$Data['DataID'] = '{32064D22-5769-C931-CBC7-6DBB7F8D1FCE}';
		$Data['Buffer'] = ['Method' => $Method, 'commandName' => $command, 'Content' => ""];
		$this->SendDebug('Method:', $Method, 0);
		$this->SendDebug('Command:', $command, 0);
		$this->SendDebug('Send:', json_encode($Data), 0);
		$this->SendDebug('Form:', json_last_error_msg(), 0);
		$ResultString = @$this->SendDataToParent(json_encode($Data));
		return $ResultString;
	}


}