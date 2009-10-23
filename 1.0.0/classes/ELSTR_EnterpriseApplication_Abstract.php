<?php
	require_once('ELSTR_ErrorResponse.php');
	
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
	 */
	abstract class ELSTR_EnterpriseApplication_Abstract
	{
		var $m_services;
		var $m_authAdapter;
		
		function __construct() {
			$m_authAdapter = $this->_initAuthAdapter();
			$m_services = array();
			$m_services = $this->_initServices();
		}
		
		/**
		 * Performs an authentication attempt
		 * @return Zend_Auth_Result
		 */
		function authenticate()
		{
		}
	
		/** 
		 * The implementation of every enterprise application must define its own auth adapter
		 * 
		 * @return Zend_Auth_Adapter
		 */	
		abstract protected function _initAuthAdapter();
	
		/**
		 * This method could be enhanced or replaces by passing an array of desired service names to the 
		 * constructor and instanciate them dynamically
		 * 
		 * @return array of services
		 */
		abstract protected function _initServices();
		
		/**
		 * Call a service method
		 * 
		 * @return 
		 * @param $service String Classname of the service definition (ELSTR_Service_Abstract)
		 * @param $method String Name of the method to call
		 * @param $params Array List of parameter for the method
		 */
		public function call($service, $method, $params) {
			if (array_key_exists($service, $this->m_services)) {
				return $this->m_services[$service]->call($method, $params);
			}
			else {
				$response = ELSTR_ErrorResponse::create(1004);
				$response['details'] = $service.' @ '.get_class($this);
				return $response;
			}
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
?>