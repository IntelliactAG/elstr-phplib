<?php

require_once ('ELSTR_WidgetServer_Abstract.php');
require_once ('ELSTR_JsonServer.php');

/**
 * This is an abstract WidgetServer implementation which returns JSON Data
 *
 * @author Felix Nyffenegger
 * @version 1.0
 * @created 19-Nov-2009 17:41:37
 */
abstract class ELSTR_WidgetServer_JSON_Abstract extends ELSTR_WidgetServer_Abstract {

    protected $m_server;

    function __construct($application, $params = null) {
        parent::__construct($application, $params);

		if ($this->m_params != null && is_string($this->m_params)) {
            $post = json_decode($this->m_params);
            $this->m_params = $post->params;
        }


        $this->m_server = new ELSTR_JsonServer();
        $this->m_server->setClass($this);
    }

    /**
     * Implementation of the abstract _getMethod
     */
    protected function _getMethod() {
        $request = $this->m_server->getRequest();
        $method = $request->getMethod();
        return $method;
    }

    /**
     * Create a JSON Server and handle itselfs
     *
     * @return void
     */
    protected function _handle() {
        $this->m_server->handle();
    }

    /**
     * Get response object
     */
    protected function _getResponse() {
        return $this->m_server->getResponse();
    }

}

?>