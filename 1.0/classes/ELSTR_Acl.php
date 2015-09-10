<?php

/**
 * This class extendes the Zend_Acl by a db loader to reviec ACL data from ELSTR DB
 *
 * @author Felix Nyffenegger
 * @version 1.0
 * @created 21-Okt-2009 14:27:39
 */
class ELSTR_Acl extends Zend_Acl {

	var $m_session_acl;
	var $m_roles;
	var $m_application;

	function __construct($application) {
		$this->m_session_acl = new Zend_Session_Namespace('ELSTR_Acl');
		$this->m_roles = array();
		$this->m_application = $application;
	}

	public function loadFromDb() {
		$db = $this->m_application->getBootstrap()->getResource('db');
		if (is_array($db)) $db = $db['elstr'];
		// Select all roles from db
		$select = $db->select();
		$select->from('Role', array('_id', 'name'));
		$stmt = $db->query($select);
		$resultRoles = $stmt->fetchAll();
		// Get the role data and check if it is registered
		for ($i = 0; $i < count($resultRoles); $i++) {
			$role = $resultRoles[$i];
			$roleName = $role['name'];
			$roleId = $role['_id'];
			// if the role is not registereed
			if (!$this->hasRole($roleName)) {
				$this->_createRoleFromDb($db, $roleId, $roleName);
			}
		}
		// Select all resources from db
		// And add the resources to the acl
		$select = $db->select();
		$select->from('Resource', array('_id', 'name'));
		$stmt = $db->query($select);
		$resultResources = $stmt->fetchAll();
		// Get the resource data and check if it is registered
		for ($i = 0; $i < count($resultResources); $i++) {
			// Add the resource
			$this->add(new Zend_Acl_Resource($resultResources[$i]['name']));
		}
		// Select the role-resource relation from db
		// And set the right defined
		$select = $db->select();
		$select->from('RoleResource', array('_id', 'access'));
		$select->join('Role', 'Role._id = RoleResource._id1', array('roleName' => 'name'));
		$select->join('Resource', 'Resource._id = RoleResource._id2', array('resourceName' => 'name'));
		$stmt = $db->query($select);
		$resultAccess = $stmt->fetchAll();

		for ($i = 0; $i < count($resultAccess); $i++) {
			$access = $resultAccess[$i]['access'];
			$roleName = $resultAccess[$i]['roleName'];
			$resourceName = $resultAccess[$i]['resourceName'];
			// set rights
			// $this->deny('role_example', 'EXAMPLE_Resource');
			// $this->allow('role_example', 'EXAMPLE_Resource');
			$this->$access($roleName, $resourceName);
		}
	}

	/**
	 * Check if the current user has at least one role
	 * If not - add it to the role_anonymous
	 */
	public function currentUserHasRole($username) {
		// Check if the role is registered
		if (!$this->hasRole($username)) {
			$aclOptions = $this->m_application->getOption("acl");

			$configRoles = array();
			// Get the configurated roles
			// create user-specific role array
			$roles = array_keys($aclOptions['userRoles']);
			for ($i = 0; $i < count($roles); $i++) {
				$users = $aclOptions['userRoles'][$roles[$i]];

				for ($n = 0; $n < count($users); $n++) {
					if ($username == $users[$n]) {
						$configRoles[] = $roles[$i];
					}
				}
			}
			// Use this the get the roles of a user!
			$sessionRoles = array();
			if (isset($this->getSession()->$username->roles)) {
				$sessionRoles = ($this->getSession()->$username->roles);
			}

			$parentRoles = array_merge($configRoles, $sessionRoles);

			// Suggestion to implement a role "role_user" for all registered users (username != 'anonymous')
			// if ($username != 'anonymous') {
			//   // Stay backward compatibel:
			//   if ($this->hasRole('role_user') {
			//     $parentRoles[] = 'role_user';
			//   }
			if (count($parentRoles) > 0) {
				$this->addRole(new Zend_Acl_Role($username), $parentRoles);
				//print_r($parentRoles);
			} else {
				// If not add only the role_anonymous
				$this->addRole(new Zend_Acl_Role($username), 'role_anonymous');
			}
		}
	}

	public function getResourcesAllowed($db, $roleName) {
		if (is_array($db)) $db = $db['elstr'];
		$resourcesAllowed = array();
		// Select all roles from db
		$select = $db->select();
		$select->from('Resource');
		$stmt = $db->query($select);
		$resultResources = $stmt->fetchAll();

		for ($i = 0; $i < count($resultResources); $i++) {
			// Get the right for every role
			$resourceName = $resultResources[$i]['name'];
			$isAllowed = $this->isAllowed($roleName, $resourceName);
			if ($isAllowed) {
				$resourcesAllowed[] = $resourceName;
			}
		}

		return $resourcesAllowed;
	}

	public function getSession() {
		return $this->m_session_acl;
	}

	/**
	 * Add a new role to the session for a specific user
	 *
	 * @param $username (string)
	 * @param $role (string)
	 * @return array
	 */
	public function addRoleToSession($username, $role) {
		$sessionRoles = array();
		if (isset($this->getSession()->$username->roles)) {
			$sessionRoles = ($this->getSession()->$username->roles);
		}
		$sessionRoles[] = $role;
		if(property_exists($this->getSession(), $username)){
			$this->getSession()->$username->roles = $sessionRoles;	
		} else {
			$this->getSession()->$username = (object) array("roles" => $sessionRoles);
		}
		// Using remove role and add role for update the roles
		try {
			$this->removeRole($username);
		}
		catch (Zend_Acl_Exception $e) {
		}
		$this->addRole(new Zend_Acl_Role($username), $sessionRoles);
	}

	/**
	 * Return all defined roles
	 *
	 * @return array
	 */
	public function getDefinedRoles() {
		return $this->m_roles;
	}

	/**
	 * Create a role from db
	 * Add/registers roles recursivly from the db structure
	 *
	 * @param mixed $db database
	 * @param string $roleId database _id of the role
	 * @param string $roleName database name of the role, the role identifier
	 * @param array $configRoles
	 * @return void
	 */
	private function _createRoleFromDb($db, $roleId, $roleName) {
		// get parent roles
		$select = $db->select();
		$select->from('Role', array('_id', 'name'));
		$select->join('RoleRole', 'Role._id = RoleRole._id1', array());
		$select->where('RoleRole._id2 = ?', $roleId);
		$stmt = $db->query($select);
		$resultParentRoles = $stmt->fetchAll();
		$parentRoles = array();
		for ($i = 0; $i < count($resultParentRoles); $i++) {
			$parentRoleName = $resultParentRoles[$i]['name'];
			$parentRoleId = $resultParentRoles[$i]['_id'];

			if (!$this->hasRole($parentRoleName)) {
				$this->_createRoleFromDb($db, $parentRoleId, $parentRoleName);
			}
			$parentRoles[] = $parentRoleName;
		}

		$this->addRole(new Zend_Acl_Role($roleName), $parentRoles);
		$this->m_roles[] = $roleName;
	}

}

?>