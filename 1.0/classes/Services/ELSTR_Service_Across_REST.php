<?php
require_once ('ELSTR_Service_Abstract.php');
require_once ('ELSTR_HttpClient.php');

/**
 * This as an example of a custom application, using http authentication
 *
 * @author Marco Egli
 */
class ELSTR_Service_Across_REST extends ELSTR_Service_Abstract {

    private $m_url;

    /**
     *
     * @return
     */
    function __construct($options) {
        parent::__construct();
        $this->m_url = $options['url'];
    }

	protected function get($object, $expand, $select) {
        $restClient = new ELSTR_HttpClient();

        // Example: .../Entries(80999)?$expand=Instance,Properties,Terms,Terms/Properties,Definitions
        $parameters = array();
        if (is_array($expand) && count($expand) > 0) {
            $parameters['$expand'] = implode(',', $expand);
        }
        if (is_array($select) && count($select) > 0) {
            $parameters['$select'] = implode(',', $select);
        }
        $restClient->setUri($this->m_url.$object);
        $restClient->setHeaders('Accept', 'application/json');
        
        $restClient->setParameterGet($parameters);
        $response = $restClient->request('GET');
        $status = $response->getStatus();
        return $response;
    }

}
?>