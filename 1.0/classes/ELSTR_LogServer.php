<?php

require_once ('ELSTR_JsonServer.php');
require_once ('ELSTR_Server_Abstract.php');
	
/**
 * Class to handle multi language strings
 *
 * @author Marco Egli
 * @copyright 2014 Intelliact AG
 */

class ELSTR_LogServer extends ELSTR_Server_Abstract {

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
     *
     * Funktion fur das Loggen einer Meldung
     *
     * @param string $level
     * @param string $arguments
     * @return array
     *
     */
    public function write($level, $arguments) {
        Zend_Session::writeClose(true);
        $logger = $this->m_application->getBootstrap()->getResource("logger");
        if (isset($logger)) {
            $logger->$level(strtoupper($level). ' [CLIENT] : ' . print_r($arguments, true));    
        }

        return null;
    }

}

