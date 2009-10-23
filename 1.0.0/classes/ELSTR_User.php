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
		var $m_applications;
	
		function __construct($username) {
			$this->m_applications = array();
			$this->m_username = $username;
		}
	
		/**
		 * Returns the credentials for the application with ID = appID
		 * Credentials are directly loaded from DB
		 * 
		 * @param appID
		 */
		function getCredentials($appID)
		{
		}
	
		function getUsername() {
			return $this->m_username;
		}
	
	}
?>