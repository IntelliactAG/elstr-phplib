<?php
	require_once 'ELSTR_Acl.php';
	require_once 'ELSTR_User.php';
	
	/**
	 * This is the Boostrap class for the ELSTR Framework. It controls proper initialisation and registration
	 * of Ressources and Components
	 * 
	 * @author Felix Nyffenegger / Marco Egli
	 * @version 1.0.0
	 * @created 19-Okt-2009 17:40:03
	 */
	class ELSTR_Bootstrap extends Zend_Application_Bootstrap_BootstrapAbstract
	{		
	    /**
	     * Constructor
	     *
	     * @param  Zend_Application|Zend_Application_Bootstrap_Bootstrapper $application 
	     * @return void
	     */
	    public function __construct($application)
	    {
	        parent::__construct($application);
	    }
	
	    /**
	     * Run the application
	     *   * 
	     * @return void     
	     */
	    public function run()
	    { 
			
	    }

		/**
		 * Initialize the Session
		 */
		protected function _initSession() {
			Zend_Session::start();
		}
		
		/**
		 * Initialize the ACL
		 * @return  ELSTR_Acl
		 */
		protected function _initAcl() {
			$m_acl = new ELSTR_Acl();
			$m_acl->loadFromDB();
			return $m_acl;
		}

		/**
		 * Initialize the User
		 * @return  ELSTR_User
		 */
		protected function _initUser() {
			// If user is authenicated, create user object, else create a guest user
			$m_user = null;
			
			if(Zend_Session::namespaceIsset('ELSTR_Auth')) {				
				$sessionAuth = new Zend_Session_Namespace('ELSTR_Auth'); 
				$username = $sessionAuth->username;
				$m_user = new ELSTR_User($username);				
			}
			else {
				$m_user = new ELSTR_User('guest');
			}
			return $m_user;
		}				
	}
?>