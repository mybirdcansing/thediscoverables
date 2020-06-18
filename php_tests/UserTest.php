<?php
declare(strict_types=1);
require_once __dir__ . '/../web/lib/messages.php';
require_once __dir__ . '/TestBase.php';

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Cookie\CookieJar;

final class UserTest extends TestBase
{
    private $userHandlerPath = 'user/';

    public function testCreate()
    {
        // created unique usernames and emails to allow for db contraints
        $username = $this->uniqueUsername();
        $email = $this->uniqueEmail();

        $password = Guid::create();

        $user = new User();
        $user->username = $username;
        $user->firstName = 'Ron';
        $user->lastName = 'Snow';
        $user->email = $email;
        $user->password = $password;
        $user->statusId = ACTIVE_USER_STATUS_ID;

        $user = $this->getUserGateway()->createUser($user);
        try {
            $this->assertEquals($username, $user->username);
            $this->assertEquals('Ron', $user->firstName);
            $this->assertEquals('Snow', $user->lastName);
            $this->assertEquals($email, $user->email);
            $this->assertEquals(ACTIVE_USER_STATUS_ID, $user->statusId);
            $authResponse = $this->authenticateUser($username, $password);
            $this->assertTrue($authResponse->authenticated, 'Failed to authenticate the user.');
        } finally {
            $this->getUserGateway()->deleteUser($user->id);
        }

    }

    public function testGetWithInvalidUUID()
    {
        $this->getUserGateway()->getWithErrors('0-0', 404);
    }

    public function testCreateWithDupeUsername()
    {
        // first create a new user
        $username = $this->uniqueUsername();
        $user1 = new User();
        $user1->username = $username;
        $user1->firstName = 'Ron';
        $user1->lastName = 'Snow';
        $user1->email = $this->uniqueEmail();
        $user1->password = 'abacadae';
        $user1->statusId = ACTIVE_USER_STATUS_ID;
        $user1 = $this->getUserGateway()->createUser($user1);

        // now create another user with the same username
        $user2 = new User();
        $user2->username = $username;
        $user2->firstName = 'Ron';
        $user2->lastName = 'Snow';
        $user2->email = $this->uniqueEmail();
        $user2->password = 'abacadae';
        $user2->statusId = ACTIVE_USER_STATUS_ID;

        $this->getUserGateway()->createWithErrors($user2, 
            [USERNAME_TAKEN_CODE => sprintf(USERNAME_TAKEN_MESSAGE, $user2->username)],
            409);

        $this->getUserGateway()->deleteUser($user1->id);
    }

    public function testCreateWithDupeEmail()
    {
        // create a new user
        $email = $this->uniqueEmail();
        $user1 = new User();
        $user1->username = $this->uniqueUsername();
        $user1->firstName = 'Ron';
        $user1->lastName = 'Snow';
        $user1->email = $email;
        $user1->password = 'abacadae';
        $user1->statusId = ACTIVE_USER_STATUS_ID;
        $user1 = $this->getUserGateway()->createUser($user1);

        $emai2 = $this->uniqueEmail();
        $user2 = new User();
        $user2->username = $this->uniqueUsername();
        $user2->firstName = 'Ron';
        $user2->lastName = 'Snow';
        $user2->email = $email;
        $user2->password = 'abacadae';
        $user2->statusId = ACTIVE_USER_STATUS_ID;

        $this->getUserGateway()->createWithErrors($user2, 
            [EMAIL_TAKEN_CODE => sprintf(EMAIL_TAKEN_MESSAGE, $user2->email)],
            409);

        $this->getUserGateway()->deleteUser($user1->id);
    }

    public function testCreateWithInvalidEmail()
    {
        // create with bad email
        $user = $this->fleshedOutUser();
        $user->email = 'thediscoverablesgmail.com';
        $this->getUserGateway()->createWithErrors($user, [EMAIL_INVALID_CODE => EMAIL_INVALID_MESSAGE]);
    }

    public function testCreateWithBlankEmail()
    {
        // create with blank email
        $user = $this->fleshedOutUser();
        $user->email = '';
        $this->getUserGateway()->createWithErrors($user, [EMAIL_BLANK_CODE => EMAIL_BLANK_MESSAGE]);
    }

    public function testCreateWithBlankPassword()
    {
        // create with blank password
        $user = $this->fleshedOutUser();
        $user->password = '';
        $this->getUserGateway()->createWithErrors($user, [PASSWORD_BLANK_CODE => PASSWORD_BLANK_MESSAGE]);
    }

    public function testCreateWithLongPassword()
    {
        // create with long password
        $user = $this->fleshedOutUser();
        $user->password = '123456789012345678901234567890123456789012345678901234567890123456';
        $this->getUserGateway()->createWithErrors($user, [PASSWORD_LONG_CODE => PASSWORD_LONG_MESSAGE]);
    }

    public function testCreateWithShortPassword()
    {
        $user = $this->fleshedOutUser();
        $user->password = '12345';
        $this->getUserGateway()->createWithErrors($user, [PASSWORD_SHORT_CODE => PASSWORD_SHORT_MESSAGE]);
    }


    public function testCreateWithInvalidPassword()
    {
        // create with invalid char
        $user = $this->fleshedOutUser();
        $user->password = 'dldsafgjshdg\\f';
        $this->getUserGateway()->createWithErrors($user, [PASSWORD_INVALID_CODE => PASSWORD_INVALID_MESSAGE]);
    }


    public function testCreateWithBlankUsername()
    {
        $user = $this->fleshedOutUser();
        $user->username = '';
        $this->getUserGateway()->createWithErrors($user, [USERNAME_BLANK_CODE => USERNAME_BLANK_MESSAGE]);
    }

    public function testCreateWithLongUsername()
    {
        $user = $this->fleshedOutUser();
        $user->username = '123456789012345678901234567890123456789012345678901234567890123456';
        $this->getUserGateway()->createWithErrors($user, [USERNAME_LONG_CODE => USERNAME_LONG_MESSAGE]);
    }

    public function testCreateWithShortUsername()
    {
        $user = $this->fleshedOutUser();
        $user->username = '123';
        $this->getUserGateway()->createWithErrors($user, [USERNAME_SHORT_CODE => USERNAME_SHORT_MESSAGE]);
    }


    public function testCreateWithInvalidUsername()
    {
        $user = $this->fleshedOutUser();
        $user->username = 'dldsafgjshdg\\f';
        $this->getUserGateway()->createWithErrors($user, [USERNAME_INVALID_CODE => USERNAME_INVALID_MESSAGE]);
    }

    public function testCreateWithLongFirstName()
    {
        $user = $this->fleshedOutUser();
        $user->firstName = '123456789012345678901234567890123456789012345678901234567890123456';
        $this->getUserGateway()->createWithErrors($user, [FIRSTNAME_LONG_CODE => FIRSTNAME_LONG_MESSAGE]);
    }

    public function testCreateWithInvalidFirstName()
    {
        // create with invalid char
        $user = $this->fleshedOutUser();
        $user->firstName = 'dldsafgjshdg\\f';
        $this->getUserGateway()->createWithErrors($user, [FIRSTNAME_INVALID_CODE => FIRSTNAME_INVALID_MESSAGE]);
    }

    public function testCreateWithLongLastName()
    {
        // create with long password
        $user = $this->fleshedOutUser();
        $user->lastName = '123456789012345678901234567890123456789012345678901234567890123456';
        $this->getUserGateway()->createWithErrors($user, [LASTNAME_LONG_CODE => LASTNAME_LONG_MESSAGE]);
    }

    public function testCreateWithInvalidLastName()
    {
        // create with invalid char
        $user = $this->fleshedOutUser();
        $user->lastName = 'dldsafgjshdg\\f';
        $this->getUserGateway()->createWithErrors($user, [LASTNAME_INVALID_CODE => LASTNAME_INVALID_MESSAGE]);
    }

    public function testCreateWithMultipleProblems()
    {
        // create with invalid char
        $user = $this->fleshedOutUser();
        $user->lastName = 'dldsafgjshdg\\f';
        $user->firstName = 'dldsafgjshdg\\f';
        $this->getUserGateway()->createWithErrors($user, [
            LASTNAME_INVALID_CODE => LASTNAME_INVALID_MESSAGE,
            FIRSTNAME_INVALID_CODE => FIRSTNAME_INVALID_MESSAGE,
        ]);
    }

    // public function testUpdatePassword() {
    //     $ts = ((new Configuration())->getSettings())->test;
    //     // first make a user
    //     $user = $this->getUserGateway()->createUser($this->fleshedOutUser());
    //     $updatedPassword = 'UpdatedPassword';

    //     try {
    //         // request the password update email
    //         $userToken = $this->getPasswordResetTokenGateway()->getToken($user);
    //         // can't get the token from the email, so go directly to the database 
    //         $dataAccess = new DataAccess($configPath = __dir__ . '/../config/test_config.json');
    //         $dbConnection = $dataAccess->getConnection();
    //         $userData = new UserData($dbConnection);
    //         $tokens = $userData->getPasswordResetTokens($user->id);
            
    //         $tokenData = reset($tokens);

    //         $this->getUserGateway()->resetPassword($user->id, $tokenData->token, $updatedPassword);
    //         // make sure it's all good
    //         $authResponse = $this->authenticateUser($user->username, $updatedPassword);
    //         $this->assertTrue($authResponse->authenticated, 'User was not authenticated.');

    //     } finally {
    //         $this->getUserGateway()->deleteUser($user->id);
    //     }
    // }
    
    public function testAuthenticateWithInvalidPassword() {
        // first make a user
        $user = $this->fleshedOutUser();
        $user = $this->getUserGateway()->createUser($user);
        try {
            $json = $this->authenticateUser($user->username, 'badpassword', 401);
            $this->assertFalse($json->authenticated, 'User was authenticated.');
        } finally {
            $this->getUserGateway()->deleteUser($user->id);
        }
    }

    public function testUpdate()
    {
        // first make a user
        $user = $this->getUserGateway()->createUser($this->fleshedOutUser());
        $userToChange = new User();
        $userToChange->id = $user->id;
        $userToChange->username = $this->uniqueUsername();
        $userToChange->email = $this->uniqueEmail();
        $userToChange->firstName = 'James';
        $userToChange->lastName = 'Nuckolls';
        $userToChange->statusId = ACTIVE_USER_STATUS_ID;
        
        try {
            $updatedUser = $this->getUserGateway()->updateUser($userToChange);
            $this->assertEquals($userToChange->firstName, $updatedUser->firstName);
            $this->assertEquals($userToChange->lastName, $updatedUser->lastName);
            $this->assertEquals($userToChange->username, $updatedUser->username);
            $this->assertEquals($userToChange->email, $updatedUser->email);
            $this->assertEquals(ACTIVE_USER_STATUS_ID, $updatedUser->statusId);
        } finally {
            $this->getUserGateway()->deleteUser($user->id);
        }
    }

    public function testUpdateWithDupeUsername()
    {
        // first make a couple user
        $user1 = $this->getUserGateway()->createUser($this->fleshedOutUser());
        $user2 = $this->getUserGateway()->createUser($this->fleshedOutUser());
        $user2->username = $user1->username;
        try
        {
            $this->getUserGateway()->updateWithErrors($user2,
                [USERNAME_TAKEN_CODE => sprintf(USERNAME_TAKEN_MESSAGE, $user1->username)],
                409);
        } finally {
            $this->getUserGateway()->deleteUser($user1->id);
            $this->getUserGateway()->deleteUser($user2->id);
        }
    }

    public function testUpdateWithDupeEmail()
    {
        // first make a couple user
        $user1 = $this->getUserGateway()->createUser($this->fleshedOutUser());
        $user2 = $this->getUserGateway()->createUser($this->fleshedOutUser());
        $user2->email = $user1->email;
        try
        {
            $this->getUserGateway()->updateWithErrors($user2,
                [EMAIL_TAKEN_CODE => sprintf(EMAIL_TAKEN_MESSAGE, $user1->email)],
                409);
        } finally {
            $this->getUserGateway()->deleteUser($user1->id);
            $this->getUserGateway()->deleteUser($user2->id);
        }
    }

    public function testDelete()
    {
        $password = 'abacadae';
        $user = $this->getUserGateway()->createUser($this->fleshedOutUser());
        $this->assertEquals($user->statusId, ACTIVE_USER_STATUS_ID);
        $user->password = $password;
        $user->statusId = INACTIVE_USER_STATUS_ID;
        // first make sure the status update is working to hide users from the user interface
        $user = $this->getUserGateway()->updateUser($user);

        try {
            $this->assertEquals($user->statusId, INACTIVE_USER_STATUS_ID);
        } finally {
            // now delete the user from the system
            // this try finally block is just to clean up 
            // the database if the above assert fails
            $this->getUserGateway()->deleteUser($user->id);
        }

        // make sure the user is gone
        $this->getUserGateway()->getWithErrors($user->id, 404);
    }

}