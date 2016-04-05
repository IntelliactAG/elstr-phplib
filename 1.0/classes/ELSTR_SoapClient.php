<?php

/**
 * ELSTER specific implementation of the Zend_Soap_Client
 * 
 * @author Marco Egli
 * @copyright 2010 Intelliact AG
 */


class ELSTR_SoapClient extends Zend_Soap_Client {
    public function __construct() {
       parent::__construct();
    }    
}

