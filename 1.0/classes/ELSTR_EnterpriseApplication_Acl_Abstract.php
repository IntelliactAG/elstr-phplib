<?php
	require_once('ELSTR_EnterpriseApplication_Abstract.php');

	/**
	 * This is an implementation of ELSTR_EnterpriseApplication_Abstract, which overrides the call
	 * method to chekc the calls against the ACL
	 * After instanciation, the setAclController method must be invoked
	 *
	 * @author Felix Nyffenegger
	 * @version 1.0
	 * @created 19-Okt-2009 17:41:50
	 * @modified 11-Jan-2010 Marco Egli
	 */
	abstract class ELSTR_EnterpriseApplication_Acl_Abstract extends ELSTR_EnterpriseApplication_Abstract
	{
		/**
		 * Call a service method if ACL allows the user to do so.
		 * current implemenation:
		 * Application and Services: Ressourcename = <Classname> (must be explicitly allowed)
		 * Methods: 				 Ressourcename = <Classname>_<functionname>
		 * If methods is not explicitly metioned in ACL, access will be allowed by default
		 * Needs to be more sophisticated in future (configure to generally allow or deny)
		 *
		 * @return
		 * @param $service String Classname of the service definition (ELSTR_Service_Abstract)
		 * @param $method String Name of the method to call
		 * @param $params Array List of parameter for the method
		 */
		public function call($service, $method) {
			// Get acl and user object from application
			$acl = $this->m_application->getBootstrap()->getResource('acl');
			$user = $this->m_application->getBootstrap()->getResource('user');

			$response = array();
			if ($user == null || $acl == null) {
				throw new ELSTR_Exception('1000',1000,null,$this);
			}
			else {
				$username = $user->getUsername();
				// check on application level
				if ($acl->isAllowed($username, get_class($this))) {
					// check on service level
					if ($acl->isAllowed($username, $service)) {
						// check on method ressource is defined
						if ($acl->has($method.'@'.$service))
							// check on method ressource is defineds
							if ($acl->isAllowed($username, $method.'@'.$service)) {
								$args = func_get_args();
								if(PHP_VERSION_ID >= 50300){
									// For PHP Version >= 5.3.0
									// for PHP 5.3 we should wirte as follows (?) Ref: http://us2.php.net/manual/en/function.call-user-func-array.php
									$response = call_user_func_array('parent::call', $args);
								} else {
									// For PHP Version < 5.3.0
									$response = call_user_func_array(array($this, 'parent::call'), $args);
								}
							}
							else {
								throw new ELSTR_Exception('1003',1003,null,$this);
							}
						else {
							$args = func_get_args();
							if(PHP_VERSION_ID >= 50300){
								// For PHP Version >= 5.3.0
								$response = call_user_func_array('parent::call', $args);
							} else {
								// For PHP Version < 5.3.0
								$response = call_user_func_array(array($this, 'parent::call'), $args);
							}
						}
					}
					else {
						throw new ELSTR_Exception('1002',1002,null,$this);
					}
				}
				else {
					throw new ELSTR_Exception('1001',1001,null,$this);
				}
			}
			return $response;
		}
	}
