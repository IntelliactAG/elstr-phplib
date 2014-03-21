<?php

require_once ('ELSTR_Service_Abstract.php');
require_once ('ELSTR_SoapClient.php');

/**
 * This as an example of a custom application, using http authentication
 *
 * @author Marco Egli
 */
class ELSTR_Service_SAP_SOAP extends ELSTR_Service_Abstract {

    private $m_wsdl;
    private $m_login;
    private $m_password;
    private $m_location;
    private $m_uri;
    private $m_cookie;

    /**
     *
     * @return
     */
    function __construct($options) {
        parent::__construct();

        if (isset($options['wsdl'])) {
            $this->m_wsdl = $options['wsdl'];
        } else {
            $this->m_wsdl = false;
            // If no wsdl is set, location and uri are mandatory
            $this->m_location = $options['location'];
            $this->m_uri = $options['uri'];
        }
        if (isset($options['login']) && isset($options['password'])) {
            $this->m_login = $options['login'];
            $this->m_password = $options['password'];
        } else {
            $this->m_login = false;
            $this->m_password = false;
        }
        if (isset($options['cookie'])) {
            $this->m_cookie = $options['cookie'];
        } else {
            $this->m_cookie = false;
        }
    }

    /**
     * Call a SAP Soap Request
     *
     * @param string $method (the sap-method to call)
     * @param array $parameters
     * @return object
     */
    protected function request($method, $parameters) {

        // Set the soap options
        $soapOptions = array();
        $soapOptions['soap_version'] = SOAP_1_1;
        if ($this->m_login && $this->m_password) {
            $soapOptions['login'] = $this->m_login;
            $soapOptions['password'] = $this->m_password;
        }


        if ($this->m_wsdl) {
            $client = new Zend_Soap_Client($this->m_wsdl, $soapOptions);
            $soapParameters = array($parameters);
        } else {
            // Non-WSDL Mode      
            $soapOptions['location'] = $this->m_location;
            $soapOptions['uri'] = $this->m_uri;
            $client = new Zend_Soap_Client(null, $soapOptions);
            $soapParameters = array();
            foreach ($parameters as $element => $value) {
                $soapParameters[] = new SoapParam($value, $element);
            }
        }

        if ($this->m_cookie && isset($this->m_cookie['name']) && isset($this->m_cookie['value'])) {
            $client->setCookie($this->m_cookie['name'], $this->m_cookie['value']);
        }

        try {
            $soapResponse = call_user_func_array(array($client, $method), $soapParameters);
        } catch (Exception $e) {
            //var_dump($e);
            //print_r($client->getLastRequest());
            throw new ELSTR_Exception($e->getMessage(),0,null,$this,array('errorObject' => $e, 'lastRequest' => $client->getLastRequest()));
        }

        return $soapResponse;
    }

}

?>