<?php

/** ELSTR_Sso_Adapter_Ntlm */


class ELSTR_Sso_Adapter_Ntlm implements Zend_Auth_Adapter_Interface
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
	 * Performs an sso (signel sign on) authentication attempt
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

		$ssoOptions = $this->m_options;
		$multiOptions = $ssoOptions['server'];

		if (isset($_SERVER['PHP_AUTH_USER'])) {

			$usernameParts = explode("\\",$_SERVER['PHP_AUTH_USER']);
			$usernameShort = $usernameParts[count($usernameParts)-1];

			$users = array();
			$ldap = new Zend_Ldap();
			foreach ($multiOptions as $options) {
				//var_dump($options);

				$ldapUsername = $ssoOptions['user'];
				$ldapPassword = $ssoOptions['password'];

				$ldap->setOptions($options);
				try {
					$ldap->bind($ldapUsername, $ldapPassword);

					$filter = str_replace('{userId}', $usernameShort, $ssoOptions['userFilter']);
					$users = array_merge($users,$ldap->search($filter, $options['baseDn'], 0)->toArray());

					//break;
				} catch (Zend_Ldap_Exception $zle) {
					//echo '  ' . $zle->getMessage() . "\n";
					if ($zle->getCode() === Zend_Ldap_Exception::LDAP_X_DOMAIN_MISMATCH) {
						continue;
					}
					throw new ELSTR_Exception("Cannot access LDAP. Error: ". $zle->getMessage());
				}
				//$opts = $options;
			}

			$count = sizeof($users);
			if ($count == 0) {
				$code = Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
				$messages[0] = 'User '.$usernameShort.' not found in LDAP';
				return new Zend_Auth_Result($code, '', $messages);
			}else if ($count > 1) {
				$code = Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
				$messages[0] = 'More than one ('.$count.') users found for '.$usernameShort;
				return new Zend_Auth_Result($code, '', $messages);
			}

			$username = $users[0]['userprincipalname'][0];

			$sessionSsoNtlm = new Zend_Session_Namespace('Sso_Ntlm');
			$sessionSsoNtlm->usernameShort = $_SERVER['PHP_AUTH_USER'];
			$sessionSsoNtlm->username = $_SERVER['PHP_AUTH_USER'];

			//successful login
			$messages[0] = '';
			$messages[1] = '';
			$messages[] = "$username sso authentication successful";
			return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $username, $messages);
		} else {
			$code = Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
			$messages[0] = 'SSO identity not found';
			return new Zend_Auth_Result($code, '', $messages);
		}

	}
}

?>