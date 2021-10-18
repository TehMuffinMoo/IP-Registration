<?php
// PLUGIN INFORMATION
$GLOBALS['plugins']['ipRegistration'] = array( // Plugin Name
	'name' => 'IP Registration', // Plugin Name
	'author' => 'TehMuffinMoo', // Who wrote the plugin
	'category' => 'Access Management', // One to Two Word Description
	'link' => '', // Link to plugin info
	'license' => 'personal', // License Type use , for multiple
	'idPrefix' => 'ipRegistration', // html element id prefix (All Uppercase)
	'configPrefix' => 'ipRegistration', // config file prefix for array items without the hypen (All Uppercase)
	'version' => '1.0.1', // SemVer of plugin
	'image' => 'api/plugins/ipRegistration/logo.png', // 1:1 non transparent image for plugin
	'settings' => true, // does plugin need a settings modal?
	'bind' => true, // use default bind to make settings page - true or false
	'api' => 'api/v2/plugins/ipregistration/settings', // api route for settings page (All Lowercase)
	'homepage' => false // Is plugin for use on homepage? true or false
);

class ipRegistrationPlugin extends Organizr
{

	public function _ipRegistrationPluginGetSettings()
	{
		return array(
			'Plugin Settings' => array(
				$this->settingsOption('auth', 'IPREGISTRATION-pluginAuth', ['label' => 'Which users you would like to be able to register and view their own IPs']),
				$this->settingsOption('input', 'IPREGISTRATION-PfSense-IP', ['label' => 'The IP / FQDN of your pfsense']),
				$this->settingsOption('input', 'IPREGISTRATION-PfSense-IPTable', ['label' => 'The name of the IP Alias in pfsense']),
				$this->settingsOption('input', 'IPREGISTRATION-PfSense-Username', ['label' => 'The username of your pfsense account']),
				$this->settingsOption('passwordalt', 'IPREGISTRATION-PfSense-Password', ['label' => 'The password of your pfsense account']),
                $this->settingsOption('input', 'IPREGISTRATION-PfSense-Maximum-IPs', ['label' => 'The maximum number of IP Addresses to retain in the database.']),
                $this->settingsOption('token', 'IPREGISTRATION-ApiToken'),
				$this->settingsOption('blank'),
				$this->settingsOption('button', '', ['label' => 'Generate API Token', 'icon' => 'fa fa-undo', 'text' => 'Retrieve', 'attr' => 'onclick="ipRegistrationPluginGenerateAPIKey();"']),
			),
		);
	}

	public function _ipRegistrationPluginLaunch()
	{
		$user = $this->getUserById($this->user['userID']);
		if ($user) {
			$this->setResponse(200, 'User approved for plugin');
			return true;
		}
		$this->setResponse(401, 'User not approved for plugin');
		return false;
	}

	public function _ipRegistrationPluginCheckRequest($request)
	{
		$result = false;
		if ($this->config['IPREGISTRATION-enabled'] && $this->hasDB()) {
			if (!$this->_ipRegistrationPluginCheckDBTablesExist()) {
				$this->_ipRegistrationPluginCreateDBTables();
			}
			$result = true;
		}
		return $result;
	}

	protected function _ipRegistrationPluginCheckDBTablesExist()
	{
		$query = [
			array(
				'function' => 'fetchSingle',
				'query' => array(
					"SELECT `name` FROM `sqlite_master` WHERE `type` = 'table' AND `name` = 'IPREGISTRATION'"
				),
				'key' => 'IPREGISTRATION'
			)
		];
		$data = $this->processQueries($query);
        return $data;
	}
	
	protected function _ipRegistrationPluginCreateDBTables()
	{
		$query = [
			array(
				'function' => 'query',
				'query' => 'CREATE TABLE `IPREGISTRATION` (
					`id`	INTEGER PRIMARY KEY AUTOINCREMENT UNIQUE,
					`datetime`	TEXT,
					`type`	TEXT,
					`ip`	INTEGER,
					`username`	TEXT
				);'
			)
		];
		$this->processQueries($query);
	}

	protected function 	_ipRegistrationPluginQueryDB($UserIP = "", $Username = "") {
        if ($UserIP != "") {
            $query = [
                array(
                    'function' => 'fetch',
                    'query' => array(
                        'SELECT * FROM `IPREGISTRATION` WHERE `ip` = ?',
                        $UserIP
                    )
                ),
            ];
        } elseif ($Username != "") {
            $query = [
                array(
                    'function' => 'fetch',
                    'query' => array(
                        'SELECT * FROM `IPREGISTRATION` WHERE `username` = ?',
                        $Username
                    )
                ),
            ];
        } elseif ($UserIP == "" && $Username == "") {
            $query = [
                array(
                    'function' => 'fetchAll',
                    'query' => 'SELECT * FROM IPREGISTRATION'
                )
            ];
        }
        if ($query) {
            return $this->processQueries($query);
        }
	}

	public function _ipRegistrationPluginIPRegistration()
	{
		$this->setLoggerChannel('IP Registration Plugin');
		$dir = __DIR__;
		$UserIP = $this->userIP();
		$Result = array (
			"Request" => $_SERVER['HTTP_X_FORWARDED_FOR'],
			"Response" => array (
				"IP" => $UserIP,
				"Username" => $this->user['username'],
				"Status" => "",
				"Location" => "",
				"Message" => "",
			)
		);
		if (filter_var($UserIP, FILTER_VALIDATE_IP)) {
			if (filter_var($UserIP, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {

                $DBResult = $this->	_ipRegistrationPluginQueryDB($UserIP, "");
                if ($DBResult) {
					$Result['Response']['Status'] = "Exists";
					$Result['Response']['Location'] = "External";
					$Result['Response']['Message'] = 'IP is already registered.';
					$this->setResponse(200, 'IP Registration Plugin: IP Address already registered in the database: '.$UserIP);
					$this->logger->debug('IP Address already registered in the database',$Result);
					return $Result;
				} else {
					// Write to DB
					$IPRegistration = [
						'datetime' => date("j F Y g:ia"),
						'type' => 'Auto',
						'ip' => $UserIP,
						'username' => $this->user['username']
					];
					$query = [
						array(
							'function' => 'query',
							'query' => array(
								'INSERT INTO [IPREGISTRATION]',
								$IPRegistration
							)
						),
					];
					$this->processQueries($query);

					// Check it was added to Database OK
					$IPs = $this->	_ipRegistrationPluginQueryDB($UserIP, "");
					if ($IPs) {
						$Result['Response']['Status'] = "Added";
						$Result['Response']['Location'] = "External";
						$Result['Response']['Message'] = 'IP Address Registered Successfully.';
						$this->setResponse(200, 'IP Registration Plugin: Added IP Address to the database successfully: '.$UserIP);
						$this->logger->info('Added IP Address to the database successfully',$Result);
						$this->_ipRegistrationPluginUpdateFirewall();
                        $this->_ipRegistrationPluginCleanupDB();
						return $Result;
					} else {
						$Result['Response']['Status'] = "Error";
						$Result['Response']['Location'] = "External";
						$Result['Response']['Message'] = 'Failed to add IP Address to database.';
						$this->setResponse(409, 'IP Registration Plugin: Failed to add IP Address to database: '.$UserIP);
						$this->logger->warning('Failed to add IP Address to database',$Result);
						return $Result;
                    }
				}
			} else {
				$Result['Response']['Status'] = "OK";
				$Result['Response']['Location'] = "Internal";
				$Result['Response']['Message'] = "Internal IP Address";
				$this->setResponse(200, "IP Registration Plugin: Internal IP Address Found");
				return $Result;
			}
		} else {
			$Result['Response']['Status'] = "Bad IP Address";
			$this->setResponse(409, "IP Registration Plugin: Bad IP Address Found");
			return $Result;
		}
	}

	public function _ipRegistrationPluginListIPs() {
		$IPs = $this->_ipRegistrationPluginQueryDB("","");
        foreach ($IPs as $IP) {
            echo $IP['ip'].PHP_EOL;
        }
	}

	public function _ipRegistrationPluginQueryIPs() {
		if ($this->qualifyRequest(1, false)) {
			$IPs = $this->_ipRegistrationPluginQueryDB("","");
			return array_reverse($IPs);
		} else {
			$IPs[] = $this->_ipRegistrationPluginQueryDB("",$this->user['username']);
			return array_reverse($IPs);
		}
	}

	public function _ipRegistrationPluginCleanupDB() {
        $query = [
            array(
                'function' => 'fetchAll',
                'query' => 'DELETE FROM IPREGISTRATION WHERE id NOT IN (SELECT id FROM IPREGISTRATION ORDER BY id DESC LIMIT '.$this->config['IPREGISTRATION-PfSense-Maximum-IPs'].')'
            )
        ];
        return $this->processQueries($query);
	}


	public function _ipRegistrationPluginUpdateFirewall() {
		$this->setLoggerChannel('IP Registration Plugin');
		require 'vendor/autoload.php';
		$ssh = new phpseclib\Net\SSH2($this->config['IPREGISTRATION-PfSense-IP']);
		if (!$ssh->login($this->config['IPREGISTRATION-PfSense-Username'], $this->decrypt($this->config['IPREGISTRATION-PfSense-Password']))) {
			$this->logger->warning('SSH Login Failed for'.$this->config['IPREGISTRATION-PfSense-Username']);
			$this->setResponse(409, "IP Registration Plugin: SSH Login Failed.");
			return $false;
		} else {
			$result = $ssh->exec('sudo /etc/rc.update_urltables now forceupdate '.$this->config['IPREGISTRATION-PfSense-IPTable']);
			if (!$result) {
				$this->logger->debug('pfsense IP table refreshed successfully.',$this->config['IPREGISTRATION-PfSense-IPTable']);
				$this->setResponse(200, 'IP Registration Plugin: pfsense IP table '.$this->config['IPREGISTRATION-PfSense-IPTable'].' refreshed successfully.');
				return $true;
			} else {
				$this->logger->warning('Failed to refresh pfsense IP table '.$this->config['IPREGISTRATION-PfSense-IPTable'],$result);
				$this->setResponse(409, 'IP Registration Plugin: Failed to refresh IP table '.$this->config['IPREGISTRATION-PfSense-IPTable']);
				return $result;
			}
		}
	}
}