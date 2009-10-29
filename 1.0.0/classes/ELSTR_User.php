<?php

	/**
	 * This class encapsulates user data and settings as well as temporarily 
	 * stored values such as passwords
	 * 
	 * @author Felix Nyffenegger
	 * @version 1.0
	 * @created 22-Okt-2009 10:35:05
	 */
	class ELSTR_User
	{
	
		var $m_username;
		var $m_credentials;
	
		function __construct($username) {
			$this->m_credentials = array();
			$this->m_username = $username;
		}
	
		/**
		 * Returns the credentials for the application with ID = appID
		 * Credentials are partly loaded from DB (username). The passowrd will only be stored during
		 * the session
		 * 
		 * @param appID
		 * @return ELSTR_Credentials Credentials
		 */
		function getCredentials($appID)
		{
			return $this->m_credentials[$appID];
		}
	
		/**
		 * Returns the ELSTR Username
		 * @return String
		 */
		public function getUsername() {
			return $this->m_username;
		}	
	}
?>