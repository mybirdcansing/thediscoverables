<?php
declare(strict_types=1);
require_once __dir__ . '/GatewayBase.php';

class UserGateway extends GatewayBase
{
	public $handlerPath = 'user/';

    public function getUser($userId)
    {
        $response = $this->httpClient->get($this->handlerPath . $userId, [
            'cookies' => $this->cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        return User::fromJson($response->getBody()->getContents());
    }

    public function createUser($user)
    {
        $response = $this->httpClient->post($this->handlerPath, [
            'json' => $user->expose(),
            'cookies' => $this->cookieJar
        ]);
        $json = json_decode($response->getBody()->getContents());
        $this->assertTrue($json->userCreated, '`userCreated` should be true');
        try {
            $this->assertEquals(201, $response->getStatusCode());
            return $this->getUser($json->userId);
        } catch (Exception $e) {
            $this->deleteUser($json->userId);
            throw $e;
        }
    }

    public function createWithErrors($user, $expectedErrors, $expectedErrorCode = 422)
    {
        $response = $this->httpClient->post($this->handlerPath, [
            'json' => $user->expose(),
            'cookies' => $this->cookieJar
        ]);
        $json = json_decode($response->getBody()->getContents());
        try {
            $this->assertEquals($expectedErrorCode, $response->getStatusCode());
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
                $this->deleteUser($json->userId);
            }
        }
    }

    public function updateUser($user)
    {
        $response = $this->httpClient->post($this->handlerPath . $user->id, [
            'json' => $user->expose(),
            'cookies' => $this->cookieJar
        ]);
        $json = json_decode($response->getBody()->getContents());
        $this->assertTrue($json->userUpdated, '`userUpdated` should be true');
        $this->assertEquals(200, $response->getStatusCode());
        return $this->getUser($json->userId);
    }

    public function updateWithErrors($user, $expectedErrors, $expectedErrorCode = 422)
    {
        $response = $this->httpClient->post($this->handlerPath . $user->id, [
            'json' => $user->expose(),
            'cookies' => $this->cookieJar
        ]);
        $json = json_decode($response->getBody()->getContents());

        $this->assertEquals($expectedErrorCode, $response->getStatusCode());
        $this->assertTrue(isset($json->userUpdated));
        $this->assertFalse($json->userUpdated, 'The created flag was not set to false.');
        $this->assertTrue(isset($json->errorMessages));
        $ems = (array)$json->errorMessages;
        foreach ($expectedErrors as $expectedErrorCode => $expectedErrorMessage) {
            $this->assertArrayHasKey($expectedErrorCode, $ems);
            $this->assertEquals($expectedErrorMessage, $ems[$expectedErrorCode]);
        }
    }

    public function getWithErrors($userId, $expectedErrorCode = 404)
    {
        $response = $this->httpClient->get($this->handlerPath . $userId, [
            'cookies' => $this->cookieJar
        ]);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function deleteUser($userId)
    {
        $response = $this->httpClient->post($this->handlerPath . $userId . '/delete', [
            'cookies' => $this->cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function resetPassword($userId, $token, $password)
    {
        $updateData = [
            'token' => $token,
            'password' => $password
        ];
        $response = $this->httpClient->post($this->handlerPath . $userId . '/password', [
            'json' => $updateData,
            'cookies' => $this->cookieJar
        ]);

        $json = json_decode($response->getBody()->getContents());
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertTrue($json->userPasswordUpdated, 'The userUpdated flag was not set to true.');

        return $json;
    }

}
