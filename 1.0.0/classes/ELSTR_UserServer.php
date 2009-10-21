<?php

/**
 * This class implements the user authentication and registration at ELSTR
 * Once the user is authenticated, his appplications will be added to the session accordingly
 * 
 * @author nyffenegger
 * @version 1.0
 * @created 19-Okt-2009 17:41:15
 */
class ELSTR_UserServer
{
	/**
	 * Post method to handle auth request
	 * @return 
	 */
	public function post($username, $password) {
		$result = array();	
		$result['action'] = 'success';
		$result['result'] = 'hanswurst';
		return $result;
	}
}
?>