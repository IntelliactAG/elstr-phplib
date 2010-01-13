<?php
	require_once ('ELSTR_WidgetServer_Abstract.php');
	require_once ('ELSTR_JsonServer.php');

	/**
	 * This is an abstract WidgetServer implementation which returns JSON Data
	 *
	 * @author Felix Nyffenegger
	 * @version 1.0
	 * @created 19-Nov-2009 17:41:37
	 */

	abstract class ELSTR_WidgetServer_JSON_Abstract extends ELSTR_WidgetServer_Abstract
	{
		protected $m_server;

		function __construct($acl = null, $user = null) {
			parent::__construct($acl, $user);
			$this->m_server = new ELSTR_JsonServer();
			$this->m_server->setClass($this);
		}

		/**
		 * Implementation of the abstract _getMethod
		 */
		protected function _getMethod() {
			$request = $this->m_server->getRequest();
			$method = $request->getMethod();
			return $method;
		}

		/**
		 * Create a JSON Server and handle itselfs
		 *
		 * @return void
		 */
		protected function _handle() {
			$this->m_server->handle();
		}
	}
?>