<?php

require_once ('ELSTR_JsonServer.php');
/**
* This class implements the user authentication and registration at ELSTR
* Once the user is authenticated, his appplications will be added to the session accordingly
*
* @author Felix Nyffenegger, Marco Egli
* @version 1.0
* @created 19-Okt-2009 17:41:15
*/
class ELSTR_AuthServer {
    private $m_application;

    function __construct($application)
    {
        $this->m_application = $application;
    }

    /**
    * Create a JSON Server and handle itselfs
    *
    * @return void
    */
    public function handle()
    {
        $server = new ELSTR_JsonServer();
        $server->setClass($this);
        $server->handle();
    }

    /**
    * Service method to handle auth request
    * If user can be authenticated, save user into session
    *
    * @param string $username
    * @param string $password
    * @return Array Response messages
    */
    public function auth($username, $password)
    {
        $response = array();
        $result = $this->_auth($username, $password);

        if (!$result->isValid()) {
            // Authentication failed; print the reasons why
            foreach ($result->getMessages() as $message) {
                $response['message'][] = $message;
            }
        }

        switch ($result->getCode()) {
            case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
                /**
                * * do stuff for nonexistent identity *
                */
                $response['action'] = "failure_identity_not_found";
                break;

            case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
                /**
                * * do stuff for invalid credential *
                */
                $response['action'] = "failure_credential_invalid";
                break;

            case Zend_Auth_Result::SUCCESS:
                /**
                * * do stuff for successful authentication *
                */
                $username = $this->m_application->getBootstrap()->getResource('auth')->getIdentity();

            	// Load the roles from LDAP or any given adapter
                $this->_loadRoles($username, $password);

                // Check if the current user has at least one role
                // If not - add it to the role_anonymous
                $this->m_application->getBootstrap()->getResource('acl')->currentUserHasRole($username);
                // Create Response
                $response['action'] = "success";
                $response['isAuth'] = $this->m_application->getBootstrap()->getResource('auth')->hasIdentity();
                $response['username'] = $username;
                $response['isAdmin'] = $this->m_application->getBootstrap()->getResource('acl')->inheritsRole($username, 'role_admin', false);
                $response['resourcesAllowed'] = $this->m_application->getBootstrap()->getResource('acl')->getResourcesAllowed($this->m_application->getBootstrap()->getResource('db'), $username);


                break;

            default:
                /**
                * * do stuff for other failure *
                */
                $response['action'] = "failure";
                break;
        }

        return $response;
    }

    /**
    * Service method to handle logout request
    *
    * @return Array Response messages
    */
    public function logout()
    {
        $response = array();

        $this->m_application->getBootstrap()->getResource('auth')->clearIdentity();

        $response['action'] = "success";
        $response['username'] = "anonymous";
        return $response;
    }

    /**
    * Service method to handle user creation
    * Creates a new user
    *
    * @return
    */
    public function create($username, $password)
    {
        return NULL_EMPTY_STRING;
    }

    /**
    * Auth implementation
    *
    * @return Boolean true and only true if user could be authenticated
    * @param  $username String username
    * @param  $password String password
    */
    private function _auth($username, $password)
    {
        $configAuth = $this->m_application->getOption("auth");
        $options = $configAuth[$configAuth['method']];
        $adapter = new $configAuth['method']($options, $username, $password);
        $result = $this->m_application->getBootstrap()->getResource('auth')->authenticate($adapter);
        return $result;
    }

    private function _loadRoles($username, $password)
    {
        $acl = $this->m_application->getBootstrap()->getResource('acl');
        $configAcl = $this->m_application->getOption("acl");

    	// Remove any roles in the session for the user
    	$acl->getSession()->$username->roles = array();

        $ldap = new Zend_Ldap($configAcl['getRoles']['Zend_Ldap']);
        $ldap->bind($username, $password);
        // $acctname = $ldap->getCanonicalAccountName('vm-user',Zend_Ldap::ACCTNAME_FORM_DN);
        // echo "$acctname\n";
        $dn = $ldap->getCanonicalAccountName($username, Zend_Ldap::ACCTNAME_FORM_DN);

        $adapterOptions = array(
            'group' => "", // the group the user must be member of; if NULL group-membership-check is disabled
            'groupDn' => $ldap->getBaseDn(), // the parent DN under which the groups are located; defaults to the baseDn of the underlying Zend_Ldap
            'groupScope' => Zend_Ldap::SEARCH_SCOPE_SUB, // the search scope when searching for groups
            'groupAttr' => 'cn', // the attribute name for the RDN
            'groupFilter' => '', // an additional group filter that's added to the search filter
            'memberAttr' => 'member', // the group attribute in which to look for the user
            'memberIsDn' => true // if TRUE then the account DN is used to check membership, otherwise the canonical account name is used
            );

        $definedRoles = $acl->getDefinedRoles();

        for ($i = 0; $i < count($definedRoles); $i++) {
            $adapterOptions['group'] = $definedRoles[$i];
            $groupResult = $this->_checkGroupMembership($ldap, $username, $dn, $adapterOptions);

            if ($groupResult === true) {
            	// Add Role to the session
                $acl->getSession()->$username->roles[] = $definedRoles[$i];
            }
        }


    }

    /**
    * Checks the group membership of the bound user
    *
    * @param Zend_Ldap $ldap
    * @param string $canonicalName
    * @param string $dn
    * @param array $adapterOptions
    * @return string |true
    */
    private function _checkGroupMembership($ldap, $canonicalName, $dn, array $adapterOptions)
    {
        if ($adapterOptions['group'] === null) {
            return true;
        }

        if ($adapterOptions['memberIsDn'] === false) {
            $user = $canonicalName;
        } else {
            $user = $dn;
        }

        /**
        *
        * @see Zend_Ldap_Filter
        */
        require_once 'Zend/Ldap/Filter.php';
        $groupName = Zend_Ldap_Filter::equals($adapterOptions['groupAttr'], $adapterOptions['group']);
        $membership = Zend_Ldap_Filter::equals($adapterOptions['memberAttr'], $user);
        $group = Zend_Ldap_Filter::andFilter($groupName, $membership);
        $groupFilter = $adapterOptions['groupFilter'];
        if (!empty($groupFilter)) {
            $group = $group->addAnd($groupFilter);
        }

        $result = $ldap->count($group, $adapterOptions['groupDn'], $adapterOptions['groupScope']);

        if ($result === 1) {
            return true;
        } else {
            return 'Failed to verify group membership with ' . $group->toString();
        }
    }
}

?>