#!/usr/local/php5-7.3.8-20190811-205217/bin/php
<?php
// You may need to change your path to php if this script doesn't work.
// This is a common path: /usr/local/bin/php
// You can also run 'which php' to find your path.

// You may also need to run 'chmod +x adminuser.php' to make this script executable.

require_once __dir__ . '/../web/lib/connecters/DataAccess.php';
require_once __dir__ . '/../web/lib/connecters/UserData.php';
require_once __dir__ . '/../web/lib/objects/User.php';
require_once __dir__ . '/../web/lib/objects/Guid.php';
require_once __dir__ . '/../web/lib/handlers/user/UserValidation.php';

$configPath = __dir__ . '/config.json';

function help()
{
    return '
Makes an initial user for the admin tool.    
Usage:
  adminuser.php

Options;
-h, --help      For help

Before running this script you must populate values in adminuser_config.json.

{
    "dataAccess" : {
        "DB_HOST": "",
        "DB_USER": "",
        "DB_PASSWORD": "",
        "DB_DATABASE": "thediscoverables"
    },
    "user" : {
        "username": "",
        "firstName": "",
        "lastName": "",
        "email": "",
        "password" : ""
    }
}

\n';
  
}
if ($argc > 1) {
    if ($argv[1] == '--help' || $argv[1] == '-h') {
        print help();
        exit();
    }
}

$dataAccess;
try {
    $dataAccess = new DataAccess($configPath);
} catch(Exception $e) {
    print "
    There was a problem connecting to the database.
    Make sure your connection settings are correct in adminuser_config.json
    \n";
    exit();
}
$dbConnection = $dataAccess->getConnection();
$userData = new UserData($dbConnection);
$config = new Configuration($configPath);
$settings = $config->getSettings();

$user = new User();
$user->id = Guid::create();
$user->username = $settings->user->username;
$user->firstName = $settings->user->firstName;
$user->lastName = $settings->user->lastName;
$user->email = $settings->user->email;
$user->password = $settings->user->password;
$user->statusId = 1;

$validationIssues = UserValidation::validationIssues($user, true);
$errorMsgHeader = "Please update adminuser_config.json file. \n";
if ((bool)$validationIssues) {
    $values = array_values($validationIssues);
    $msg = $errorMsgHeader;
    for ($j = 0; $j < count($values); ++$j) {
        $msg .= "* $values[$j]\n";
    }
    print $msg;
    exit();
}

try {
    $userData->insert($user, $user);
    print "User has been created \n";
} catch (DuplicateUsernameException | DuplicateEmailException $e) {
    print $errorMsgHeader . $e->getMessage() . "\n";
}

?>