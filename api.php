<?php
$app->get('/plugins/ipRegistration/settings', function ($request, $response, $args) {
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
$app->get('/plugins/ipRegistration/launch', function ($request, $response, $args) {
	$ipRegistrationPlugin = new ipRegistrationPlugin();
	if ($ipRegistrationPlugin->checkRoute($request)) {
		if ($ipRegistrationPlugin->qualifyRequest($ipRegistrationPlugin->config['ipRegistration-pluginAuth'], true)) {
			$ipRegistrationPlugin->_ipRegistrationPluginLaunch();
		}
	}
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});
$app->get('/plugins/ipRegistration/register', function ($request, $response, $args) {
	$ipRegistrationPlugin = new ipRegistrationPlugin();
	if ($ipRegistrationPlugin->checkRoute($request)) {
		if ($ipRegistrationPlugin->qualifyRequest($ipRegistrationPlugin->config['ipRegistration-pluginAuth'], true)) {
			$GLOBALS['api']['response']['data'] = $ipRegistrationPlugin->_ipRegistrationPluginIPRegistration();
		}
	}
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});
$app->get('/plugins/ipRegistration/update', function ($request, $response, $args) {
	$ipRegistrationPlugin = new ipRegistrationPlugin();
	if ($ipRegistrationPlugin->checkRoute($request)) {
		if ($ipRegistrationPlugin->qualifyRequest($ipRegistrationPlugin->config['ipRegistration-pluginAuth'], true)) {
			$GLOBALS['api']['response']['data'] = $ipRegistrationPlugin->_ipRegistrationPluginUpdateFirewall();
		}
	}
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});
$app->get('/plugins/ipRegistration/list', function ($request, $response, $args) {
	$ipRegistrationPlugin = new ipRegistrationPlugin();
	if ($ipRegistrationPlugin->checkRoute($request)) {
		if ($ipRegistrationPlugin->qualifyRequest(999, true)) {
			$GLOBALS['api'] = $ipRegistrationPlugin->_ipRegistrationPluginListIPs();
		}
	}
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});