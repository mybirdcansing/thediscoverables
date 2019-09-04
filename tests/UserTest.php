<?php
declare(strict_types=1);

require_once __dir__ . '/../web/lib/consts.php';
require_once __dir__ . '/TestBase.php';

use GuzzleHttp\Psr7\Request;


final class UserTest extends TestBase
{
    private $userHandlerPath = 'user/index.php';
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
        $response = $client->post($this->userHandlerPath, [
            'json' => [
                'username' => $username,
                'firstName' => 'Ron',
                'lastName' => 'Snow',
                'email' => $email,
                'password' => 'abacadae',
                'status_id' => 1
            ]
        ]);

        $json = json_decode($response->getBody()->getContents());
        $this->assertTrue($json->userCreated);
        $this->assertEquals($response->getStatusCode(), 201);

        $user = $this->_getUser($json->userId, $client);

        $this->assertEquals($user->firstName, 'Ron');
        $this->assertEquals($user->lastName, 'Snow');
        $this->assertEquals($user->username, $username);
        $this->assertEquals($user->email, $email);
    }

    private function _getUser($userId, $client = null) {
        if (!$client) $client = $this->getHandlerClient();
        // get the user and make sure it's all good
        $response = $client->get('user', ['query' => 'id=' . $userId]);
        $this->assertEquals($response->getStatusCode(), 200);
        $user = User::fromJson($response->getBody());
        return $user;
    }

    public function testUpdate()
    {
        $userId = 0;
        $client = $this->getHandlerClient();
        $username = 'test-user-' . GUID();
        $email = GUID() . '@test.com';
        $response = $client->post($this->userHandlerPath, [
            'json' => [
                'id' => $userId,
                'username' => $username,
                'firstName' => 'Ron',
                'lastName' => 'Snow',
                'email' => $email,
                'password' => 'abacadae',
                'status_id' => 1
            ]
        ]);
        $code = $response->getStatusCode();
        $json = json_decode($response->getBody()->getContents());
        $this->assertTrue($json->userCreated);
        $this->assertEquals($code, 201);

        // get the user and make sure it's all good
        $user = $this->_getUser($json->userId, $client);
        $this->assertEquals($user->firstName, "Ron");
        $this->assertEquals($user->lastName, 'Snow');
        $this->assertEquals($user->username, $username);
        $this->assertEquals($user->email, $email);
    }

}