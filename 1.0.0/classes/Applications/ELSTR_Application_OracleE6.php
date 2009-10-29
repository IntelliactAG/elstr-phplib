<?php
	require_once('ELSTR_EnterpriseApplication_Acl_Abstract.php');
	require_once('Services/ELSTR_Service_OracleE6.php');
	
	/**
	 * This as an example of a custom application, using http authentication
	 * 
	 * @author Marco Egli
	 */
	class ELSTR_Application_OracleE6 extends ELSTR_EnterpriseApplication_Acl_Abstract
	{
		/**
		 * Define the Auth adapter for this application
		 * @return 
		 */
		protected function _initAuthAdapter() {
			// No auth needed right now
		}
		
		/**
		 * Required implementation of abstract method
		 * Register the services offered by this application
		 * 
		 * @return
		 */
		protected function _initServices() {
			$this->registerService(new ELSTR_Service_OracleE6());
		}
	}
?>
