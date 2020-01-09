<?php

declare(strict_types=1);
class TaHomaConfigurator extends IPSModule
{
    public function Create()
    {
        //Never delete this line!
        parent::Create();

        //Connect to available splitter or create a new one
        $this->ConnectParent('{6F83CEDB-BC40-63BB-C209-88D6B252C9FF}');

        $this->RegisterPropertyString('SiteID', '');
    }

    private function searchDevice($deviceID)
    {
        $ids = IPS_GetInstanceListByModuleID('{4434685E-551F-D887-3163-006833D318E3}');
        foreach ($ids as $id) {
            if (IPS_GetProperty($id, 'DeviceID') == $deviceID) {
                return $id;
            }
        }

        return 0;
    }

    public function GetConfigurationForm()
    {
        $data = json_decode(file_get_contents(__DIR__ . '/form.json'));

        if ($this->HasActiveParent()) {
            $devices = json_decode($this->SendDataToParent(json_encode([
                'DataID'   => '{656566E9-4C78-6C4C-2F16-63CDD4412E9E}',
                'Endpoint' => '/v1/site/' . $this->ReadPropertyString('SiteID') . '/device',
                'Payload'  => ''
            ])));

            foreach ($devices as $device) {
                $this->SendDebug('Device', json_encode($device), 0);

                if (!isset($device->name)) {
                    continue;
                } //skip devices without names (e.g. the hub)

                $data->actions[0]->values[] = [
                    'address'    => $device->id,
                    'name'       => $device->name,
                    'type'       => $device->type,
                    'instanceID' => $this->searchDevice($device->id),
                    'create'     => [
                        'moduleID'      => '{4434685E-551F-D887-3163-006833D318E3}',
                        'configuration' => [
                            'SiteID'   => $this->ReadPropertyString('SiteID'),
                            'DeviceID' => $device->id
                        ]
                    ]
                ];
            }
        }

        return json_encode($data);
    }
}
