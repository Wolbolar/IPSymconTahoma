<?php
declare(strict_types=1);
class TaHomaCloud extends IPSModule
{
    //This one needs to be available on our OAuth client backend.
    //Please contact us to register for an identifier: https://www.symcon.de/kontakt/#OAuth
    // private $oauthIdentifer = 'somfy';
    //Use the somfy_dev endpoint. The default somfy endpoint seems to be broken for unknown reasons.
    private $oauthIdentifer = 'somfy_dev';

    private $apiURL = 'https://api.somfy.com/api';

    public function Create()
    {
        //Never delete this line!
        parent::Create();

        $this->RegisterAttributeString('Token', '');
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();

        $this->RegisterOAuth($this->oauthIdentifer);

        if ($this->ReadAttributeString('Token') == '') {
            $this->SetStatus(IS_INACTIVE);
        } else {
            $this->SetStatus(IS_ACTIVE);
        }
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
        return json_decode($this->GetData($this->apiURL . '/v1/site'));
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
            return $this->PostData($this->apiURL . $data->Endpoint, $data->Payload);
        } else {
            $this->SendDebug('ForwardData', $data->Endpoint, 0);
            return $this->GetData($this->apiURL . $data->Endpoint);
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
                'caption' => 'TaHoma: ' . sprintf('Found %d Sites', $number)
            ],
            [
                'type' => 'Button',
                'visible' => true,
                'caption' => 'Register',
                'onClick' => 'echo TAHOMA_Register($id);'
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
