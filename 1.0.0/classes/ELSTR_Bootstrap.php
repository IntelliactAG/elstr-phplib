<?php
	require_once 'ELSTR_Acl';
	
	/**
	 * This is the Boostrap class for the ELSTR Framework. It controls proper initialisation and registration
	 * of Ressources and Components
	 * 
	 * @author Felix Nyffenegger / Marco Egli
	 * @version 1.0.0
	 * @created 19-Okt-2009 17:40:03
	 */
	class ELSTR_Bootstrap extends Zend_Application_Bootstrap_BootstrapAbstract
	{
	
	    /**
	     * Constructor
	     *
	     * 
	     * @param  Zend_Application|Zend_Application_Bootstrap_Bootstrapper $application 
	     * @return void
	     */
	    public function __construct($application)
	    {
	        parent::__construct($application);        
	    }
	
	    /**
	     * Run the application
	     *   * 
	     * @return void     
	     */
	    public function run()
	    {
	        
	    }
		
		protected function _initACL() {
			
		}
	}
?>