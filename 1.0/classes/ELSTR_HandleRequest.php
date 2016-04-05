<?php

/**
 * ELSTR Generic Service Handler
 * Authenticate and register the user for this session at ELSTR
 * Use the follwing URL to call services:
 * http://<myserver>/services/<classname of ELSTR_WidgetServer_Abstract implemetation>
 */
$protocol = "http://";
if (isset($_SERVER["HTTPS"]) == true && $_SERVER["HTTPS"] == "on") {
    $protocol = "https://";
}

$url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$urlInfo = parse_url($url);
$_PATHS = explode('/', substr($urlInfo['path'], 1));
$key = array_search('services', $_PATHS);


if ($key > -1 && isset($_PATHS[$key + 1])) {
    // Read the service from the URL
    $servername = $_PATHS[$key + 1];

    // Here we could be more efficient if we destinguish ELSTR_WidgetServer_Abstract
    // from ELSTR_WidgetServer_Acl_Abstract and only set $acl and $user if needed
    // als then we could call a very simple bootstrap such as ServiceSimple.php
    require_once $servername . ".php";
    require_once ('ELSTR_Exception.php');

    $params = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $params = file_get_contents('php://input');
    }
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $params = $_GET;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'HEAD') {
        if(isset($_REQUEST['header-content-type'])){
            header("Content-Type: ".$_REQUEST['header-content-type']);
        }
        return;
    }

    $logger = $application->getBootstrap()->getResource("logger");
    if (isset($logger)) {
        $logger->debug('ELSTR_HandleRequest params: ' . print_r($params,true));
    }

    try {
        $widgetserver = new $servername($application, $params);
        $widgetserver->handle();
    } catch (ELSTR_Exception $e) {

        header($e->getHeader());
        header('Content-type: application/json');
        print json_encode($e->getResponse());
        if (isset($logger)) {
            $logger->err('ELSTR_Exception: ' . print_r($e->getMessage(), true));
            $logger->debug('ELSTR_Exception: ' . print_r($e, true));
        }
    }
} else {
    // No service was defined
    header("HTTP/1.0 404 Not Found");
}
