<?php
$app->get('/plugins/registerip/settings', function ($request, $response, $args) {
	$registerIPPlugin = new registerIPPlugin();
	if ($registerIPPlugin->checkRoute($request)) {
		if ($registerIPPlugin->qualifyRequest(1, true)) {
			$GLOBALS['api']['response']['data'] = $registerIPPlugin->_registerIPPluginGetSettings();
		}
	}
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});
$app->get('/plugins/registerip/launch', function ($request, $response, $args) {
	$registerIPPlugin = new registerIPPlugin();
	if ($registerIPPlugin->checkRoute($request)) {
		if ($registerIPPlugin->qualifyRequest($registerIPPlugin->config['REGISTERIP-pluginAuth'], true)) {
			$registerIPPlugin->_registerIPPluginLaunch();
		}
	}
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});
$app->get('/plugins/registerip/register', function ($request, $response, $args) {
	$registerIPPlugin = new registerIPPlugin();
	if ($registerIPPlugin->checkRoute($request)) {
		if ($registerIPPlugin->qualifyRequest($registerIPPlugin->config['REGISTERIP-pluginAuth'], true)) {
			$GLOBALS['api']['response']['data'] = $registerIPPlugin->_registerIPPluginRegisterIP();
		}
	}
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});
$app->get('/plugins/registerip/update', function ($request, $response, $args) {
	$registerIPPlugin = new registerIPPlugin();
	if ($registerIPPlugin->checkRoute($request)) {
		if ($registerIPPlugin->qualifyRequest($registerIPPlugin->config['REGISTERIP-pluginAuth'], true)) {
			$GLOBALS['api']['response']['data'] = $registerIPPlugin->_registerIPPluginUpdateFirewall();
		}
	}
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});