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
    protected $m_response;
    protected $m_isWsdlRequest = false;
    protected $m_soapOptions;

    function __construct($application, $params = null) {
        parent::__construct($application, $params);
        // set options
        $soapOptions = $application->getOption('ELSTR_WidgetServer_SOAP');
        if (isset($soapOptions)) {
            $this->m_soapOptions = $soapOptions;
        }        
        
        if (isset($_GET['wsdl'])) {
            $this->m_isWsdlRequest = true;

            if(isset($this->m_soapOptions['Zend_Soap_AutoDiscover_Strategy'])){
                // Example: ELSTR_WidgetServer_SOAP.Zend_Soap_AutoDiscover_Strategy = Zend_Soap_Wsdl_Strategy_ArrayOfTypeComplex
                $this->m_server = new Zend_Soap_AutoDiscover($this->m_soapOptions['Zend_Soap_AutoDiscover_Strategy']);
            } else {
                $this->m_server = new Zend_Soap_AutoDiscover();
            }

            if(isset($this->m_soapOptions['Zend_Soap_AutoDiscover_Namespace'])){
                $this->m_server->setUri($this->m_soapOptions['Zend_Soap_AutoDiscover_Namespace']);
            }

            if(isset($this->m_soapOptions['Zend_Soap_AutoDiscover_OperationBodyStyle'])){
                // http://framework.zend.com/issues/browse/ZF-6349
                // $this->m_server->setOperationBodyStyle(array('use' => 'literal'));
                $this->m_server->setOperationBodyStyle($this->m_soapOptions['Zend_Soap_AutoDiscover_OperationBodyStyle']);
            }

            if(isset($this->m_soapOptions['Zend_Soap_AutoDiscover_BindingStyle'])){                
                 // $this->m_server->setBindingStyle(array('style' => 'document'));
                 $this->m_server->setBindingStyle($this->m_soapOptions['Zend_Soap_AutoDiscover_BindingStyle']);
            }

            $this->m_server->setClass(get_class($this));
        } else {
            //TODO: Add possibilities of $options loaded from configurations file
            $options = null;

            $protocol = "http://";
            if (isset($_SERVER["HTTPS"]) == true && $_SERVER["HTTPS"] == "on") {
                $protocol = "https://";
            }
            $this->m_server = new ELSTR_SoapServer($protocol.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].$_SERVER['REQUEST_URI']."?wsdl",$options);
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
        $this->m_response = $this->m_server->handle($this->m_params);
        return $this->m_response;
    }

    /**
     * Get response object
     */
    protected function _getResponse() {
        //The method "$this->m_server->getResponse()" does not exist
        return $this->m_response;
    }

}
