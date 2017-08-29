<?php

require_once('Data/ELSTR_Result_Object.php');

/**
 * Elstr Request Simplifier
 *
 * @version 0.2
 * @copyright 2017
 */
class ELSTR_Resource_Simplifier
{

    /* @var ELSTR_Db $this ->$db */
    public $db;
    /* @var ELSTR_User $this ->$user */
    public $user;
    /* @var ELSTR_LogServer $this ->$logger */
    public $logger;
    /* @var ELSTR_Language $this ->$language */
    public $language;
    /* @var ELSTR_Language $this ->$translations */
    public $translations;

    public function __construct($contextInstance, $sessionWriteClose = true)
    {

        Zend_Session::writeClose($sessionWriteClose);


        $this->context = $contextInstance;

        $m_application = $contextInstance->m_application;

        $this->db = $m_application->getBootstrap()->getResource('db');
        $this->user = $m_application->getBootstrap()->getResource('user');
        $this->logger = $m_application->getBootstrap()->getResource("logger");

        $this->language = $m_application->getBootstrap()->getResource("language");
        $this->translations = $this->language->getTranslation();

        $this->m_application = $m_application;

    }

    /**
     * requestResult
     * @param $data
     * @param array $messages
     * @return ELSTR_Result_Object
     */
    function requestResult($data, $messages = array())
    {
        return new ELSTR_Result_Object($data, $messages);
    }


    function translate($string)
    {
        return $this->translations->_($string);
    }


}
