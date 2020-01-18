<?php
declare(strict_types=1);

require_once(__DIR__ . "/../bootstrap.php");
require_once __DIR__ . '/../libs/ProfileHelper.php';
require_once __DIR__ . '/../libs/ConstHelper.php';

use Fonzo\TaHoma\TaHoma;

class TaHomaWebinterface extends IPSModule
{
    use ProfileHelper;

    // helper properties
    private $position = 0;

    public function Create()
    {
        //Never delete this line!
        parent::Create();

        $this->RegisterPropertyString('user', '');
        $this->RegisterPropertyString('password', '');
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
        $this->RegisterVariables();
        $this->SetStatus(IS_ACTIVE);
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
        $this->RegisterVariableString('Webinterface', $this->Translate('Webinterface'), '~HTMLBox', $this->_getPosition());
        $html = '<iframe src="https://www.tahomalink.com/enduser-mobile-web/steer-html5-client/tahoma/" border="0" frameborder="0" style= "width: 1050px; height: 700px;"/></iframe>';
        $this->SetValue('Webinterface', $html);
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
                'type' => 'Label',
                'label' => 'TaHoma Webinterface'
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
        $form = [];
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
