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
        $settings = ((new Configuration())->getSettings())->test;
        // login as the test user (see sql/schema.sql)
        $json = $this->authenticateUser($settings->TEST_USERNAME, $settings->TEST_PASSWORD);
        // set the cookie for future requests
        $this->cookieJar = CookieJar::fromArray([
            'login' => $json->cookie
        ], $settings->TEST_DOMAIN);
    }

    public function testGET()
    {
        // This test user was created in the setup of the database. See sql/schema.sql
        $user = $this->_getUser('00000000-0000-0000-0000-000000000000');
        $this->assertEquals('adam', $user->username, '`username` is incorrect');
        $this->assertEquals('Adam', $user->firstName, '`firstName` is incorrect');
        $this->assertEquals('Cohen', $user->lastName, '`lastName` is incorrect');
        $this->assertEquals('thediscoverables@gmail.com', $user->email, '`email` is incorrect');
    }

    public function testCreate()
    {
        // created unique usernames and emails to allow for db contraints
        $username = $this->_uniqueUsername();
        $email = $this->_uniqueEmail();

        $password = GUID();

        $user = new User();
        $user->username = $username;
        $user->firstName = 'Ron';
        $user->lastName = 'Snow';
        $user->email = $email;
        $user->password = $password;
        $user->statusId = ACTIVE_USER_STATUS_ID;

        $user = $this->_createUser($user);
        try {
            $this->assertEquals($username, $user->username);
            $this->assertEquals('Ron', $user->firstName);
            $this->assertEquals('Snow', $user->lastName);
            $this->assertEquals($email, $user->email);
            $this->assertEquals(ACTIVE_USER_STATUS_ID, $user->statusId);
            $authResponse = $this->authenticateUser($username, $password);
            $this->assertTrue($authResponse->authenticated, 'Failed to authenticate the user.');
        } finally {
            $this->_deleteUser($user->id);
        }

    }

    public function testCreateWithDupeUsername()
    {
        // create with bad email
        $email = $this->_uniqueEmail();
        $password = 'abacadae';
        $user = new User();
        $user->username = 'adam';
        $user->firstName = 'Ron';
        $user->lastName = 'Snow';
        $user->email = $email;
        $user->password = $password;
        $user->statusId = ACTIVE_USER_STATUS_ID;

        $client = $this->getHandlerClient();

        $response = $client->post($this->userHandlerPath, [
            'json' => $user->expose(),
            'cookies' => $this->cookieJar
        ]);
        $json = json_decode($response->getBody()->getContents());
        try {
            $this->assertEquals(409, $response->getStatusCode());
            $this->assertFalse($json->userCreated, 'The created flag was not set to true.');
            $this->assertTrue(isset($json->errorMessages));
            $this->assertArrayHasKey(USERNAME_TAKEN_CODE, (array)$json->errorMessages);
            $this->assertEquals(((array)$json->errorMessages)[USERNAME_TAKEN_CODE], 
                sprintf(USERNAME_TAKEN_MESSAGE, $user->username));
        } finally {
            if ($json->userCreated) {
                $this->_deleteUser($json->userId);
            }
        }
    }

    public function testCreateWithDupeEmail()
    {
        // create with bad email
        $email = 'thediscoverables@gmail.com';
        $password = 'abacadae';
        $user = new User();
        $user->username = $this->_uniqueUsername();
        $user->firstName = 'Ron';
        $user->lastName = 'Snow';
        $user->email = $email;
        $user->password = $password;
        $user->statusId = ACTIVE_USER_STATUS_ID;

        $client = $this->getHandlerClient();

        $response = $client->post($this->userHandlerPath, [
            'json' => $user->expose(),
            'cookies' => $this->cookieJar
        ]);
        $json = json_decode($response->getBody()->getContents());
        $this->assertEquals(409, $response->getStatusCode());
        $this->assertFalse($json->userCreated, 'The created flag was not set to true.');
        $this->assertTrue(isset($json->errorMessages));
        $this->assertArrayHasKey(EMAIL_TAKEN_CODE, (array)$json->errorMessages);
        $this->assertEquals(
            ((array)$json->errorMessages)[EMAIL_TAKEN_CODE], 
            sprintf(EMAIL_TAKEN_MESSAGE, $user->email));
    }

    public function testCreateWithInvalidEmail()
    {
        // create with bad email
        $email = 'thediscoverablesgmail.com';
        $password = 'abacadae';
        $user = new User();
        $user->username = $this->_uniqueUsername();
        $user->firstName = 'Ron';
        $user->lastName = 'Snow';
        $user->email = $email;
        $user->password = $password;
        $user->statusId = ACTIVE_USER_STATUS_ID;

        $client = $this->getHandlerClient();

        $response = $client->post($this->userHandlerPath, [
            'json' => $user->expose(),
            'cookies' => $this->cookieJar
        ]);

        try {        
            $this->assertEquals(422, $response->getStatusCode());
            $json = json_decode($response->getBody()->getContents());
            $this->assertFalse($json->userCreated, 
                'The created flag was not set to false.');
            $this->assertTrue(isset($json->errorMessages), 
                'No errorMessages were sent in the response');
            $this->assertArrayHasKey(EMAIL_INVALID_CODE, (array)$json->errorMessages);
            $this->assertEquals(
                sprintf(EMAIL_INVALID_MESSAGE, $user->email),
                ((array)$json->errorMessages)[EMAIL_INVALID_CODE]);
        } finally {
            if ($json->userCreated) {
                $this->_deleteUser($json->userId);
            }
        }
    }

    public function testCreateWithBlankEmail()
    {
        // create with bad email
        $email = '';
        $password = 'abacadae';
        $user = new User();
        $user->username = $this->_uniqueUsername();
        $user->firstName = 'Ron';
        $user->lastName = 'Snow';
        $user->email = $email;
        $user->password = $password;
        $user->statusId = ACTIVE_USER_STATUS_ID;

        $client = $this->getHandlerClient();

        $response = $client->post($this->userHandlerPath, [
            'json' => $user->expose(),
            'cookies' => $this->cookieJar
        ]);
        $json = json_decode($response->getBody()->getContents());
        try {
            $this->assertEquals(422, $response->getStatusCode());
            $this->assertFalse($json->userCreated, 'The created flag was not set to true.');
            $this->assertTrue(isset($json->errorMessages));
            $this->assertArrayHasKey(EMAIL_BLANK_CODE, (array)$json->errorMessages);
            $this->assertEquals(
                sprintf(EMAIL_BLANK_MESSAGE, $user->email),
                ((array)$json->errorMessages)[EMAIL_BLANK_CODE]);
        } finally {
            if ($json->userCreated) {
                $this->_deleteUser($json->userId);
            }
        }
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
        $user->statusId = ACTIVE_USER_STATUS_ID;

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

        $json = json_decode($response->getBody()->getContents());
        try {

            $this->assertEquals($response->getStatusCode(), 200);
            $this->assertTrue($json->userUpdated, 'The userUpdated flag was not set to true.');

            // get the user and make sure it's all good
            $user = $this->_getUser($json->userId);
            $this->assertEquals('James', $user->firstName);
            $this->assertEquals('Nuckolls', $user->lastName);
            $this->assertEquals($updatedUsername, $user->username);
            $this->assertEquals($updatedEmail, $user->email);
            $this->assertEquals(ACTIVE_USER_STATUS_ID, $user->statusId);

            $authResponse = $this->authenticateUser($updatedUsername, $updatedPassword);
            $this->assertTrue($authResponse->authenticated, 'User was not authenticated.');
        } finally {
            $this->_deleteUser($json->userId);
        }
    }

    public function testUpdateWithDupeUsername()
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
        $user->statusId = ACTIVE_USER_STATUS_ID;

        $user = $this->_createUser($user);

        $updatedUsername = 'adam';

        $client = $this->getHandlerClient();
        $response = $client->post($this->userHandlerPath, [
            'json' => [
                'id' => $user->id,
                'username' => $updatedUsername,
                'firstName' => 'James',
                'lastName' => 'Nuckolls',
                'email' => $this->_uniqueEmail(),
                'password' => 'updatedpassword',
                'statusId' => ACTIVE_USER_STATUS_ID
            ],
            'cookies' => $this->cookieJar
        ]);
        $json = json_decode($response->getBody()->getContents());
        try {
            $this->assertEquals(409, $response->getStatusCode());
            $this->assertFalse($json->userUpdated, 'The userUpdated flag was set to true.');
            $this->assertTrue(isset($json->errorMessages));
            $this->assertArrayHasKey(USERNAME_TAKEN_CODE, (array)$json->errorMessages);
            $this->assertEquals(
                ((array)$json->errorMessages)[USERNAME_TAKEN_CODE], 
                sprintf(USERNAME_TAKEN_MESSAGE, $updatedUsername));
        } finally {
            $this->_deleteUser($user->id);
        }
    }

    public function testUpdateWithDupeEmail()
    {
        // first make a user
        $user = new User();
        $username = $this->_uniqueUsername('DupeEmail1');
        $email = $this->_uniqueEmail();
        $password = 'abacadae';
        $user->username = $username;
        $user->firstName = 'Ron';
        $user->lastName = 'Snow';
        $user->email = $email;
        $user->password = $password;
        $user->statusId = ACTIVE_USER_STATUS_ID;

        $user = $this->_createUser($user);
        try
        {
            $dupeEmail = 'thediscoverables@gmail.com';

            $client = $this->getHandlerClient();
            $response = $client->post($this->userHandlerPath, [
                'json' => [
                    'id' => $user->id,
                    'username' => $this->_uniqueUsername('DupeEmail2'),
                    'firstName' => 'James',
                    'lastName' => 'Nuckolls',
                    'email' => $dupeEmail,
                    'password' => 'updatedpassword',
                    'statusId' => 1
                ],
                'cookies' => $this->cookieJar
            ]);
            $json = json_decode($response->getBody()->getContents());
            $this->assertEquals(409, $response->getStatusCode(), 'Status code shold be 409');
            $this->assertFalse($json->userUpdated, 'The userUpdated flag was set to true.');
            $this->assertTrue(isset($json->errorMessages));
            $this->assertArrayHasKey(EMAIL_TAKEN_CODE, (array)$json->errorMessages);
            $this->assertEquals(
                ((array)$json->errorMessages)[EMAIL_TAKEN_CODE], 
                sprintf(EMAIL_TAKEN_MESSAGE, $dupeEmail));
        } finally {
            $this->_deleteUser($user->id);
        }
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
        $user->statusId = ACTIVE_USER_STATUS_ID;

        $user = $this->_createUser($user);

        $this->assertEquals($user->statusId, ACTIVE_USER_STATUS_ID);

        // first make sure the status update is working to hide users from the user interface
        $user->statusId = INACTIVE_USER_STATUS_ID;
        $client = $this->getHandlerClient();
        $response = $client->post($this->userHandlerPath, [
            'json' => $user->expose(),
            'cookies' => $this->cookieJar
        ]);

        $user = $this->_getUser($user->id);
        $this->assertEquals(INACTIVE_USER_STATUS_ID, $user->statusId);        

        // now delete the user from the system
        $this->_deleteUser($user->id);

        // make sure the user is gone
        $response = $client->get('user', [
            'query' => 'id=' . $user->id,
            'cookies' => $this->cookieJar
        ]);

        $this->assertEquals(404, $response->getStatusCode());
    }

    private function _getUser($userId)
    {
        $client = $this->getHandlerClient();
        $response = $client->get('user', [
            'query' => 'id=' . $userId,
            'cookies' => $this->cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
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
        $this->assertTrue($json->userCreated, '`userCreated` should be true');
        try {
            $this->assertEquals(201, $response->getStatusCode());
            return $this->_getUser($json->userId);
        } catch (Exception $e) {
            $this->_deleteUser($json->userId);
            throw $e;
        }
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
        $this->assertEquals(200, $response->getStatusCode());
    }

    private function _uniqueUsername($prefix = 0)
    {
        if ($prefix) {
            return $prefix . '-test-user-' . GUID();
        }
        return 'test-user-' . GUID();
    }

    private function _uniqueEmail()
    {
        return GUID() . '@test.com';
    }
}