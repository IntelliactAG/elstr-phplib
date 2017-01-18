<?php

require_once('ELSTR_JsonServer.php');
require_once('ELSTR_Server_Abstract.php');

/**
 * Application Data Server
 *
 * @version $Id$
 * @copyright 2009
 */
class ELSTR_ApplicationDataServer extends ELSTR_Server_Abstract
{

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
     * Get the client IP address
     *
     * @return string
     */
    private function getClientIp()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if (isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    /**
     * Get the preview for an item or document or project
     *
     * @param string $appName Name of the application
     * @return array
     */
    public function load($appName)
    {
        //Zend_Session::writeClose(true);
        //No write close of session because of elstr 2.0
        //load service is never called parallel
        $configPublic = $this->m_application->getOption("public");

        if (isset($configPublic[$appName]) && isset($configPublic['shared'])) {
            $appConfigPublic = array_merge($configPublic['shared'], $configPublic[$appName]);
        } elseif (isset($configPublic['shared'])) {
            $appConfigPublic = $configPublic['shared'];
        } elseif (isset($configPublic[$appName])) {
            $appConfigPublic = $configPublic[$appName];
        } else {
            $appConfigPublic = array();
        }

        $result['config'] = $appConfigPublic;

        if ($this->m_application->getBootstrap()->getResource('auth')->hasIdentity()) {
            $result['user']['username'] = $this->m_application->getBootstrap()->getResource('auth')->getIdentity();
            // used username from identity becuase of sso. user cannot be recreated after sso authentication
            //$result['user']['username'] = $this->m_application->getBootstrap()->getResource('user')->getUsername();
        } else {
            $result['user']['username'] = "anonymous";
        }
        $result['user']['isAuth'] = $this->m_application->getBootstrap()->getResource('auth')->hasIdentity();
        $result['user']['isAdmin'] = $this->m_application->getBootstrap()->getResource('acl')->inheritsRole($result['user']['username'], 'role_admin', false);
        $result['user']['resourcesAllowed'] = $this->m_application->getBootstrap()->getResource('acl')->getResourcesAllowed($this->m_application->getBootstrap()->getResource('db'), $result['user']['username']);
        $result['user']['enterpriseApplicationData'] = $this->m_application->getBootstrap()->getResource('user')->getEnterpriseApplicationData();
        $result['user']['clientIp'] = $this->getClientIp();
        $memberOf = array();
        $definedRoles = $this->m_application->getBootstrap()->getResource('acl')->getDefinedRoles();
        foreach ($definedRoles as $role) {
            if ($this->m_application->getBootstrap()->getResource('acl')->inheritsRole($result['user']['username'], $role, false)) {
                $memberOf[] = $role;
            }
        }
        $result['user']['memberOf'] = $memberOf;

        $result['language']['current'] = $this->m_application->getBootstrap()->getResource("language")->getTranslation()->getLocale();
        $result['language']['default'] = $this->m_application->getBootstrap()->getResource("language")->defaultLanguage();
        $result['language']['modules'] = $this->m_application->getBootstrap()->getResource("language")->getRegisteredModules();
        $result['language']['dataOptions'] = $this->m_application->getBootstrap()->getResource("language")->dataOptions();
        //$result['language']['translations'] = $this->m_application->getBootstrap()->getResource("language")->getTranslation()->getMessages();
        $result['language']['translations'] = $this->m_application->getBootstrap()->getResource("language")->getTranslationMessages();

        return $result;
    }
}
