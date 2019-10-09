<?php
require_once __dir__ . '/../connecters/DataAccess.php';
require_once __dir__ . '/../connecters/UserData.php';
require_once __dir__ . '/../AuthCookie.php';
require_once __dir__ . '/../Configuration.php';
require_once __dir__ . '/../../../vendor/PHPMailer/src/PHPMailer.php';
require_once __dir__ . '/../../../vendor/PHPMailer/src/SMTP.php';
// require '../../../vendor/autoload.php';
// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\SMTP;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


if(!AuthCookie::isValid()) {
    header("'HTTP/1.1 403 Forbidden'");
	echo json_encode(
        array(
        	"authorized" => false, 
        	"passwordResetSent" => false, 
        	"message" => "You do not have permission to be here."
        )
    );
    exit();
}

$user = User::fromJson(file_get_contents('php://input'));
$dbConnection = (new DataAccess())->getConnection();
$userData = new UserData($dbConnection);
$existingUser = $userData->find($user->id);
if (!$existingUser) {
    header('HTTP/1.1 404 Not Found');
	echo json_encode(
        array(
        	"passwordResetSent" => false, 
        	"message" => "The user is not in the database")
    );
    exit();
}

$administrator = $userData->getByUsername(AuthCookie::getUsername());
$token = $userData->insertPasswordResetToken($user, $administrator);
$settings = (new Configuration())->getSettings();
// put a row in a table and send an email

$mail = new PHPMailer\PHPMailer\PHPMailer;
$mail->isSMTP();
$mail->SMTPDebug = PHPMailer\PHPMailer\SMTP::DEBUG_OFF;
$mail->Host = 'smtp.gmail.com';
$mail->Port = 587;
$mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS; //'tls';
$mail->SMTPAuth = true;
$mail->Username = $settings->email->USER;
$mail->Password = $settings->email->PASSWORD;
$mail->setFrom('thediscoverables@gmail.com', 'The Discoverables Support');
$mail->addReplyTo('thediscoverables@gmail.com', 'The Discoverables Support');
$mail->addAddress($user->email, "$user->firstName $user->lastName");
$mail->Subject = 'Administration: The Discoverables';
$link = 'http://' . $settings->host->DOMAIN . '/admin/updatepassword.php?token=' . $token;
$msg = "<p>Click this link to update you're The Discoverables administration site password.</p>";
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
	    	"userId" => $user->id
	    )
	);
}