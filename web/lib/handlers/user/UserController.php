<?php

class UserController {

    private $db;
    private $requestMethod;
    private $userId;
    private $userData;
    private $administrator;

    public function __construct($db, $requestMethod, $userId, $administrator)
    {
        $this->requestMethod = $requestMethod;
        $this->userId = $userId;
        $this->userData = new UserData($db);
        $this->administrator = $administrator;
    }

    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->userId) {
                    $response = $this->_getUser($this->userId);
                } else {
                    $response = $this->_getAllUsers();
                };
                break;
            case 'POST':
                $objJson = json_decode(file_get_contents('php://input'));
                if (isset($objJson->delete)) {
                    $response = $this->_deleteUser($objJson->id);
                } elseif (isset($objJson->updatePassword)) {

                    $response = $this->_updatePassword();
                } else {
                    $user = User::fromJson(file_get_contents('php://input'));
                    if ($user->id) {
                        $response = $this->_updateUser();
                    } else {
                        $response = $this->_createUser();
                    }
                }
                break;
            case 'PUT':
                // doesn't work on most budget hosting services, so we'll 
                // use the userId hack in POST
                break;
            case 'DELETE':
                // doesn't work on most budget hosting services, so we'll 
                // use a flag hack in POST
                break;
            default:
                $response = $this->_notFoundResponse();
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function _getAllUsers()
    {
        $result = $this->userData->findAll();
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(array_map(function($val) { return $val->expose(); }, $result));
        return $response;
    }

    private function _getUser($id)
    {
        $user = $this->userData->find($id);
        if (!$user) {
            return $this->_notFoundResponse();
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($user->expose());
        return $response;
    }

    private function _createUser()
    {
        $user = User::fromJson(file_get_contents('php://input'));
        $validationIssues = $this->_validationIssues($user, true);
        
        if ((bool)$validationIssues) {
            return $this->_unprocessableEntityResponse([
                "userCreated" => false,
                "errorMessages" => $validationIssues
            ]);
        }
        try {
            $userId = $this->userData->insert($user, $this->administrator);
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode([
                "userCreated" => true, 
                "userId" => $userId
            ]);
        } catch (DuplicateUsernameException | DuplicateEmailException $e) {
            $response['status_code_header'] = 'HTTP/1.1 409 Conflict';
            $response['body'] = json_encode([
                "userCreated" => false,
                "errorMessages" => [$e->getCode() => $e->getMessage()]
            ]);
        }
        return $response;
    }

    private function _updateUser()
    {
        $user = User::fromJson(file_get_contents('php://input'));
        $validationIssues = $this->_validationIssues($user, false);
        if ((bool)$validationIssues) {
            return $this->_unprocessableEntityResponse([
                "userUpdated" => false,
                "errorMessages" => $validationIssues
            ]);
        }
        
        $existingUser = $this->userData->find($user->id);
        if (!$existingUser) {
            return $this->_notFoundResponse();
        }

        try {
            // error_log("try to update user");
            $this->userData->update($user, $this->administrator);

            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode([
                "userUpdated" => true, 
                "userId" => $user->id
            ]);
        } catch (DuplicateUsernameException | DuplicateEmailException $e) {
            $response['status_code_header'] = 'HTTP/1.1 409 Conflict';
            $response['body'] = json_encode([
                "userUpdated" => false, 
                "userId" => $user->id,
                "errorMessages" => array($e->getCode() => $e->getMessage())
            ]);
        }

        return $response;
    }

    private function _updatePassword()
    {
        $objJson = json_decode(file_get_contents('php://input'));
        $token = $objJson->token;
        $password = $objJson->password;

        $validationIssues = $this->_validatePassword($password);
        if ((bool)$validationIssues) {
            return $this->_unprocessableEntityResponse([
                "userPasswordUpdated" => false,
                "errorMessages" => $validationIssues
            ]);
        }
        
        $tokenData = $this->userData->getPasswordResetTokenInfo($token);
        if (!$tokenData) {
            return $this->_notFoundResponse([
                "userPasswordUpdated" => false, 
                "errorMessages" => [
                    PASSWORD_TOKEN_NOT_FOUND_CODE => PASSWORD_TOKEN_NOT_FOUND_MESSAGE
                ]
            ]);
        }

        $user = $this->userData->find($tokenData->userId);
        if (!$user) {
            return $this->_notFoundResponse();
        }

        $this->userData->updatePassword($user->id, $password, $user);

        $this->userData->markPasswordTokenUsed($token);

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode([
            "userPasswordUpdated" => true, 
            "userId" => $tokenData->userId
        ]);

        return $response;
    }

    private function _deleteUser($id)
    {
        $result = $this->userData->find($id);

        if (!$result) {
            return $this->_notFoundResponse();
        }
        $deleted = $this->userData->delete($id);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;
        return $response;
    }

    private function _validatePassword($password)
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
            if ($this->_isInputStrValid($password)) {
                $errorMessages[PASSWORD_INVALID_CODE] = PASSWORD_INVALID_MESSAGE;
            }
        }
        return $errorMessages;
    }

    private function _validationIssues($user, $checkPassword)
    {
        $errorMessages = [];
        if (!isset($user->email) || $user->email == '') {
            $errorMessages[EMAIL_BLANK_CODE] = EMAIL_BLANK_MESSAGE;
        }
        $user->email = trim($user->email);

        if (!filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            $errorMessages[EMAIL_INVALID_CODE] = EMAIL_INVALID_MESSAGE;
        }

        // password
        if ($checkPassword) {
            foreach ($this->_validatePassword($user->password) as $code => $message) {
                $errorMessages[$code] = $message;
            }
        }

        // username
        if (!isset($user->username) || $user->username == '') {
            $errorMessages[USERNAME_BLANK_CODE] = USERNAME_BLANK_MESSAGE;
        } else {
            if (strlen($user->username) > 64) {
                $errorMessages[USERNAME_LONG_CODE] = USERNAME_LONG_MESSAGE;
            } elseif (strlen($user->username) < 4) {
                $errorMessages[USERNAME_SHORT_CODE] = USERNAME_SHORT_MESSAGE;
            }
            if ($this->_isInputStrValid($user->username)) {
                $errorMessages[USERNAME_INVALID_CODE] = USERNAME_INVALID_MESSAGE;
            }
        }
        
        if (strlen($user->firstName) > 64) {
            $errorMessages[FIRSTNAME_LONG_CODE] = FIRSTNAME_LONG_MESSAGE;
        }
        if ($this->_isInputStrValid($user->firstName)) {
            $errorMessages[FIRSTNAME_INVALID_CODE] = FIRSTNAME_INVALID_MESSAGE;
        }

        if (strlen($user->lastName) > 64) {
            $errorMessages[LASTNAME_LONG_CODE] = LASTNAME_LONG_MESSAGE;
        }
        if ($this->_isInputStrValid($user->lastName)) {
            $errorMessages[LASTNAME_INVALID_CODE] = LASTNAME_INVALID_MESSAGE;
        }

        return $errorMessages;
    }

    private function _unprocessableEntityResponse($json)
    {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $response['body'] = json_encode($json);
        return $response;
    }

    private function _notFoundResponse($json = null)
    {
        if ($json == null) {
            $json = [
                "errorMessages" => [
                    USER_NOT_FOUND_CODE => USER_NOT_FOUND_MESSAGE
                ]
            ];
        }
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = json_encode($json);
        return $response;
    }

    private function _isInputStrValid($str) {
        // invalid chars are ' \ ` | ; " < > \
        return preg_match('/[\'\/`\|;"\<\>\\\]/', $str);
    }
}