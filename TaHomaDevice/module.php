<?php
declare(strict_types=1);

require_once(__DIR__ . "/../bootstrap.php");
require_once __DIR__ . '/../libs/ProfileHelper.php';
require_once __DIR__ . '/../libs/ConstHelper.php';

use Fonzo\TaHoma\TaHoma;

class TaHomaDevice extends IPSModule
{
    use ProfileHelper;

    private const ROLLER_SHUTTER_POSITIONABLE_STATEFUL_ROOF = 'roller_shutter_positionable_stateful_roof';
    private const EXTERIOR_BLIND_POSITIONABLE_STATEFUL_GENERIC = 'exterior_blind_positionable_stateful_generic';
    private const ROLLER_SHUTTER_DISCRETE_GENERIC = 'roller_shutter_discrete_generic';
    private const ROLLER_SHUTTER_POSITIONABLE_STATEFUL_DUAL = 'roller_shutter_positionable_stateful_dual';
    private const ROLLER_SHUTTER_POSITIONABLE_STATEFUL_RS100 = 'roller_shutter_positionable_stateful_rs100';
    private const ROLLER_SHUTTER = 'roller_shutter'; // Rollladen
    private const EXTERIOR_BLIND = 'exterior_blind'; // Außenjalousie

    // helper properties
    private $position = 0;

    public function Create()
    {
        //Never delete this line!
        parent::Create();

        //Connect to available splitter or create a new one
        $this->ConnectParent('{6F83CEDB-BC40-63BB-C209-88D6B252C9FF}');

        $this->RegisterPropertyString('SiteID', '');
        $this->RegisterPropertyString('DeviceID', '');
        $this->RegisterPropertyString('Type', '');
        $this->RegisterAttributeString('categories', '[]');
        $this->RegisterAttributeString('capabilities', '[]');
        $this->RegisterAttributeString('states', '[]');
        $this->RegisterAttributeBoolean('available', true);
        $this->RegisterPropertyInteger('updateinterval', 5);
        $this->RegisterAttributeInteger('ControlShutter', 1);
        $this->RegisterAttributeInteger('position', 0);
        $this->RegisterAttributeBoolean('low_speed', false);
        $this->RegisterAttributeBoolean('low_speed_enabled', false);
        $this->RegisterTimer('TaHomaUpdate', 0, 'TAHOMA_UpdateStatus(' . $this->InstanceID . ');');
    }

    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {

        switch ($Message) {
            case IM_CHANGESTATUS:
                if ($Data[0] === IS_ACTIVE) {
                    $this->ApplyChanges();
                }
                break;

            case IPS_KERNELMESSAGE:
                if ($Data[0] === KR_READY) {
                    $this->ApplyChanges();
                }
                break;

            default:
                break;
        }
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();

        if (IPS_GetKernelRunlevel() !== KR_READY) {
            return;
        }
        $this->ValidateConfiguration();

    }

    private function ValidateConfiguration()
    {
        $SiteID = $this->ReadPropertyString('SiteID');
        $DeviceID = $this->ReadPropertyString('DeviceID');
        $Type = $this->ReadPropertyString('Type');

        if ($SiteID == '' || $DeviceID == '' || $Type == '') {
            $this->SetStatus(205);
        } elseif ($SiteID != '' && $DeviceID != '' && $Type != '') {
            $this->RegisterVariables();
            $this->SetStatus(IS_ACTIVE);
        }
    }


    /** @noinspection PhpMissingParentCallCommonInspection */

    private function RegisterVariables(): void
    {
        // $type = $this->ReadPropertyString('Type');
        $data = $this->RequestStatus();
        if($data != [])
        {
            $categories = $data['categories'];
            $this->WriteAttributeString('categories', json_encode($categories));
            $states = $data['states'];
            $this->WriteAttributeString('states', json_encode($states));
            $capabilities = $data['capabilities'];
            $this->WriteAttributeString('capabilities', json_encode($capabilities));

            if (count($data['states']) > 0) {
                $tahoma_interval = $this->ReadPropertyInteger('updateinterval');
                $this->SetTaHomaInterval($tahoma_interval);
            }
            foreach ($capabilities as $capability) {
                if ($capability['name'] === 'position') {
                    $this->SetupVariable(
                        'position', $this->Translate('Position'), '~Intensity.100', $this->_getPosition(), VARIABLETYPE_INTEGER, true, true
                    );
                }
            }

            //Shutter Control Variable
            $this->RegisterProfileAssociation(
                'Tahoma.Control', 'Move', '', '', 0, 3, 0, 0, VARIABLETYPE_INTEGER, [
                    [0, $this->Translate('Open'), 'HollowDoubleArrowUp', -1],
                    [1, $this->Translate('Stop'), 'Close', -1],
                    [2, $this->Translate('Close'), 'HollowDoubleArrowDown', -1]]
            );
            $this->SetupVariable(
                'ControlShutter', $this->Translate('Control'), 'Tahoma.Control', $this->_getPosition(), VARIABLETYPE_INTEGER, true, true
            );

            $this->SetupVariable(
                'low_speed', $this->Translate('low speed'), '~Switch', $this->_getPosition(), VARIABLETYPE_BOOLEAN, true, false
            );
        }
    }

    /** Variable anlegen / löschen
     *
     * @param $ident
     * @param $name
     * @param $profile
     * @param $position
     * @param $vartype
     * @param $visible
     *
     * @return bool|int
     */
    protected function SetupVariable($ident, $name, $profile, $position, $vartype, $enableaction, $visible = false)
    {
        $objid = false;
        if ($visible) {
            $this->SendDebug('TaHoma Variable:', 'Variable with Ident ' . $ident . ' is visible', 0);
        } else {
            $visible = $this->ReadAttributeBoolean($ident . '_enabled');
            $this->SendDebug('TaHoma Variable:', 'Variable with Ident ' . $ident . ' is shown ' . print_r($visible, true), 0);
        }
        if ($visible == true) {
            switch ($vartype) {
                case VARIABLETYPE_BOOLEAN:
                    $objid = $this->RegisterVariableBoolean($ident, $name, $profile, $position);
                    $value = $this->ReadAttributeBoolean($ident);
                    break;
                case VARIABLETYPE_INTEGER:
                    $objid = $this->RegisterVariableInteger($ident, $name, $profile, $position);
                    $value = $this->ReadAttributeInteger($ident);
                    break;
                case VARIABLETYPE_FLOAT:
                    $objid = $this->RegisterVariableFloat($ident, $name, $profile, $position);
                    $value = $this->ReadAttributeFloat($ident);
                    break;
                case VARIABLETYPE_STRING:
                    $objid = $this->RegisterVariableString($ident, $name, $profile, $position);
                    $value = $this->ReadAttributeString($ident);
                    break;
            }
            $this->SetValue($ident, $value);
            if ($enableaction) {
                $this->EnableAction($ident);
            }
        } else {
            $objid = @$this->GetIDForIdent($ident);
            if ($objid > 0) {
                $this->UnregisterVariable($ident);
            }
        }
        return $objid;
    }

    private function SetTaHomaInterval($tahoma_interval): void
    {
        // todo Rate limit quota violation. Quota limit  exceeded
        $interval = $tahoma_interval * 1000; // min update interval from somfy not known
        $this->SetTimerInterval('TaHomaUpdate', $interval);
    }

    /** @noinspection PhpMissingParentCallCommonInspection */

    /**
     * return incremented position
     * @return int
     */
    private function _getPosition()
    {
        $this->position++;
        return $this->position;
    }

    public function RequestAction($Ident, $Value)
    {
        if ($Ident === 'ControlShutter') {
            switch ($Value) {
                case 0: // open
                    $this->Open();
                    break;
                case 1: // stop
                    $this->Stop();
                    break;
                case 2: // close
                    $this->Close();
                    break;
            }
        }
        if ($Ident === 'position') {
            $this->Position($Value);
        }
        if ($Ident === 'low_speed') {
            $this->SetLowSpeedMode($Value);
        }
    }

    public function Open()
    {
        $this->SendCommand('open');
        $this->SetValue('ControlShutter', 0);
    }

    public function SendCommand(string $name)
    {
        $result = json_decode($this->SendDataToParent(json_encode([
            'DataID' => '{656566E9-4C78-6C4C-2F16-63CDD4412E9E}',
            'Endpoint' => '/v1/device/' . $this->ReadPropertyString('DeviceID') . '/exec',
            'Payload' => json_encode([
                'name' => $name,
                'parameters' => []
            ])
        ])));
        return $result;
    }

    public function SendCustomCommand(string $name, string $parameter = null)
    {
        if ($parameter !== null) {
            $parameter = json_decode($parameter, true);
        }
        else{
            $parameter = [];
        }
        $result = json_decode($this->SendDataToParent(json_encode([
            'DataID' => '{656566E9-4C78-6C4C-2F16-63CDD4412E9E}',
            'Endpoint' => '/v1/device/' . $this->ReadPropertyString('DeviceID') . '/exec',
            'Payload' => json_encode([
                'name' => $name,
                'parameters' => $parameter
            ])
        ])));
        return $result;
    }

    public function Stop()
    {
        $this->SendCommand('stop');
        $this->SetValue('ControlShutter', 1);
    }

    public function Close()
    {
        $this->SendCommand('close');
        $this->SetValue('ControlShutter', 2);
    }

    public function Position(int $position)
    {
        $low_speed = $this->ReadAttributeBoolean('low_speed');
        if($low_speed)
        {
            $name = 'position_low_speed';
        }
        else{
            $name = 'position';
        }

        $result = json_decode($this->SendDataToParent(json_encode([
            'DataID' => '{656566E9-4C78-6C4C-2F16-63CDD4412E9E}',
            'Endpoint' => '/v1/device/' . $this->ReadPropertyString('DeviceID') . '/exec',
            'Payload' => json_encode([
                'name' => $name,
                'parameters' => [['name' => 'position', 'value' => $position]]
            ])
        ])));
        $this->SetValue('position', $position);
        return $result;
    }

    public function UpdateStatus()
    {
        $data = $this->RequestStatus();
        if($data != [])
        {
            if (count($data['states']) > 0) {
                foreach ($data['states'] as $state) {
                    if ($state['name'] === 'position') {
                        $this->SetValue('position', $state['value']);
                        $this->WriteAttributeInteger('position', intval($state['value']));
                    }
                }
            }
        }
    }


    public function SetLowSpeedMode(bool $value)
    {
        $this->WriteAttributeBoolean('low_speed', $value);
        if ($value) {
            $this->SendDebug('TaHoma Low Speed Mode', 'enabled', 0);
        } else {
            $this->SendDebug('TaHoma Low Speed Mode', 'disabled', 0);
        }
        $this->RegisterVariables();
    }

    public function SetWebFrontVariable(string $ident, bool $value)
    {
        $this->WriteAttributeBoolean($ident, $value);
        if ($value) {
            $this->SendDebug('TaHoma Webfront Variable', $ident . ' enabled', 0);
        } else {
            $this->SendDebug('TaHoma Webfront Variable', $ident . ' disabled', 0);
        }
        $this->RegisterVariables();
    }

    public function GetCommandList()
    {
        $type = $this->ReadPropertyString('Type');

        $form = [];
        if ($type == 1) {
            $form = [
                [
                    'type' => 'Button',
                    'caption' => 'Update',
                    'onClick' => 'TAHOMA_RequestStatus($id);'
                ],
                [
                    'type' => 'Button',
                    'caption' => 'Identify',
                    'onClick' => 'TAHOMA_SendCommand($id, \'identify\');'
                ],
                [
                    'type' => 'RowLayout',
                    'items' => [
                        [
                            'type' => 'Button',
                            'caption' => 'open',
                            'onClick' => 'TAHOMA_SendCommand($id, \'open\');'
                        ],
                        [
                            'type' => 'Button',
                            'caption' => 'stop',
                            'onClick' => 'TAHOMA_SendCommand($id, \'stop\');'
                        ],
                        [
                            'type' => 'Button',
                            'caption' => 'close',
                            'onClick' => 'TAHOMA_SendCommand($id, \'close\');'
                        ]
                    ]
                ]];
        }
        return $form;
    }

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

    /***********************************************************
     * Configuration Form
     ***********************************************************/

    /**
     * return form configurations on configuration step
     * @return array
     */
    protected function FormHead()
    {
        $data = $this->CheckRequest();
        $device_id = $this->ReadPropertyString('DeviceID');
        if ($data != false) {
            $form = [
                [
                    'type' => 'Label',
                    'label' => 'Device ID:'
                ],
                [
                    'type' => 'Label',
                    'label' => $device_id
                ],
                [
                    'type' => 'Label',
                    'label' => 'Type:'
                ],
                [
                    'type' => 'Label',
                    'label' => $this->Translate($this->ReadPropertyString('Type'))
                ],
                [
                    'type' => 'Label',
                    'label' => 'Update interval in seconds:'
                ],
                [
                    'name' => 'updateinterval',
                    'type' => 'NumberSpinner',
                    'minimum' => 5,
                    'suffix' => 'seconds',
                    'visible' => true,
                    'caption' => 'seconds'
                ]
            ];
        } else {
            $form = [
                [
                    'type' => 'Label',
                    'label' => 'This device can only created by the TaHoma configurator, please use the TaHoma configurator for creating TaHoma devices.'
                ]
            ];
        }
        return $form;
    }

    private function CheckRequest()
    {
        $SiteID = $this->ReadPropertyString('SiteID');
        $DeviceID = $this->ReadPropertyString('DeviceID');
        $Type = $this->ReadPropertyString('Type');
        $check = false;
        if ($SiteID == '' || $DeviceID == '' || $Type == '') {
            $this->SetStatus(205);
        } elseif ($SiteID != '' && $DeviceID != '' && $Type != '') {
            $data = $this->RequestStatus();
            $check = true;
            if($data == [])
            {
                $check = false;
            }
        }
        return $check;
    }

    public function RequestStatus()
    {
        $data = json_decode($this->SendDataToParent(json_encode([
            'DataID' => '{656566E9-4C78-6C4C-2F16-63CDD4412E9E}',
            'Endpoint' => '/v1/device/' . $this->ReadPropertyString('DeviceID'),
            'Payload' => ''
        ])), true);
        $this->SendDebug('TaHoma Request Response:', json_encode($data), 0);
        $check = $this->CheckResponse($data);
        if($check)
        {
            return $data;
        }
        else{
            return [];
        }
    }

    private function CheckResponse($data)
    {
        $check = false;
        If(isset($data['categories']))
        {
            $categories = $data['categories'];
            $this->WriteAttributeString('categories', json_encode($categories));
            $this->SendDebug('TaHoma categories:', json_encode($categories), 0);
        }
        If(isset($data['states']))
        {
            $states = $data['states'];
            $this->WriteAttributeString('states', json_encode($states));
            $this->SendDebug('TaHoma states:', json_encode($states), 0);
        }
        If(isset($data['capabilities']))
        {
            $capabilities = $data['capabilities'];
            $this->WriteAttributeString('capabilities', json_encode($capabilities));
            $this->SendDebug('TaHoma capabilities:', json_encode($capabilities), 0);
        }
        If(isset($data['available']))
        {
            $available = $data['available'];
            $this->WriteAttributeBoolean('available', $available);
            $this->SendDebug('TaHoma available:', json_encode($available), 0);
        }
        If(isset($data['categories']) && isset($data['states']) && isset($data['capabilities']) && isset($data['available']))
        {
            $check = true;
        }
        return $check;
    }

    /**
     * return form actions by token
     * @return array
     */
    protected function FormActions()
    {


        $Type = $this->ReadPropertyString('Type');
        $form = [];
        if ($Type == self::EXTERIOR_BLIND_POSITIONABLE_STATEFUL_GENERIC || $Type == self::ROLLER_SHUTTER_DISCRETE_GENERIC || $Type == self::ROLLER_SHUTTER_POSITIONABLE_STATEFUL_DUAL || $Type == self::ROLLER_SHUTTER_POSITIONABLE_STATEFUL_ROOF || $Type == self::ROLLER_SHUTTER_POSITIONABLE_STATEFUL_RS100) {
            $form = [
                [
                    'type' => 'Label',
                    'label' => 'Change to low speed mode'
                ],
                [
                    'type'    => 'RowLayout',
                    'visible' => true,
                    'items'   => [
                        [
                            'type'    => 'CheckBox',
                            'name'    => 'low_speed',
                            'visible' => true,
                            'caption' => 'set low speed mode',
                            'value'   => $this->ReadAttributeBoolean('low_speed'),
                            'onClick' => 'TAHOMA_SetLowSpeedMode($id, $low_speed);'],
                        [
                            'name'     => 'low_speed_enabled',
                            'type'     => 'CheckBox',
                            'caption'  => 'Create Variable for Webfront',
                            'visible'  => true,
                            'value'    => $this->ReadAttributeBoolean('low_speed_enabled'),
                            'onChange' => 'TAHOMA_SetWebFrontVariable($id, "low_speed_enabled", $low_speed_enabled);'],]],
                [
                    'type' => 'Button',
                    'caption' => 'Update',
                    'onClick' => 'TAHOMA_RequestStatus($id);'
                ],
                [
                    'type' => 'Button',
                    'caption' => 'Identify',
                    'onClick' => 'TAHOMA_SendCommand($id, \'identify\');'
                ],
                [
                    'type' => 'RowLayout',
                    'items' => [
                        [
                            'type' => 'Button',
                            'caption' => 'open',
                            'onClick' => 'TAHOMA_SendCommand($id, \'open\');'
                        ],
                        [
                            'type' => 'Button',
                            'caption' => 'stop',
                            'onClick' => 'TAHOMA_SendCommand($id, \'stop\');'
                        ],
                        [
                            'type' => 'Button',
                            'caption' => 'close',
                            'onClick' => 'TAHOMA_SendCommand($id, \'close\');'
                        ]
                    ]
                ]
            ];
        } else if ($Type == 2) {
            $form = [
                [
                    'type' => 'Label',
                    'label' => 'Change to low speed mode'
                ],
                [
                    'type'    => 'RowLayout',
                    'visible' => true,
                    'items'   => [
                        [
                            'type'    => 'CheckBox',
                            'name'    => 'low_speed',
                            'visible' => true,
                            'caption' => 'set low speed mode',
                            'value'   => $this->ReadAttributeBoolean('low_speed'),
                            'onClick' => 'TAHOMA_SetLowSpeedMode($id, $low_speed);'],
                        [
                            'name'     => 'low_speed_enabled',
                            'type'     => 'CheckBox',
                            'caption'  => 'Create Variable for Webfront',
                            'visible'  => true,
                            'value'    => $this->ReadAttributeBoolean('low_speed_enabled'),
                            'onChange' => 'TAHOMA_SetWebFrontVariable($id, "low_speed_enabled", $low_speed_enabled);'],]],
                [
                    'type' => 'Button',
                    'caption' => 'Update',
                    'onClick' => 'TAHOMA_RequestStatus($id);'
                ],
                [
                    'type' => 'Button',
                    'caption' => 'Identify',
                    'onClick' => 'TAHOMA_SendCommand($id, \'identify\');'
                ],
                [
                    'type' => 'RowLayout',
                    'items' => [
                        [
                            'type' => 'Button',
                            'caption' => 'open',
                            'onClick' => 'TAHOMA_SendCommand($id, \'open\');'
                        ],
                        [
                            'type' => 'Button',
                            'caption' => 'stop',
                            'onClick' => 'TAHOMA_SendCommand($id, \'stop\');'
                        ],
                        [
                            'type' => 'Button',
                            'caption' => 'close',
                            'onClick' => 'TAHOMA_SendCommand($id, \'close\');'
                        ]
                    ]
                ]
            ];
        } else if ($Type == 3) {
            $form = [
                [
                    'type' => 'Label',
                    'label' => 'Change to low speed mode'
                ],
                [
                    'type'    => 'RowLayout',
                    'visible' => true,
                    'items'   => [
                        [
                            'type'    => 'CheckBox',
                            'name'    => 'low_speed',
                            'visible' => true,
                            'caption' => 'set low speed mode',
                            'value'   => $this->ReadAttributeBoolean('low_speed'),
                            'onClick' => 'TAHOMA_SetLowSpeedMode($id, $low_speed);'],
                        [
                            'name'     => 'low_speed_enabled',
                            'type'     => 'CheckBox',
                            'caption'  => 'Create Variable for Webfront',
                            'visible'  => true,
                            'value'    => $this->ReadAttributeBoolean('low_speed_enabled'),
                            'onChange' => 'TAHOMA_SetWebFrontVariable($id, "low_speed_enabled", $low_speed_enabled);'],]],
                [
                    'type' => 'Button',
                    'caption' => 'Update',
                    'onClick' => 'TAHOMA_RequestStatus($id);'
                ],
                [
                    'type' => 'Button',
                    'caption' => 'Identify',
                    'onClick' => 'TAHOMA_SendCommand($id, \'identify\');'
                ],
                [
                    'type' => 'RowLayout',
                    'items' => [
                        [
                            'type' => 'Button',
                            'caption' => 'open',
                            'onClick' => 'TAHOMA_SendCommand($id, \'open\');'
                        ],
                        [
                            'type' => 'Button',
                            'caption' => 'stop',
                            'onClick' => 'TAHOMA_SendCommand($id, \'stop\');'
                        ],
                        [
                            'type' => 'Button',
                            'caption' => 'close',
                            'onClick' => 'TAHOMA_SendCommand($id, \'close\');'
                        ]
                    ]
                ]
            ];
        }else{

            $form = [
                [
                    'type' => 'Label',
                    'label' => 'Change to low speed mode'
                ],
                [
                    'type'    => 'RowLayout',
                    'visible' => true,
                    'items'   => [
                        [
                            'type'    => 'CheckBox',
                            'name'    => 'low_speed',
                            'visible' => true,
                            'caption' => 'set low speed mode',
                            'value'   => $this->ReadAttributeBoolean('low_speed'),
                            'onClick' => 'TAHOMA_SetLowSpeedMode($id, $low_speed);'],
                        [
                            'name'     => 'low_speed_enabled',
                            'type'     => 'CheckBox',
                            'caption'  => 'Create Variable for Webfront',
                            'visible'  => true,
                            'value'    => $this->ReadAttributeBoolean('low_speed_enabled'),
                            'onChange' => 'TAHOMA_SetWebFrontVariable($id, "low_speed_enabled", $low_speed_enabled);'],]],
                [
                    'type' => 'Button',
                    'caption' => 'Update',
                    'onClick' => 'TAHOMA_RequestStatus($id);'
                ],
                [
                    'type' => 'Button',
                    'caption' => 'Identify',
                    'onClick' => 'TAHOMA_SendCommand($id, \'identify\');'
                ],
                [
                    'type' => 'TestCenter',
                ]
            ];
        }

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
                'code' => IS_CREATING,
                'icon' => 'inactive',
                'caption' => 'Creating instance.'
            ],
            [
                'code' => IS_ACTIVE,
                'icon' => 'active',
                'caption' => 'TaHoma device created.'
            ],
            [
                'code' => IS_INACTIVE,
                'icon' => 'inactive',
                'caption' => 'interface closed.'
            ],
            [
                'code' => 201,
                'icon' => 'inactive',
                'caption' => 'Please follow the instructions.'
            ],
            [
                'code' => 202,
                'icon' => 'error',
                'caption' => 'Device code must not be empty.'
            ],
            [
                'code' => 203,
                'icon' => 'error',
                'caption' => 'Device code has not the correct lenght.'
            ],
            [
                'code' => 204,
                'icon' => 'error',
                'caption' => 'no type selected.'
            ],
            [
                'code' => 205,
                'icon' => 'error',
                'caption' => 'This device can only created by the TaHoma configurator, please use the TaHoma configurator for creating TaHoma devices.'
            ]
        ];

        return $form;
    }
}
