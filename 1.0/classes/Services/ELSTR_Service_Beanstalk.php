<?php
require_once ('ELSTR_Service_Abstract.php');
require_once ('ELSTR_HttpClient.php');

/**
 * This as an example of a custom application, using http authentication
 *
 * @author Martin Bichsel
 */
class ELSTR_Service_Beanstalk extends ELSTR_Service_Abstract {

    private $m_url;

    /**
     *
     * @return
     */
    function __construct($options) {
        parent::__construct();

		$sessionAuthBeanstalk = new Zend_Session_Namespace('Auth_Beanstalk');
 		$this->m_username = $sessionAuthBeanstalk->username;
 		$this->m_password = $sessionAuthBeanstalk->password;
         
        $this->m_url = $options['url'];
    }
	
	
	 /**
     *
     * @param $repository_id
     * @param $name (development, staging, or production)
     * @param $local_path
     * @param $remote_path
     * @param $remote_addr
     * @param $protocol
     * @param $port
     * @param $password
     * @param $automatic
     * @param $state
     * @return id of release server
     */
 	protected function createOrUpdateReleaseServer($repository_id,$name,$local_path,$remote_path,$remote_addr,$protocol,$port,$login,$password,$automatic,$state) {
		try { // if release server does not yet exists we will end up in catch
 			$response = $this->getReleaseServer($repository_id,$name);
		}
		catch (ELSTR_Exception $e)
		{
			if ($e->m_code == 404)
			{
				$response = $this->createReleaseServer($repository_id,$name,$local_path,$remote_path,$remote_addr,$protocol,$port,$login,$password,$automatic,$state);
		    	$status = $response->getStatus();
				if ($status!=201)
		    	{
		    		throw new ELSTR_Exception("createOrUpdateReleaseServer failed with status $status",0,null,$this);
	 	    	}			
			}
			else
			{
				throw $e;
			}
		}
    	$xmlData = $response->getBody();
    	$xml = new SimpleXMLElement($xmlData);
     	$serverID = $xml->id;
     	
     	$response = $this->activateReleaseServer('elstr',$name);
     	$xmlData2 = $response->getBody();
     	
     	return $xml->id;
	}
	
	
	/**
     *
     * @param $repository_id
     * @param $name
     * @param $local_path
     * @param $remote_path
     * @param $remote_addr
     * @param $protocol
     * @param $port
     * @param $password
     * @param $automatic
     * @param $state
     * @return response
     */
	private function createReleaseServer($repository_id,$name,$local_path,$remote_path,$remote_addr,$protocol,$port,$login,$password,$automatic,$state) {
		$release_server = array();
		$release_server->name        = $name;
		$release_server->local_path  = $local_path;
		$release_server->remote_path = $remote_path;
		$release_server->remote_addr = $remote_addr;
		$release_server->protocol    = $protocol;
		$release_server->port        = $port;
		$release_server->login       = $login;
		$release_server->password    = $password;
		$release_server->state       = $state;
		$xml = 	$this->arrayToXML('release_server',$release_server);
		return $this->postXML("/api/$repository_id/release_servers.xml",$xml);
	}
	
	
	protected function activateReleaseServer($repository_id,$name) {
		$release_server = array();
		$release_server['state']       = 1;
		$xml = 	$this->arrayToXML('release_server',$release_server);
		return $this->putXML("/api/$repository_id/release_servers/$name.xml",$xml);
	}
	
	
	private function getReleaseServer($repository_id,$name) {
		return $this->get("/api/$repository_id/release_servers/$name.xml", array());
	}
	
	
	protected function release($server_id,$repository_id,$revision) {
		$release = array();
		$release['release-server-id']=$server_id;
		$release['revision']=$revision;
		$release['deploy_from_scratch']=1;
		$xml = 	$this->arrayToXML('release',$release);
		return $this->postXML("/api/$repository_id/releases.xml",$xml);
	}
	
	
	protected function get($serviceUrl, $parameters) {
		$restClient = new ELSTR_HttpClient();

		$restClient->setUri($this->m_url.$serviceUrl);
		$restClient->setAuth($this->m_username, $this->m_password);
		$restClient->setHeaders('Accept', 'application/xml');
		
		$restClient->setParameterGet($parameters);
		$response = $restClient->request('GET');
    	$status = $response->getStatus();
		return $response;
	}

	
	protected function postXML($serviceUrl, $xml) {
		$restClient = new ELSTR_HttpClient();

		$restClient->setUri($this->m_url.$serviceUrl);
		$restClient->setAuth($this->m_username, $this->m_password);
		$restClient->setHeaders('Accept', 'application/xml');
		
		$restClient->setRawData($xml,'text/xml');
		$response = $restClient->request('POST');
    	$status = $response->getStatus();
		return $response;
	}

	
	protected function putXML($serviceUrl, $xml) {
		$restClient = new ELSTR_HttpClient();

		$restClient->setUri($this->m_url.$serviceUrl);
		$restClient->setAuth($this->m_username, $this->m_password);
		$restClient->setHeaders('Accept', 'application/xml');
		
		$restClient->setRawData($xml,'text/xml');
		$response = $restClient->request('PUT');
    	$status = $response->getStatus();
		return $response;	
	}
	
	
	private function arrayToXML($rootName,$elements) {
 		$dom = new DOMDocument('1.0', 'UTF-8');
		$root = $dom->createElement($rootName, '');
		$dom->appendChild($root);
		foreach ($elements as $key => $value)
		{
			$root->appendChild($dom->createElement($key,$value));
		}
		return $dom->saveXML();
	}
}
?>