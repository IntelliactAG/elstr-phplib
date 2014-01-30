<?php

require_once ('ELSTR_WidgetServer_Abstract.php');
require_once ('ELSTR_SoapServer.php');

/**
 * This is an abstract SOAP WidgetServer implementation
 *
 * @author Marco Egli, Felix Nyffenegger
 * @version 1.0
 * @created 11-May-2012 10:41:59
 */
abstract class ELSTR_WidgetServer_SOAP_Abstract extends ELSTR_WidgetServer_Abstract {

    protected $m_server;
    protected $m_isWsdlRequest = false;

    function __construct($application, $params = null) {
        parent::__construct($application, $params);

        //echo $params;
        
        if (isset($_GET['wsdl'])) {
            $this->m_isWsdlRequest = true;
            $this->m_server = new Zend_Soap_AutoDiscover();
            $this->m_server->setClass(get_class($this));
        } else {
            //TODO: Add possibilities of $options loaded from configurations file
            $options = null;
            $this->m_server = new ELSTR_SoapServer($_SERVER['SCRIPT_URI']."?wsdl",$options);
            $this->m_server->setClass(get_class($this));
            $this->m_server->setObject($this);
        }
    }

    /**
     * Implementation of the abstract _getMethod
     */
    protected function _getMethod() {
        //TODO: Return requested method for ACL
    }

    /**
     * Create a SOAP Server and handle itself
     *
     * @return void
     */
    protected function _handle() {        
        $this->m_server->handle($this->m_params);
    }

    /**
     * Get response object
     */
    protected function _getResponse() {
        //TODO: Return something. The method "$this->m_server->getResponse()" does not exist
    }

}

?>