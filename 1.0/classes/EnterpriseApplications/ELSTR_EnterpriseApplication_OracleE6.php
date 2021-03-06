<?php
    require_once('ELSTR_EnterpriseApplication_Acl_Abstract.php');
    require_once('Services/ELSTR_Service_OracleE6.php');

    /**
     * This as an example of a custom application, using http authentication
     *
     * @author Marco Egli
     */
    class ELSTR_EnterpriseApplication_OracleE6 extends ELSTR_EnterpriseApplication_Acl_Abstract
    {

        /**
         * Required implementation of abstract method
         * Register the services offered by this application
         *
         * @return
         */
        protected function _initServices() {        	
        	$this->registerService(new ELSTR_Service_OracleE6($this->m_options));
        }

    	/**
    	 * Create a query string for oracle e6
    	 * @param string $input
    	 * @param string $operator blank characters will be replaced with AND "&" or OR "|" operator. Default is AND: "&"
    	 * @return string
    	 */
    	public function createQueryString($input,$operator = "&") {
    		$input = ltrim($input);
    		$input = rtrim($input);
    		$input = str_replace("\'", "'", $input);
    		$input = str_replace("\"", "'", $input);
    		$input = str_replace('*', '%', $input);
    		if (substr($input, -1) == "'" && substr($input, 0, 1) == "'") {
    			if (strlen($input) > 3){
    				$input = str_replace("'", '%', $input);
    				$input = "'".$input."'";
    			}
    		} else {
    			$input = str_replace(' ', '% & %', $input);
    			if ($input != "") {
    				$input = "%".$input."%";
    			}
    		}
    		return ($input);
    	}

    }
?>