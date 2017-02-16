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

// Ge the sso configuration
$configSso = $application->getOption("sso");
if(!$application->getBootstrap()->getResource('auth')->hasIdentity() && $configSso){
    include_once 'ELSTR_AuthServer.php';
    $ELSTR_AuthServer = new ELSTR_AuthServer($application);
    $ELSTR_AuthServer->sso();
}


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

// Only mandatory for Elstr 2.x and 3.x projects
require_once ('ELSTR_ApplicationDataServer.php');
$applicationDataServer = new ELSTR_ApplicationDataServer($application);

// Redirect to HTTPS
/*
if(isset($applicationDataServer->load(APPLICATION_NAME)['config']['forceHttps']) && $applicationDataServer->load(APPLICATION_NAME)['config']['forceHttps']){
    if(!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == ""){
        $redirect = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: $redirect");
        exit();
    }
} 
*/

// Load the correct YUI-Seedfile
if (strpos($elstrVersion, "1.") === 0) {
    // Load the Elstr 1.x frontend with YUI2
    $elstrHeader .= "<!-- Elstr 1.x frontend with YUI2 -->".PHP_EOL;     
    $yuiVersion = $configPublic['libs']['yuiVersion'];
    $elstrHeader .= "<script type='text/javascript' src='jslib/yui/" . $yuiVersion . "/build/yuiloader/yuiloader-min.js' ></script>".PHP_EOL;
    $application->getBootstrap()->getResource("language")->cleanup();
    $elstrHeader .= "<script type='text/javascript' src='" . APPLICATION_VERSION . "/" . APPLICATION_NAME . "/" . APPLICATION_NAME . ".js' ></script>";
} elseif(strpos($elstrVersion, "2.") === 0) {
    // Load the Elstr 2.x frontend with YUI3
    $elstrHeader .= "<!-- Elstr 2.x frontend with YUI3 -->".PHP_EOL;    
    $yuiVersion = $configPublic['libs']['yuiVersion'];
    $elstrHeader .= "<script type='text/javascript' src='jslib/yui/" . $yuiVersion . "/build/yui/yui-min.js' ></script>".PHP_EOL;
    $application->getBootstrap()->getResource("language")->cleanup();
    if (isset($languageModulesToRegister)) {
        $application->getBootstrap()->getResource("language")->registerModules($languageModulesToRegister);
        $translations = $application->getBootstrap()->getResource("language")->getTranslation();
    }
    $elstrHeader .= "<script  type='text/javascript'>".PHP_EOL;
    $elstrHeader .= "ELSTR = {".PHP_EOL;
    $elstrHeader .= "    applicationEnv : \"" . APPLICATION_ENV . "\",".PHP_EOL;    
    $elstrHeader .= "    applicationData : " . Zend_Json::encode($applicationDataServer->load(APPLICATION_NAME)) . ",".PHP_EOL;
    $elstrHeader .= "    modules : " . file_get_contents(APPLICATION_PATH . "/public/jslib/elstr/" . $configPublic['libs']['elstrVersion'] . "/build/modules.txt") . PHP_EOL;
    $elstrHeader .= "};".PHP_EOL;
    $elstrHeader .= "</script>".PHP_EOL;
    $elstrHeader .= "<script type='text/javascript' src='" . APPLICATION_VERSION . "/" . APPLICATION_NAME . "/" . APPLICATION_NAME . ".js' ></script>";
} elseif(strpos($elstrVersion, "3.") === 0) {
    // Load the Elstr 3.x frontend with React
    $elstrHeader .= "<!-- Elstr 3.x frontend with React -->".PHP_EOL;

    define('APPLICATION_BUILD_FOLDER', $configPublic['libs']['appBuildFolder']);

    $application->getBootstrap()->getResource("language")->cleanup();
    if (isset($languageModulesToRegister)) {
        $application->getBootstrap()->getResource("language")->registerModules($languageModulesToRegister);
        $translations = $application->getBootstrap()->getResource("language")->getTranslation();
    }
    $elstrHeader .= "<script  type='text/javascript'>".PHP_EOL;
    $elstrHeader .= "ELSTR = {".PHP_EOL;
    $elstrHeader .= "    applicationEnv : \"" . APPLICATION_ENV . "\",".PHP_EOL; 
    $elstrHeader .= "    applicationData : " . Zend_Json::encode($applicationDataServer->load(APPLICATION_NAME)) . ",".PHP_EOL;    
    $elstrHeader .= "};".PHP_EOL;
    $elstrHeader .= "</script>".PHP_EOL;

    if (isset($configPublic['libs']) &&
        isset($configPublic['libs']['liveReloadHost'])) {

        $elstrHeader .= "<script type='text/javascript' src='" . $configPublic['libs']['liveReloadHost'] . "public/app.dev/".APPLICATION_BUILD_FOLDER."/" . APPLICATION_NAME . ".main.js' ></script>";

    }else{
        $elstrHeader .= "<script type='text/javascript' src='" . APPLICATION_VERSION . "/".APPLICATION_BUILD_FOLDER."/" . APPLICATION_NAME . ".main.js' ></script>";
    }
}
