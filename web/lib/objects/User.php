<?php
declare(strict_types=1);
require_once __dir__ . '/JsonConvertible.php';

class User extends JsonConvertible {

    public $id;
    public $username;
    public $firstName;
    public $lastName;
    public $email;
    public $password;
    public $statusId;

    function __construct() {
    }
}
