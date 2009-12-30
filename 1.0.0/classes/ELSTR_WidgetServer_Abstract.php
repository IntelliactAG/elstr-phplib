<?php
	require_once ('ELSTR_EnterpriseApplication_Abstract.php');
	require_once ('Zend/Session.php');

	/**
	 * This is the abstract class every WidgetServer must implement.
	 * Note: $acl and $user are optional, but must be set if one of the applications needs ACL control.
	 *
	 * These methods must be implemented:
	 * _initApplications($acl, $user) : Tell the WidgetServer which applications to use with $this->registerApplication()
	 *
	 * @author Felix Nyffenegger
	 * @version 1.0
	 * @created 19-Okt-2009 17:41:37
	 */
	abstract class ELSTR_WidgetServer_Abstract
	{
		protected $m_application;
		protected $m_applications;
		protected $m_acl;
		protected $m_user;

		function __construct($application) {
			$this->m_applications = array();
			$this->m_application = $application;
			$this->m_user = $this->m_application->getBootstrap()->getResource('user');
			$this->m_acl  = $this->m_application->getBootstrap()->getResource('acl');
			$this->_initApplications($this->m_acl, $this->m_user);
		}

		/**
		 * The implementation class must implement this method in order
		 * to add all the applications needed to the $m_applications array
		 * [OPTIPON] This could later be replaced by pure configuration
		 */
		abstract protected function _initApplications($acl, $user);

		/**
		 * This function will be called by the RequestHandler. Inside the handle
		 * function the response musst be generated and returned. This method will
		 * first Check against the ACL, if the user is allowed to handle the request.
		 *
		 * @return void
		 */
		public function handle() {
			$username = $this->m_user->getUsername();
			// Check on Widget Level
			if ($this->m_acl->isAllowed($username, get_class($this))) {
				// check if method ressource is defined, if not allow to execute
				if ($this->m_acl->has($this->_getMethod().'@'.get_class($this))) {
					// check on method ressource is defineds
					if ($this->m_acl->isAllowed($username, $this->_getMethod().'@'.get_class($this))) {
                        $this->_handle();
					}
					else {
						throw new Exception('1007');
					}
				}
				else {
				    $this->_handle();
				}
			}
			else {
				throw new Exception('1006');
			}
		}
		/**
		 * This method must returen the name of the method to be called by handle
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
		 * Register an application for this WidgetServer
		 * Carefull: yet, only one instance of an application can be registered at a time
		 *
		 * @param $application ELSTR_EnterpriseApplication_Abstract
		 * @return void
		 */
		protected function registerApplication($application) {
			$this->m_applications[get_class($application)] = $application;
		}

		/**
		 * Get a registered servcie
		 *
		 * @param $name String
		 * @return ELSTR_Service_Abstract
		 */
		protected function getApplication($name) {
			if (array_key_exists($name, $this->m_applications)) {
	            return $this->m_applications[$name];
	        }
	        return false;
		}

		/**
		 * Remove a service from the application
		 *
		 * @param $service ELSTR_Service_Abstract
		 * @return void
		 */
		protected function unregisterApplication($application) {
			unset($this->m_applications[get_class($application)]);
		}
	}
?>