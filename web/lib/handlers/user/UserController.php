<?php
require_once __dir__ . '/../ControllerBase.php';
require_once __dir__ . '/UserValidation.php';

class UserController extends ControllerBase {

    private $db;
    private $action;
    private $userId;
    private $userData;
    private $administrator;

    public function __construct($db)
    {
        parent :: __construct('user', false, $db);

        $this->action = $this->getActionName();
        $this->userId = $this->entityId;
        $this->userData = new UserData($db);
        $this->administrator = $this->getAdministrator();
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
        $validationIssues = UserValidation::validationIssues($user, true);

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
        $validationIssues = UserValidation::validationIssues($user, false);
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
        if (isset($objJson->data)) {
            $objJson = $objJson->data;
        }

        $token = $objJson->token;
        $password = $objJson->password;

        $validationIssues = UserValidation::validatePassword($password);
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
