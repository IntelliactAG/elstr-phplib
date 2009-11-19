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
		function __construct($acl = null, $user = null) {
			parent::__construct($acl, $user);
		}
		
		/**
		 * Create a JSON Server and handle itselfs
		 * 
		 * @return void
		 */
		public function handle() {
			$server = new ELSTR_JsonServer();
			$server->setClass($this);
			$server->handle();
		}
	}
?>
