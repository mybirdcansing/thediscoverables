<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

class GatewayBase extends TestCase
{
    public $httpClient;
    public $cookieJar;
    
    function __construct($httpClient, $cookieJar)
    {
        $this->httpClient = $httpClient;
        $this->cookieJar = $cookieJar;
    }

}
