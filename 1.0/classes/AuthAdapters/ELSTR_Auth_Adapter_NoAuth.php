<?php

/**
 * Elstr_Auth_Adapter_NoAuth
 *
 * This adapter allows any username to be authenticated. This Adabter makes sence, if you want
 * to develop an open application, but still e.g. have different views for an admin, controlled by
 * the ACL.
 * The password can be anything.
 *
 * @version $Id$
 * @copyright 2010
 */


class Elstr_Auth_Adapter_NoAuth implements Zend_Auth_Adapter_Interface
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
		
		$sessionAuthNoAuth = new Zend_Session_Namespace('Auth_NoAuth');
		$sessionAuthNoAuth->username = $username;
		$sessionAuthNoAuth->password = $password;

		//successful login
		$messages[0] = '';
		$messages[1] = '';
		$messages[] = "$username authentication successful";
		return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $username, $messages);
	}
}

?>