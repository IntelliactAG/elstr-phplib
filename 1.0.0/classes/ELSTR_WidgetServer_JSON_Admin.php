<?php
require_once ('ELSTR_WidgetServer_JSON_Abstract.php');

/**
* This is the WidgetServer for administrating the elstr application
* All public actions in this class will be available for post requests
*
* @author Marco Egli
*/
class ELSTR_WidgetServer_JSON_Admin extends ELSTR_WidgetServer_JSON_Abstract {
    /**
    * Get attribute list to a class from plm
    *
    * @param string $classCid
    * @param array $filter
    * @return array
    */
    public function getClassDataTable($classCid, $filter)
    {
        $parameters = array($classCid, count($filter));
        for ($i = 0; $i < count($filter); $i++) {
            $parameters[] = $filter[$i]["key"];
            $parameters[] = $this->getApplication('ELSTR_Application_OracleE6')->createQueryString(utf8_decode(urldecode($filter[$i]["value"])));
        }

        Zend_Session::writeClose(true);
        try {
            $xmlData = $this->getApplication('ELSTR_Application_OracleE6')->call('ELSTR_Service_OracleE6', 'invokeConnectorProcedure', "plmGetClassDataTable", $parameters);
        }
        catch (Exception $e) {
            return ELSTR_ErrorResponse::create($e->getMessage());
        }

        $jsonContents = Zend_Json::fromXml($xmlData, true);
        return Zend_Json::decode($jsonContents);
    }

    /**
    * This method must be implemented to initialize the applications
    *
    * @return
    */
    protected function _initApplications($acl, $user)
    {
        $app = new ELSTR_Application_OracleE6();
        $app->setAclControler($acl, $user);
        $this->registerApplication($app);
    }
}

?>