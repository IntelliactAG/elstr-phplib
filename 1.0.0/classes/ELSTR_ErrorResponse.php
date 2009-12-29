<?php
	/**
	 * Class for centralized Error handling
	 *
	 * @author Felix Nyffenegger
	 */
	class ELSTR_ErrorResponse {

		/**
		 * This function globally creats error messages based on a defined code
		 *
		 * Todo: Load messages from language file
		 *
		 * @return Array error object to return to the service consumer
		 * @param $message Object
		 */
		static function create($message) {
			$response = array();
			$response['action']	 = 'failed';
			switch ($message) {
				case 1000:
					$response['message'] = 'User and ACL must be provided to call this application';
					$response['code'] = '1000';
					break;
				case 1001:
					$response['message'] = 'Access to application denyed: ';
					$response['code'] = '1001';
					break;
				case 1002:
					$response['message'] = 'Access to service denyed: ';
					$response['code'] = '1002';
					break;
				case 1003:
					$response['message'] = 'Access to method denyed: ';
					$response['code'] = '1003';
					break;
				case 1004:
					$response['message'] = 'Servcie is not registered: ';
					$response['code'] = '1004';
					break;
				case 1005:
					$response['message'] = 'No password provided: ';
					$response['code'] = '1005';
					break;
				case 1006:
					$response['message'] = 'Access to widget denyed: ';
					$response['code'] = '1007';
					break;
				case 1007:
					$response['message'] = 'Access to widget method denyed:  ';
					$response['code'] = '1007';
					break;
				case 1008:
					$response['message'] = 'ACL Ressource not definied:  ';
					$response['code'] = '1008';
					break;
				default:
					$response['message'] = $message;
					$response['code'] = '';
					break;
			}
			return $response;
		}
	}
?>