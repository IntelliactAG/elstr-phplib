<?php
/**
* Class for centralized Error handling
*
* @author Felix Nyffenegger
*/
class ELSTR_Exception extends Exception{    
	private $m_context;
	private $m_code;
	private $m_response;
	private $m_header;
	
	    // Redefine the exception so message isn't optional
    public function __construct($message, $code = 0, Exception $previous = null, $context = null) {
        // some code
    	$this->m_context = $context;
    	$this->m_code    = $code;
  
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
        
        $this->m_response = $this->createResponse();
    }
    
    public function getHeader(){
    	return $this->m_header;
    }
    
    public function getResponse(){
    	return $this->m_response;
    }

    /**
    * This function globally creats error messages based on a defined code
    *
    * Todo: Load messages from language file
    *
    
    * @return Array error object to return to the service consumer
    * @param  $message Object
    */
    private function createResponse()
    {
    	$response = array();
    	$response['message'] = array();
    	$response['message']['type'] = "error";
        $response['message']['code'] = $this->m_code;
        $response['context'] = get_class($this->m_context);
        
        switch ($this->m_code) {
            case 1000:
                $response['message']['text'] = 'User and ACL must be provided to call this application';
                break;
            case 1001:
                $response['message']['text'] = 'Access to application denyed: ';
                $this->m_header = 'HTTP/1.0 403 Forbidden';
                break;
            case 1002:
                $response['message']['text'] = 'Access to service denyed: ';
                $this->m_header = 'HTTP/1.0 403 Forbidden';
                break;
            case 1003:
                $response['message']['text'] = 'Access to method denyed: ';
                $this->m_header = 'HTTP/1.0 403 Forbidden';
                break;
            case 1004:
                $response['message']['text'] = 'Servcie is not registered: ';
                $this->m_header = 'HTTP/1.0 400 Bad Request';
                break;
            case 1005:
                $response['message']['text'] = 'No password provided: ';
                $this->m_header = 'HTTP/1.0 400 Bad Request';
                break;
            case 1006:
                $response['message']['text'] = 'Access to widget denyed: ';
                $this->m_header = 'HTTP/1.0 403 Forbidden';
                break;
            case 1007:
                $response['message']['text'] = 'Access to widget method denyed:  ';
                $this->m_header = 'HTTP/1.0 403 Forbidden';
                break;
            case 1008:
                $response['message']['text'] = 'ACL Ressource not definied:  ';
                $this->m_header = 'HTTP/1.0 400 Bad Request';
                break;
            case 1009:
                $response['message']['text'] = 'It is not allowed to modify or delete core values:  ';
                break;
            case 1010:
                $response['message']['text'] = 'Default translation file does not exist:  ';
                break;             
            default:
                $response['message']['text'] = $this->message;
                break;
        }
        
        
        
        return $response;
        
    }
}

?>