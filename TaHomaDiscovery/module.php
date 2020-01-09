<?php

declare(strict_types=1);
class TaHomaDiscovery extends IPSModule
{
    public function Create()
    {
        //Never delete this line!
        parent::Create();

        //Connect to available splitter or create a new one
        $this->ConnectParent('{6F83CEDB-BC40-63BB-C209-88D6B252C9FF}');
    }

    private function searchSiteConfigurator($siteID)
    {
        $ids = IPS_GetInstanceListByModuleID('{0AEE6E12-86BF-D8A1-90EA-66C9D45D052E}');
        foreach ($ids as $id) {
            if (IPS_GetProperty($id, 'SiteID') == $siteID) {
                return $id;
            }
        }

        return 0;
    }

    public function GetConfigurationForm()
    {
        $data = json_decode(file_get_contents(__DIR__ . '/form.json'));

        if ($this->HasActiveParent()) {
            $result = json_decode($this->SendDataToParent(json_encode([
                'DataID'   => '{656566E9-4C78-6C4C-2F16-63CDD4412E9E}',
                'Endpoint' => '/v1/site',
                'Payload'  => ''
            ])));

            foreach ($result as $site) {
                $data->actions[0]->values[] = [
                    'address'    => $site->id,
                    'name'       => $site->label,
                    'instanceID' => $this->searchSiteConfigurator($site->id),
                    'create'     => [
                        'moduleID'      => '{0AEE6E12-86BF-D8A1-90EA-66C9D45D052E}',
                        'configuration' => [
                            'SiteID' => $site->id
                        ]
                    ]
                ];
            }
        }

        return json_encode($data);
    }
}
