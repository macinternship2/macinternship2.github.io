<?php namespace App\Libraries;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Log;

class Emailer 
{
	// Used in SignUpController
	public static function send($to, $subject, $msg, $isUsingHTML = false)
	{
		if (config('app.gmail_sender') === '') {
			// failed because email is not configured.
			return false;
		}

		//Create a new PHPMailer instance
		$mail = new PHPMailer;
		//Tell PHPMailer to use SMTP
		$mail->isSMTP();
		//Enable SMTP debugging
		// 0 = off (for production use)
		// 1 = client messages
		// 2 = client and server messages
		$mail->SMTPDebug = config('gmail_debug_level');
		//Set the hostname of the mail server
		$mail->Host = 'smtp.gmail.com';
		// use
		// $mail->Host = gethostbyname('smtp.gmail.com');
		// if your network does not support SMTP over IPv6
		//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
		$mail->Port = 587;
		//Set the encryption system to use - ssl (deprecated) or tls
		$mail->SMTPSecure = 'tls';
		//Whether to use SMTP authentication
		$mail->SMTPAuth = true;

		//Username to use for SMTP authentication - use full email address for gmail
		$mail->Username = config('app.gmail_sender');
		//Password to use for SMTP authentication
		$mail->Password = config('app.gmail_password');
		//Set who the message is to be sent from
		$mail->setFrom(config('app.gmail_from_address'), config('app.gmail_from_name'));
		//Set an alternative reply-to address
		//$mail->addReplyTo('replyto@example.com', 'First Last');
		//Set who the message is to be sent to
		$mail->addAddress($to, '');
		//Set the subject line
		$mail->Subject = $subject;
		//Read an HTML message body from an external file, convert referenced images to embedded,
		//convert HTML into a basic plain-text alternative body
		if ($isUsingHTML) {
			$mail->msgHTML($msg);
			$mail->IsHTML(true);
			$mail->CharSet = "text/html; charset=UTF-8;";
		}
		else {
			$mail->Body = $msg;
			$mail->IsHTML(false);
			$mail->ContentType = 'text/plain'; 
		}
		//Replace the plain text body with one created manually
		$mail->AltBody = 'This is a plain-text message body';
		//Attach an image file
		//$mail->addAttachment('images/phpmailer_mini.png');
		//send the message, check for errors
		if (!$mail->send()) {
			Log::debug("Mailer Error: " . $mail->ErrorInfo);
			return false;
		} else {
			Log::debug('Successfully sent email with subject: '. $subject .
				', body = '.$msg);
			return true;
		}
	}

}
