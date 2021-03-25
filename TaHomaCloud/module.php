<?php
declare(strict_types=1);
class TaHomaCloud extends IPSModule
{
    //This one needs to be available on our OAuth client backend.
    //Please contact us to register for an identifier: https://www.symcon.de/kontakt/#OAuth
    // private $oauthIdentifer = 'somfy';
    //Use the somfy_dev endpoint. The default somfy endpoint seems to be broken for unknown reasons.
    private $oauthIdentifer = 'somfy_dev';

    // somfy API
    private const SOMFY_BASE_URL = 'https://api.somfy.com/api/v1';
    private const ALL_SITES = '/site'; // List all available sites for the current user.
    private const SITE = '/site/'; // Get a specific site for the current user.
    private const DEVICES = '/device'; // List all available devices for a user’s specific site.

    private const PICTURE_LOGO_SOMFY = 'iVBORw0KGgoAAAANSUhEUgAAAXgAAACWCAYAAADUpJPgAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsQAAA7EAZUrDhsAAAUWaVRYdFhNTDpjb20uYWRvYmUueG1wAAAAAAA8P3hwYWNrZXQgYmVnaW49Iu+7vyIgaWQ9Ilc1TTBNcENlaGlIenJlU3pOVGN6a2M5ZCI/PiA8eDp4bXBtZXRhIHhtbG5zOng9ImFkb2JlOm5zOm1ldGEvIiB4OnhtcHRrPSJBZG9iZSBYTVAgQ29yZSA1LjYtYzE0MCA3OS4xNjA0NTEsIDIwMTcvMDUvMDYtMDE6MDg6MjEgICAgICAgICI+IDxyZGY6UkRGIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyI+IDxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSIiIHhtbG5zOnhtcD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLyIgeG1sbnM6ZGM9Imh0dHA6Ly9wdXJsLm9yZy9kYy9lbGVtZW50cy8xLjEvIiB4bWxuczpwaG90b3Nob3A9Imh0dHA6Ly9ucy5hZG9iZS5jb20vcGhvdG9zaG9wLzEuMC8iIHhtbG5zOnhtcE1NPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIiB4bWxuczpzdEV2dD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL3NUeXBlL1Jlc291cmNlRXZlbnQjIiB4bXA6Q3JlYXRvclRvb2w9IkFkb2JlIFBob3Rvc2hvcCBDQyAoTWFjaW50b3NoKSIgeG1wOkNyZWF0ZURhdGU9IjIwMTgtMDQtMTdUMDg6NTc6MDUrMDI6MDAiIHhtcDpNb2RpZnlEYXRlPSIyMDE4LTA0LTE3VDA4OjU4OjM3KzAyOjAwIiB4bXA6TWV0YWRhdGFEYXRlPSIyMDE4LTA0LTE3VDA4OjU4OjM3KzAyOjAwIiBkYzpmb3JtYXQ9ImltYWdlL3BuZyIgcGhvdG9zaG9wOkNvbG9yTW9kZT0iMyIgcGhvdG9zaG9wOklDQ1Byb2ZpbGU9InNSR0IgSUVDNjE5NjYtMi4xIiB4bXBNTTpJbnN0YW5j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';

    public function Create()
    {
        //Never delete this line!
        parent::Create();

        $this->RegisterPropertyInteger("UpdateInterval", 60);
        $this->RegisterTimer("Update", 0, "TAHOMA_Update(" . $this->InstanceID . ");");
        $this->RegisterAttributeInteger('LastUpdate', 0);
        $this->RegisterAttributeString('Token', '');
        $this->RegisterAttributeString('DeviceStates', '[]');
        $this->RegisterAttributeString('Sites', '[]');

        //we will wait until the kernel is ready
        $this->RegisterMessage(0, IPS_KERNELMESSAGE);
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();

        if (IPS_GetKernelRunlevel() !== KR_READY) {
            return;
        }

        $this->RegisterOAuth($this->oauthIdentifer);

        $sites = $this->ReadAttributeString('Sites');
        if ($this->ReadAttributeString('Token') == '') {
            $this->SetStatus(IS_INACTIVE);
        } else {
            if($sites == '[]')
            {
                $this->GetAllSites();
            }
            $tahoma_interval = $this->ReadPropertyInteger('UpdateInterval');
            $this->SetTaHomaInterval($tahoma_interval);
            $this->SetStatus(IS_ACTIVE);
        }
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

    private function SetTaHomaInterval($tahoma_interval): void
    {
        if ($tahoma_interval < 60 && $tahoma_interval != 0) {
            $tahoma_interval = 60;
        }
        $interval = $tahoma_interval * 1000 * 60; // minutes
        $this->SetTimerInterval('Update', $interval);
    }

    public function Update()
    {
        $last_update = $this->ReadAttributeInteger('LastUpdate');
        $this->SendDebug('last update', date('H:i:s', $last_update), 0);
        $current_time = time();
        $interval = $current_time - $last_update;
        if($last_update == 0)
        {
            $snapshot = $this->GetDevicesStates();
            $this->WriteAttributeInteger('LastUpdate', time());
            $this->WriteAttributeString('DeviceStates', json_encode($snapshot));
            $this->SendDebug('somfy update', json_encode($snapshot), 0);
        }
        elseif($interval < 60)
        {
            $this->SendDebug('last update', 'last update less than 60 seconds ago', 0);
            $snapshot = $this->ReadAttributeString('DeviceStates');
            $this->SendDebug('somfy update', $snapshot, 0);
        }
        else{
            $snapshot = $this->GetDevicesStates();
            $this->WriteAttributeInteger('LastUpdate', time());
            $this->WriteAttributeString('DeviceStates', json_encode($snapshot));
            $this->SendDebug('somfy update', json_encode($snapshot), 0);
        }

        return $snapshot;
    }

    private function GetDevicesStates()
    {
        $sites = $this->ReadAttributeString('Sites');
        $this->SendDebug('Somfy sites ', $sites, 0);
        $sites = json_decode($sites, true);
        $devices_states = [];
        if(empty($sites))
        {
            $sites = $this->GetAllSites();
        }
        if(isset($sites['fault']))
        {
            $this->SendDebug('Tahoma Error', $sites['fault']['faultstring'], 0);
            $this->SendDebug('Tahoma Error Detail', $sites['fault']['detail']['errorcode'], 0);
        }
        else
        {
            foreach($sites as $site)
            {
                $site_id = $site['id'];
                $site_label = $site['label'];
                $devices = $this->GetData(self::SOMFY_BASE_URL . self::SITE . $site_id . self::DEVICES);
                $this->SendDebug('Somfy devices for site ' . $site_label, $devices, 0);
                $this->SendDataToChildren(json_encode(array("DataID" => "{59020227-ED4A-9F60-F34C-E2E7771D9764}", "Buffer" => $devices)));
                $devices_states[$site['id']] = $devices;
            }
        }
        return $devices_states;
    }

    private function GetAllSites()
    {
        $sites = $this->GetData(self::SOMFY_BASE_URL . self::ALL_SITES);
        $sites = json_decode($sites, true);
        foreach ($sites as $site) {
            if(isset($site['id']))
            {
                $this->SendDebug('Somfy site',  $site['label'], 0);
            }
        }
        if(isset($sites[0]['id']))
        {
            $this->WriteAttributeString('Sites', json_encode($sites));
        }
        return $sites;
    }

    public function GetToken()
    {
        $token = $this->GetBuffer('AccessToken');
        return $token;
    }

    private function RegisterOAuth($WebOAuth)
    {
        $ids = IPS_GetInstanceListByModuleID('{F99BF07D-CECA-438B-A497-E4B55F139D37}');
        if (count($ids) > 0) {
            $clientIDs = json_decode(IPS_GetProperty($ids[0], 'ClientIDs'), true);
            $found = false;
            foreach ($clientIDs as $index => $clientID) {
                if ($clientID['ClientID'] == $WebOAuth) {
                    if ($clientID['TargetID'] == $this->InstanceID) {
                        return;
                    }
                    $clientIDs[$index]['TargetID'] = $this->InstanceID;
                    $found = true;
                }
            }
            if (!$found) {
                $clientIDs[] = ['ClientID' => $WebOAuth, 'TargetID' => $this->InstanceID];
            }
            IPS_SetProperty($ids[0], 'ClientIDs', json_encode($clientIDs));
            IPS_ApplyChanges($ids[0]);
        }
    }

    /**
     * This function will be called by the register button on the property page!
     */
    public function Register()
    {

        //Return everything which will open the browser
        return 'https://oauth.ipmagic.de/authorize/' . $this->oauthIdentifer . '?username=' . urlencode(IPS_GetLicensee());
    }

    private function FetchRefreshToken($code)
    {
        $this->SendDebug('FetchRefreshToken', 'Use Authentication Code to get our precious Refresh Token!', 0);

        //Exchange our Authentication Code for a permanent Refresh Token and a temporary Access Token
        $options = [
            'http' => [
                'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query(['code' => $code]),
                'ignore_errors' => true
            ]
        ];
        $context = stream_context_create($options);
        $result = file_get_contents('https://oauth.ipmagic.de/access_token/' . $this->oauthIdentifer, false, $context);

        $data = json_decode($result);

        if (!isset($data->token_type) || $data->token_type != 'bearer') {
            die('Bearer Token expected');
        }

        //Save temporary access token
        $this->FetchAccessToken($data->access_token, time() + $data->expires_in);

        //Return RefreshToken
        return $data->refresh_token;
    }

    /**
     * This function will be called by the OAuth control. Visibility should be protected!
     */
    protected function ProcessOAuthData()
    {

        //Lets assume requests via GET are for code exchange. This might not fit your needs!
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            if (!isset($_GET['code'])) {
                die('Authorization Code expected');
            }

            $token = $this->FetchRefreshToken($_GET['code']);

            $this->SendDebug('ProcessOAuthData', "OK! Let's save the Refresh Token permanently", 0);

            $this->WriteAttributeString('Token', $token);

            //This will enforce a reload of the property page. change this in the future, when we have more dynamic forms
            IPS_ApplyChanges($this->InstanceID);
        } else {

            //Just print raw post data!
            echo file_get_contents('php://input');
        }
    }

    private function FetchAccessToken($Token = '', $Expires = 0)
    {

        //Exchange our Refresh Token for a temporary Access Token
        if ($Token == '' && $Expires == 0) {

            //Check if we already have a valid Token in cache
            $data = $this->GetBuffer('AccessToken');
            if ($data != '') {
                $data = json_decode($data);
                if (time() < $data->Expires) {
                    $this->SendDebug('FetchAccessToken', 'OK! Access Token is valid until ' . date('d.m.y H:i:s', $data->Expires), 0);
                    return $data->Token;
                }
            }

            $this->SendDebug('FetchAccessToken', 'Use Refresh Token to get new Access Token!', 0);

            //If we slipped here we need to fetch the access token
            $options = [
                'http' => [
                    'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query(['refresh_token' => $this->ReadAttributeString('Token')]),
                    'ignore_errors' => true

                ]
            ];
            $context = stream_context_create($options);
            $result = file_get_contents('https://oauth.ipmagic.de/access_token/' . $this->oauthIdentifer, false, $context);

            $data = json_decode($result);

            if (!isset($data->token_type) || $data->token_type != 'bearer') {
                die('Bearer Token expected');
            }

            //Update parameters to properly cache it in the next step
            $Token = $data->access_token;
            $Expires = time() + $data->expires_in;

            //Update Refresh Token if we received one! (This is optional)
            if (isset($data->refresh_token)) {
                $this->SendDebug('FetchAccessToken', "NEW! Let's save the updated Refresh Token permanently", 0);

                $this->WriteAttributeString('Token', $data->refresh_token);
            }
        }

        $this->SendDebug('FetchAccessToken', 'CACHE! New Access Token is valid until ' . date('d.m.y H:i:s', $Expires), 0);

        //Save current Token
        $this->SetBuffer('AccessToken', json_encode(['Token' => $Token, 'Expires' => $Expires]));

        //Return current Token
        return $Token;
    }

    public function GetAllUserSite()
    {
        return json_decode($this->GetData(self::SOMFY_BASE_URL . self::ALL_SITES));
    }

    private function GetData($url)
    {
        $opts = [
            'http'=> [
                'method' => 'GET',
                'header' => 'Authorization: Bearer ' . $this->FetchAccessToken() . "\r\n" . 'Content-Type: application/json' . "\r\n",
                'ignore_errors' => true
            ]
        ];
        $context = stream_context_create($opts);

        $result = file_get_contents($url, false, $context);

        $this->SendDebug("DATA", $result, 0);

        return $result;
    }

    private function PostData($url, $content)
    {
        $opts = [
            'http'=> [
                'method'  => 'POST',
                'header'  => 'Authorization: Bearer ' . $this->FetchAccessToken() . "\r\n" . 'Content-Type: application/json' . "\r\n" . 'Content-Length: ' . strlen($content) . "\r\n",
                'content' => $content,
                'ignore_errors' => true
            ]
        ];
        $context = stream_context_create($opts);

        $result = file_get_contents($url, false, $context);

        $this->SendDebug("DATA", $result, 0);

        return $result;
    }

    public function ForwardData($data)
    {
        $data = json_decode($data);

        if (strlen($data->Payload) > 0) {
            $this->SendDebug('ForwardData', $data->Endpoint . ', Payload: ' . $data->Payload, 0);
            return $this->PostData(self::SOMFY_BASE_URL . $data->Endpoint, $data->Payload);
        } else {
            $this->SendDebug('ForwardData', $data->Endpoint, 0);
            return $this->GetData(self::SOMFY_BASE_URL . $data->Endpoint);
        }
    }

    /**
     * build configuration form
     * @return string
     */
    public function GetConfigurationForm()
    {
        // return current form
        $Form = json_encode([
            'elements' => $this->FormHead(),
            'actions' => $this->FormActions(),
            'status' => $this->FormStatus()
        ]);
        $this->SendDebug('FORM', $Form, 0);
        $this->SendDebug('FORM', json_last_error_msg(), 0);
        return $Form;
    }

    /**
     * return form configurations on configuration step
     * @return array
     */
    protected function FormHead()
    {
        $visibility_register = false;
        //Check Gardena connection
        if ($this->ReadAttributeString('Token') == '') {
            $visibility_register = true;
            $visibility_register1 = false;
            $visibility_register2 = false;
            $number = 0;
        }
        else{
            $result = $this->GetAllUserSite();
            if ($result === false || $result === null || !is_countable($result)) {
                $visibility_register1 = true;
                $visibility_register2 = false;
                $number = 0;
            } else {
                $number = count($result);
                $visibility_register1 = false;
                $visibility_register2 = true;
            }
        }

        $form = [
            [
                'type' => 'Image',
                'image' => 'data:image/png;base64, ' . self::PICTURE_LOGO_SOMFY],
            [
                'type' => 'Label',
                'visible' => $visibility_register,
                'caption' => 'TaHoma: Please register with your TaHoma account!'
            ],
            [
                'type' => 'Label',
                'visible' => $visibility_register1,
                'caption' => 'TaHoma: Error. Please check your internet connection or register again!'
            ],
            [
                'type' => 'Label',
                'visible' => $visibility_register2,
                'caption' => 'TaHoma: ' . sprintf($this->Translate('Found %d Sites'), $number)
            ],
            [
                'type' => 'Button',
                'visible' => true,
                'caption' => 'Register',
                'onClick' => 'echo TAHOMA_Register($id);'
            ],
            [
                'name' => 'UpdateInterval',
                'visible' => true,
                'type' => 'NumberSpinner',
                'suffix' => 'seconds',
                'minimum' => 15,
                'enabled' => true,
                'caption' => 'update interval'
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
        //Check Connect availability
        $ids = IPS_GetInstanceListByModuleID('{9486D575-BE8C-4ED8-B5B5-20930E26DE6F}');
        if (IPS_GetInstance($ids[0])['InstanceStatus'] != IS_ACTIVE) {
            $visibility_label1 = true;
            $visibility_label2 = false;
        } else {
            $visibility_label1 = false;
            $visibility_label2 = true;
        }
        $form = [
            [
                'type' => 'Label',
                'visible' => $visibility_label1,
                'caption' => 'Error: Symcon Connect is not active!'
            ],
            [
                'type' => 'Label',
                'visible' => $visibility_label2,
                'caption' => 'Status: Symcon Connect is OK!'
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
                'code' => IS_CREATING,
                'icon' => 'inactive',
                'caption' => 'Creating instance.'
            ],
            [
                'code' => IS_ACTIVE,
                'icon' => 'active',
                'caption' => 'configuration valid.'
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
                'caption' => 'no category selected.'
            ]
        ];

        return $form;
    }
}
