<?php
	/**
	 * This is a generic server class. All requests from the Elstr client are served by an implementation of
	 * ESLTR_Service_Abstract in order to fulfill the generic reqirements of HandelRequest.php.
	 *
	 * @author Felix Nyffenegger
	 * @version 1.0
	 * @created 19-Okt-2009 17:41:37
	 */

	abstract class ELSTR_Server_Abstract
	{
		protected $m_application;
		
		/** 
		 * The constructure requires $application to be handled
		 * @constructor
		 * @param $application Object
		 */
		function __construct($application) {
			$this->m_application = $application;
		}
		
		/**
		 * This function will be called by the RequestHandler. Inside the handle
		 * function the response musst be generated and returned.
		 *
		 * @return void
		 */
		abstract public function handle();
	}
?>
