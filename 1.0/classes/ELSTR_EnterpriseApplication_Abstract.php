<?php

require_once ('ELSTR_Exception.php');

/**
 * This class encapsulates the common functionality of access to enterpries applications
 * All enterpries applications must be inherited from this class and service calls done by
 * widget server must be call by an application that inherits from this class
 *
 * These methods must be implemented:
 * _initServices() : Tell the application which services to use with $this->registerService()
 * _initAuthAdapter() : Return the desired Zend_Auth_Adapter implementaion or null
 *
 * @author Felix Nyffenegger
 * @version 1.0
 * @created 19-Okt-2009 17:41:50
 * @modified 11-Jan-2010 Marco Egli
 */
abstract class ELSTR_EnterpriseApplication_Abstract {

    protected $m_application;
    protected $m_services;
    protected $m_authAdapter;
    protected $m_auth;
    protected $m_options;

    /**
     *
     * @param <type> $application The ZEND application object
     * @param <type> $configPostFix If multiple applications of the same type exist, they can have a postfix for the different configurations
     */
    function __construct($application, $configPostFix = '') {
        $this->m_application = $application;


        $configEnterpriseApp = $this->m_application->getOption(get_class($this) . $configPostFix);
        $this->m_options = $configEnterpriseApp;
        //var_dump($configEnterpriseApp);
        if (isset($configEnterpriseApp['auth'])) {
            $configAuth = $configEnterpriseApp['auth'];
            if (isset($configAuth['method'])) {
                $this->m_authAdapter = $configAuth['method'];
            }
        }

        $this->_initServices();

        $this->m_auth = Zend_Auth::getInstance();
        $this->m_auth->setStorage(new Zend_Auth_Storage_Session(get_class($this)));
    }

    /**
     * Performs an authentication attempt, first check if credetials are present,
     * then check if is authenticated allreadey then try to authenticated.
     *
     * @return true if authentication attempt was successful
     */
    public function authenticate($username = null, $password = null) {

        if (isset($this->m_authAdapter)) {

            $configEnterpriseApp = $this->m_application->getOption(get_class($this));

            if (isset($configEnterpriseApp['auth'])) {
                $configAuth = $configEnterpriseApp['auth'];

                if (isset($configAuth['includeAdapter'])) {
                    include_once($configAuth['includeAdapter']);
                }

                $options = array();
                if (isset($configAuth[$this->m_authAdapter])) {
                    $options = $configAuth[$this->m_authAdapter];
                }
                $adapter = new $this->m_authAdapter($options, $username, $password);
                $result = $this->m_auth->authenticate($adapter);

                // Get special authentification attributes
                // and add them to the user session
                foreach ($result->getMessages() as $message) {
                    if (is_array($message) && isset($message['attributes'])) {

                        $arrayKeys = array_keys($message['attributes']);
                        for ($i = 0; $i < count($arrayKeys); $i++) {
                            $key = $arrayKeys[$i];
                            $value = $message['attributes'][$key];

                            $this->m_application->getBootstrap()->getResource('user')->setEnterpriseApplicationData(get_class($this), $key, $value);
                        }
                    }
                }


                return $result;
            } else {

                // Keine Konfiguration vorhanden
            }
        } else {
            return true;
        }
    }

    /**
     * Performs an logout attempt
     *
     * @return void
     */
    public function logout() {
        $this->m_auth->clearIdentity();
    }

    /**
     * This method could be enhanced or replaces by passing an array of desired service names to the
     * constructor and instanciate them dynamically
     *
     * @return array of services
     */
    abstract protected function _initServices();

    /**
     * Call a service method, if application needs authentication, the current user will be
     * authenticated, if credentials are present. If not an error response will be fired.
     * Authentication will be invoked, if auth if the enterprise application is configured in config.ini.
     *
     * @return
     * @param $service String Classname of the service definition (ELSTR_Service_Abstract)
     * @param $method String Name of the method to call
     * @param $params Array List of parameter for the method
     */
    public function call($service, $method) {
        // Handle authentications
        if (isset($this->m_authAdapter)) {
            $isAuth = $this->m_auth->hasIdentity();
        } else {
            $isAuth = true;
        }
        // DEBUG HACK:
        // $isAuth = false;

        $response = array();
        if ($isAuth) {
            if (array_key_exists($service, $this->m_services)) {
                // Get all parameters expect the furst two
                $params = array_slice(func_get_args(), 2);
                $response = $this->m_services[$service]->call($method, $params);
            } else {
                throw new ELSTR_Exception('1004', 1004, null, $this);
            }
        } else {
            throw new ELSTR_Exception('1005', 1005, null, $this);
        }
        return $response;
    }

    /**
     * Add a new service to the application
     *
     * @param $service ELSTR_Service_Abstract
     * @return void
     */
    public function registerService($service) {
        $this->m_services[get_class($service)] = $service;
    }

    /**
     * Get a registered servcie
     *
     * @param $name String
     * @return ELSTR_Service_Abstract
     */
    public function getService($name) {
        if (array_key_exists($name, $this->m_services)) {
            return $this->m_services[$name];
        }
        return false;
    }

    /**
     * Remove a service from the application
     *
     * @param $service ELSTR_Service_Abstract
     * @return void
     */
    public function unregisterService($service) {
        unset($this->m_services[get_class($service)]);
    }

}

