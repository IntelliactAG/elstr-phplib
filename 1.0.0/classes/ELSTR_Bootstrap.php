<?php
require_once 'ELSTR_Acl.php';
require_once 'ELSTR_User.php';
require_once 'ELSTR_Language.php';

/**
 * This is the Boostrap class for the ELSTR Framework. It controls proper initialisation and registration
 * of Ressources and Components
 *
 * @author Felix Nyffenegger / Marco Egli
 * @version 1.0.0
 * @created 19-Okt-2009 17:40:03
 */
class ELSTR_Bootstrap extends Zend_Application_Bootstrap_BootstrapAbstract {
    /**
     * Constructor
     *
     * @param  Zend_Application|Zend_Application_Bootstrap_Bootstrapper $application
     * @return void
     */
    public function __construct($application) {
        parent::__construct($application);
    }

    /**
     * Run the application
     *   *
     * @return void
     */
    public function run() {

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
     * Initialize the Auth for ESLTR web application
     * @return
     */
    protected function _initAuth() {
        $auth = Zend_Auth::getInstance();
    	$auth->setStorage(new Zend_Auth_Storage_Session('ELSTR_Auth'));

    	return $auth;
    }

    /**
     * Initialize the Language
     * @return Zend_Translate
     */
    protected function _initLanguage() {

    	/*
    	   $options = $this->getApplication()->getOption("language");
    	   $textTranslations = new Zend_Translate('tmx', APPLICATION_PATH.$options['file'], $options['default']);

    	   $sessionLanguage = new Zend_Session_Namespace('ELSTR_Language');
    	   if (!isset($sessionLanguage->language)) {
    	   $locale = new Zend_Locale();
    	   Zend_Registry::set('Zend_Locale', $locale);
    	   if (!$textTranslations->isAvailable($locale->getLanguage())) {
    	   // when user requests a not available language reroute to default
    	   $sessionLanguage->language = $defaultlanguage;
    	   } else {
    	   $sessionLanguage->language = $locale->getLanguage();
    	   }
    	   }
    	   $textTranslations->setLocale($sessionLanguage->language);

    	   return $textTranslations;
    	*/


        $options = $this->getApplication()->getOption("language");

    	//$translation = new Zend_Translate('tmx', APPLICATION_PATH.$options['module']['default'], $options['default']);

    	// Create language object
    	$m_language = new ELSTR_Language($options);


    	return $m_language;
    }

    /**
     * Initialize the User
     * @return  ELSTR_User
     */
    protected function _initUser() {
        // If user is authenicated, create user object, else create a guest user
        $m_user = null;

    	$auth = $this->getResource("auth");
    	if (isset($auth) && $auth->hasIdentity()) {
    		// Identity exists; get it
    		$identity = $auth->getIdentity();
    		$m_user = new ELSTR_User($identity);
    	} else {
    		$m_user = new ELSTR_User('anonymous');
    	}

        return $m_user;
    }
}
?>