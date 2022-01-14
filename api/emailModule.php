<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

class emailModel
{

	function sendemail($title,$content){
		$mail = new PHPMailer(true);                              // Passing `true` enables exceptions
	    //Server settings
	    $mail->SMTPDebug = 0;                                 // Enable verbose debug output
	    $mail->isSMTP();                                      // Set mailer to use SMTP
	    $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
	    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
	    $mail->SMTPAuth = true;                               // Enable SMTP authentication
	    $mail->Username = "a2957360@gmail.com";                 // SMTP username
	    $mail->Password = 'uclmjwfqwxrljuif';                           // SMTP password
	    $mail->SMTPSecure = 'tls';                     // Enable TLS encryption, `ssl` also accepted
	    $mail->Port = 587;                                    // TCP port to connect to
	    $mail->CharSet="UTF-8";
	    $mail->setFrom('a2957360@gmail.com', 'E-Drive');
	    $mail->addAddress('w2957360@gmail.com', 'guest'); 
	      try {
	    //Content
	    $mail->isHTML(true);                                  // Set email format to HTML
	    // $mail->AddEmbeddedImage('static/img/icon.png','logo');
	    $mail->Subject = $title;
	    $mail->Body    = $content;

	    if($mail->send()){

	    }
	  } catch (Exception $e) {
	    echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;

	  }
	}
}
