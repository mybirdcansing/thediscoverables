<?php
require_once __dir__ . '/consts.php';

class AuthorizeCookie 
{ 
	private $isValid;
	private $username;

    function __construct($loginCookie) 
    { 
		if($loginCookie) {
			list($c_username, $cookie_hash) = explode(',', $loginCookie); 
			if (md5($c_username . SECRET_WORD) == $cookie_hash) { 
				$this->isValid = true;
				$this->username = $c_username;
			} else {
				$this->isValid = false;
			}
		}
    }
    
    public function getUsername() {
    	return $this->isValid ? $this->username : '';
    }

    public function isValid() {
		return $this->isValid;
    }

}