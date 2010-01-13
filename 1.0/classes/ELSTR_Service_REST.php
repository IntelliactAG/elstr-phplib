<?php
	require_once('ELSTR_Service_Abstract.php');
	require_once('ELSTR_HttpClient.php');
	/**
	 * This is very simple implementation of ELSTR_Service_Abstract to allow cosumation
	 * of REST services
	 *
	 * @author Felix Nyffenegger
	 * @version 1.0
	 * @created 19-Okt-2009 17:41:37
	 */
	abstract class ELSTR_Service_REST extends ELSTR_Service_Abstract
	{
		var $restClient;

		function __construct() {
			parent::__construct();
			$this->restClient = new ELSTR_HttpClient();
		}

	}
?>