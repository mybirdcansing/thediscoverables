<?php
require_once __dir__ . '/../connecters/DataAccess.php';
require_once __dir__ . '/../connecters/UserData.php';
require_once __dir__ . '/../AuthCookie.php';
require_once __dir__ . '/../Configuration.php';
require_once __dir__ . '/../../../vendor/PHPMailer/src/PHPMailer.php';
require_once __dir__ . '/../../../vendor/PHPMailer/src/SMTP.php';
// require_once __dir__ . '/../../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$inputJsonObj = json_decode(file_get_contents('php://input'));

$userData = new UserData((new DataAccess())->getConnection());

$user = 0;

if ((!isset($inputJsonObj->username) || strlen($inputJsonObj->username) == 0) 
	&& (!isset($inputJsonObj->email) || strlen($inputJsonObj->email) == 0)) {
	header('HTTP/1.1 400 Bad Request');
	echo json_encode(
        array(
        	"passwordResetSent" => false, 
        	"errorMessages" => [
                    ENTER_EMAIL_OR_USERNAME_CODE => ENTER_EMAIL_OR_USERNAME_MESSAGE
                ])
    );
    exit();
}

if (isset($inputJsonObj->username)) {
	$user = $userData->getByUsername($inputJsonObj->username);
}

if (!$user && isset($inputJsonObj->email)) {
	$user = $userData->getByEmail($inputJsonObj->email);
}

if (!$user) {
    header('HTTP/1.1 404 Not Found');
	echo json_encode(
        array(
        	"passwordResetSent" => false, 
        	"errorMessages" => [
                    USER_NOT_FOUND_CODE => USER_NOT_FOUND_MESSAGE
                ])
    );
    exit();
}

// if there is an authenticated user, they are using the admin page
// to request the reset
if(AuthCookie::isValid()) {
	$requester = $userData->getByUsername(AuthCookie::getUsername());
} else {
	$requester = $user;
}

$token = $userData->insertPasswordResetToken($user, $requester);
$settings = (new Configuration())->getSettings();
// put a row in a table and send an email

$mail = new PHPMailer;
$mail->isSMTP();
$mail->SMTPDebug = SMTP::DEBUG_OFF;
$mail->Host = 'smtp.gmail.com';
$mail->Port = 587;
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->SMTPAuth = true;
$mail->Username = $settings->email->USER;
$mail->Password = $settings->email->PASSWORD;
$mail->setFrom($settings->email->FROM_ADDRESS, $settings->email->FROM_NAME);
$mail->addReplyTo($settings->email->FROM_ADDRESS, $settings->email->FROM_NAME);
$mail->addAddress($user->email, "$user->firstName $user->lastName");
$mail->Subject = 'Administration: The Discoverables';
$link = 'http://' . $settings->host->DOMAIN . '/admin/updatepassword.php?token=' . $token;
$msg = "<p>Click this <a href=\"$link\">link</a> to update your password for The Discoverables administration site.</p>";
$msg = $msg . "<a href='" . $link . "'>Update Password</a>";
$msg = wordwrap($msg, 70);
$mail->msgHTML($msg, __DIR__);
$mail->AltBody = $msg;

if (!$mail->send()) {
	header('HTTP/1.1 500 Internal Server Error');
	echo json_encode(
	    array(
	    	"passwordResetSent" => false, 
	    	"userId" => $user->id
	    )
	);
    error_log("Mailer Error: " . $mail->ErrorInfo);
} else {
	header('HTTP/1.1 200 OK');
	echo json_encode(
	    array(
	    	"passwordResetSent" => true, 
	    	"user" => $user->expose()
	    )
	);
}