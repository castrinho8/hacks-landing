<?php
// Check for empty fields
if(empty($_POST['name'])  		||
   empty($_POST['email']) 		||
   !filter_var($_POST['email'],FILTER_VALIDATE_EMAIL))
   {
	echo "No arguments Provided!";
	return false;
   }
	
$name = $_POST['name'];
$email_address = $_POST['email'];
	
// Create the email and send the message
$to = 'yourname@yourdomain.com'; // Add your email address inbetween the '' replacing yourname@yourdomain.com - This is where the form will send a message to.
$email_subject = "[Novo voluntario GPUL Labs]  $name";
$email_body = "Quero formar parte do equipo de voluntarios para o proxecto GPUL Labs.\n\n"."Estos son os meus datos:\n\nNome: $name\n\nEmail: $email_address\n";
$headers = "From: spammer@hostname.example\n";
$headers .= "Reply-To: $email_address00";	
mail($to,$email_subject,$email_body,$headers);
return true;			
?>