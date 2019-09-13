<?php

class UserController {

    private $db;
    private $requestMethod;
    private $userId;
    private $userData;

    public function __construct($db, $requestMethod, $userId)
    {
        $this->requestMethod = $requestMethod;
        $this->userId = $userId;
        $this->userData = new UserData($db);
    }

    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->userId) {
                    $response = $this->getUser($this->userId);
                } else {
                    $response = $this->getAllUsers();
                };
                break;
            case 'POST':
                $objJson = json_decode(file_get_contents('php://input'));
                if (isset($objJson->delete)) {
                    $response = $this->deleteUser($objJson->id);
                } else {
                    $user = User::fromJson(file_get_contents('php://input'));
                    if ($user->id) {
                        $response = $this->updateUser();
                    } else {
                        $response = $this->createUser();
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
                $response = $this->notFoundResponse();
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function getAllUsers()
    {
        $result = $this->userData->findAll();
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(array_map(function($val) { return $val->expose(); }, $result));
        return $response;
    }

    private function getUser($id)
    {
        $user = $this->userData->find($id);
        if (!$user) {
            return $this->notFoundResponse();
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($user->expose());
        return $response;
    }

    private function createUser()
    {
        $user = User::fromJson(file_get_contents('php://input'));
        if (!$this->validateUser($user)) {
            return $this->unprocessableEntityResponse();
        }
        
        $userId = $this->userData->insert($user);
        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode([
            "userCreated" => true, 
            "userId" => $userId
        ]);
        return $response;
    }

    private function updateUser()
    {
        $user = User::fromJson(file_get_contents('php://input'));
        if (!$this->validateUser($user)) {
            return $this->unprocessableEntityResponse();
        }
        $existingUser = $this->userData->find($user->id);
        if (!$existingUser) {
            return $this->notFoundResponse();
        }
        $this->userData->update($user);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode([
            "userUpdated" => true, 
            "userId" => $user->id
        ]);
        return $response;
    }

    private function deleteUser($id)
    {
        $result = $this->userData->find($id);

        if (!$result) {
            return $this->notFoundResponse();
        }
        $deleted = $this->userData->delete($id);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;

        error_log('deleting user: ' . $id);
        return $response;
    }

    private function validateUser($input)
    {



        return true;
    }

    private function unprocessableEntityResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $response['body'] = json_encode([
            'error' => 'Invalid input'
        ]);
        return $response;
    }

    private function notFoundResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = null;
        return $response;
    }
}