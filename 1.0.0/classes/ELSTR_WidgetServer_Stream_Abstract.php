<?php
	require_once ('ELSTR_WidgetServer_Abstract.php');
	require_once ('');
	/**
	 * This is an abstract WidgetServer implementation which returns a binary stream 
	 * 
	 * @author Felix Nyffenegger
	 * @version 1.0
	 * @created 19-Nov-2009 17:41:37
	 */
	
	abstract class ELSTR_WidgetServer_Stream_Abstract extends ELSTR_WidgetServer_Abstract
	{	
		private $m_response;
			
		function __construct($acl = null, $user = null) {
			parent::__construct($acl, $user);
			$m_response = new Zend_Controller_Response_Abstract();
		}
		
		protected function setHeader() {
			$m_response->setHeader();
		}
		
		/**
		 * Create a Response and handle itselfs
		 * 
		 * @return void
		 */
		public function handle() {
			//TODO: implement generic file response
			// - read $GET 
			// - create Response Object
			// - set Header
			$callmethod = $_GET['method'];
			$params = $_GET;
			$result =  $this->$callmethod($params);
			// $m_response->appendBody($this->$callmethod($params));
			// $m_response->sendResponse();
		}
	}
?>
