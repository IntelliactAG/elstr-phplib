<?php
/**
 * Class for centralized Error handling
 *
 * @author Felix Nyffenegger, Martin Bichsel
 */
class ELSTR_Exception extends Exception{
	private $m_context;
	private $m_code;
	private $m_response;
	private $m_header;
	private $m_details;

	// Redefine the exception so message isn't optional
	public function __construct($message, $code = 0, Exception $previous = null, $context = null, $details = null) {
		// some code
		$this->m_context = $context;
		$this->m_code    = $code;
		$this->m_details = $details;

		// make sure everything is assigned properly
		// parent::__construct($message, $code, $previous);
		parent::__construct($message, $code);

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
		$response['error'] = array();
		$response['error']['code'] = $this->m_code;
		$response['error']['data'] = array();
		$response['error']['data']['context'] = get_class($this->m_context);
		$response['error']['data']['details'] = $this->m_details;


		switch ($this->m_code) {
			case 1000:
				$response['error']['message'] = 'User and ACL must be provided to call this application';
				break;
			case 1001:
				$response['error']['message'] = 'Access to application denied: ';
				$this->m_header = 'HTTP/1.0 403 Forbidden';
				break;
			case 1002:
				$response['error']['message'] = 'Access to service denied: ';
				$this->m_header = 'HTTP/1.0 403 Forbidden';
				break;
			case 1003:
				$response['error']['message'] = 'Access to method denied: ';
				$this->m_header = 'HTTP/1.0 403 Forbidden';
				break;
			case 1004:
				$response['error']['message'] = 'Service is not registered: ';
				$this->m_header = 'HTTP/1.0 400 Bad Request';
				break;
			case 1005:
				$response['error']['message'] = 'Trying to access unauthorized application';
				$this->m_header = 'HTTP/1.0 401 Unauthorized';
				break;
			case 1006:
				$response['error']['message'] = 'Access to widget denied: ';
				$this->m_header = 'HTTP/1.0 403 Forbidden';
				break;
			case 1007:
				$response['error']['message'] = 'Access to widget method denied:  ';
				$this->m_header = 'HTTP/1.0 403 Forbidden';
				break;
			case 1008:
				$response['error']['message'] = 'ACL Resource not defined:  ';
				$this->m_header = 'HTTP/1.0 400 Bad Request';
				break;
			case 1009:
				$response['error']['message'] = 'It is not allowed to modify or delete core values:  ';
				break;
			case 1010:
				$response['error']['message'] = 'Default translation file does not exist:  ';
				break;
			case 1011:
				$status = $this->m_details['status'];
				$response['error']['message'] = 'Request failed with status '.$status;
				if ($status == 401) {
					$this->m_header = 'HTTP/1.0 401 Unauthorized';
				}
				else if ($status == 403) {
					$this->m_header = 'HTTP/1.0 403 Forbidden';
				}
				else {
					$this->m_header = 'HTTP/1.0 '.$status;
				}
				break;
			default:
				$response['error']['message'] = $this->message;
				break;
		}

		return $response;

	}
}

?>