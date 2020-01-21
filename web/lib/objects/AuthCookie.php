<?php
require_once __dir__ . '/../messages.php';
require_once __dir__ . '/Configuration.php';

class AuthCookie 
{
    public static function setCookie($username) {
        $settings = ((new Configuration())->getSettings())->security;
        $loginCookie = $username . ',' . md5($username . $settings->SECRET_WORD);

        // $params = [
        //     'expires' => time() + (3600 * 24 * 30), 
        //     'path' => rawurlencode('/'),
        //     'samesite' => rawurlencode('None')
        // ];
        // 'domain' =>  rawurlencode($domain)),
        // setcookie($settings->LOGIN_COOKIE_NAME, $loginCookie, $params);
    	setcookie($settings->LOGIN_COOKIE_NAME, $loginCookie, time() + (3600 * 24 * 30), '/');
        return $loginCookie;
    }

    public static function logout() {
        $settings = ((new Configuration())->getSettings())->security;
        if (!isset($_COOKIE[$settings->LOGIN_COOKIE_NAME])) return 0;
		unset($_COOKIE[$settings->LOGIN_COOKIE_NAME]);
		setcookie($settings->LOGIN_COOKIE_NAME, '', time() - 3600, '/');
    }

    public static function getUsername() {
        $settings = ((new Configuration())->getSettings())->security;
        if (!isset($_COOKIE[$settings->LOGIN_COOKIE_NAME])) return 0;
        $cookie = $_COOKIE[$settings->LOGIN_COOKIE_NAME];
        return substr($cookie, 0, strrpos($cookie, ',')); 
    }

    public static function isValid() {
        $settings = ((new Configuration())->getSettings())->security;
        if (!isset($_COOKIE[$settings->LOGIN_COOKIE_NAME])) return 0;
        $cookie = $_COOKIE[$settings->LOGIN_COOKIE_NAME];
        list($c_username, $cookie_hash) = explode(',', $cookie); 
        return (md5($c_username . $settings->SECRET_WORD) == $cookie_hash);
    }
}