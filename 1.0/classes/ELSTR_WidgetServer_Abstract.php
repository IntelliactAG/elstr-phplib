<?php

require_once ('ELSTR_EnterpriseApplication_Abstract.php');
require_once ('ELSTR_Server_Abstract.php');
require_once ('Zend/Session.php');
require_once ('ELSTR_Exception.php');
require_once ('ELSTR_Exception_Handling.php');

/**
 * This is the abstract class every WidgetServer must implement.
 * Note: $acl and $user are optional, but must be set if one of the applications needs ACL control.
 *
 * These methods must be implemented:
 * _initEnterpriseApplications($acl, $user) : Tell the WidgetServer which applications to use with $this->registerEnterpriseApplication()
 *
 * @author Felix Nyffenegger
 * @version 1.0
 * @created 19-Okt-2009 17:41:37
 */
abstract class ELSTR_WidgetServer_Abstract extends ELSTR_Server_Abstract {

    protected $m_enterpriseApplications;
    protected $m_params;
    protected $m_options;

    /**
     * Constructor of the WidgetServer
     *
     * @param $application ZEND Application
     * @param $params Get/JSON Poste parameters from the request
     */
    function __construct($application, $params = null) {
        parent::__construct($application);
        $this->m_enterpriseApplications = array();
        // set options
        $options = $application->getOption(get_class($this));
        if (isset($options)) {
            $this->m_options = $options;
        }
        // Init
        $this->_initEnterpriseApplications($this->m_application);
        $this->m_params = $params;
    }

    /**
     * The implementation class must implement this method in order
     * to add all the applications needed to the $m_enterpriseApplications array
     * [OPTION] This could later be replaced by pure configuration
     */
    abstract protected function _initEnterpriseApplications();

    private function _logResponse() {
        $logger = $this->m_application->getBootstrap()->getResource("logger");
        if (isset($logger)) {
            $byteLimit = 100000;
            $response = print_r($this->_getResponse(), true);
            if (strlen($response)>$byteLimit) {
                $response = substr($response,0,$byteLimit)."... (response truncated at $byteLimit bytes)";
            }
            $logger->debug('ELSTR_WidgetServer_Abstract response: ' . $response);
        }
    }

    /**
     * This function will be called by the RequestHandler. Inside the handle
     * function the response musst be generated and returned. This method will
     * first Check against the ACL, if the user is allowed to handle the request.
     *
     * @throws ELSTR_Exception
     */
    public function handle() {
        // Get acl and user object from application
        $acl = $this->m_application->getBootstrap()->getResource('acl');
        $user = $this->m_application->getBootstrap()->getResource('user');

        $username = $user->getUsername();
        // Check on Widget Level
        if ($acl->isAllowed($username, get_class($this))) {
            // check if method ressource is defined, if not allow to execute
            if ($acl->has($this->_getMethod() . '@' . get_class($this))) {
                // check on method ressource is defined
                if ($acl->isAllowed($username, $this->_getMethod() . '@' . get_class($this))) {
                    $this->_handle();
                    $this->_logResponse();
                } else {
                    throw new ELSTR_Exception('1007', 1007, null, $this);
                }
            } else {
                $this->_handle();
                $this->_logResponse();
            }
        } else {
            throw new ELSTR_Exception('1006', 1006, null, $this);
        }
    }

    /**
     * This method must return the name of the method to be called by handle
     * Depending on the request method (GET, POST) and the argument specification
     * this might be implemented in different flavours.
     *
     * @return
     */
    abstract protected function _getMethod();

    /**
     * Handle will call the _handle method to actually handle the request.
     * This method must be implmented according to the Response realized by
     * the Widget (e.g. JSON, Stream, etc.)
     *
     * @return void
     */
    abstract protected function _handle();

    /**
     * Get response object
     *
     * @return
     */
    abstract protected function _getResponse();


    /**
     * Get a registered servcie
     *
     * @param  string $name
     * @return object ELSTR_EnterpriseApplication_Abstract
     */
    protected function getEnterpriseApplication($name) {
        if (array_key_exists($name, $this->m_enterpriseApplications)) {
            return $this->m_enterpriseApplications[$name];
        }
        return false;
    }

    /**
     * Register an application for this WidgetServer
     * Carefull: yet, only one instance of an application can be registered at a time
     *
     * @param $enterpriseApplication ELSTR_EnterpriseApplication_Abstract
     * @return void
     */
    protected function registerEnterpriseApplication($enterpriseApplication) {
        $this->m_enterpriseApplications[get_class($enterpriseApplication)] = $enterpriseApplication;
    }

    /**
     * Remove a service from the application
     *
     * @param $enterpriseApplication ELSTR_Service_Abstract
     * @return void
     */
    protected function unregisterEnterpriseApplication($enterpriseApplication) {
        unset($this->m_enterpriseApplications[get_class($enterpriseApplication)]);
    }

}
