<?php
require_once __DIR__ . '/../../messages.php';

class UserController {

    private $db;
    private $action;
    private $userId;
    private $userData;
    private $administrator;

    public function __construct($db, $action, $userId, $administrator)
    {
        $this->action = $action;
        $this->userId = $userId;
        $this->userData = new UserData($db);
        $this->administrator = $administrator;
    }

    public function processRequest()
    {
        switch ($this->action) {
            case GET_ACTION:
                if ($this->userId) {
                    $response = $this->_getUser();
                } else {
                    $response = $this->_getAllUsers();
                };
                break;
            case DELETE_ACTION:
                $response = $this->_deleteUser();
                break;
            case UPDATE_PASSWORD_ACTION:
                $response = $this->_updatePassword();
                break;
            case UPDATE_ACTION:
                $response = $this->_updateUser();
                break;
            case CREATE_ACTION:
                $response = $this->_createUser();
                break;
            default:
                $response = $this->_notFoundResponse();
                break;
        }

        if (array_key_exists('problem_header', $response)) {
            header('Content-Type: application/problem+json; charset=UTF-8');
        }

        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function _getAllUsers()
    {
        $result = $this->userData->findAll();
        return $this->_okResponse(array_map(function($val) {
            return $val->expose();
        }, $result));
    }

    private function _getUser()
    {
        $user = $this->userData->find($this->userId);
        if (!$user) {
            return $this->_notFoundResponse();
        }
        return $this->_okResponse($user->expose());
    }

    private function _createUser()
    {
        $json = json_decode(file_get_contents('php://input'));
        if (isset($json->data)) {
            $user = User::fromJson(json_encode($json->data));
        } else {
            $user = User::fromJson(file_get_contents('php://input'));
        }
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
            return $this->_conflictResponse([
                "userCreated" => false,
                "errorMessages" => [$e->getCode() => $e->getMessage()]
            ]);
        }
        return $response;
    }

    private function _updateUser()
    {
        $json = json_decode(file_get_contents('php://input'));
        if (isset($json->data)) {
            $user = User::fromJson(json_encode($json->data));
        } else {
            $user = User::fromJson(file_get_contents('php://input'));
        }
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
            $this->userData->update($user, $this->administrator);

            return $this->_okResponse([
                "userUpdated" => true,
                "userId" => $user->id
            ]);
        } catch (DuplicateUsernameException | DuplicateEmailException $e) {
            return $this->_conflictResponse([
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

        return $this->_okResponse([
            "userPasswordUpdated" => true,
            "userId" => $tokenData->userId
        ]);

        return $response;
    }

    private function _deleteUser()
    {
        $result = $this->userData->find($this->userId);
        if (!$result) {
            return $this->_notFoundResponse();
        }
        $deleted = $this->userData->delete($this->userId);

        return $this->_okResponse();
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

        if ($checkPassword) {
            foreach ($this->_validatePassword($user->password) as $code => $message) {
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

    private function _okResponse($json = null)
    {
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = $json ? json_encode($json) : "{}";
        return $response;
    }

    private function _conflictResponse($json = null)
    {
        $response['problem_header'] = true;
        $response['status_code_header'] = 'HTTP/1.1 409 Conflict';
        $response['body'] = $json ? json_encode($json) : null;
        return $response;
    }

    private function _unprocessableEntityResponse($json = null)
    {
        $response['problem_header'] = true;
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $response['body'] = $json ? json_encode($json) : null;
        return $response;
    }

    private function _notFoundResponse($json = null)
    {
        $response['problem_header'] = true;
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
        // invalid chars are ' \ ` | ; @ " < > \
        return preg_match('/[\'\/`\|;@"\<\>\\\]/', $str);
    }
}
