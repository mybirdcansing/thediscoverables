<?php
require_once __dir__ . '/consts.php';

class AuthCookie 
{ 
    public static function setCookie($username) {
        $loginCookie = $username . ',' . md5($username . SECRET_WORD);
    	setcookie(LOGIN_COOKIE_NAME, $loginCookie, time() + (3600 * 24 * 30), '/');
        return $loginCookie;
    }

    public static function logout() {
        if (!isset($_COOKIE[LOGIN_COOKIE_NAME])) return 0;
		unset($_COOKIE[LOGIN_COOKIE_NAME]);
		setcookie(LOGIN_COOKIE_NAME, '', time() - 3600, '/');
    }

    public static function getUsername() {
        if (!isset($_COOKIE[LOGIN_COOKIE_NAME])) return 0;
        $cookie = $_COOKIE[LOGIN_COOKIE_NAME];
        return substr($cookie, 0, strrpos($cookie, ',')); 
    }

    public static function isValid() {
        if (!isset($_COOKIE[LOGIN_COOKIE_NAME])) return 0;
        $cookie = $_COOKIE[LOGIN_COOKIE_NAME];
        list($c_username, $cookie_hash) = explode(',', $cookie); 
        return (md5($c_username . SECRET_WORD) == $cookie_hash);
    }
}