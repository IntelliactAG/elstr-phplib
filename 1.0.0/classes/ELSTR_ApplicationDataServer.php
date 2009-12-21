<?php

/**
* bla
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
    * @param string $ Name of the application
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

    	return $result;
    }
}

?>