<?php

require_once 'ELSTR_Db.php';
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
     * Initialize the Logger
     * @return Zend_Logger
     */
    protected function _initLogger() {
        $options = $this->getApplication()->getOption("logger");
        $m_logger = null;
        // Create language object
        //$m_language = new ELSTR_Language($options);
        if (isset($options)) {
            $writer = new Zend_Log_Writer_Stream($options['file']);
            $m_logger = new Zend_Log($writer);
            $filter = new Zend_Log_Filter_Priority((int) $options['level']);
            $m_logger->addFilter($filter);
        }
        return $m_logger;
    }


    /**
     * Initialize the Session
     */
    protected function _initSession() {
        $configSession = $this->getApplication()->getOption("session");
        if(is_array($configSession)){
            Zend_Session::setOptions($configSession);
        }
        Zend_Session::start();
    }

    /**
     * Initialize the Database
     * @return  ELSTR_Db
     */
    protected function _initDb() {
        $configDb = $this->getApplication()->getOption("database");
        // Es sind mehrere Datenbanken konfiguriert
        if (!isset($configDb['adapter']) && count($configDb) > 1) {
            $m_db = array();

            foreach ($configDb as $instance => $options) {
                $adapter = $options['adapter'];
                $params = $options[$adapter];

                $dbAdapter = Zend_Db::factory($adapter, $params);
                $profilerEnabled = false;
                if(isset($options['profiler']) && ($options['profiler'] === "1" || $options['profiler'] === 1 || $options['profiler'] === true)){
                    $profilerEnabled = true;
                    $dbAdapter->getProfiler()->setEnabled($profilerEnabled);
                }
                $dbAdapter->getConnection();
                $m_db[$instance] = new ELSTR_Db($dbAdapter,$this->getApplication()->getBootstrap()->getResource("logger"), $profilerEnabled);
                // Alle Operatione sollen in UTF-8 codiert werden
                switch ($adapter) {
                    case "Pdo_Mysql":
                        $m_db[$instance]->query("set character set utf8;");
                        break;
                    case "Sqlsrv":
                        ini_set('mssql.charset', 'UTF-8');
                        break;
                }
            }

            return $m_db;
        } else {
            $adapter = $configDb['adapter'];
            $params = $configDb[$configDb['adapter']];

            $dbAdapter = Zend_Db::factory($adapter, $params);
            $profilerEnabled = false;
            if(isset($options['profiler']) && ($options['profiler'] === "1" || $options['profiler'] === 1 || $options['profiler'] === true)){
                $profilerEnabled = true;
                $dbAdapter->getProfiler()->setEnabled($profilerEnabled);
            }
            $dbAdapter->getConnection();
            $m_db[$instance] = new ELSTR_Db($dbAdapter,$this->getApplication()->getBootstrap()->getResource("logger"), $profilerEnabled);

            $m_db = new ELSTR_Db($dbAdapter);
            // Alle Operatione sollen in UTF-8 codiert werden
            $m_db->query('set character set utf8;');

            return $m_db;
        }
    }

    /**
     * Initialize the Auth for ESLTR web application
     * @return
     */
    protected function _initAuth() {
        $m_auth = Zend_Auth::getInstance();
        $m_auth->setStorage(new Zend_Auth_Storage_Session('ELSTR_Auth'));

        return $m_auth;
    }

    /**
     * Initialize the Language
     * @return Zend_Translate
     */
    protected function _initLanguage() {
        $options = $this->getApplication()->getOption("language");

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

    /**
     * Initialize the ACL
     * @return  ELSTR_Acl
     */
    protected function _initAcl() {
        $m_acl = new ELSTR_Acl($this->getApplication());
        $m_acl->loadFromDb();
        //
        // Check if the current user has at least one role
        // If not - add it to the role_anonymous
        $m_acl->currentUserHasRole($this->getResource("user")->getUsername());

        return $m_acl;
    }


    /**
     * Helper function that converts both, an object or an array, into an array
     * @param array or object $thing
     * @return array
     */
    static public function getArrayForObjectOrArray($thing) {
        if (is_array($thing)) {
            return $thing;
        } else {
            return array($thing);
        }
    }

}

?>