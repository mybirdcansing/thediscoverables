<?php
declare(strict_types=1);

require_once __dir__ . '/../web/lib/consts.php';
require_once __dir__ . '/TestBase.php';

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Cookie\CookieJar;

final class UserTest extends TestBase
{
    private $userHandlerPath = 'user/index.php';

    protected function setUp(): void
    {
        // login as the test user
        $json = $this->authenticateUser(TEST_USERNAME, TEST_PASSWORD);
        // set the cookie for future requests
        $this->cookieJar = CookieJar::fromArray([
            'login' => $json->cookie
        ], TEST_DOMAIN);
    }

    public function testGET()
    {
        // created in the original setup for the database in sql/schema.sql
        $user = $this->_getUser('00000000-0000-0000-0000-000000000000');
        $this->assertEquals($user->username, 'adam');
        $this->assertEquals($user->firstName, 'Adam');
        $this->assertEquals($user->lastName, 'Cohen');
        $this->assertEquals($user->email, 'thediscoverables@gmail.com');
    }

    public function testCreate()
    {
        // created unique username and email for db contraints
        $username = $this->_uniqueUsername();
        $email = $this->_uniqueEmail();

        $password = 'abacadae';

        $user = new User();
        $user->username = $username;
        $user->firstName = 'Ron';
        $user->lastName = 'Snow';
        $user->email = $email;
        $user->password = $password;
        $user->statusId = 1;

        $user = $this->_createUser($user);

        $this->assertEquals($user->username, $username);
        $this->assertEquals($user->firstName, 'Ron');
        $this->assertEquals($user->lastName, 'Snow');
        $this->assertEquals($user->email, $email);
        $this->assertEquals($user->statusId, 1);

        $authResponse = $this->authenticateUser($username, $password);
        $this->assertTrue($authResponse->authenticated);

        $this->_deleteUser($user->id);
    }

    public function testUpdate()
    {

        // first make a user
        $user = new User();
        $username = $this->_uniqueUsername();
        $email = $this->_uniqueEmail();
        $password = 'abacadae';
        $user->username = $username;
        $user->firstName = 'Ron';
        $user->lastName = 'Snow';
        $user->email = $email;
        $user->password = $password;
        $user->statusId = 1;

        $user = $this->_createUser($user);

        $updatedUsername = $this->_uniqueUsername();
        $updatedEmail = $this->_uniqueEmail();
        $updatedPassword = 'updatedpassword';
        $client = $this->getHandlerClient();
        $response = $client->post($this->userHandlerPath, [
            'json' => [
                'id' => $user->id,
                'username' => $updatedUsername,
                'firstName' => 'James',
                'lastName' => 'Nuckolls',
                'email' => $updatedEmail,
                'password' => $updatedPassword,
                'statusId' => 1
            ],
            'cookies' => $this->cookieJar
        ]);

        $this->assertEquals($response->getStatusCode(), 200);
        $json = json_decode($response->getBody()->getContents());

        $this->assertTrue($json->userUpdated);

        // get the user and make sure it's all good
        $user = $this->_getUser($json->userId);
        $this->assertEquals($user->firstName, "James");
        $this->assertEquals($user->lastName, 'Nuckolls');
        $this->assertEquals($user->username, $updatedUsername);
        $this->assertEquals($user->email, $updatedEmail);
        $this->assertEquals($user->statusId, 1);

        $authResponse = $this->authenticateUser($updatedUsername, $updatedPassword);
        $this->assertTrue($authResponse->authenticated);
        
        $this->_deleteUser($user->id, $client);
    }


    public function testDelete()
    {

        $username = 'test-user-' . GUID();
        $email = GUID() . '@test.com';

        $user = new User();
        $user->username = $username;
        $user->firstName = 'Ron';
        $user->lastName = 'Snow';
        $user->email = $email;
        $user->password = 'abacadae';
        $user->statusId = 1;

        $user = $this->_createUser($user);
        $this->assertEquals($user->statusId, 1);

        // first make sure the status update is working to hide users from the user interface
        $user->statusId = 2;
        $client = $this->getHandlerClient();
        $response = $client->post($this->userHandlerPath, [
            'json' => $user->expose(),
            'cookies' => $this->cookieJar
        ]);

        $user = $this->_getUser($user->id);
        $this->assertEquals($user->statusId, 2);        

        // now delete the user from the system
        $this->_deleteUser($user->id);

        // make sure the user is gone
        $response = $client->get('user', [
            'query' => 'id=' . $user->id,
            'cookies' => $this->cookieJar
        ]);

        $this->assertEquals($response->getStatusCode(), 404);
    }

    private function _getUser($userId)
    {
        $client = $this->getHandlerClient();

        $response = $client->get('user', [
            'query' => 'id=' . $userId,
            'cookies' => $this->cookieJar
        ]);

        $this->assertEquals($response->getStatusCode(), 200);

        return User::fromJson($response->getBody()->getContents());
    }


    private function _createUser($user)
    {
        $client = $this->getHandlerClient();

        $response = $client->post($this->userHandlerPath, [
            'json' => $user->expose(),
            'cookies' => $this->cookieJar
        ]);
        $json = json_decode($response->getBody()->getContents());

        $this->assertTrue($json->userCreated);
        $this->assertEquals($response->getStatusCode(), 201);

        return $this->_getUser($json->userId);
    }


    private function _deleteUser($userId)
    {
        $client = $this->getHandlerClient();

        $response = $client->post($this->userHandlerPath, [
            'json' => [
                'id' => $userId,
                'delete' => true,
            ],
            'cookies' => $this->cookieJar
        ]);

        $this->assertEquals($response->getStatusCode(), 200);
    }

    private function _uniqueUsername()
    {
        return 'test-user-' . GUID();
    }

    private function _uniqueEmail()
    {
        return GUID() . '@test.com';
    }
}