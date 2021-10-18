<?php
$app->get('/plugins/ipregistration/settings', function ($request, $response, $args) {
	$ipRegistrationPlugin = new ipRegistrationPlugin();
	if ($ipRegistrationPlugin->_ipRegistrationPluginCheckRequest($request) && $ipRegistrationPlugin->checkRoute($request)) {
		if ($ipRegistrationPlugin->qualifyRequest(1, true)) {
			$GLOBALS['api']['response']['data'] = $ipRegistrationPlugin->_ipRegistrationPluginGetSettings();
		}
	}
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});
$app->get('/plugins/ipregistration/launch', function ($request, $response, $args) {
	$ipRegistrationPlugin = new ipRegistrationPlugin();
	if ($ipRegistrationPlugin->checkRoute($request)) {
		if ($ipRegistrationPlugin->qualifyRequest($ipRegistrationPlugin->config['IPREGISTRATION-pluginAuth'], true)) {
			$ipRegistrationPlugin->_ipRegistrationPluginLaunch();
		}
	}
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});
$app->get('/plugins/ipregistration/register', function ($request, $response, $args) {
	$ipRegistrationPlugin = new ipRegistrationPlugin();
	if ($ipRegistrationPlugin->checkRoute($request)) {
		if ($ipRegistrationPlugin->qualifyRequest($ipRegistrationPlugin->config['IPREGISTRATION-pluginAuth'], true)) {
			$GLOBALS['api']['response']['data'] = $ipRegistrationPlugin->_ipRegistrationPluginIPRegistration();
		}
	}
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});
$app->get('/plugins/ipregistration/update', function ($request, $response, $args) {
	$ipRegistrationPlugin = new ipRegistrationPlugin();
	if ($ipRegistrationPlugin->checkRoute($request)) {
		if ($ipRegistrationPlugin->qualifyRequest($ipRegistrationPlugin->config['IPREGISTRATION-pluginAuth'], true)) {
			$GLOBALS['api']['response']['data'] = $ipRegistrationPlugin->_ipRegistrationPluginUpdateFirewall();
		}
	}
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});
$app->get('/plugins/ipregistration/query', function ($request, $response, $args) {
	$ipRegistrationPlugin = new ipRegistrationPlugin();
	if ($ipRegistrationPlugin->checkRoute($request)) {
		if ($ipRegistrationPlugin->qualifyRequest($ipRegistrationPlugin->config['IPREGISTRATION-pluginAuth'], true)) {
			$GLOBALS['api']['response']['data'] = $ipRegistrationPlugin->_ipRegistrationPluginQueryIPs();
		}
	}
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});
$app->get('/plugins/ipregistration/list', function ($request, $response, $args) {
	$ipRegistrationPlugin = new ipRegistrationPlugin();
	if ($ipRegistrationPlugin->checkRoute($request)) {
		if ($_GET['ApiKey'] == $ipRegistrationPlugin->config['IPREGISTRATION-ApiToken'] || $ipRegistrationPlugin->qualifyRequest(1, true)) {
			$GLOBALS['api'] = $ipRegistrationPlugin->_ipRegistrationPluginListIPs();
		}
	}
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});