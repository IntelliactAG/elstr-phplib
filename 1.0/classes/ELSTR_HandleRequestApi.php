<?php

/**
 * ELSTR Generic Api Handler
 * Use the follwing URL to call the api:
 * http://<myserver>/api/<application name>/<api parameters>
 */

$protocol = "http://";
if (isset($_SERVER["HTTPS"]) == true && $_SERVER["HTTPS"] == "on") {
    $protocol = "https://";
}

$url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$urlInfo = parse_url($url);
$_PATHS = explode('/', substr($urlInfo['path'], 1));
$key = array_search('api', $_PATHS);

if ($key > -1 && isset($_PATHS[$key + 1])) {
    $isApiRequest = true;

    // Read the api name from the URL
    $apiName = $_PATHS[$key + 1];
    
    $n = 0;
    $apiParameters = $_GET;
    for ($i = $key + 2; $i < count($_PATHS); $i++) {
        $apiParameters["_" . $n] = urldecode($_PATHS[$i]);
        $n += 1;
    }

    $apiBase = $protocol . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'];
    for ($i = 0; $i < $key; $i++) {
        $apiBase .= "/";
        $apiBase .= $_PATHS[$i];
    }
    $apiBase .= "/";

    if (file_exists($apiName . ".php")) {
        include_once($apiName . ".php");
    } else {
        // Application name does not exist
        header("HTTP/1.0 404 Not Found");
    }
} else {
    // No api was defined
    header("HTTP/1.0 404 Not Found");
}
