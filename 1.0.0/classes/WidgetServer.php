<?php
require_once ('ELSTR_JsonServer.php');
require_once ('..\..\..\..\..\..\Temp\ELSTR_Application.php');
require_once ('..\..\..\zf\1.8.2\Zend\Session.php');
require_once ('ArticleWidgetServer.php');

/**
 * @author egli
 * @version 1.0
 * @created 19-Okt-2009 17:10:39
 */
class WidgetServer extends ExamleWidgetServer
{

	var $m_JsonServer;
	var $m_EnterpriseApplication;
	var $m_Zend_Session;

	/**
	 * Load all Applications into Applications Array
	 */
	function WidgetServer()
	{
	}

}
?>