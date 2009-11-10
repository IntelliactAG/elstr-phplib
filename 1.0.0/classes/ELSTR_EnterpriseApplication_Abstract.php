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
		protected $m_services;
		protected $m_authAdapter;
		protected $m_user;
		
		function __construct($user = null) {
			$this->m_user = $user;
			$this->m_authAdapter = $this->_initAuthAdapter();
			$this->_initServices();
		}
		
		/**
		 * Performs an authentication attempt, first check if credetials are present,
		 * then check if is authenticated allreadey then try to authenticated.
		 * 
		 * @return true if authentication attempt was successful
		 */
		protected function _authenticate()
		{
			if (isset($this->m_authAdapter)) {
				// Cehck if a user is present
				if (isset($this->m_user)) {
					//Check if credentials are present
					$credentials = $this->m_user->getCredetials(get_class($this)); 
					if (isset($credentials)) {
						// Check if a password is present
						if ($credentials->getPassword() != NULL_EMPTY_STRING) {
							// do authentication attempt
							// $this->m_authAdapter->authenticate()
						}
						else {
							return false;
						}
					}
					else {
						return false;
					}
				}
				else {
					return false;
				}
			}
			else {
				return true;	
			}
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
		 * Call a service method, if application needs authentication, the current user will be
		 * authenticated, if credentials are present. If not an error response will be fired.
		 * 
		 * @return 
		 * @param $service String Classname of the service definition (ELSTR_Service_Abstract)
		 * @param $method String Name of the method to call
		 * @param $params Array List of parameter for the method
		 */
		public function call($service, $method) {
			// Handle authentications
			$isauth = $this->_authenticate();			
			if ($isauth) {
				if (array_key_exists($service, $this->m_services)) {
					// Get all parameters expect the furst two
					$params = array_slice(func_get_args(), 2);
					return $this->m_services[$service]->call($method, $params);
				}
				else {
					$response = ELSTR_ErrorResponse::create(1004);
					$response['details'] = $service.' @ '.get_class($this);
					return $response;
				}
			}
			else  {
				$response = ELSTR_ErrorResponse::create(1005);
				$response['details'] = get_class($this);
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