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
	 */
	abstract class ELSTR_EnterpriseApplication_Acl_Abstract extends ELSTR_EnterpriseApplication_Abstract
	{
		protected $m_acl;
		protected $m_user;
		
		/**
		 * Create an ACL controler for this applications
		 * 
		 * @return 
		 * @param $user Object
		 * @param $acl Object
		 */
		public function setAclControler($acl, $user) {
			// the controler is currently directly implemented here (function call()). It could also be a seperate class
			// in future, to allow implementation of different controlers
			$this->m_acl = $acl;
			$this->m_user = $user;
		}
		
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
		public function call($service, $method, $params) {	
			$response = array();
			if ($this->m_user == null || $this->m_acl == null) {
				$response = ELSTR_ErrorResponse::create(1000);
			}
			else {
				$username = $this->m_user->getUsername();
				// check on application level
				if ($this->m_acl->isAllowed($username, get_class($this))) {
					// check on service level
					if ($this->m_acl->isAllowed($username, $service)) {
						// check on method ressource is defined
						if ($this->m_acl->has($service.'_'.$method))
							// check on method ressource is defineds
							if ($this->m_acl->isAllowed($username, $service.'_'.$method)) {					
								$response = parent::call($service, $method, $params);						
							}
							else {
								$response = ELSTR_ErrorResponse::create(1003);
								$response['message'] .= $method.'@'.$service;
							}
						else {
							$response = parent::call($service, $method, $params);	
						}
					}
					else {
						$response = ELSTR_ErrorResponse::create(1002);
						$response['message'] .= $service;
					}
				}
				else {
					$response = ELSTR_ErrorResponse::create(1001);
					$response['message'] .= get_class($this);
				}
			}
			return $response;
		}
	}
?>