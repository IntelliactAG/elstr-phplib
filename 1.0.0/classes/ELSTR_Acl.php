<?php

/**
 * This class extendes the Zend_Acl by a db loader to reviec ACL data from ELSTR DB
 *
 * @author Felix Nyffenegger
 * @version 1.0
 * @created 21-Okt-2009 14:27:39
 */

class ELSTR_Acl extends Zend_Acl
{
	function loadFromDB()
	{
		//TODO: Here, load the ACL from Database

		// [DEBUG] For now, just add some dummy data
		$this->addRole(new Zend_Acl_Role('role_anonymous'));
		$this->addRole(new Zend_Acl_Role('role_member'), 'role_anonymous');
		$this->addRole(new Zend_Acl_Role('role_engineering'), 'role_member');
		$this->addRole(new Zend_Acl_Role('role_admin'));

		//create admin and guest user
		$this->addRole(new Zend_Acl_Role('anonymous'), 'role_anonymous');
		$this->addRole(new Zend_Acl_Role('userFoo'), 'role_member');
		$this->addRole(new Zend_Acl_Role('userBar'), 'role_member');
		$this->addRole(new Zend_Acl_Role('egli@intelliact-net.local'), array('role_anonymous','role_member'));
		$this->addRole(new Zend_Acl_Role('nyffenegger@intelliact-net.local'), array('role_anonymous','role_member'));
		$this->addRole(new Zend_Acl_Role('admin'), 'role_admin');

		//add ressources
		$this->add(new Zend_Acl_Resource('EXAMPLE_Application_YAHOO'));
		$this->add(new Zend_Acl_Resource('EXAMPLE_Service_YQL'));
		$this->add(new Zend_Acl_Resource('pizzaService@EXAMPLE_Service_YQL'));
        $this->add(new Zend_Acl_Resource('ELSTR_Service_OracleE6'));
		$this->add(new Zend_Acl_Resource('ELSTR_Application_OracleE6'));
		$this->add(new Zend_Acl_Resource('SULZER_WidgetServer_JSON_ArtikelinfoPreview'));
		$this->add(new Zend_Acl_Resource('getDetails@SULZER_WidgetServer_JSON_ArtikelinfoPreview'));

		//$this->add(new Zend_Acl_Resource('SULZER_WidgetServer_JSON_ClassSettings'));

		//set rights
		//$this->deny('role_anonymous');
		$this->deny('role_anonymous', 'EXAMPLE_Application_YAHOO');
		$this->allow('role_member', 'EXAMPLE_Application_YAHOO');
		$this->allow('role_member', 'EXAMPLE_Service_YQL');
		$this->deny('role_member', 'pizzaService@EXAMPLE_Service_YQL');
		//$this->allow('role_admin');
        //$this->allow('role_anonymous');
		$this->allow('role_anonymous', 'ELSTR_Service_OracleE6');
		$this->allow('role_anonymous', 'ELSTR_Application_OracleE6');
		//$this->deny('role_anonymous', 'SULZER_WidgetServer_JSON_ClassSettings');
		$this->allow('role_member', 'SULZER_WidgetServer_JSON_ArtikelinfoPreview');
		$this->deny('role_member', 'getDetails@SULZER_WidgetServer_JSON_ArtikelinfoPreview');

	}

}
?>