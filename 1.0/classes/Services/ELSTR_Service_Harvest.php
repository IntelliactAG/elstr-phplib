<?php
require_once ('ELSTR_Service_Abstract.php');
require_once ('ELSTR_HttpClient.php');

/**
 * This as an example of a custom application, using http authentication
 *
 * @author Marco Egli
 */
class ELSTR_Service_Harvest extends ELSTR_Service_Abstract {

    private $m_url;

    /**
     *
     * @return
     */
    function __construct($options) {
        parent::__construct();

        $this->m_username = $options['username'];
        $this->m_password = $options['password'];
        $this->m_url = $options['url'];

    }

	protected function get($serviceUrl, $parameters) {
			$sessionAuthHarvest = new Zend_Session_Namespace('Auth_Harvest');
			$restClient = new ELSTR_HttpClient();
	
			$restClient->setUri($this->m_url.$serviceUrl);
			$restClient->setHeaders('Accept', 'application/xml');
			$authenticationString = "Basic (". base64_encode($this->m_username.':'.$this->m_password) .")";
			$restClient->setHeaders('Authorization', $authenticationString);
			
			$restClient->setParameterGet($parameters);
			return $restClient->request('GET');
	}

	protected function post($serviceUrl, $record) {
			$sessionAuthHarvest = new Zend_Session_Namespace('Auth_Harvest');
			$restClient = new ELSTR_HttpClient();
	
			$restClient->setUri($this->m_url.$serviceUrl);
			$restClient->setHeaders('Accept', 'application/xml');
			$authenticationString = "Basic (". base64_encode($this->m_username.':'.$this->m_password) .")";
			$restClient->setHeaders('Authorization', $authenticationString);
			
			$restClient->setParameterPost($record);
			return $restClient->request('POST');
	}

}
?>