<?php
require_once ('ELSTR_Service_Abstract.php');
require_once ('ELSTR_HttpClient.php');

/**
 * This as an example of a custom application, using http authentication
 *
 * @author Marco Egli
 */
class ELSTR_Service_CouchDB extends ELSTR_Service_Abstract {

    private $m_url;

    /**
     *
     * @return
     */
    function __construct($options) {
        parent::__construct();

        $this->m_url = $options['url'];

    }

	/*
	protected function get($serviceUrl, $parameters) {

		$restClient = new ELSTR_HttpClient();

		$restClient->setUri($this->m_url.$serviceUrl);
		//$restClient->setHeaders('Accept', 'application/xml');

		$restClient->setParameterGet($parameters);
		return $restClient->request('GET');
	}
	*/

	protected function put($serviceUrl, $record) {

		$restClient = new ELSTR_HttpClient();

		$restClient->setUri($this->m_url."/".$serviceUrl);
		$restClient->setRawData($record, 'application/json');

		$response = $restClient->request('PUT');
		//print_r($restClient);

		return $response;
	}


}
?>