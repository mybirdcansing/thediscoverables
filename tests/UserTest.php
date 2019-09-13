<?php
declare(strict_types=1);

require_once __dir__ . '/../web/lib/consts.php';
require_once __dir__ . '/TestBase.php';

use GuzzleHttp\Psr7\Request;

final class UserTest extends TestBase
{
    private $userHandlerPath = 'user/index.php';

    protected function setUp(): void
    {
        $this->authenticateUser('adam', 'abacadae');
    }

    public function testGET() 
    {
        $user = $this->_getUser('00000000-0000-0000-0000-000000000000');
        $this->assertEquals($user->username, 'adam');
        $this->assertEquals($user->firstName, 'Adam');
        $this->assertEquals($user->lastName, 'Cohen');
        $this->assertEquals($user->email, 'thediscoverables@gmail.com');
    }

    public function testCreate()
    {
        $client = $this->getHandlerClient();

        $username = 'test-user-' . GUID();
        $email = GUID() . '@test.com';

        $user = new User();
        $user->username = $username;
        $user->firstName = 'Ron';
        $user->lastName = 'Snow';
        $user->email = $email;
        $user->password = 'abacadae';
        $user->statusId = 1;

        $user = $this->_createUser($user, $client);

        $this->assertEquals($user->firstName, 'Ron');
        $this->assertEquals($user->lastName, 'Snow');
        $this->assertEquals($user->username, $username);
        $this->assertEquals($user->email, $email);

        $this->_deleteUser($user->id, $client);
    }

    public function testUpdate()
    {
        $client = $this->getHandlerClient();

        $username = 'test-user-' . GUID();
        $email = GUID() . '@test.com';

        $user = new User();
        $user->username = $username;
        $user->firstName = 'Ron';
        $user->lastName = 'Snow';
        $user->email = $email;
        $user->password = 'abacadae';
        $user->status_id = 1;

        $user = $this->_createUser($user, $client);

        $this->assertEquals($user->firstName, "Ron");
        $this->assertEquals($user->lastName, 'Snow');
        $this->assertEquals($user->username, $username);
        $this->assertEquals($user->email, $email);

        $client = $this->getHandlerClient();
        $username = 'test-user-' . GUID();
        $email = GUID() . '@test.com';
        $response = $client->post($this->userHandlerPath, [
            'json' => [
                'id' => $user->id,
                'username' => $username,
                'firstName' => 'James',
                'lastName' => 'Nuckolls',
                'email' => $email,
                'password' => 'abacadae',
                'statusId' => 1
            ],
            'cookies' => $this->cookieJar
        ]);

        $this->assertEquals($response->getStatusCode(), 200);
        $json = json_decode($response->getBody()->getContents());
        echo "json: " . $response->getBody()->getContents();
        $this->assertTrue($json->userUpdated);

        // get the user and make sure it's all good
        $user = $this->_getUser($json->userId, $client);
        $this->assertEquals($user->firstName, "James");
        $this->assertEquals($user->lastName, 'Nuckolls');
        $this->assertEquals($user->username, $username);
        $this->assertEquals($user->email, $email);
        $this->assertEquals($user->statusId, 1);


        $this->_deleteUser($user->id, $client);
    }

    private function _getUser($userId, $client = null)
    {
        if (!$client) $client = $this->getHandlerClient();

        $response = $client->get('user', [
            'query' => 'id=' . $userId,
            'cookies' => $this->cookieJar
        ]);
        $this->assertEquals($response->getStatusCode(), 200);
        $user = User::fromJson($response->getBody()->getContents());
        return $user;
    }


    private function _createUser($user, $client = null)
    {
        if (!$client) $client = $this->getHandlerClient();

        $response = $client->post($this->userHandlerPath, [
            'json' => [
                'username' => $user->username,
                'firstName' => $user->firstName,
                'lastName' => $user->lastName,
                'email' => $user->email,
                'password' => $user->password,
                'statusId' => $user->statusId
            ],
            'cookies' => $this->cookieJar
        ]);

        $json = json_decode($response->getBody()->getContents());
        $this->assertTrue($json->userCreated);
        $this->assertEquals($response->getStatusCode(), 201);

        $user = $this->_getUser($json->userId, $client);
        return $user;
    }


    private function _deleteUser($userId, $client = null)
    {
        if (!$client) $client = $this->getHandlerClient();

        $response = $client->post($this->userHandlerPath, [
            'json' => [
                'id' => $userId,
                'delete' => true,
            ],
            'cookies' => $this->cookieJar
        ]);

        $this->assertEquals($response->getStatusCode(), 200);
    }
}