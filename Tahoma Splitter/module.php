<?php

class TahomaSplitter extends IPSModule
{

    public function Create()
    {
//Never delete this line!
        parent::Create();
		
		//These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.
		$this->RequireParent("{469C3076-0077-7214-C7EE-72F8694AADE7}"); //  I/O
    }

    public function ApplyChanges()
    {
	//Never delete this line!
        parent::ApplyChanges();

    }

	// Data an Child weitergeben
	// Type String, Declaration can be used when PHP 7 is available
	//public function ReceiveData(string $JSONString)
	public function ReceiveData($JSONString)
	{

		// Empfangene Daten vom I/O
		$data = json_decode($JSONString);
		$dataio = json_encode($data->Buffer);
		$this->SendDebug("Splitter ReceiveData:",$dataio,0);

		// Weiterleitung zu allen Gerät-/Device-Instanzen
		$this->SendDataToChildren(json_encode(Array("DataID" => "{8B7D91D7-81AE-3C04-4336-220BBAB23927}", "Buffer" => $data->Buffer))); // Splitter Interface GUI
	}

	// Type String, Declaration can be used when PHP 7 is available
	//public function ForwardData(string $JSONString)
	public function ForwardData($JSONString)
	{

		// Empfangene Daten von der Device Instanz
		$data = json_decode($JSONString);
		$datasend = $data->Buffer;
		$datasend = json_encode($datasend);
		$this->SendDebug("Splitter Forward Data:",$datasend,0);

		// Weiterleiten zur I/O Instanz
		$result = $this->SendDataToParent(json_encode(Array("DataID" => "{7B8E4696-0AD2-64EA-F22E-3CBD9E22D6C9}", "Buffer" => $data->Buffer))); // TX GUI

		return $result;

	}

}
