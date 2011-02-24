<?php

/**
 * Elstr_Auth_Adapter_OracleE6_Session
 *
 * @version $Id$
 * @copyright 2011
 */

require_once ('ELSTR_HttpClient.php');

class ELSTR_Auth_Adapter_OracleE6_Session implements Zend_Auth_Adapter_Interface
{
	var $m_options;
	var $m_username;
	var $m_password;

	/**
	 * Sets username and password for authentication
	 *
	 * @return void
	 */
	public function __construct($options, $username, $password, $enterpriseApp)
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
		$username = $this->m_username; // plm username
		$password = $this->m_password; // session id of plm connector

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


		// Verify plm connector session id
		//$parameters = array($this->m_secureLevel, $part_id, $part_version);
        //$xmlData = $this->getEnterpriseApplication('ELSTR_EnterpriseApplication_OracleE6')->call('ELSTR_Service_OracleE6', 'invokeConnectorProcedure', "plmTableBomArchive", $parameters);




		// Extrahieren des Tickets aus dem XML



		if (true) {
			$sessionAuthAlfresco = new Zend_Session_Namespace('Auth_OracleE6_Session');
			$sessionAuthAlfresco->username = $username;
			$sessionAuthAlfresco->session = $password;

			//return $ticket;
			echo "ok";

			$messages[0] = '';
			$messages[1] = '';
			$messages[] = "$username authentication successful";
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