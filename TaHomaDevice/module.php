<?php

declare(strict_types=1);
class TaHomaDevice extends IPSModule
{
    public function Create()
    {
        //Never delete this line!
        parent::Create();

        //Connect to available splitter or create a new one
        $this->ConnectParent('{6F83CEDB-BC40-63BB-C209-88D6B252C9FF}');

        $this->RegisterPropertyString('SiteID', '');
        $this->RegisterPropertyString('DeviceID', '');
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();

        if (IPS_GetKernelRunlevel() !== KR_READY) {
            return;
        }

        $this->RegisterVariables();
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
        //Shutter Control Variable
        $this->RegisterProfileAssociation(
            'Tahoma.Control', 'Move', '', '', 0, 3, 0, 0, VARIABLETYPE_INTEGER, [
                             [0, $this->Translate('Open'), 'HollowDoubleArrowUp', -1],
                             [1, $this->Translate('Stop'), 'Close', -1],
                             [2, $this->Translate('Close'), 'HollowDoubleArrowUp', -1]]
        );
        $this->RegisterVariableInteger('ControlShutter', 'Control', 'Tahoma.Control', 1);
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

    public function RequestStatus()
    {
        $result = json_decode($this->SendDataToParent(json_encode([
            'DataID'   => '{656566E9-4C78-6C4C-2F16-63CDD4412E9E}',
            'Endpoint' => '/v1/device/' . $this->ReadPropertyString('DeviceID'),
            'Payload'  => ''
        ])));

        var_dump($result);
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

        var_dump($result);
    }

    /** register profiles
     *
     *
     * @param $Name
     * @param $Icon
     * @param $Prefix
     * @param $Suffix
     * @param $MinValue
     * @param $MaxValue
     * @param $StepSize
     * @param $Digits
     * @param $Vartype
     */
    private function RegisterProfile($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits, $Vartype): void
    {

        if (IPS_VariableProfileExists($Name)) {
            $profile = IPS_GetVariableProfile($Name);
            if ($profile['ProfileType'] !== $Vartype) {
                $this->SendDebug('Profile', 'Variable profile type does not match for profile ' . $Name, 0);
            }
        } else {
            IPS_CreateVariableProfile($Name, $Vartype); // 0 boolean, 1 int, 2 float, 3 string
            $this->SendDebug('Variablenprofil angelegt', $Name, 0);
        }

        IPS_SetVariableProfileIcon($Name, $Icon);
        IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
        IPS_SetVariableProfileDigits($Name, $Digits); //  Nachkommastellen
        IPS_SetVariableProfileValues(
            $Name, $MinValue, $MaxValue, $StepSize
        ); // string $ProfilName, float $Minimalwert, float $Maximalwert, float $Schrittweite
        $this->SendDebug(
            'Variablenprofil konfiguriert',
            'Name: ' . $Name . ', Icon: ' . $Icon . ', Prefix: ' . $Prefix . ', $Suffix: ' . $Suffix . ', Digits: ' . $Digits . ', MinValue: '
            . $MinValue . ', MaxValue: ' . $MaxValue . ', StepSize: ' . $StepSize, 0
        );
    }

    /** register profile association
     *
     * @param $Name
     * @param $Icon
     * @param $Prefix
     * @param $Suffix
     * @param $MinValue
     * @param $MaxValue
     * @param $Stepsize
     * @param $Digits
     * @param $Vartype
     * @param $Associations
     */
    private function RegisterProfileAssociation($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $Stepsize, $Digits, $Vartype,
                                                $Associations): void
    {
        if (is_array($Associations) && count($Associations) === 0) {
            $MinValue = 0;
            $MaxValue = 0;
        }
        $this->RegisterProfile($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $Stepsize, $Digits, $Vartype);

        if (is_array($Associations)) {
            //zunächst werden alte Assoziationen gelöscht
            //bool IPS_SetVariableProfileAssociation ( string $ProfilName, float $Wert, string $Name, string $Icon, integer $Farbe )
            if ($Vartype === 1 || $Vartype === 2) // 0 boolean, 1 int, 2 float, 3 string
            {
                foreach (IPS_GetVariableProfile($Name)['Associations'] as $Association) {
                    IPS_SetVariableProfileAssociation($Name, $Association['Value'], '', '', -1);
                }
            }

            //dann werden die aktuellen eingetragen
            foreach ($Associations as $Association) {
                IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
            }
        } else {
            $Associations = $this->$Associations;
            foreach ($Associations as $code => $association) {
                IPS_SetVariableProfileAssociation($Name, $code, $this->Translate($association), $Icon, -1);
            }
        }
    }
}
