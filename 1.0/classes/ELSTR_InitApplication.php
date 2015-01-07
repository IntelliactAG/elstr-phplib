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
//Override default libs config values with application specific libs config values
if (isset($configPublic[APPLICATION_NAME]['libs'])) {
    $configPublic['libs'] = array_merge($configPublic['libs'], $configPublic[APPLICATION_NAME]['libs']);
}

define('APPLICATION_VERSION', $configPublic['libs']['appVersion']);
$elstrVersion = $configPublic['libs']['elstrVersion'];

$elstrHeader = "";
$elstrHeader .= "<script  type='text/javascript'>".PHP_EOL;
$elstrHeader .= "LIBS = " . Zend_Json::encode($configPublic['libs']) . ";".PHP_EOL;
$elstrHeader .= "LIBS.appName = '" . APPLICATION_NAME . "';".PHP_EOL;
//if ($_SERVER['REQUEST_METHOD'] == "GET" && isset($_GET['api'])) {
//    $elstrHeader .= "API = " . Zend_Json::encode($_GET) . ";".PHP_EOL;
//}
$elstrHeader .= "</script>".PHP_EOL;

if ($_SERVER['REQUEST_METHOD'] == "GET" && isset($isApiRequest) && $isApiRequest == true) {
    $elstrHeader .= "<script  type='text/javascript'>".PHP_EOL;
    $elstrHeader .= "API = " . Zend_Json::encode($apiParameters) . ";".PHP_EOL;
    $elstrHeader .= "</script>".PHP_EOL;
    $elstrHeader .= "<base href='" . $apiBase . "' />";
} elseif ($_SERVER['REQUEST_METHOD'] == "GET" && isset($_GET['api'])) {
    // Needed for backward compatibility where an api call was redirected
    $elstrHeader .= "<script  type='text/javascript'>".PHP_EOL;
    $elstrHeader .= "API = " . Zend_Json::encode($_GET) . ";".PHP_EOL;
    $elstrHeader .= "</script>".PHP_EOL;
}

// Load the correct YUI-Seedfile
if (strpos($elstrVersion, "1.") === 0) {
    $yuiVersion = $configPublic['libs']['yuiVersion'];
    $elstrHeader .= "<script type='text/javascript' src='jslib/yui/" . $yuiVersion . "/build/yuiloader/yuiloader-min.js' ></script>".PHP_EOL;
    $application->getBootstrap()->getResource("language")->cleanup();
    $elstrHeader .= "<script type='text/javascript' src='" . APPLICATION_VERSION . "/" . APPLICATION_NAME . "/" . APPLICATION_NAME . ".js' ></script>";
} elseif(strpos($elstrVersion, "2.") === 0) {
    // Load the YUI3 used with elstr 2.0 on frontend
    $yuiVersion = $configPublic['libs']['yuiVersion'];
    $elstrHeader .= "<script type='text/javascript' src='jslib/yui/" . $yuiVersion . "/build/yui/yui-min.js' ></script>".PHP_EOL;
    $application->getBootstrap()->getResource("language")->cleanup();
    if (isset($languageModulesToRegister)) {
        $application->getBootstrap()->getResource("language")->registerModules($languageModulesToRegister);
        $translations = $application->getBootstrap()->getResource("language")->getTranslation();
    }
    require_once ('ELSTR_ApplicationDataServer.php');
    $applicationDataServer = new ELSTR_ApplicationDataServer($application);
    $elstrHeader .= "<script  type='text/javascript'>".PHP_EOL;
    $elstrHeader .= "ELSTR = {".PHP_EOL;
    $elstrHeader .= "    applicationEnv : \"" . APPLICATION_ENV . "\",".PHP_EOL;    
    $elstrHeader .= "    applicationData : " . Zend_Json::encode($applicationDataServer->load(APPLICATION_NAME)) . ",".PHP_EOL;
    $elstrHeader .= "    modules : " . file_get_contents(APPLICATION_PATH . "/public/jslib/elstr/" . $configPublic['libs']['elstrVersion'] . "/build/modules.txt") . PHP_EOL;
    $elstrHeader .= "}".PHP_EOL;
    $elstrHeader .= "</script>".PHP_EOL;
    $elstrHeader .= "<script type='text/javascript' src='" . APPLICATION_VERSION . "/" . APPLICATION_NAME . "/" . APPLICATION_NAME . ".js' ></script>";
} elseif(strpos($elstrVersion, "3.") === 0) {
    // Load the Elstr 3.0 frontend
    $application->getBootstrap()->getResource("language")->cleanup();
    if (isset($languageModulesToRegister)) {
        $application->getBootstrap()->getResource("language")->registerModules($languageModulesToRegister);
        $translations = $application->getBootstrap()->getResource("language")->getTranslation();
    }
    require_once ('ELSTR_ApplicationDataServer.php');
    $applicationDataServer = new ELSTR_ApplicationDataServer($application);
    $elstrHeader .= "<script  type='text/javascript'>".PHP_EOL;
    $elstrHeader .= "ELSTR = {".PHP_EOL;
    $elstrHeader .= "    applicationEnv : \"" . APPLICATION_ENV . "\",".PHP_EOL; 
    $elstrHeader .= "    applicationData : " . Zend_Json::encode($applicationDataServer->load(APPLICATION_NAME)) . ",".PHP_EOL;    
    $elstrHeader .= "}".PHP_EOL;
    $elstrHeader .= "</script>".PHP_EOL;
    $elstrHeader .= "<script type='text/javascript' src='" . APPLICATION_VERSION . "/dist/" . APPLICATION_NAME . ".main.js' ></script>";
}


?>