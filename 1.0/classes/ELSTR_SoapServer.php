<?php

/**
 * ELSTR specific wrapper around Zend_Soap_Server.
 * Rethrow errors.
 *
 * @author Marco Egli, Felix Nyffenegger
 * @version 1.0
 * @created 11-May-2012 10:41:59
 */
class ELSTR_SoapServer extends Zend_Soap_Server {
    /**
     * Internal method for handling request
     * Overrid of Zend_Json_Server::_handle();
     * In order handle exception on the ELSTR side, we need to push the exception foreward
     * 
     * @return void
     */
    // TODO: Implement Elstr specific protected function _handle()
}

