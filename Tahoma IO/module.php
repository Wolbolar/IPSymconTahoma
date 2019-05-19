<?

class TahomaIO extends IPSModule
{
	private $cookie_file = '';
	private $COOKIEJAR = false;

	public function Create()
	{
		//Never delete this line!
		parent::Create();

		//These lines are parsed on Symcon Startup or Instance creation
		//You cannot use variables here. Just static values.

		$this->RegisterPropertyString('user', '');
		$this->RegisterPropertyString('password', '');
		$this->RegisterPropertyString('domain', 'https://www.tahomalink.com');
		//the following Properties are only used internally
		$this->RegisterPropertyString(
			'browser', 'Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.7.12) Gecko/20050915 Firefox/1.0.7'
		);
	}

	public function ApplyChanges()
	{
		//Never delete this line!
		parent::ApplyChanges();

		$this->ValidateConfiguration();

	}

	/**
	 * Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
	 * Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wiefolgt zur Verfügung gestellt:
	 *
	 *
	 */

	private function ValidateConfiguration()
	{
		$user = $this->ReadPropertyString('user');
		$password = $this->ReadPropertyString('password');


		//User und Passwort prüfen
		if ($user == "" || $password == "") {
			$this->SetStatus(205); //Felder dürfen nicht leer sein
		} else {
			// Status Aktiv
			$this->SetStatus(102);
		}
	}

	/**
	 * Interne Funktion des SDK.
	 *
	 * @param $JSONString IPS-Datenstring
	 *
	 * @return string Die Antwort an den anfragenden Child
	 */
	public function ForwardData($JSONString)
	{
		$this->SendDebug('Forward Data:', $JSONString, 0);
		$data = json_decode($JSONString);
		$data = $data->Buffer;
		$result = "[]";
		if (property_exists($data, 'Method')) {
			$method = $data->Method;
			$command = $data->commandName;
			$this->SendDebug('Method:', $method, 0);
			$this->SendDebug('Command:', $command, 0);
			if ($method == "GET") {
				if ($command == "devices") {
					$devices = $this->GetDevices();
					if ($devices == "Not authenticated") {
						$result = "Not authenticated";
					} else {
						$result = json_encode($devices);
					}
				}
			}
			if ($method == "SET") {
				$deviceURL = $data->deviceURL;
				$commandName = $data->commandName;
				$parameters = $data->parameters;
				$result = $this->SendCommand($deviceURL, $commandName,  $parameters);
			}
			return $result;
		}
		return $result;
	}

	/**
	 * Get a list of configurable devices
	 *
	 * @return mixed json object on success
	 */
	public function GetDevices()
	{

		$url = '/enduser-mobile-web/enduserAPI/login';
		$login = new Request($url, $this->ReadPropertyString('user'), $this->ReadPropertyString('password'));
		$cookie_file = $login->CreateCookieFile();
		$login->SendData();
		$url = '/enduser-mobile-web/externalAPI/refreshAllStates';
		$refresh = new Request($url, $this->ReadPropertyString('user'), $this->ReadPropertyString('password'), $cookie_file);
		$refresh->SendData();
		$url = '/enduser-mobile-web/externalAPI/json/getSetup?_=1434999539745';
		$setup = new Request($url, $this->ReadPropertyString('user'), $this->ReadPropertyString('password'), $cookie_file);
		$payload = $setup->SendData();
		$this->SendDebug("Tahoma", $payload, 0);
		$error = strpos($payload, "Not authenticated");
		if ($error > 0) {
			$this->SendDebug("Tahoma", "Not authenticated", 0);
			return "Not authenticated";
		} else {
			$tahoma = json_decode($payload);
			return $tahoma->setup->devices;
		}
	}

	/**
	 * Get groups
	 *
	 * @return mixed json object on success
	 */
	public function GetScenarios()
	{
		$url = '/enduser-mobile-web/enduserAPI/login';
		$this->CreateCookieFile();
		$headers = [];
		$postfields = [
			'userId' => $this->ReadPropertyString('user'),
			'userPassword' => $this->ReadPropertyString('password')];
		$this->SendTahomaData($url, $headers, $postfields);
		$url = "/enduser-mobile-web/externalAPI/json/getActionGroups";
		$payload = $this->SendTahomaData($url, $headers, $postfields);
		$output = $payload["body"];
		$this->SendDebug("Tahoma", $output, 0);
		$tahoma = json_decode($output);
		return $tahoma->actionGroups;
	}

	/**
	 * @param $execId
	 * @return string
	 */
	public function CancelExecutions($execId)
	{
		$url = "https://www.tahomalink.com/enduser-mobile-web/enduserAPI/login";
		$cookiefile = tempnam("/tmp", "CURLCOOKIE");
		$headers = [];
		$postfields = [
			'userId' => $this->ReadPropertyString('user'),
			'userPassword' => $this->ReadPropertyString('password')];
		$this->SendTahomaData($url, $headers, $postfields);

		$url = "https://www.tahomalink.com/enduser-mobile-web/externalAPI/json/cancelExecutions";
		//	$url = "https://www.tahomalink.com/enduser-mobile-web/enduserAPI//exec/cancelExecutions";
		$this->SendDebug("Tahoma", "cancelExecutions: (" . $execId . ")", 0);
		$headers = [
			'Content-Type: application/json'
		];
		$postfields = ['execId' => $execId];
		$payload = $this->SendTahomaData($url, $headers, $postfields, $cookiefile);
		$output = $payload["body"];
		$this->SendDebug("Tahoma Canel Executions", $output, 0);
		return "";
	}

	/**
	 * Method to send commands to Tahoma API to handle action.
	 *
	 * @param string $deviceURL
	 * @param string $commandName
	 * @param string $parameters
	 * @param string $equipmentName
	 * @return string
	 */
	private function SendCommand(string $deviceURL, string $commandName, string $parameters, string $equipmentName = NULL)
	{
		if (empty($equipmentName)) {
			$equipmentName = "Equipment";
		}
		$url = '/enduser-mobile-web/enduserAPI/login';
		$this->CreateCookieFile();
		$headers = [];
		$postfields = [
			'userId' => $this->ReadPropertyString('user'),
			'userPassword' => $this->ReadPropertyString('password')];
		$this->SendTahomaData($url, $headers, $postfields);

		$command["name"] = $commandName;
		if ($parameters != "") {
			$command["parameters"] = $parameters;
		}
		$action["commands"][] = $command;
		$action["deviceURL"] = $deviceURL;
		$row["label"] = $equipmentName;
		$row["actions"][] = $action;
		$url = '/enduser-mobile-web/enduserAPI/exec/apply';
		$headers = [
			'Content-Type: application/json'
		];
		$payload = $this->SendTahomaData($url, $headers, $row);
		$output = $payload["body"];
		$outputJSON = json_decode($output);
		return $outputJSON->execId;
	}

	/**
	 * Method to execute an action
	 *
	 * @param string $oid
	 * @param int $delay
	 *
	 * @return string
	 */
	public function ExecuteAction($oid, $delay = 0)
	{
		$url = '/enduser-mobile-web/enduserAPI/login';
		$this->CreateCookieFile();
		$headers = [];
		$postfields = [
			'userId' => $this->ReadPropertyString('user'),
			'userPassword' => $this->ReadPropertyString('password')];
		$this->SendTahomaData($url, $headers, $postfields);
		$url = sprintf("https://www.tahomalink.com/enduser-mobile-web/externalAPI/json/scheduleActionGroup?oid=%s&delay=%d", $oid, $delay);
		$headers = [
			'Content-Type: application/json'
		];
		$payload = $this->SendTahomaData($url, $headers, $postfields, $this->cookie_file);
		$output = $payload["body"];
		$outputJson = json_decode($output);
		return $outputJson->actionGroup;
	}

	private function SendTahomaData(string $url, array $header, array $postfields = null, $cookiefile = null): array
	{
		$this->SendDebug(__FUNCTION__, 'url: ' . $url, 0);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if ($this->COOKIEJAR) {
			curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file);
		} else {
			curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);
		}
		curl_setopt($ch, CURLOPT_USERAGENT, $this->ReadPropertyString('browser'));
		curl_setopt($ch, CURLOPT_ENCODING, '');
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

		if ($postfields !== null) {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
		}
		if ($cookiefile !== null) {
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiefile);
		}

		curl_setopt($ch, CURLOPT_URL, $url);

		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			trigger_error('Error:' . curl_error($ch));
		}
		$info = curl_getinfo($ch);
		curl_close($ch);

		return $this->getReturnValues($info, $result);
	}

	private function getReturnValues(array $info, string $result): array
	{
		$HeaderSize = $info['header_size'];

		$http_code = $info['http_code'];
		$this->SendDebug(__FUNCTION__, 'Response (http_code): ' . $http_code, 0);

		$header = explode("\n", substr($result, 0, $HeaderSize));
		$this->SendDebug(__FUNCTION__, 'Response (header): ' . json_encode($header), 0);

		$body = substr($result, $HeaderSize);
		$this->SendDebug(__FUNCTION__, 'Response (body): ' . $body, 0);


		return ['http_code' => $http_code, 'header' => $header, 'body' => $body];
	}

	/**
	 * Method to create cookieFile
	 *
	 * @return string name of tmp file
	 */
	protected function CreateCookieFile()
	{
		$cookie_file = tempnam("/tmp", "CURLCOOKIE");
		$this->COOKIEJAR = true;
		$this->cookie_file = $cookie_file;
		// $this->SendDebug("Tahoma", $cookie_file, 0);
		return $cookie_file;
	}

	protected function SendJSON($data)
	{
		// Weiterleitung zu allen Gerät-/Device-Instanzen
		$this->SendDataToChildren(json_encode(Array("DataID" => "{8C2D8198-5E10-A042-6047-757EF82DDE25}", "Buffer" => $data))); //  I/O RX GUI
	}

	//Add this Polyfill for IP-Symcon 4.4 and older
	protected function SetValue($Ident, $Value)
	{

		if (IPS_GetKernelVersion() >= 5) {
			parent::SetValue($Ident, $Value);
		} else {
			SetValue($this->GetIDForIdent($Ident), $Value);
		}
	}
}

class Request
{
	/**
	 * @var string $url
	 */
	private $url = '';
	private $domain = '';
	private $postData = '';
	private $ckfile = '';
	private $createCookieJar = false;
	private $isJSONPostData = false;

	/**
	 * tahomeRequest constructor.
	 *
	 * @param string $url string
	 * @param string $userId normally an e-mailaddress
	 * @param string $password
	 * @param string $ckfile
	 *
	 * @example /enduser-mobile-web/enduserAPI/login
	 */
	public function __construct($url, $userId, $password, $ckfile = '')
	{
		$this->url = $url;
		$this->domain = 'https://www.tahomalink.com';
		$this->postData = "userId=$userId&userPassword=$password";
		$this->ckfile = $ckfile;
	}

	/**
	 * @return mixed
	 */
	public function SendData()
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->domain . $this->url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		if ($this->isJSONPostData) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
		}
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->postData);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.7.12) Gecko/20050915 Firefox/1.0.7");
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		if ($this->createCookieJar) {
			curl_setopt($ch, CURLOPT_COOKIEJAR, $this->ckfile);
		} else {
			curl_setopt($ch, CURLOPT_COOKIEFILE, $this->ckfile);
		}
		$output = curl_exec($ch);
		curl_close($ch);
		if ($output == "") {
			echo $this->url . ": Invalid return\n";
		}
		return $output;
	}

	/**
	 * Method to set data for POST
	 *
	 * @param array $postData
	 *
	 * @return void
	 */
	public function setPostData($postData)
	{
		$this->postData = $postData;
	}

	/**
	 * Method to set data for POST (in Json Format)
	 *
	 * @param string $postData Json format
	 *
	 * @return void
	 */
	public function setJSONPostData($postData)
	{
		$this->postData = $postData;
		$this->isJSONPostData = true;
	}

	/**
	 * Method to create cookieFile
	 *
	 * @return string name of tmp file
	 */
	public function CreateCookieFile()
	{
		$ckfile = @tempnam("/tmp", "CURLCOOKIE");
		$this->createCookieJar = true;
		$this->ckfile = $ckfile;
		return $ckfile;
	}
}
