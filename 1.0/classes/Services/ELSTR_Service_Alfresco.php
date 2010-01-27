<?php
require_once ('ELSTR_Service_Abstract.php');
require_once ('ELSTR_HttpClient.php');

/**
 * This as an example of a custom application, using http authentication
 *
 * @author Marco Egli
 */
class ELSTR_Service_Alfresco extends ELSTR_Service_Abstract {

    private $m_timeout;
    private $m_maxredirects;
    private $m_url;

    /**
     *
     * @return
     */
    function __construct($options) {
        parent::__construct();

        $this->m_timeout = $options['timeout'];
        $this->m_maxredirects = $options['maxredirects'];
        $this->m_url = $options['url'];

    }

	protected function request($serviceUrl, $parameters) {

			$sessionAuthAlfresco = new Zend_Session_Namespace('Auth_Alfresco');
			$restClient = new ELSTR_HttpClient();
			$restClient->setAuth($sessionAuthAlfresco->username, $sessionAuthAlfresco->password);
			$restClient->setUri($this->m_url.$serviceUrl);
			$restClient->setConfig(array(
			'maxredirects' => $this->m_maxredirects,
			'timeout'      => $this->m_timeout));
			
			$restClient->setParameterGet($parameters);
			return $restClient->request();
	}

}
?>