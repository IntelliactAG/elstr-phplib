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
    * @param  $message Object
    */
    static function create($message)
    {
        $response = array();
        $response['action'] = 'failed';
        $response['code'] = $message;
        switch ($message) {
            case 1000:
                $response['message'] = 'User and ACL must be provided to call this application';
                break;
            case 1001:
                $response['message'] = 'Access to application denyed: ';
                break;
            case 1002:
                $response['message'] = 'Access to service denyed: ';
                break;
            case 1003:
                $response['message'] = 'Access to method denyed: ';
                break;
            case 1004:
                $response['message'] = 'Servcie is not registered: ';
                break;
            case 1005:
                $response['message'] = 'No password provided: ';
                break;
            case 1006:
                $response['message'] = 'Access to widget denyed: ';
                break;
            case 1007:
                $response['message'] = 'Access to widget method denyed:  ';
                break;
            case 1008:
                $response['message'] = 'ACL Ressource not definied:  ';
                break;
            case 1009:
                $response['message'] = 'It is not allowed to modify or delete core values:  ';
                break;
            case 1010:
                $response['message'] = 'Default translation file does not exist:  ';
                break;
            default:
                $response['message'] = $message;
                break;
        }
        return $response;
    }
}

?>