<?php

/**
 * This module creates an application and bootstraps the full application
 * Before the initialisation the constant APPLICATION_NAME must be defined
 *
 * @author Felix Nyffenegger, Marco Egli
 */
require_once 'Zend/Application.php';

// Create application, bootstrap main application, and run
$application = new Zend_Application(
                APPLICATION_ENV,
                APPLICATION_PATH . '/application/configs/config.ini'
);
$application->bootstrap()
        ->run();

// Get the frontend configruations
$configPublic = $application->getOption("public");
$yuiVersion = $configPublic['libs']['yuiVersion'];
$appVersion = $configPublic['libs']['appVersion'];
define('APPLICATION_VERSION', $appVersion);


$elstrHeader = "";
$elstrHeader .= "<script  type='text/javascript'>\n";
$elstrHeader .= "if (LIBS == undefined) {\n";
$elstrHeader .= "    var LIBS = new Object();\n";
$elstrHeader .= "};\n";
$libKeys = array_keys($configPublic['libs']);
for ($i = 0; $i < count($libKeys); $i++) {
    $elstrHeader .= "LIBS." . $libKeys[$i] . " = '" . $configPublic['libs'][$libKeys[$i]] . "';\n";
}
$elstrHeader .= "LIBS.appName = '" . APPLICATION_NAME . "';\n";

if ($_SERVER['REQUEST_METHOD'] == "GET" && isset($_GET['api'])) {
    $elstrHeader .= "if (API == undefined) {\n";
    $elstrHeader .= "    var API = new Object();\n";
    $elstrHeader .= "};\n";
    $getKeys = array_keys($_GET);
    for ($i = 0; $i < count($getKeys); $i++) {
        $elstrHeader .= "API." . $getKeys[$i] . " = '" . str_replace("'", "\'", $_GET[$getKeys[$i]]) . "';\n";
    }
}

$elstrHeader .= "</script>\n";

// Load the correct YUI-Seedfile
if (strpos($yuiVersion, "2.") === 0) {
    $elstrHeader .= "<script type='text/javascript' src='jslib/yui/$yuiVersion/build/yuiloader/yuiloader-min.js' ></script>\n";
} else {
    // Load the YUI3 used with elstr 2.0 on frontend
    $elstrHeader .= "<script type='text/javascript' src='http://yui.yahooapis.com/$yuiVersion/build/yui/yui-min.js' ></script>\n";
    require_once ('ELSTR_ApplicationDataServer.php');
    $applicationDataServer = new ELSTR_ApplicationDataServer($application);
    $elstrHeader .= "<script  type='text/javascript'>\n";
    $elstrHeader .= "ELSTR = {\n";
    $elstrHeader .= "    applicationData : " . Zend_Json::encode($applicationDataServer->load(APPLICATION_NAME)) . "\n";
    $elstrHeader .= "}\n";
    $elstrHeader .= "</script>\n";
}

$elstrHeader .= "<script type='text/javascript' src='" . APPLICATION_VERSION . "/" . APPLICATION_NAME . "/" . APPLICATION_NAME . ".js' ></script>";

$application->getBootstrap()->getResource("language")->cleanup();
?>