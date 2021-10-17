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
	'api' => 'api/v2/plugins/ipRegistration/settings', // api route for settings page (All Lowercase)
	'homepage' => false // Is plugin for use on homepage? true or false
);

class ipRegistrationPlugin extends Organizr
{

    public function _ipRegistrationPluginGetSettings()
	{
		return array(
			'Plugin Settings' => array(
				$this->settingsOption('auth', 'IPREGISTRATION-pluginAuth'),
				$this->settingsOption('input', 'IPREGISTRATION-PfSense-IP', ['label' => 'The IP / FQDN of your pfsense']),
                $this->settingsOption('input', 'IPREGISTRATION-PfSense-IPTable', ['label' => 'The name of the IP Alias in pfsense']),
                $this->settingsOption('input', 'IPREGISTRATION-PfSense-Username', ['label' => 'The username of your pfsense account']),
                $this->settingsOption('passwordalt', 'IPREGISTRATION-PfSense-Password', ['label' => 'The password of your pfsense account']),

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

	public function _ipRegistrationPluginipRegistration()
	{
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
            if (filter_var($UserIP, FILTER_VALIDATE_IP)) {

                $IPs = file("$dir/IP.log");
                $IPsfound = false;
                foreach($IPs as $IPsLine)
                {
                  if(strpos($IPsLine, $UserIP) !== false) {
                    $IPslineData = explode(',', $IPsLine);
                    if ($IPslineData[2] == $UserIP) {
                        $exists = TRUE;
                      }
                  }
                }
            
                if ($exists == TRUE) {
                    $this->setResponse(200, 'IP Address already registered in the database: '.$UserIP);
                    $Result['Response']['Status'] = "Exists";
                    $Result['Response']['Location'] = "External";
                    $Result['Response']['Message'] = 'IP is already registered.';
                    return $Result;
                } else {
                    $file = "$dir/IP.log";
                    $f = fopen($file, 'r');
                    $line = fgets($f);
                    fclose($f);
                    $contents = file($file, FILE_IGNORE_NEW_LINES);
                    $first_line = array_shift($contents);
                    file_put_contents($file, implode("\r\n", $contents));
            
                    // Write to log file
                    $log = fopen("$dir/IP.log", "a");
                    $logdate = date("j F Y @ g:ia");
                    $logstring = PHP_EOL . $logdate . ",Auto," . $UserIP . "," . $this->user['username'] . ",New" . PHP_EOL;
                    fwrite($log, $logstring);
                    fclose($log);
                    
                    // Check to make sure it was entered OK
                    $exists = FALSE;
                    $IPs = file("$dir/IP.log");
                    foreach($IPs as $IPsLine) {
                        if(strpos($IPsLine, $UserIP) !== false) {
                            $IPslineData = explode(',', $IPsLine);
                            if ($IPslineData[2] == $UserIP) {
                                $this->setResponse(200, 'Added IP Address to the database successfully: '.$UserIP);
                                $Result['Response']['Status'] = "Added";
                                $Result['Response']['Location'] = "External";
                                $Result['Response']['Message'] = 'IP Address Registered Successfully.';
                                //$this->_ipRegistrationPluginUpdateFirewall();
                                return $Result;
                            }
                        }
                    }
                    if ($Result['Response']['Status'] != true) {
                        $this->setResponse(409, 'IP Registration Plugin : Failed to add IP Address to database: '.$UserIP);
                        $this->writeLog('error', 'IP Registration Plugin : Failed to add IP Address to database: '.$UserIP, $this->user['username']);
                        $Result['Response']['Status'] = "Error";
                        $Result['Response']['Location'] = "External";
                        $Result['Response']['Message'] = 'Failed to add IP Address to database.';
                        return $Result;
                    }
                }
            } else {
                $this->setResponse(200, "Internal IP Address");
                $Result['Response']['Status'] = "OK";
                $Result['Response']['Location'] = "Internal";
                $Result['Response']['Message'] = "Internal";
                return $Result;
            }
        } else {
            $this->setResponse(409, "Bad IP Address");
            $Result['Response']['Status'] = "Bad IP Address";
            return $Result;
        }
	}

    public function _ipRegistrationPluginListIPs() {
        $dir = __DIR__;
        $IPFile = file("$dir/IP.log");
        $IPs = [];
        foreach(array_reverse($IPFile) as $IPsLine)
        {
            $IPData = explode(",",$IPsLine);
            $IPs[] = $IPData[2];
            echo $IPData[2].PHP_EOL;
        }
    }

    public function _ipRegistrationPluginUpdateFirewall() {
        require 'vendor/autoload.php';
        $ssh = new phpseclib\Net\SSH2($this->config['IPREGISTRATION-PfSense-IP']);
        if (!$ssh->login($this->config['IPREGISTRATION-PfSense-Username'], $this->decrypt($this->config['IPREGISTRATION-PfSense-Password']))) {
            $this->setResponse(409, "IP Registration Plugin : SSH Login Failed.");
            $this->writeLog('error', 'IP Registration Plugin : SSH Login Failed for'.$this->config['IPREGISTRATION-PfSense-Username'], $this->user['username']);
            return $false;
        } else {
            $result = $ssh->exec('sudo /etc/rc.update_urltables now forceupdate '.$this->config['IPREGISTRATION-PfSense-IPTable']);
            if (!$result) {
                $this->setResponse(200, 'IP Registration Plugin : '.$this->config['IPREGISTRATION-PfSense-IPTable'].' refreshed successfully.');
                $this->writeLog('success', 'IP Registration Plugin : '.$this->config['IPREGISTRATION-PfSense-IPTable'].' refreshed successfully.', $this->user['username']);
                return $true;
            } else {
                $this->setResponse(409, 'IP Registration Plugin : Failed to refresh '.$this->config['IPREGISTRATION-PfSense-IPTable']);
                $this->writeLog('error', 'IP Registration Plugin : Failed to refresh '.$this->config['IPREGISTRATION-PfSense-IPTable'].' : '.$result, $this->user['username']);
                return $result;
            }
        }
    }
}