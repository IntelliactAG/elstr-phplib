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

    private $m_cookieJarCache = null;

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

        if(isset($options['cookieJarCache'])){
            $this->m_cookieJarCache = $options['cookieJarCache'];
        }
        
    }

	protected function request($serviceUrl, $parameters) {

		$restClient = new ELSTR_HttpClient();
		$restClient->setAuth($this->m_username, $this->m_password, Zend_Http_Client::AUTH_BASIC);
		$restClient->setUri($this->m_url.$serviceUrl);
		$restClient->setConfig(array(
    		'maxredirects' => $this->m_maxredirects,
    		'timeout'      => $this->m_timeout)
            );			
		$restClient->setParameterGet($parameters);

        if($this->m_cookieJarCache) {
            $last_char = substr(sys_get_temp_dir(), -1, 1);
            $systemTempDir = sys_get_temp_dir();
            //last char might be \n => account for that
            if (strpos($last_char, "/")===false || strpos($last_char, "\\")===false) {
                $systemTempDir .= "/";
            }
            $systemTempDir .= "ELSTR_Service_Windchill_REST/";
            if (!file_exists($systemTempDir)){
                mkdir($systemTempDir);
            }

            $cache = Zend_Cache::factory('Core','File',$this->m_cookieJarCache,array('cache_dir' => $systemTempDir));            
            if(!$cookieJar = $cache->load('cookieJar_'.Zend_Session::getId())) {
                // Cache miss; 
                $restClient->setCookieJar();
            } else {
                $restClient->setCookieJar($cookieJar);
            }
        }

        $response = $restClient->request();

        //print_r($restClient->getLastRequest());
        //print_r($restClient->getLastResponse());

        $responseCookieJar = Zend_Http_CookieJar::fromResponse($restClient->getLastResponse(),$this->m_url.$serviceUrl);

        if($this->m_cookieJarCache) {
            if ($responseCookieJar !== $cookieJar && $responseCookieJar->isEmpty() === false){
                $cache->save($responseCookieJar,'cookieJar_'.Zend_Session::getId());
            }
        }

		return $response;
	}

}
?>