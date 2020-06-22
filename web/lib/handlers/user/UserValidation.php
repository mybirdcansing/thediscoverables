<?php
require_once __DIR__ . '/../../messages.php';

class UserValidation {

    public static function validationIssues($user, $checkPassword)
    {
        $errorMessages = [];
        
        if (!isset($user->email) || $user->email == '') {
            $errorMessages[EMAIL_BLANK_CODE] = EMAIL_BLANK_MESSAGE;
        }
        $user->email = trim($user->email);

        if (!filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            $errorMessages[EMAIL_INVALID_CODE] = EMAIL_INVALID_MESSAGE;
        }
        
       if ($checkPassword) {
            foreach (UserValidation::validatePassword($user->password) as $code => $message) {
                $errorMessages[$code] = $message;
            }
        }

        if (!isset($user->username) || $user->username == '') {
            $errorMessages[USERNAME_BLANK_CODE] = USERNAME_BLANK_MESSAGE;
        } else {
            if (strlen($user->username) > 64) {
                $errorMessages[USERNAME_LONG_CODE] = USERNAME_LONG_MESSAGE;
            } elseif (strlen($user->username) < 4) {
                $errorMessages[USERNAME_SHORT_CODE] = USERNAME_SHORT_MESSAGE;
            }
            if (UserValidation::isInputStrValid($user->username)) {
                $errorMessages[USERNAME_INVALID_CODE] = USERNAME_INVALID_MESSAGE;
            }
        }

        if (strlen($user->firstName) > 64) {
            $errorMessages[FIRSTNAME_LONG_CODE] = FIRSTNAME_LONG_MESSAGE;
        }
        if (UserValidation::isInputStrValid($user->firstName)) {
            $errorMessages[FIRSTNAME_INVALID_CODE] = FIRSTNAME_INVALID_MESSAGE;
        }

        if (strlen($user->lastName) > 64) {
            $errorMessages[LASTNAME_LONG_CODE] = LASTNAME_LONG_MESSAGE;
        }

        if (UserValidation::isInputStrValid($user->lastName)) {
            $errorMessages[LASTNAME_INVALID_CODE] = LASTNAME_INVALID_MESSAGE;
        }

        return $errorMessages;
    }

    public static function validatePassword($password)
    {
        $errorMessages = [];
        if (!isset($password) || $password == '') {
            $errorMessages[PASSWORD_BLANK_CODE] = PASSWORD_BLANK_MESSAGE;
        } else {
            if (strlen($password) > 64) {
                $errorMessages[PASSWORD_LONG_CODE] = PASSWORD_LONG_MESSAGE;
            } elseif (strlen($password) < 6) {
                $errorMessages[PASSWORD_SHORT_CODE] = PASSWORD_SHORT_MESSAGE;
            }
            if (UserValidation::isInputStrValid($password)) {
                $errorMessages[PASSWORD_INVALID_CODE] = PASSWORD_INVALID_MESSAGE;
            }
        }
        return $errorMessages;
    }

    public static function isInputStrValid($str) {
        // invalid chars are ' \ ` | ; @ " < > \
        return preg_match('/[\'\/`\|;@"\<\>\\\]/', $str);
    }
}