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
   	private $m_svnCommand;
    
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
        if (isset($options['svnCommand']))  {
       		$this->m_svnCommand = $options['svnCommand'];
        }
        else {
        	$this->m_svnCommand = 'svn';
        }
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
	
	
	/**
     * Triggers an export to an FTP server as configured in the Beanstalk release server
     * WARNING: Call returns before export has completed
	 * @param $local_path
     * @param $server_id Beanstalk release server id
     * @return response
     */
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
		

	
	/**
     * Checkout repository using SVN client
     * REQUIRES: SVN client must be installed on web server
     * @param $repository Repository name
     * @param $local_path
     * @return response
     */
	protected function svnCheckout($repository, $local_path,$revision=null)
	{
		mkdir($path);
		
		$command = 'svn checkout '. $this->getSvnUrl($repository);
		if (isset($this->m_username)) { $command = $command .' --username '. $this->m_username; }
		if (isset($this->m_password)) { $command = $command .' --password '. $this->m_password; }
		if (isset($revision)) { $command = $command .' --revision '. $revision; }
		$command = $command .' '. $local_path;
		$output = shell_exec($command); 
		
		if ($output=='')
		{
			throw new ELSTR_Exception('Could not execute SVN checkout command. Is SVN configured properly?',0,null,$this);
		}
		return $output; 
	}
	
	/**
     * Checkout repository using SVN client
     * REQUIRES: SVN client must be installed on web server
     * @param $repository Repository name
     * @param $local_path
     * @return response
     */
	protected function svnExport($repository, $local_path,$revision=null)
	{
		if (file_exists($local_path))
		{
			$isOK = $this->deleteDir($local_path);
			if (!$isOK)
			{
				throw new ELSTR_Exception('Could not delete directory '. $local_path ,0,null,$this);
			}
		}
		
		$command = $this->m_svnCommand .' export '. $this->getSvnUrl($repository);
		if (isset($this->m_username)) { $command = $command .' --username '. $this->m_username; }
		if (isset($this->m_password)) { $command = $command .' --password '. $this->m_password; }
		if (isset($revision)) { $command = $command .' --revision '. $revision; }
		$command = $command .' '. $local_path;
		//echo "SVN command : $command";
		$output = exec($command); 
		 
		if ($output=='')
		{
			throw new ELSTR_Exception('Could not execute SVN export command. Is SVN configured properly?',0,null,$this);
		}
		return $output; 
	}
	
	
	private function getSvnUrl($repository)
	{
		$url = str_replace('.beanstalkapp.com','.svn.beanstalkapp.com',$this->m_url);
		return $url.$repository;
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
	
	private function deleteDir($dir)
	{
		if (substr($dir, strlen($dir)-1, 1)!= '/')
		$dir .= '/';
		if ($handle = opendir($dir))
		{
			while ($obj = readdir($handle))
			{
				if ($obj!= '.' && $obj!= '..')
				{
					if (is_dir($dir.$obj))
					{
						if (!$this->deleteDir($dir.$obj))
						return false;
					}
					elseif (is_file($dir.$obj))
					{
						if (!unlink($dir.$obj))
						return false;
					}
				}
			}
			closedir($handle);
			if (!@rmdir($dir)){
				return false;
			}
			return true;
		}
		return false;
	}
	
}
?>