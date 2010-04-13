<?php

/**
 * ELSTER specific implementation of the Zend_Http_Client
 *
 * @author Marco Egli, Martin Bichsel
 * @copyright 2009 Intelliact AG
 */


class ELSTR_HttpClient extends Zend_Http_Client {
	public function __construct() {
		parent::__construct();
	}

    /**
    * extends standard request mechanism so that server errors are transformed into an ELSTR_Exception.
    *
    * @param string $request
    * @param boolean $transmit_details The server response is handed over to the ELSTR_Exception (and later to the client), only if $transmit_details is true (default)
    * @return Array request response
    */
public function request($request=null,$transmit_details=true)
	{
		$response = parent::request($request);
		 
		// detect lowlevel errors in response
		$status = $response->getStatus();
		if ($status>=400 && $status<=599) {
			$details=array();
			$details['status'] = $status;
			if ($transmit_details)
			{
				$details['response']= $response->getBody();
			}
			throw new ELSTR_Exception('Request failed',1011,null,$this,$details);
		}

		return $response;
	}
}

?>
