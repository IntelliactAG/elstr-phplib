<?php
    require_once('ELSTR_EnterpriseApplication_Acl_Abstract.php');
    require_once('Services/ELSTR_Service_Alfresco.php');

    /**
     * This as an example of a custom application, using http authentication
     *
     * @author Marco Egli
     */
    class ELSTR_EnterpriseApplication_Alfresco extends ELSTR_EnterpriseApplication_Acl_Abstract
    {

        /**
         * Required implementation of abstract method
         * Register the services offered by this application
         *
         * @return
         */
        protected function _initServices() {
        	$options = $this->m_application->getOption(get_class($this));
        	$this->registerService(new ELSTR_Service_Alfresco($options));
        }
        
        public function call($service, $method){
	        $args = func_get_args();
	        
	        $enterpriseApplicationData = $this->m_application->getBootstrap()->getResource('user')->getEnterpriseApplicationData();
	        
	        $args[3]['ticket'] = $enterpriseApplicationData[get_class($this)]['ticket'];
	               
			if(PHP_VERSION_ID >= 50300){
				// For PHP Version >= 5.3.0
				// for PHP 5.3 we should wirte as follows (?) Ref: http://us2.php.net/manual/en/function.call-user-func-array.php
				$response = call_user_func_array('parent::call', $args);
			} else {
				// For PHP Version < 5.3.0
				$response = call_user_func_array(array($this, 'parent::call'), $args);
			}
			
			return $response;
        }

    }
?>