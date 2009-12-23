<?php

require_once ('ELSTR_JsonServer.php');
/**
* Application Data Server
*
* @version $Id$
* @copyright 2009
*/

require_once ("ELSTR_JsonServer.php");

class ELSTR_ApplicationDataServer {
    private $m_application;

    function __construct($application)
    {
        $this->m_application = $application;
    }

    /**
    * Create a JSON Server and handle itselfs
    *
    * @return void
    */
    public function handle()
    {
        $server = new ELSTR_JsonServer();
        $server->setClass($this);
        $server->handle();
    }

    /**
    * Get the preview for an item or document or project
    *
    * @param string $appNam Name of the application
    * @return array
    */
    public function load($appName)
    {
        $configPublic = $this->m_application->getOption("public");

        if (isset($configPublic[$appName])) {
            $appConfigPublic = array_merge($configPublic['shared'], $configPublic[$appName]);
        } else {
            $appConfigPublic = $configPublic['shared'];
        }

        $result['config'] = $appConfigPublic;

    	$result['user']['username'] = $this->m_application->getBootstrap()->getResource('user')->getUsername();
    	$result['user']['isAuth'] = $this->m_application->getBootstrap()->getResource('auth')->hasIdentity();

    	$result['language']['current'] = $this->m_application->getBootstrap()->getResource("language")->getLocale();
    	$result['language']['translations'] = $this->m_application->getBootstrap()->getResource("language")->getMessages();

    	return $result;
    }
}

?>