<?php
require_once ('..\..\..\zf\1.8.2\Zend\Auth\Adapter\Interface.php');
require_once ('IService.php');
require_once ('..\..\..\zf\1.8.2\Zend\Session.php');

/**
 * @author nyffenegger
 * @version 1.0
 * @created 19-Okt-2009 17:41:50
 */
class ELSTR_EnterpriseApplication
{

	var $_services;
	var $m_Zend_Session;

	function ELSTR_EnterpriseApplication()
	{
	}



	/**
	 * Performs an authentication attempt
	 * @return Zend_Auth_Result
	 */
	function authenticate()
	{
	}

}
?>