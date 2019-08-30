<?php
require_once __dir__ . '/consts.php';

class AuthCookie 
{ 
    public static function setCookie($username) {
        $loginCookie = $username . ',' . md5($username . SECRET_WORD);
    	setcookie(LOGIN_COOKIE_NAME, $loginCookie, time() + (3600 * 24 * 30), '/');
    }

    public static function logout() {
		unset($_COOKIE[LOGIN_COOKIE_NAME]);
		setcookie(LOGIN_COOKIE_NAME, '', time() - 3600, '/');
    }

    public static function getUsername() {
        $cookie = $_COOKIE[LOGIN_COOKIE_NAME];
        return substr($cookie, 0, strrpos($cookie, ',')); 
    }

    public static function isValid() {
        $cookie = $_COOKIE[LOGIN_COOKIE_NAME];
        list($c_username, $cookie_hash) = explode(',', $cookie); 
        return (md5($c_username . SECRET_WORD) == $cookie_hash);
    }
}