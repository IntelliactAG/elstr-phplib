<?php


/**
 * ELSTR specific wrapper around Zend_Json_Server.
 * Disable autoEmitResponse and rethrow errors.
 *
 * @author Marco Egli, Felix Nyffenegger
 * @version 1.0
 * @created 19-Okt-2009 17:14:59
 */
class ELSTR_JsonServer extends Zend_Json_Server
{
    /**
     * Override:
     * Handle request
     *
     * @param  Zend_Json_Server_Request $request
     * @return null|Zend_Json_Server_Response
     */
	
    /*
     * This override is depricated
     */
	
    /*
    public function handle($request = false)
    {
        $this->setAutoEmitResponse(false);
        $response = parent::handle();
        echo $response;
    }
    */
	
	 /**
     * Internal method for handling request
     * Overrid of Zend_Json_Server::_handle();
     * In order handle exception on the ELSTR side, we need to push the exception foreward
     * 
     * @return void
     */
    protected function _handle()
    {
        $request = $this->getRequest();

        if (!$request->isMethodError() && (null === $request->getMethod())) {
            return $this->fault('Invalid Request', -32600);
        }

        if ($request->isMethodError()) {
            return $this->fault('Invalid Request', -32600);
        }

        $method = $request->getMethod();
        if (!$this->_table->hasMethod($method)) {
            return $this->fault('Method not found', -32601);
        }

        $params        = $request->getParams();
        $invocable     = $this->_table->getMethod($method);
        $serviceMap    = $this->getServiceMap();
        $service       = $serviceMap->getService($method);
        $serviceParams = $service->getParams();

        if (count($params) < count($serviceParams)) {
            $params = $this->_getDefaultParams($params, $serviceParams);
        }

        try {
            $result = $this->_dispatch($invocable, $params);
        } catch (Exception $e) {
        	// HERE KICK THE ERROR BACK TO ELSTR
            throw $e;
        }

        $this->getResponse()->setResult($result);
    }

}
