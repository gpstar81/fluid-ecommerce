<?php
// fluid.sendmail.php
// Michael Rajotte - 2017 Juliet
// Send a email

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . "/3rd-party-src/phpmailer-api/vendor/autoload.php";
require_once (__DIR__ . "/../fluid.db.php");

$f_data = json_decode(base64_decode(stripslashes($argv[1])));

if(isset($f_data->multiple_emails)) {
	foreach($f_data->multiple_emails as $emails) {
		php_send_email($f_data->from, $emails->u_email, base64_decode($f_data->subject), base64_decode($f_data->message), $f_data->html_email, $f_data->attachments);
		sleep(5);
	}
}
else {
	php_send_email($f_data->from, $f_data->to, $f_data->subject, $f_data->message);
}

function php_send_email($from, $to, $subject, $message, $html_email = TRUE, $attachments = NULL) {
	$mail = new PHPMailer(true);

	try {
		//$mail->SMTPDebug = 2;
		$mail->isSMTP();	// Set mailer to use SMTP
		$mail->Host = FLUID_EMAIL_SERVER;	// Specify main and backup SMTP servers
		$mail->SMTPAuth = true;	// Enable SMTP authentication
		$mail->Username = FLUID_EMAIL;	// SMTP username
		$mail->Password = FLUID_EMAIL_PASSWORD;	// SMTP password
		$mail->SMTPSecure = FLUID_EMAIL_ENCRYPTION_METHOD;	// Enable TLS encryption, `ssl` also accepted
		$mail->Port = FLUID_EMAIL_PORT;	// TCP port to connect to

		$mail->SMTPOptions = array(
		    'ssl' => array(
		        'verify_peer' => false,
		        'verify_peer_name' => false,
		        'allow_self_signed' => true
		    )
		);
		
		//Recipients
		$mail->setFrom(FLUID_EMAIL, "Leo's Camera Supply");
		$mail->addAddress($to);
		$mail->addReplyTo(FLUID_EMAIL, "Leo's Camera Supply");
		//$mail->SMTPDebug = true;

		//Attachments
		if(isset($attachments)) {
			foreach($attachments as $attachment) {
				$mail->addAttachment(FOLDER_ROOT . "uploads/" . $attachment); // Add attachments
			}
			//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
		}

		if($html_email == TRUE) {
			$mail->isHTML(true);
			$mail->Body = $message . "<br>" . EMAIL_FOOTER;
		}

		$mail->Subject = $subject;

		$message_alt = str_replace("<br>", "\r\n", $message);
		$message_alt = str_replace("<br/>", "\r\n", $message_alt);
		$message_alt = str_replace("<br />", "\r\n", $message_alt);
		$message_alt = str_replace("<p>", "\r\n", $message_alt);
		$message_alt = str_replace("</p>", "\r\n", $message_alt);

		$message_alt = strip_tags($message_alt);

		$mail->AltBody = $message_alt . "\r\n" . EMAIL_FOOTER_RAW;

		$mail->send();

		// --> Now lets save a copy to our sent folder.
        $message = $mail->getSentMIMEMessage();
        $path = FLUID_EMAIL_SENT_FOLDER;

        $imapStream = imap_open("{" . $mail->Host . "}" . $path , $mail->Username, $mail->Password);
        imap_append($imapStream, "{" . $mail->Host . "}" . $path, $message);
        imap_close($imapStream);
	}
	catch (Exception $err) {
		echo 'Mailer Error: ' . $mail->ErrorInfo;
	}
}
?>
