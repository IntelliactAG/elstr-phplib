<?php


/**
 * @author Felix Nyffenegger
 * @version 1.0
 * @created 22-Okt-2009 10:36:48
 */
class ELSTR_Credentials
{

	protected $m_password;
	protected $m_username;

	function ELSTR_Credentials()
	{
	}
	
	public function setPassword($password) {
		$this->m_password = password;
	}
	
	public function getPassword() {
		return $this->m_password;
	}

	public function setUsername($username) {
		$this->m_username = username;
	}
	
	public function getUsername() {
		return $this->m_username;
	}
}
