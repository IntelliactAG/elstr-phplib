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
    
    private $m_username;
    private $m_password;

    /**
     *
     * @return
     */
    function __construct($options) {
        parent::__construct();

        $this->m_wsdl = $options['wsdl'];       
        $this->m_login = $options['login'];
        $this->m_password = $options['password'];
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
		$soapOptions = array('login' => $this->m_login,
							'password' => $this->m_password,
							'soap_version' => SOAP_1_1);
		
		// Create the soap client
		$client = new Zend_Soap_Client($this->m_wsdl,$soapOptions);
		$soapResponse = call_user_func_array(array($client, $method), array($parameters));
	
		return $soapResponse;
			
	}
	

}
?>