<?php

ob_start();

define('ENTRY_POINT', true);
require_once('./api.php');

// Check for empty fields
if(empty($_POST['name'])  		||
   empty($_POST['email']) 		||
   empty($_POST['g-recaptcha-response']) ||
   !filter_var($_POST['email'],FILTER_VALIDATE_EMAIL))
   {
	http_response_code(400);
	echo "No arguments Provided!";
	return false;
   }

if(!(new ReCaptchaVerify())->verify($_POST['g-recaptcha-response'])) {
    http_response_code(412);
    echo "Captcha did not pass";
    return false;
}

$name = $_POST['name'];
$email_address = $_POST['email'];

$from = MAIL_FROM;
$to = MAIL_TO;

// Create the email and send the message
$email_subject = "[Novo voluntario GPUL Labs]  $name";
$email_body = "Quero formar parte do equipo de voluntarios para o proxecto GPUL Labs.\n\n"."Estos son os meus datos:\n\nNome: $name\n\nEmail: $email_address\n";
$headers = "From: $from\n";
$headers .= "Reply-To: $email_address\n";

mail($to,$email_subject,$email_body,$headers);

return true;

?>
