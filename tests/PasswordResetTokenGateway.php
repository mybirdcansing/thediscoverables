<?php
declare(strict_types=1);
require_once __dir__ . '/GatewayBase.php';

class PasswordResetTokenGateway extends GatewayBase
{
    public $handlerPath = 'requestpasswordreset.php';
    
    public function getToken($user)
    {
        // request the password update email
        return $this->httpClient->post($this->handlerPath, [
            'json' => $user->expose(),
            'cookies' => $this->cookieJar
        ]);
    }

}
