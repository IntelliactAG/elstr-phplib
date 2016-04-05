<?php

/**
* This class encapsulates user data and settings as well as temporarily
* stored values such as passwords
*
* @author Felix Nyffenegger
* @version 1.0
* @created 22-Okt-2009 10:35:05
*/
class ELSTR_User {
    var $m_username;
    var $m_credentials;
    var $m_session;

    function __construct($username)
    {
        $this->m_credentials = array();
        $this->m_username = $username;
        $this->m_session = new Zend_Session_Namespace('ELSTR_User');
        
		if (!isset($this->m_session->enterpriseApplicationData)) {
			$this->m_session->enterpriseApplicationData = array();
		}
    }

    /**
    * Returns the credentials for the application with ID = appID
    * Credentials are partly loaded from DB (username). The passowrd will only be stored during
    * the session
    *
    * @param appID $
    * @return ELSTR_Credentials Credentials
    */
    function getCredentials($appID)
    {
        return $this->m_credentials[$appID];
    }

    /**
    * Returns the ELSTR Username
    *
    * @return String
    */
    public function getUsername()
    {
        return $this->m_username;
    }
    
    public function setEnterpriseApplicationData($enterpriseApplication, $key, $value){
    	$this->m_session->enterpriseApplicationData[$enterpriseApplication][$key] = $value;
    }
    
    public function getEnterpriseApplicationData(){
    	return $this->m_session->enterpriseApplicationData;
    }
}
