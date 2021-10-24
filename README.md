# RegisterIP
Organizr Plugin for IP Registration against PfSense. It allows restricting access to Plex or other services through using PfSense firewall(s) Alias IP Tables.

You create an alias within PfSense to be used against your preferred rules, the URL used in the Alias is provided by the plugin. Within Organizr, you specify an account (non-privileged accounts can be set up using the info below), the IP/FQDN and the Alias IP Table name to the plugin and it will maintain a list of registered IP addresses and force update the pfsense table on demand.

This can be used to restrict access to services at a firewall level until a user has successfully authenticated in Organizr. It is quick and seamless and will update the firewall in under 2 seconds from Organizr homepage launch.

| :exclamation: Important                                                          |
|:---------------------------------------------------------------------------|
| To add this plugin to Organizr, please add https://github.com/TehMuffinMoo/Organizr-Plugins to the Plugins Marketplace within your Organizr instance. |


## How-to
### Configuring the plugin
Before configuring PfSense, you must generate a new API key in the plugin settings and save. Once you have created an Alias and optional privileged account within PfSense, you must return to the plugin settings to enter these here.

An administrator can view all registered IP addresses via the same top-right menu of Organizr.

| :exclamation: Important                                                          |
|:---------------------------------------------------------------------------|
| Plugins do not currently support homepage items natively for Organizr, so you will need to instead make use of Custom HTML for the actual registration component of this plugin. |

Paste the code at the bottom of this README into a Custom HTML homepage item, ensuring to provide the same minimum authentication as you have specified in the plugin. There are also two parameters to change in the script at the bottom, one for an IP or FQDN and one for a TCP port to be checked. I use this for checking connectivity to Plex to know the IP has been successfully registered and updated in the firewall. An example can be seen in the clip below;

<img src="https://user-images.githubusercontent.com/51195492/138614316-cdf7c842-9f67-4b6b-a93e-fb6ec097a6bc.gif" width="40%" height="40%"/>
### Configuring PfSense
There are only a couple of steps required in PfSense.

#### Create Alias
1) Create a new IP Alias via Firewall -> Aliases
2) Select "URL (IPs)" as the Type
3) For the URL, enter your Organizr URL followed by `api/plugins/ipregistration/list?ApiKey=PluginAPIKey`
    - I.e: `https://yourorganizrurl.com/api/v2/plugins/ipregistration/list?ApiKey=feFgGh4rt4twses`

#### Create Restricted Account (Recommended)
It is recommended to create a non-privilleged account to be used by the IP Registration plugin. You can do this by making use of the PfSense sudo package.
1) In PfSense, go to System -> Package Manager -> Available Packages and install sudo
2) Once installed, head to System -> User Manager to create a new account
3) Add a new account granting it `User - System: Shell account access` and saving
4) Next go to System -> Sudo
5) Add a new User Privilege, selecting your new privileged user.
6) Select "user: root" as the Run As account, and **check** the `No Password` checkbox to prevent re-prompting for sudo password.
7) In the Command List, enter `/etc/rc.update_urltables now forceupdate IP_Alias_Name` where IP_Alias_Name is the Alias you created earlier
8) Save and you're done

### Custom HTML
```
<style>
    .card-body {
        margin-bottom: 0%!important;
    }
    .IP-cards {
        display: block;
    }
</style>
<div class="el-element-overlay row">
  <div class="col-md-12" data-toggle="collapse" href="#ip-collapse" data-parent="#ip" aria-expanded="false" aria-controls="ip-collapse">
	<h4 class="pull-left homepage-element-title"><span lang="en">IP Registration</span></h4>
	<h4 class="pull-left"> </h4>
    <hr class="hidden-xs ml-2">
  </div>
  <div class="panel-collapse collapse in" id="ip-collapse" aria-labelledby="ip-heading" role="tabpanel" aria-expanded="false" style="">
	<div class="ipregistrationcards">
      <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-xs-12">
        <div class="card bg-inverse text-white mb-3 monitorr-card">
          <div class="card-body bg-org-alt pt-1 pb-1">
            <div class="d-flex no-block align-items-center">
              <div class="left-health bg-light" id="Connection-Health"></div>
              <div class="ml-1 w-100">
                <i class="spinner-border text-light font-20 pull-right mt-3 mb-2" id="Connection-Circle"></i>
                <h3 class="d-flex no-block align-items-center mt-2 mb-2" id="Connection"><img class="lazyload loginTitle loading">Checking...</h3>
                <div class="clearfix"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-xs-12">
        <div class="card bg-inverse text-white mb-3 monitorr-card">
          <div class="card-body bg-org-alt pt-1 pb-1">
            <div class="d-flex no-block align-items-center">
              <div class="left-health bg-light" id="Info-Health"></div>
              <div class="ml-1 w-100">
                <i class="spinner-border text-light font-20 pull-right mt-3 mb-2" id="Info-Circle"></i>
                <h3 class="d-flex no-block align-items-center mt-2 mb-2" id="Info"><img class="lazyload loginTitle loading">Checking...</h3>
                <div class="clearfix"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-xs-12">
        <div class="card bg-inverse text-white mb-3 monitorr-card">
          <div class="card-body bg-org-alt pt-1 pb-1">
            <div class="d-flex no-block align-items-center">
              <div class="left-health bg-light" id="IP-Health"></div>
              <div class="ml-1 w-100">
                <i class="spinner-border text-light font-20 pull-right mt-3 mb-2" id="IP-Circle"></i>
                <h3 class="d-flex no-block align-items-center mt-2 mb-2" id="IP"><img class="lazyload loginTitle loading">Checking...</h3>
                <div class="clearfix"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
var xmlhttp = new XMLHttpRequest();
xmlhttp.onreadystatechange = function() {
  if (this.readyState == 4 && this.status == 200) {
    var RequestJSON = JSON.parse(this.responseText);
    document.getElementById("IP").innerHTML = RequestJSON.response.data.Response.IP;
    document.getElementById("Info").innerHTML = RequestJSON.response.data.Response.Message;
    
    if ($.inArray(RequestJSON.response.data.Response.Status, ['Error', 'Adding', 'Added', 'OK', 'Exists']) >= 0) {
        document.getElementById("IP-Circle").classList.remove("spinner-border","text-light");
        document.getElementById("IP-Health").classList.remove("bg-light");
        document.getElementById("Info-Circle").classList.remove("spinner-border","text-light");
        document.getElementById("Info-Health").classList.remove("bg-light");
    }
    if ($.inArray(RequestJSON.response.data.Response.Status, ['Error']) >= 0) {
        document.getElementById("IP-Circle").classList.add("text-danger","fa","fa-times-circle");
        document.getElementById("IP-Health").classList.add("bg-danger");
        document.getElementById("Info-Circle").classList.add("text-danger","fa","fa-times-circle");
        document.getElementById("Info-Health").classList.add("bg-danger");
    }
    if (RequestJSON.response.data.Response.Status == "Added") {
        document.getElementById("IP-Circle").classList.add("text-info","fa","fa-check-circle");
        document.getElementById("IP-Health").classList.add("bg-info");
        document.getElementById("Info-Circle").classList.add("text-info","fa","fa-check-circle");
        document.getElementById("Info-Health").classList.add("bg-info");
    }
    if ($.inArray(RequestJSON.response.data.Response.Status, ['Exists', 'OK']) >= 0) {
        document.getElementById("IP-Circle").classList.add("text-success","fa","fa-check-circle");
        document.getElementById("IP-Health").classList.add("bg-success");
        document.getElementById("Info-Circle").classList.add("text-success","fa","fa-check-circle");
        document.getElementById("Info-Health").classList.add("bg-success");
    }
  }
};
xmlhttp.open("GET", "/api/v2/plugins/ipregistration/register", true);
xmlhttp.send();
</script>

<script src="/apilet/Status/js/ping.js"></script>
<script type="text/javascript">
	function checkServer() {
		var p = new Ping();
		var server = "yourplexiporfqdn"; // Enter Plex IP or FQDN here, no http/s or port
		var timeout = 3000; //Milliseconds
		p.ping(server+":32400", function(data) { // Specify port number here if different to TCP/32400
			var serverMsg = document.getElementById( "Connection" );
	   		if (data < 3000){
				serverMsg.innerHTML = "Plex is reachable.";
                document.getElementById("Connection-Circle").classList.remove("spinner-border","text-light");
                document.getElementById("Connection-Health").classList.remove("bg-light","bg-danger");
				document.getElementById("Connection-Circle").classList.add("fa","fa-check-circle","text-success");
                document.getElementById("Connection-Health").classList.add("bg-success");
			}else{
				serverMsg.innerHTML = "Plex is unavailable.";
                document.getElementById("Connection-Circle").classList.remove("spinner-border","text-light");
                document.getElementById("Connection-Health").classList.remove("bg-light","bg-success");
				document.getElementById("Connection-Circle").classList.add("fa","fa-times-circle","text-danger");
                document.getElementById("Connection-Health").classList.add("bg-danger");
				setTimeout("checkServer()",5000);
			}
		}, timeout);
	}
    checkServer();
</script>
```
