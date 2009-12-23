<?php
	/**
	 * This class implements the user authentication and registration at ELSTR
	 * Once the user is authenticated, his appplications will be added to the session accordingly
	 *
	 * @author Felix Nyffenegger, Marco Egli
	 * @version 1.0
	 * @created 19-Okt-2009 17:41:15
	 */
	class ELSTR_AuthServer
	{
		private $m_application;

		function __construct($application)
		{
			$this->m_application = $application;
		}

		/**
		 * Create a JSON Server and handle itselfs
		 *
		 * @return void
		 */
		public function handle()
		{
			$server = new ELSTR_JsonServer();
			$server->setClass($this);
			$server->handle();
		}

		/**
		 * Service method to handle auth request
		 * If user can be authenticated, save user into session
		 *
		 * @param string $username
		 * @param string $password
		 * @return Array Response messages
		 */
		public function auth($username,$password) {
			$response = array();
			$result = $this->_auth($username, $password);

			if (!$result->isValid()) {
				// Authentication failed; print the reasons why
				foreach ($result->getMessages() as $message) {
					$response['message'][] =  $message;
				}
			}

			switch ($result->getCode()) {

				case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
					/** do stuff for nonexistent identity **/
					$response['action'] = "failure_identity_not_found";
					break;

				case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
					/** do stuff for invalid credential **/
					$response['action'] = "failure_credential_invalid";
					break;

				case Zend_Auth_Result::SUCCESS:
					/** do stuff for successful authentication **/
					$response['action'] = "success";
					$response['username'] = $username;
					break;

				default:
					/** do stuff for other failure **/
					$response['action'] = "failure";
					break;
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

			$this->m_application->getBootstrap()->getResource('auth')->clearIdentity();

			$response['action'] = "success";
			$response['username'] = "anonymous";
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

			$configAuth = $this->m_application->getOption("auth");

			switch($configAuth['method']){
				case "ldap":

					$options = $configAuth[$configAuth['method']];

					$adapter = new Zend_Auth_Adapter_Ldap($options, $username,$password);

					$result = $this->m_application->getBootstrap()->getResource('auth')->authenticate($adapter);


					return $result;;
					break;
				default:
					// custom adpter
					// require_once($configAuth['method'] . ".php");
					// $options = $configAuth[$configAuth['method']];
					// $adapter = new $configAuth['method']($options, $username,$password);
					// $result = $this->m_application->getBootstrap()->getResource('auth')->authenticate($adapter);
					return $result;
			}

		}
	}
?>