<?php


/**
 * Class to create ELSTR specific JSON data
 * 
 * @author nyffenegger
 * @version 1.0
 * @created 19-Okt-2009 17:14:59
 */
class ELSTR_JsonServer extends Zend_Json_Server
{

	function _construct()
	{
	}

    /**
     * Override:
     * Handle request
     * 
     * @param  Zend_Json_Server_Request $request 
     * @return null|Zend_Json_Server_Response
     */
    public function handle($request = false)
    {
        $this->setAutoEmitResponse(false);
        $response = parent::handle();
        echo $response;
    }

}
?>