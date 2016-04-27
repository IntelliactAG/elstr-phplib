<?php

require_once ('ELSTR_JsonServer.php');
require_once ('ELSTR_Server_Abstract.php');

/**
 * This class implements the user authentication and registration at ELSTR
 * Once the user is authenticated, his appplications will be added to the session accordingly
 *
 * @author Felix Nyffenegger, Marco Egli
 * @version 1.0
 * @created 19-Okt-2009 17:41:15
 */
class ELSTR_AuthServer extends ELSTR_Server_Abstract {

    /**
     * Create a JSON Server and handle itselfs
     *
     * @return void
     */
    public function handle() {
        $server = new ELSTR_JsonServer();
        $server->setClass($this);
        $server->handle();
    }


    private function _addResponseParamsFromResults(&$response, $result, $enterpriseApplication, $password, $configSso = null) {

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
                 * do stuff for successful authentication *
                 */
                $username = $result->getIdentity();

                $configSession = $this->m_application->getOption("session");
                if (is_array($configSession) && isset($configSession['remember_me_seconds'])) {
                    Zend_Session::rememberMe();
                }

                if ($enterpriseApplication == null) {

                    $username = $this->m_application->getBootstrap()->getResource('auth')->getIdentity();

                    // Load the roles from LDAP or any given adapter
                    $this->_loadRolesToSession($username, $password, $configSso);

                    // Check if the current user has at least one role
                    // If not - add it to the role_anonymous
                    $this->m_application->getBootstrap()->getResource('acl')->currentUserHasRole($username);
                    // Create Response
                    $response['action'] = "success";
                    $response['isAuth'] = $this->m_application->getBootstrap()->getResource('auth')->hasIdentity();
                    $response['username'] = $username;
                    $response['isAdmin'] = $this->m_application->getBootstrap()->getResource('acl')->inheritsRole($username, 'role_admin', false);
                    $response['resourcesAllowed'] = $this->m_application->getBootstrap()->getResource('acl')->getResourcesAllowed($this->m_application->getBootstrap()->getResource('db'), $username);
                } else {
                    $response['action'] = "success";
                }

                // Check if the current user has at least one role
                // If not - add it to the role_anonymous
                $this->m_application->getBootstrap()->getResource('acl')->currentUserHasRole($username);
                // Create Response
                $response['action'] = "success";
                $response['isAuth'] = $this->m_application->getBootstrap()->getResource('auth')->hasIdentity();
                $response['username'] = $username;
                $response['isAdmin'] = $this->m_application->getBootstrap()->getResource('acl')->inheritsRole($username, 'role_admin', false);
                $response['resourcesAllowed'] = $this->m_application->getBootstrap()->getResource('acl')->getResourcesAllowed($this->m_application->getBootstrap()->getResource('db'), $username);
                $response['enterpriseApplicationData'] = $this->m_application->getBootstrap()->getResource('user')->getEnterpriseApplicationData();

                $memberOf = array();
                $definedRoles = $this->m_application->getBootstrap()->getResource('acl')->getDefinedRoles();
                foreach($definedRoles as $role){
                    if($this->m_application->getBootstrap()->getResource('acl')->inheritsRole($username,$role,false)){
                        $memberOf[] = $role;
                    }
                }
                $response['memberOf'] = $memberOf;

                break;

            default:
                /**
                 * * do stuff for other failure *
                 */
                $response['action'] = "failure";
                break;
        }
    }

    /**
     * Service method to handle auth request
     * If user can be authenticated, save user into session
     *
     * @param string $username
     * @param string $password
     * @param string $enterpriseApplication
     * @return Array Response messages
     */
    public function auth($username, $password, $enterpriseApplication) {
        $response = array();

        if ($enterpriseApplication == null) {
            // Login to Elstr application
            $result = $this->_auth($username, $password);
        } else {
            require_once ("EnterpriseApplications/" . $enterpriseApplication . ".php");
            $enterpriseApp = new $enterpriseApplication($this->m_application);
            $result = $enterpriseApp->authenticate($username, $password);
        }


        if (!$result->isValid()) {
            // Authentication failed; print the reasons why
            foreach ($result->getMessages() as $message) {
                $response['message'][] = $message;
            }
        }

        $this->_addResponseParamsFromResults($response, $result, $enterpriseApplication, $password);

        return $response;
    }

    /**
     * Service method to handle sso request
     * If user can be authenticated, save user into session
     *
     * @return Array Response messages
     */
    public function sso() {
        $response = array();

        $configAuth = $this->m_application->getOption("auth");
        $configSso = $this->m_application->getOption("sso");

        $result = $this->_auth(null, null, array_merge($configAuth,$configSso));

        $enterpriseApplication ="";
        $password ="";

        $this->_addResponseParamsFromResults($response, $result, $enterpriseApplication, $password, $configSso);

        return $response;
    }

    /**
     * Service method to handle logout request
     *
     * @return Array Response messages
     */
    public function logout() {
        $response = array();

        // Get the Zend_Auth Obejct this session
        $this->m_application->getBootstrap()->getResource('auth')->clearIdentity();

        // Do logout on all ELSTR_EnterpriseApplication_*
        $sessionNamspaceArray = Zend_Session::getIterator();
        for ($i = 0; $i < count($sessionNamspaceArray); $i++) {
            if (strpos(strtolower($sessionNamspaceArray[$i]), "elstr_enterpriseapplication_") === 0) {
                $enterpriseApplication = $sessionNamspaceArray[$i];
                require_once ("EnterpriseApplications/" . $enterpriseApplication . ".php");
                $enterpriseApp = new $enterpriseApplication($this->m_application);
                $result = $enterpriseApp->logout();
            }
        }

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
    public function create($username, $password) {
        return NULL_EMPTY_STRING;
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
    public function checkGroupMembership($ldap, $canonicalName, $dn, array $adapterOptions) {
        return $this->_checkGroupMembership($ldap, $canonicalName, $dn, $adapterOptions);
    }

    /**
     * Auth implementation
     *
     * @return Boolean true and only true if user could be authenticated
     * @param  $username String username
     * @param  $password String password
     */
    private function _auth($username, $password, $configAuth = null) {

        // $configAuth must be NULL for all not SSO cases
        // in every normal auth case $configAuth must be NULL
        if($configAuth === null){
            $configAuth = $this->m_application->getOption("auth");
        }

        if (isset($configAuth['includeAdapter'])) {
            include_once($configAuth['includeAdapter']);
        }

        $options = array();
        if (isset($configAuth[$configAuth['method']])) {
            $options = $configAuth[$configAuth['method']];
        }
        $adapter = new $configAuth['method']($options, $username, $password);
        $result = $this->m_application->getBootstrap()->getResource('auth')->authenticate($adapter);
        return $result;
    }

    private function _loadRolesToSession($username, $password, $configSso = null) {
        $acl = $this->m_application->getBootstrap()->getResource('acl');
        $configAcl = $this->m_application->getOption("acl");

        // Remove any roles in the session for the user
        if($acl->getSession()->$username){
            $acl->getSession()->$username->roles = array();
        }
        

        if (isset($configAcl['getRoles']['method'])) {
            $getRolesMethod = $configAcl['getRoles']['method'];
            switch ($getRolesMethod) {
                case "Zend_Ldap":
                    $ldap = new Zend_Ldap();
                    $definedRoles = $acl->getDefinedRoles();
                    $multiOptions = $configAcl['getRoles'][$getRolesMethod];
                    foreach ($multiOptions as $server => $options) {
                        //echo "Versuch zu binden un die Serveroptionen für '$server' zu verwenden\n";
                        $ldap->setOptions($options);
                        try {
                            // Normally LDAP bind is done on login with user that loges in.
                            // SSO is an exception
                            if(isset($configSso['ELSTR_Sso_Adapter_Ntlm'])){
                                $ldap->bind($configSso['ELSTR_Sso_Adapter_Ntlm']['user'], $configSso['ELSTR_Sso_Adapter_Ntlm']['password']);
                            } else {
                                $ldap->bind($username, $password);
                            }

                            $dn = $ldap->getCanonicalAccountName($username, Zend_Ldap::ACCTNAME_FORM_DN);
                            //echo "Erfolgreich: $username authentifiziert\n";

                            // Old structure with direct configuration with no server nodes
                            // $ldap = new Zend_Ldap($configAcl['getRoles'][$getRolesMethod]);
                            // $ldap->bind($username, $password);
                            // $dn = $ldap->getCanonicalAccountName($username, Zend_Ldap::ACCTNAME_FORM_DN);
                            $adapterOptions = array(
                                'group' => "", // the group the user must be member of; if NULL group-membership-check is disabled
                                'groupDn' => $ldap->getBaseDn(), // the parent DN under which the groups are located; defaults to the baseDn of the underlying Zend_Ldap
                                'groupScope' => Zend_Ldap::SEARCH_SCOPE_SUB, // the search scope when searching for groups
                                'groupAttr' => 'cn', // the attribute name for the RDN
                                'groupFilter' => '', // an additional group filter that's added to the search filter
                                'memberAttr' => 'member', // the group attribute in which to look for the user
                                'memberIsDn' => true // if TRUE then the account DN is used to check membership, otherwise the canonical account name is used
                            );                            

                            for ($i = 0; $i < count($definedRoles); $i++) {
                                $adapterOptions['group'] = $definedRoles[$i];
                                $groupResult = $this->_checkGroupMembership($ldap, $username, $dn, $adapterOptions);

                                if ($groupResult === true) {
                                    // Add Role to the session
                                    $acl->getSession()->$username->roles[] = $definedRoles[$i];
                                }
                            }

                            break;
                        } catch (Zend_Ldap_Exception $zle) {
                            //echo '  ' . $zle->getMessage() . "\n";
                            if ($zle->getCode() === Zend_Ldap_Exception::LDAP_X_DOMAIN_MISMATCH) {
                                continue;
                            }
                        }
                    }

                    break;
                default:
                    ;
            } // switch
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
    private function _checkGroupMembership($ldap, $canonicalName, $dn, array $adapterOptions) {
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
