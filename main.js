/* This file is loaded when Organizr is loaded */
function ipRegistrationPluginGenerateAPIKey() {
	document.getElementsByName("IPREGISTRATION-ApiToken")[0].value = createRandomString(20);
}