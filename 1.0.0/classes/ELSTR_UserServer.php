<?php	
	/**
	 * This class implements the user authentication and registration at ELSTR
	 * Once the user is authenticated, his appplications will be added to the session accordingly
	 * 
	 * @author Felix Nyffenegger
	 * @version 1.0
	 * @created 19-Okt-2009 17:41:15
	 */
	class ELSTR_UserServer
	{
		/**
		 * Service method to handle auth request
		 * If user can be authenticated, save user into session
		 * 
		 * @return Array Response messages
		 */
		public function auth($password, $username) {	
			$response = array();	
			$result = $this->_auth($username, $password);
			if ($result == true) {
				$sessionAuth = new Zend_Session_Namespace('ELSTR_Auth'); 
				$sessionAuth->username = $username;
				$response['isAuth'] = "true";
				$response['username'] = $username;
			} else {
				Zend_Session::namespaceUnset('ELSTR_Auth');
				$response['isAuth'] = "false";
			}
			return $response;
		}
		
		/**
		 * Service method to handle logout request
		 * 
		 * @return Array Response messages
		 */
		public function logout() {	
			$response = array();	
			Zend_Session::namespaceUnset('ELSTR_Auth');
			$response['action'] = "success";
			return $response;
		}
	
		/**
		 * Service method to handle user creation
		 * Creates a new user
		 * 
		 * @return 
		 */
		public function create($username, $password) {
			return NULL_EMPTY_STRING;
		}
	
		/**
		 * Auth implementation
		 * 
		 * @return Boolean true and only true if user could be authenticated
		 * @param $username String username
		 * @param $password String password
		 */
		private function _auth($username, $password) {
			// Here implement the Auth according to configured Auth method
			// Example for DBTble:
			// if (config->authmethod === 'dbtable')
				/*$authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter, 'users', 'username', 'password');
				$authAdapter
				    ->setIdentity($username)
				    ->setCredential($password);
				$result = $authAdapter->authenticate();*/
			// end if
			
			// [DEBUG] For now just loop trough
			return true;
		}
	}
?>