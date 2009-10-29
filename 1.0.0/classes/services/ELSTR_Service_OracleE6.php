<?php
	require_once('ELSTR_Service_REST.php');
	
	/**
	 * This as an example of a custom application, using http authentication
	 * 
	 * @author Felix Nyffenegger
	 */
	class EXAMPLE_Service_YQL extends ELSTR_Service_REST
	{	
		/**
		 * This is a very simple REST Get service, asking for Pizza services
		 * 
		 * @return Array Resultser
		 */
		protected function pizzaService() {
			$this->restClient->setUri('http://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20yelp.review.search%20where%20term%3D%27pizza%27%20and%20location%3D%27sunnyvale%2C%20ca%27%20and%20ywsid%3D%276L0Lc-yn1OKMkCKeXLD4lg%27&format=json&diagnostics=false&env=http%3A%2F%2Fdatatables.org%2Falltables.env&callback=');
			$this->restClient->setConfig(array(
			    'maxredirects' => 0,
    			'timeout'      => 30));
			$response = $this->restClient->request();
			return Zend_Json::decode($response->getBody($response));
		}
	}
?>
