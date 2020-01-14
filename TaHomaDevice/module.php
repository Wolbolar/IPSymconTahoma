<?php
declare(strict_types=1);

require_once(__DIR__ . "/../bootstrap.php");
require_once __DIR__ . '/../libs/ProfileHelper.php';
require_once __DIR__ . '/../libs/ConstHelper.php';

use Fonzo\TaHoma\TaHoma;

class TaHomaDevice extends IPSModule
{
    use ProfileHelper;

    const ROLLER_SHUTTER_POSITIONABLE_STATEFUL_ROOF = 'roller_shutter_positionable_stateful_roof';
    const EXTERIOR_BLIND_POSITIONABLE_STATEFUL_GENERIC = 'exterior_blind_positionable_stateful_generic';
    const ROLLER_SHUTTER_DISCRETE_GENERIC = 'roller_shutter_discrete_generic';
    const ROLLER_SHUTTER_POSITIONABLE_STATEFUL_DUAL = 'roller_shutter_positionable_stateful_dual';

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
        $this->RegisterTimer('TaHomaUpdate', 0, 'TAHOMA_UpdateStatus(' . $this->InstanceID . ');');
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

        if($SiteID == '' || $DeviceID == '' || $Type == '')
        {
            $this->SetStatus(205);
        }
        elseif($SiteID != '' && $DeviceID != '' && $Type != '')
        {
            $this->RegisterVariables();
            $this->SetStatus(IS_ACTIVE);
        }
    }

    private function CheckRequest()
    {
        $SiteID = $this->ReadPropertyString('SiteID');
        $DeviceID = $this->ReadPropertyString('DeviceID');
        $Type = $this->ReadPropertyString('Type');
        $data = false;
        if($SiteID == '' || $DeviceID == '' || $Type == '')
        {
            $this->SetStatus(205);
        }
        elseif($SiteID != '' && $DeviceID != '' && $Type != '')
        {
            $data = $this->RequestStatus();
        }
        return $data;
    }


    /** @noinspection PhpMissingParentCallCommonInspection */
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

    private function RegisterVariables(): void
    {
        // $type = $this->ReadPropertyString('Type');
        $data = $this->RequestStatus();
        $categories = $data->categories;
        $this->WriteAttributeString('categories', json_encode($categories));
        $states = $data->states;
        $this->WriteAttributeString('states', json_encode($states));
        $capabilities = $data->capabilities;
        $this->WriteAttributeString('capabilities', json_encode($capabilities));

        if(count($data->states) > 0)
        {
            $tahoma_interval = $this->ReadPropertyInteger('updateinterval');
            $this->SetTaHomaInterval($tahoma_interval);
        }
        foreach($capabilities as $capability)
        {
            if($capability->name === 'position')
            {
                $this->RegisterVariableInteger('position', $this->Translate('Position'), '~Intensity.100', $this->_getPosition());
                $this->EnableAction('position');
            }
        }

        //Shutter Control Variable
        $this->RegisterProfileAssociation(
            'Tahoma.Control', 'Move', '', '', 0, 3, 0, 0, VARIABLETYPE_INTEGER, [
                             [0, $this->Translate('Open'), 'HollowDoubleArrowUp', -1],
                             [1, $this->Translate('Stop'), 'Close', -1],
                             [2, $this->Translate('Close'), 'HollowDoubleArrowUp', -1]]
        );
        $this->RegisterVariableInteger('ControlShutter', $this->Translate('Control'), 'Tahoma.Control', $this->_getPosition());
        $this->EnableAction('ControlShutter');
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
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
    }

    public function UpdateStatus()
    {
        $data = $this->RequestStatus();
        if(count($data->states) > 0)
        {
            foreach($data->states as $state)
            {
                if($state->name === 'position')
                {
                    $this->SetValue('position', $state->value);
                }
            }
        }
    }

    private function SetTaHomaInterval($tahoma_interval): void
    {
        $interval     = $tahoma_interval * 1000;
        $this->SetTimerInterval('TaHomaUpdate', $interval);
    }

    public function Position(int $position)
    {
        $result = json_decode($this->SendDataToParent(json_encode([
                                                                      'DataID'   => '{656566E9-4C78-6C4C-2F16-63CDD4412E9E}',
                                                                      'Endpoint' => '/v1/device/' . $this->ReadPropertyString('DeviceID') . '/exec',
                                                                      'Payload'  => json_encode([
                                                                                                    'name'       => 'position',
                                                                                                    'parameters' => [$position]
                                                                                                ])
                                                                  ])));
        $this->SetValue('Position', $position);
        return $result;
    }

    public function Open()
    {
        $this->SendCommand('open');
        $this->SetValue('ControlShutter', 0);
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

    public function GetCommandList()
    {
        $data = $this->RequestStatus();
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

    public function RequestStatus()
    {
        $data = json_decode($this->SendDataToParent(json_encode([
            'DataID'   => '{656566E9-4C78-6C4C-2F16-63CDD4412E9E}',
            'Endpoint' => '/v1/device/' . $this->ReadPropertyString('DeviceID'),
            'Payload'  => ''
        ])));
        $categories = $data->categories;
        $this->WriteAttributeString('categories', json_encode($categories));
        $states = $data->states;
        $this->WriteAttributeString('states', json_encode($states));
        $capabilities = $data->capabilities;
        $this->WriteAttributeString('capabilities', json_encode($capabilities));
        $available = $data->available;
        $this->WriteAttributeBoolean('available', $available);
        return $data;
    }

    public function SendCommand($name)
    {
        $result = json_decode($this->SendDataToParent(json_encode([
            'DataID'   => '{656566E9-4C78-6C4C-2F16-63CDD4412E9E}',
            'Endpoint' => '/v1/device/' . $this->ReadPropertyString('DeviceID') . '/exec',
            'Payload'  => json_encode([
                'name'       => $name,
                'parameters' => []
            ])
        ])));
        return $result;
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
        $data = $this->CheckRequest();
        if($data != false)
        {
            $form = [
                [
                    'type' => 'Label',
                    'label' => 'Device ID:'
                ],
                [
                    'type' => 'Label',
                    'label' => $this->ReadPropertyString('DeviceID')
                ],
                [
                    'type' => 'Label',
                    'label' => 'Type:'
                ],
                [
                    'type' => 'Label',
                    'label' => $this->Translate($this->ReadPropertyString('Type'))
                ]
            ];
            if (count($data->states) > 0) {
                $form = array_merge_recursive(
                    $form, [
                             [
                                 'type' => 'Label',
                                 'label' => 'Update interval in seconds:'
                             ],
                             [
                                 'name' => 'updateinterval',
                                 'type' => 'IntervalBox',
                                 'caption' => 'seconds'
                             ]]
                );
            }
        }
        else
        {
            $form = [
                [
                    'type' => 'Label',
                    'label' => 'This device can only created by the TaHoma configurator, please use the TaHoma configurator for creating TaHoma devices.'
                ]
            ];
        }
        return $form;
    }

    /**
     * return form actions by token
     * @return array
     */
    protected function FormActions()
    {


        $Type = $this->ReadPropertyString('Type');
        $form = [];
        if ($Type == self::EXTERIOR_BLIND_POSITIONABLE_STATEFUL_GENERIC || $Type == self::ROLLER_SHUTTER_DISCRETE_GENERIC || $Type == self::ROLLER_SHUTTER_POSITIONABLE_STATEFUL_DUAL || $Type == self::ROLLER_SHUTTER_POSITIONABLE_STATEFUL_ROOF) {
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
                ]
            ];
        } else if ($Type == 2) {
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
                ]
            ];
        } else if ($Type == 3) {
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

    /**
     * return incremented position
     * @return int
     */
    private function _getPosition()
    {
        $this->position++;
        return $this->position;
    }
}
