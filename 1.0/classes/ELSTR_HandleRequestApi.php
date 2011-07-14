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
    // Read the api name from the URL
    $apiname = $_PATHS[$key + 1];

    $parameterString = "?api";

    $n = 0;
    for ($i = $key + 2; $i < count($_PATHS); $i++) {
        $parameterString .= "&";
        $parameterString .= "_" . $n;
        $parameterString .= "=";
        $parameterString .= $_PATHS[$i];
        $n += 1;
    }

    $getKeys = array_keys($_GET);
    for ($i = 0; $i < count($getKeys); $i++) {
        $parameterString .= "&";
        $parameterString .= $getKeys[$i];
        $parameterString .= "=";
        $parameterString .= $_GET[$getKeys[$i]];
    }

    $redirectUrl = $protocol . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'];
    for ($i = 0; $i < $key; $i++) {
        $redirectUrl .= "/";
        $redirectUrl .= $_PATHS[$i];
    }
    $redirectUrl .= "/";
    $redirectUrl .= $apiname . ".php";
    $redirectUrl .= $parameterString;

    if (file_exists("../" . $apiname . ".php")) {
        header('Location: ' . $redirectUrl);
    } else {
        // Application name does not exist
        header("HTTP/1.0 404 Not Found");
    }
} else {
    // No api was defined
    header("HTTP/1.0 404 Not Found");
}
?>