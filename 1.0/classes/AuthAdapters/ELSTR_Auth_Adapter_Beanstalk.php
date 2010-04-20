<?php

/**
 * Elstr_Auth_Adapter_Beanstalk
 *
 * @version $Id$
 * @copyright 2010
 */

require_once ('ELSTR_HttpClient.php');

class ELSTR_Auth_Adapter_Beanstalk implements Zend_Auth_Adapter_Interface
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
		if (!$options['url']) {
			// A password is required
			throw new ELSTR_Exception('Missing url for Beanstalk authentication',0,null,$this);
		}
		
		// try any specific request to test user authentication
		$loginUrl = $options['url'].'api/repositories.xml';

		$restClient = new ELSTR_HttpClient();
		$restClient->setUri($loginUrl);
		$restClient->setAuth($username, $password);
		
		//echo "ELSTR_Auth_Adapter_Beanstalk password: $password, username: $username, url: $loginUrl";
		try {
			$response = $restClient->request('GET');
			$sessionAuthBeanstalk = new Zend_Session_Namespace('Auth_Beanstalk');
			$sessionAuthBeanstalk->username = $username;
			$sessionAuthBeanstalk->password = $password;

			//successful login
			$messages[0] = '';
			$messages[1] = '';
			$messages[] = "$username authentication successful";
			return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $username, $messages);
		}
		catch (ELSTR_Exception $e)
		{
			// Invalid loing
			$code = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
			$messages[0] = 'Invalid credentials';
			return new Zend_Auth_Result($code, '', $messages);
		}
	}
}

?>