<?php
declare(strict_types=1);
require_once __dir__ . '/../web/lib/Configuration.php';
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
        // first create a new user
        $username = $this->_uniqueUsername();
        $user1 = new User();
        $user1->username = $username;
        $user1->firstName = 'Ron';
        $user1->lastName = 'Snow';
        $user1->email = $this->_uniqueEmail();
        $user1->password = 'abacadae';
        $user1->statusId = ACTIVE_USER_STATUS_ID;
        $user1 = $this->_createUser($user1);

        // now create another user with the same username
        $user2 = new User();
        $user2->username = $username;
        $user2->firstName = 'Ron';
        $user2->lastName = 'Snow';
        $user2->email = $this->_uniqueEmail();
        $user2->password = 'abacadae';
        $user2->statusId = ACTIVE_USER_STATUS_ID;

        $client = $this->getHandlerClient();

        $response = $client->post($this->userHandlerPath, [
            'json' => $user2->expose(),
            'cookies' => $this->cookieJar
        ]);

        $json = json_decode($response->getBody()->getContents());
        try {
            $this->assertEquals(409, $response->getStatusCode());
            $this->assertFalse($json->userCreated, 'The created flag was not set to true.');
            $this->assertTrue(isset($json->errorMessages));
            $this->assertArrayHasKey(USERNAME_TAKEN_CODE, (array)$json->errorMessages);
            $this->assertEquals(((array)$json->errorMessages)[USERNAME_TAKEN_CODE], 
                sprintf(USERNAME_TAKEN_MESSAGE, $user2->username));
        } finally {
            $this->_deleteUser($user1->id);
            if ($json->userCreated) {
                $this->_deleteUser($json->userId);
            }
        }
    }

    public function testCreateWithDupeEmail()
    {
        // create a new user
        $email = $this->_uniqueEmail();
        $user1 = new User();
        $user1->username = $this->_uniqueUsername();
        $user1->firstName = 'Ron';
        $user1->lastName = 'Snow';
        $user1->email = $email;
        $user1->password = 'abacadae';
        $user1->statusId = ACTIVE_USER_STATUS_ID;
        $user1 = $this->_createUser($user1);

        $emai2 = $this->_uniqueEmail();
        $user2 = new User();
        $user2->username = $this->_uniqueUsername();
        $user2->firstName = 'Ron';
        $user2->lastName = 'Snow';
        $user2->email = $email;
        $user2->password = 'abacadae';
        $user2->statusId = ACTIVE_USER_STATUS_ID;

        $client = $this->getHandlerClient();

        $response = $client->post($this->userHandlerPath, [
            'json' => $user2->expose(),
            'cookies' => $this->cookieJar
        ]);

        $json = json_decode($response->getBody()->getContents());
        try {
            $this->assertEquals(409, $response->getStatusCode());
            $this->assertFalse($json->userCreated, 'The created flag was not set to true.');
            $this->assertTrue(isset($json->errorMessages));
            $this->assertArrayHasKey(EMAIL_TAKEN_CODE, (array)$json->errorMessages);
            $this->assertEquals(
                ((array)$json->errorMessages)[EMAIL_TAKEN_CODE], 
                sprintf(EMAIL_TAKEN_MESSAGE, $user2->email));
        } finally {
            $this->_deleteUser($user1->id);
            if ($json->userCreated) {
                $this->_deleteUser($json->userId);
            }
        }
    }

    public function testCreateWithInvalidEmail()
    {
        // create with bad email
        $user = $this->_fleshedOutUser();
        $user->email = 'thediscoverablesgmail.com';
        $this->_createWithErrors($user, [EMAIL_INVALID_CODE => EMAIL_INVALID_MESSAGE]);
    }

    public function testCreateWithBlankEmail()
    {
        // create with blank email
        $user = $this->_fleshedOutUser();
        $user->email = '';
        $this->_createWithErrors($user, [EMAIL_BLANK_CODE => EMAIL_BLANK_MESSAGE]);
    }

    public function testCreateWithBlankPassword()
    {
        // create with blank password
        $user = $this->_fleshedOutUser();
        $user->password = '';
        $this->_createWithErrors($user, [PASSWORD_BLANK_CODE => PASSWORD_BLANK_MESSAGE]);
    }

    public function testCreateWithLongPassword()
    {
        // create with long password
        $user = $this->_fleshedOutUser();
        $user->password = '123456789012345678901234567890123456789012345678901234567890123456';
        $this->_createWithErrors($user, [PASSWORD_LONG_CODE => PASSWORD_LONG_MESSAGE]);
    }

    public function testCreateWithShortPassword()
    {
        // create with long password
        $user = $this->_fleshedOutUser();
        $user->password = '12345';
        $this->_createWithErrors($user, [PASSWORD_SHORT_CODE => PASSWORD_SHORT_MESSAGE]);
    }


    public function testCreateWithInvalidPassword()
    {
        // create with invalid char
        $user = $this->_fleshedOutUser();
        $user->password = 'dldsafgjshdg\\f';
        $this->_createWithErrors($user, [PASSWORD_INVALID_CODE => PASSWORD_INVALID_MESSAGE]);
    }


    public function testCreateWithBlankUsername()
    {
        // create with blank password
        $user = $this->_fleshedOutUser();
        $user->username = '';
        $this->_createWithErrors($user, [USERNAME_BLANK_CODE => USERNAME_BLANK_MESSAGE]);
    }

    public function testCreateWithLongUsername()
    {
        // create with long password
        $user = $this->_fleshedOutUser();
        $user->username = '123456789012345678901234567890123456789012345678901234567890123456';
        $this->_createWithErrors($user, [USERNAME_LONG_CODE => USERNAME_LONG_MESSAGE]);
    }

    public function testCreateWithShortUsername()
    {
        // create with long password
        $user = $this->_fleshedOutUser();
        $user->username = '123';
        $this->_createWithErrors($user, [USERNAME_SHORT_CODE => USERNAME_SHORT_MESSAGE]);
    }


    public function testCreateWithInvalidUsername()
    {
        // create with invalid char
        $user = $this->_fleshedOutUser();
        $user->username = 'dldsafgjshdg\\f';
        $this->_createWithErrors($user, [USERNAME_INVALID_CODE => USERNAME_INVALID_MESSAGE]);
    }

    public function testCreateWithLongFirstName()
    {
        // create with long password
        $user = $this->_fleshedOutUser();
        $user->firstName = '123456789012345678901234567890123456789012345678901234567890123456';
        $this->_createWithErrors($user, [FIRSTNAME_LONG_CODE => FIRSTNAME_LONG_MESSAGE]);
    }

    public function testCreateWithInvalidFirstName()
    {
        // create with invalid char
        $user = $this->_fleshedOutUser();
        $user->firstName = 'dldsafgjshdg\\f';
        $this->_createWithErrors($user, [FIRSTNAME_INVALID_CODE => FIRSTNAME_INVALID_MESSAGE]);
    }

    public function testCreateWithLongLastName()
    {
        // create with long password
        $user = $this->_fleshedOutUser();
        $user->lastName = '123456789012345678901234567890123456789012345678901234567890123456';
        $this->_createWithErrors($user, [LASTNAME_LONG_CODE => LASTNAME_LONG_MESSAGE]);
    }

    public function testCreateWithInvalidLastName()
    {
        // create with invalid char
        $user = $this->_fleshedOutUser();
        $user->lastName = 'dldsafgjshdg\\f';
        $this->_createWithErrors($user, [LASTNAME_INVALID_CODE => LASTNAME_INVALID_MESSAGE]);
    }

    public function testCreateWithMultipleProblems()
    {
        // create with invalid char
        $user = $this->_fleshedOutUser();
        $user->lastName = 'dldsafgjshdg\\f';
        $user->firstName = 'dldsafgjshdg\\f';
        $this->_createWithErrors($user, [
            LASTNAME_INVALID_CODE => LASTNAME_INVALID_MESSAGE,
            FIRSTNAME_INVALID_CODE => FIRSTNAME_INVALID_MESSAGE,
        ]);
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
        $password = 'abacadae';
        $user = new User();
        $user->username = $this->_uniqueUsername();
        $user->firstName = 'Ron';
        $user->lastName = 'Snow';
        $user->email = $this->_uniqueEmail();
        $user->password = $password;
        $user->statusId = ACTIVE_USER_STATUS_ID;

        $user = $this->_createUser($user);

        $this->assertEquals($user->statusId, ACTIVE_USER_STATUS_ID);

        // first make sure the status update is working to hide users from the user interface
        $client = $this->getHandlerClient();
        $response = $client->post($this->userHandlerPath, [
            'json' => [
                'id' => $user->id,
                'username' => $user->username,
                'firstName' => $user->firstName,
                'lastName' => $user->lastName,
                'email' => $user->email,
                'password' => $password,
                'statusId' => INACTIVE_USER_STATUS_ID
            ],
            'cookies' => $this->cookieJar
        ]);

        $user = $this->_getUser($user->id);

        try {
            $this->assertEquals($user->statusId, INACTIVE_USER_STATUS_ID);
        } finally {
            // now delete the user from the system
            // this try finally block is just to clean up 
            // the database if the above assert fails
            $this->_deleteUser($user->id);
        }

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

    private function _createWithErrors($user, $expectedErrors)
    {
        $client = $this->getHandlerClient();

        $response = $client->post($this->userHandlerPath, [
            'json' => $user->expose(),
            'cookies' => $this->cookieJar
        ]);
        $json = json_decode($response->getBody()->getContents());
        try {
            $this->assertEquals(422, $response->getStatusCode());
            $this->assertTrue(isset($json->userCreated));
            $this->assertFalse($json->userCreated, 'The created flag was not set to false.');
            $this->assertTrue(isset($json->errorMessages));
            $ems = (array)$json->errorMessages;
            foreach ($expectedErrors as $expectedErrorCode => $expectedErrorMessage) {
                $this->assertArrayHasKey($expectedErrorCode, $ems);
                $this->assertEquals($expectedErrorMessage, $ems[$expectedErrorCode]);
            }

        } finally {
            // cleanup the database
            if ($json->userCreated) {
                $this->_deleteUser($json->userId);
            }
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

    private function _fleshedOutUser() {
        $user = new User();
        $user->username = $this->_uniqueUsername();
        $user->firstName = 'Ron';
        $user->lastName = 'Snow';
        $user->email = $this->_uniqueEmail();
        $user->password = 'dummypassword';
        $user->statusId = ACTIVE_USER_STATUS_ID;
        return $user;
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