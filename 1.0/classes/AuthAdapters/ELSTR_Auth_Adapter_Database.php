<?php

/**
 * Elstr_Auth_Adapter_Beanstalk
 *
 * @version $Id$
 * @copyright 2012
 * @author egli@intelliact.ch
 *
 */

/** Example configuration
 *
 *
 *
 */

class ELSTR_Auth_Adapter_Database implements Zend_Auth_Adapter_Interface
{
	var $m_options;
	var $m_username;
	var $m_password;

	/**
	 * Sets username and password for authentication
	 *
	 * @return void
	 */
	public function __construct($options, $username, $password)
	{
		$this->m_options = $options;
		$this->m_username = $username;
		$this->m_password = $password;
	}

	/**
	 * Performs an authentication attempt
	 *
	 * @throws Zend_Auth_Adapter_Exception If authentication cannot be performed                              
	 * @return Zend_Auth_Result
	 */
	public function authenticate()
	{
		// ...
		$messages = array();
		$messages[0] = ''; // reserved
		$messages[1] = ''; // reserved

		$options = $this->m_options;
		$username = $this->m_username;
		$password = $this->m_password;		
		$result = array();

		if (!$username) {
			$code = Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
			$messages[0] = 'A username is required';
			return new Zend_Auth_Result($code, '', $messages);
		}
		if (!$password) {
			// A password is required
			$code = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
			$messages[0] = 'A password is required';
			return new Zend_Auth_Result($code, '', $messages);
		}
		
		if (isset($options['adapter'])) {
			// An db adapter is required
			$adapter = $options['adapter'];

			if (isset($options[$adapter])) {
				$params = $options[$adapter];

				try {
			        $dbAdapter = Zend_Db::factory($adapter, $params);
			        $dbAdapter->getConnection();

			        $db = new ELSTR_Db($dbAdapter);

					if (isset($options['character'])) {
				        // Alle Operatione sollen entsprechend codiert werden
				        $db->query('set character set '.$options['character'].';');
					}

				} 
				catch (Exception $e){
					throw new ELSTR_Exception('No database connection: '.$e->getMessage(),0,null,$this);
				}

			} else {
				throw new ELSTR_Exception('No database adapter configuration settings',0,null,$this);
			}				
			
		} else {
			throw new ELSTR_Exception('No database adapter in configuration',0,null,$this);
		}
 


		if (isset($options['table']) && isset($options['col_username']) && isset($options['col_password'])){

			$hashedPassword = $password;
			if(isset($options['hash_method'])){
				if($options['hash_method'] == 'crypt'){
					$salt = $options['crypt']['salt'];
					$hashedPassword = crypt($password, $salt);
				} elseif($options['hash_method'] == 'md5'){					
					$hashedPassword = md5($password);				
				}
			} 
			

	 		$select = $db->select();
	        $select->from($options['table']);
			$select->where($options['col_username'].' = ?', $username);
			$select->where($options['col_password'].' = ?', $hashedPassword);
	 
	        //echo $select->__toString();
	        $stmt = $db->query($select);
	        $result = $stmt->fetchAll();
		} else {
			throw new ELSTR_Exception('No configuration settings for table or col_username or col_password',0,null,$this);
		}


        //print_r($result);

        if(count($result) == 1) {
			$sessionAuthDatabase = new Zend_Session_Namespace('Auth_Database');
			$sessionAuthDatabase->username = $username;
			$sessionAuthDatabase->password = $password;

			//successful login
			$messages[0] = '';
			$messages[1] = '';
			$messages[] = "$username authentication successful";
			return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $username, $messages);
        } else {
			$code = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
			$messages[0] = 'Invalid credentials';
			return new Zend_Auth_Result($code, '', $messages);        	
        }

	}
}

?>