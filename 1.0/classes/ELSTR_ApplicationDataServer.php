<?php

require_once ('ELSTR_JsonServer.php');
require_once ('ELSTR_Server_Abstract.php');

/**
* Application Data Server
*
* @version $Id$
* @copyright 2009
*/

class ELSTR_ApplicationDataServer  extends ELSTR_Server_Abstract {

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
    	Zend_Session::writeClose(true);
    	$configPublic = $this->m_application->getOption("public");

        if (isset($configPublic[$appName])) {
            $appConfigPublic = array_merge($configPublic['shared'], $configPublic[$appName]);
        } else {
            $appConfigPublic = $configPublic['shared'];
        }

        $result['config'] = $appConfigPublic;

    	$result['user']['username'] = $this->m_application->getBootstrap()->getResource('user')->getUsername();
    	$result['user']['isAuth'] = $this->m_application->getBootstrap()->getResource('auth')->hasIdentity();
		$result['user']['isAdmin'] = $this->m_application->getBootstrap()->getResource('acl')->inheritsRole($result['user']['username'],'role_admin',false);
    	$result['user']['resourcesAllowed'] = $this->m_application->getBootstrap()->getResource('acl')->getResourcesAllowed($this->m_application->getBootstrap()->getResource('db'),$result['user']['username']);
		$result['user']['enterpriseApplicationData'] = $this->m_application->getBootstrap()->getResource('user')->getEnterpriseApplicationData();
    	
    	$result['language']['current'] = $this->m_application->getBootstrap()->getResource("language")->getTranslation()->getLocale();
    	$result['language']['modules'] = $this->m_application->getBootstrap()->getResource("language")->getRegisteredModules();
    	$result['language']['translations'] = $this->m_application->getBootstrap()->getResource("language")->getTranslation()->getMessages();
    	

    	return $result;
    }
}

?>