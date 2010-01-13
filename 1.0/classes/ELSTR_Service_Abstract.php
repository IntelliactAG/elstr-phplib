<?php
	/**
	 * This is a generic Servcie class. All services accessed by an application must
	 * inherit from this class.
	 *
	 * These methods must be implemented:
	 *
	 * @author Felix Nyffenegger
	 * @version 1.0
	 * @created 19-Okt-2009 17:41:37
	 */

	abstract class ELSTR_Service_Abstract
	{

		function __construct() {
		}

		/**
		 * This ist the centeral method to call webservice functions used by applications.
		 * To make sure, service call methods are never called directely, implement them as
		 * protected functions
		 *
		 * @return Array Resultset
		 * @param $function String name of the functionto call
		 * @param $params Array the parameters used by the function
		 */
		public function call($function, $params) {
			//TODO: Check if function is present
			if ($params != null) {
				$resultArray = call_user_func_array(array($this, $function), $params);
                return $resultArray;
			}
			else {
				return $this->$function();
			}

		}
	}
?>