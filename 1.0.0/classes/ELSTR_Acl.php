<?php
require_once ('Zend\Acl.php');
require_once ('Zend\Session.php');

/**
 * @author nyffenegger
 * @version 1.0
 * @created 21-Okt-2009 14:27:39
 */
class ELSTR_Acl
{

	var $m_Zend_Acl;

	function __construct () {
		$m_Zend_Acl = new Zend_Acl();
	}

	function load()
	{
		$elstrCore = new Zend_Session_Namespace('ELSTR.Core');
		//TODO: Here, load the ACL from Database
				
		$m_Zend_Acl->addRole(new Zend_Acl_Role('role_guest'))
		    ->addRole(new Zend_Acl_Role('role_member'), 'role_guest')
		    ->addRole(new Zend_Acl_Role('role_admin'));
		
		//create admin and guest user			
		$m_Zend_Acl->addRole(new Zend_Acl_Role('guest'), 'role_guest');
		$m_Zend_Acl->addRole(new Zend_Acl_Role('member'), 'role_guest');
		
		//add ressources
		$m_Zend_Acl->add(new Zend_Acl_Resource('yql'));
				
		//set rights
		$m_Zend_Acl->deny('role_guest', 'yql');
		$m_Zend_Acl->allow('role_member', 'yql');
		$m_Zend_Acl->allow('role_admin');
			
		$elstrCore->acl = $m_Zend_Acl;
	}

}
?>