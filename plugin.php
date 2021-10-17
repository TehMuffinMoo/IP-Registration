<?php
// PLUGIN INFORMATION
$GLOBALS['plugins']['registerip'] = array( // Plugin Name
	'name' => 'Register IP', // Plugin Name
	'author' => 'TehMuffinMoo', // Who wrote the plugin
	'category' => 'Access Management', // One to Two Word Description
	'link' => '', // Link to plugin info
	'license' => 'personal', // License Type use , for multiple
	'idPrefix' => 'REGISTERIP', // html element id prefix (All Uppercase)
	'configPrefix' => 'REGISTERIP', // config file prefix for array items without the hypen (All Uppercase)
	'version' => '1.0.1', // SemVer of plugin
	'image' => 'api/plugins/registerIP/logo.png', // 1:1 non transparent image for plugin
	'settings' => true, // does plugin need a settings modal?
	'bind' => true, // use default bind to make settings page - true or false
	'api' => 'api/v2/plugins/registerip/settings', // api route for settings page (All Lowercase)
	'homepage' => false // Is plugin for use on homepage? true or false
);

class registerIPPlugin extends Organizr
{

    public function _registerIPPluginGetSettings()
	{
		return array(
			'Plugin Settings' => array(
				$this->settingsOption('auth', 'REGISTERIP-pluginAuth'),
				$this->settingsOption('input', 'REGISTERIP-PfSense-IP', ['label' => 'The IP / FQDN of your pfsense']),
                $this->settingsOption('input', 'REGISTERIP-PfSense-IPTable', ['label' => 'The name of the IP Alias in pfsense']),
                $this->settingsOption('input', 'REGISTERIP-PfSense-Username', ['label' => 'The username of your pfsense account']),
                $this->settingsOption('passwordalt', 'REGISTERIP-PfSense-Password', ['label' => 'The password of your pfsense account']),

			),
		);
	}

	public function _registerIPPluginLaunch()
	{
		$user = $this->getUserById($this->user['userID']);
		if ($user) {
			$this->setResponse(200, 'User approved for plugin');
			return true;
		}
		$this->setResponse(401, 'User not approved for plugin');
		return false;
	}

	public function _registerIPPluginRegisterIP()
	{
        $dir = "/var/www/portal.tmmn.uk/api/plugins/registerIP";
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

                $IPs = file("$dir/IP.txt");
                $IPsfound = false;
                foreach($IPs as $IPsLine)
                {
                  if(strpos($IPsLine, $UserIP) !== false) {
                    $IPslineData = explode(' # ', $IPsLine);
                    if ($IPslineData[0] == $UserIP) {
                        $exists = TRUE;
                      }
                  }
                }
            
                if ($exists == TRUE) {
                    // Write to log file
                    $log = fopen("$dir/IP.log", "a");
                    $logdate = date("j F Y @ g:ia");
                    $logstring = $logdate . ",Auto," . $UserIP . "," . $this->user['username'] . ",Exists" . PHP_EOL;
                    fwrite($log, $logstring);
                    fclose($log);
            
                    $this->setResponse(200, 'IP Address already registered in the database: '.$UserIP);
                    $Result['Response']['Status'] = "Exists";
                    $Result['Response']['Location'] = "External";
                    $Result['Response']['Message'] = 'IP Address is already registered in the database: '.$UserIP;
            
                }
                else if ($exists == FALSE) {
                    $file = "$dir/IP.txt";
                    $f = fopen($file, 'r');
                    $line = fgets($f);
                    fclose($f);
                    $contents = file($file, FILE_IGNORE_NEW_LINES);
                    $first_line = array_shift($contents);
                    file_put_contents($file, implode("\r\n", $contents));
            
                    $this->setResponse(200, 'Adding IP Address to database: '.$UserIP);
                    $Result['Response']['Status'] = "Adding";
                    $Result['Response']['Location'] = "External";
                    $Result['Response']['Message'] = 'Adding IP Address to database: '.$UserIP;
            
                    // Add new IP Address
                    $file = fopen("$dir/IP.txt", "a");
                    $date = date("j F Y g:ia");
                    $string = PHP_EOL . $UserIP . " # Auto # Added on " . $date . " by " . $this->user['username'] . PHP_EOL;
                    fwrite($file, $string);
                    fclose($file);
            
                    // Write to log file
                    $log = fopen("$dir/IP.log", "a");
                    $logdate = date("j F Y @ g:ia");
                    $logstring = $logdate . ",Auto," . $UserIP . "," . $this->user['username'] . ",New" . PHP_EOL;
                    fwrite($log, $logstring);
                    fclose($log);
                    
                    // Check to make sure it was entered OK
                    $exists = FALSE;
                    $IPs = file("$dir/IP.txt");
                    foreach($IPs as $IPsLine) {
                        if(strpos($IPsLine, $UserIP) !== false) {
                            $IPslineData = explode(' # ', $IPsLine);
                            if ($IPslineData[0] == $UserIP) {
                                $this->setResponse(200, 'Added IP Address to the database successfully: '.$UserIP);
                                $Result['Response']['Status'] = "Added";
                                $Result['Response']['Location'] = "External";
                                $Result['Response']['Message'] = 'Added IP Address to the database successfully: '.$UserIP;
                                $this->_registerIPPluginUpdateFirewall();
                                return $Result;
                            }
                        }
                    }
                    if ($Result->response->Added != true) {
                        $this->setResponse(409, 'Register IP Plugin : Failed to add IP Address to database: '.$UserIP);
                        $this->writeLog('error', 'Register IP Plugin : Failed to add IP Address to database: '.$UserIP, $this->user['username']);
                        $Result['Response']['Status'] = "Error";
                        $Result['Response']['Location'] = "External";
                        $Result['Response']['Message'] = 'Register IP Plugin : Failed to add IP Address to database: '.$UserIP;
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

    public function _registerIPPluginUpdateFirewall() {

        require 'vendor/autoload.php';
        $ssh = new phpseclib\Net\SSH2($this->config['REGISTERIP-PfSense-IP']);
        if (!$ssh->login($this->config['REGISTERIP-PfSense-Username'], $this->decrypt($this->config['REGISTERIP-PfSense-Password']))) {
            $this->setResponse(409, "Register IP Plugin : SSH Login Failed.");
            $this->writeLog('error', 'Register IP Plugin : SSH Login Failed for'.$this->config['REGISTERIP-PfSense-Username'], $this->user['username']);
            return $false;
        } else {
            $result = $ssh->exec('sudo /etc/rc.update_urltables now forceupdate '.$this->config['REGISTERIP-PfSense-IPTable']);
                if (!$result) {
                    $this->setResponse(200, 'Register IP Plugin : '.$this->config['REGISTERIP-PfSense-IPTable'].' refreshed successfully.');
                    $this->writeLog('success', 'Register IP Plugin : '.$this->config['REGISTERIP-PfSense-IPTable'].' refreshed successfully.', $this->user['username']);
                    return $true;
                } else {
                    $this->setResponse(409, 'Register IP Plugin : Failed to refresh '.$this->config['REGISTERIP-PfSense-IPTable']);
                    $this->writeLog('error', 'Register IP Plugin : Failed to refresh '.$this->config['REGISTERIP-PfSense-IPTable'].' : '.$result, $this->user['username']);
                    return $result;
                }

        }

    }
    
}