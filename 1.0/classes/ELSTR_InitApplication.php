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
	$elstrHeader .= "LIBS.".$libKeys[$i]." = '" . $configPublic['libs'][$libKeys[$i]] . "';\n";
}
$elstrHeader .= "LIBS.appName = '" . APPLICATION_NAME . "';\n";

if ($_SERVER['REQUEST_METHOD'] == "GET" && isset($_GET['api'])) {
	$elstrHeader .= "if (API == undefined) {\n";
	$elstrHeader .= "    var API = new Object();\n";
	$elstrHeader .= "};\n";
	$getKeys = array_keys($_GET);
	for ($i = 0; $i < count($getKeys); $i++) {
		$elstrHeader .= "API.".$getKeys[$i]." = '" . $_GET[$getKeys[$i]] . "';\n";
    }	
}

$elstrHeader .= "</script>\n";

$elstrHeader .= "<script type='text/javascript' src='jslib/yui/$yuiVersion/build/yuiloader/yuiloader-min.js' ></script>\n";
$elstrHeader .= "<script type='text/javascript' src='".APPLICATION_VERSION."/".APPLICATION_NAME."/".APPLICATION_NAME.".js' ></script>";

$application->getBootstrap()->getResource("language")->cleanup();

?>