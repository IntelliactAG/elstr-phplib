<?php
require_once ('ELSTR_Service_Abstract.php');
require_once ('ELSTR_HttpClient.php');

/**
 * This as an example of a custom application, using http authentication
 *
 * @author Marco Egli
 */
class ELSTR_Service_Windchill_REST extends ELSTR_Service_Abstract {

    private $m_timeout;
    private $m_maxredirects;
    private $m_url;
    
    private $m_username;
    private $m_password;

    /**
     *
     * @return
     */
    function __construct($options) {
        parent::__construct();

        $this->m_timeout = $options['timeout'];
        $this->m_maxredirects = $options['maxredirects'];
        $this->m_url = $options['url'];
        
        $this->m_username = $options['username'];
        $this->m_password = $options['password'];

    }

	protected function request($serviceUrl, $parameters) {
			
			$restClient = new ELSTR_HttpClient();
			$restClient->setAuth($this->m_username, $this->m_password);
			$restClient->setUri($this->m_url.$serviceUrl);
			$restClient->setConfig(array(
			'maxredirects' => $this->m_maxredirects,
			'timeout'      => $this->m_timeout));			
			$restClient->setParameterGet($parameters);
            $response = $restClient->request();
            //print_r($restClient->getLastRequest());
			return $response;
	}	

}
?>