<?php

/**
 * Elstr_Auth_Adapter_Alfresco
 *
 * @version $Id$
 * @copyright 2010
 */

require_once ('ELSTR_HttpClient.php');

class ELSTR_Auth_Adapter_Alfresco implements Zend_Auth_Adapter_Interface
{
	var $m_options;
	var $m_username;
	var $m_password;

	/**
	 * Sets username and password for authentication
	 *
	 * @return void
	 */
	public function __construct($options, $username, $password)
	{
		$this->m_options = $options;
		$this->m_username = $username;
		$this->m_password = $password;
	}

	/**
	 * Performs an authentication attempt
	 *
	 * @throws Zend_Auth_Adapter_Exception If authentication cannot
	 *                                     be performed
	 * @return Zend_Auth_Result
	 */
	public function authenticate()
	{
		// ...
		$messages = array();
		$messages[0] = ''; // reserved
		$messages[1] = ''; // reserved

		$options = $this->m_options;
		$username = $this->m_username;
		$password = $this->m_password;

		if (!$username) {
			$code = Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
			$messages[0] = 'A username is required';
			return new Zend_Auth_Result($code, '', $messages);
		}
		if (!$password) {
			// A password is required
			$code = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
			$messages[0] = 'A password is required';
			return new Zend_Auth_Result($code, '', $messages);
		}


		$alfrecsoLoginUrl = $options['url'].'/service/api/login';

		// Laden des Tickets
		$restClient = new ELSTR_HttpClient();
		$restClient->setUri($alfrecsoLoginUrl);
		$restClient->setParameterGet(array('u'=>$username, 'pw'=>$password));
		$response = $restClient->request();

		// Extrahieren des Tickets aus dem XML
		$responseBody = $response->getBody();
		$xmlDoc = new DOMDocument();
		$xmlDoc->loadXML($responseBody);
		$tickets = $xmlDoc->getElementsByTagName("ticket");
		if ($tickets->length > 0) {
			// Es gibt ein Ticket
			$ticket = $tickets->item(0)->nodeValue;

			$sessionAuthAlfresco = new Zend_Session_Namespace('Auth_Alfresco');
			$sessionAuthAlfresco->username = $username;
			$sessionAuthAlfresco->password = $password;
			$sessionAuthAlfresco->ticket = $ticket;

			//return $ticket;


			$messages[0] = '';
			$messages[1] = '';
			$messages[] = "$username authentication successful";
			$messages[] = array("attributes" => array("ticket" => $ticket));
			return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $username, $messages);

		} else {
			// Invalid loing
			$code = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
			$messages[0] = 'Invalid credentials';
			return new Zend_Auth_Result($code, '', $messages);
		}
	}
}

?>