<?php
include("../include/sql.php");
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

http_response_code(200);
header('content-type:application/json;charset=utf8');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

$data = file_get_contents('php://input');
$data = json_decode($data,true);

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (isset($data['isGet'])) {
    $emailData = array();
    $stmt = $pdo->prepare("SELECT * From `emailTable` ORDER BY `createTime`");
    $stmt->execute();
    if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        $row['id'] = $row['emailId'];
        $emailData[] = $row;
      }
    }else{
        echo json_encode(["message"=>"database error"]);
        exit();
    }
    echo json_encode(["message"=>"success","data"=>$emailData]);
    exit();
  }

  if($data['emailTitle'] != null && $data['emailContent'] != null && $data['emailTitle'] != "" && $data['emailContent'] != ""){
    $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
    $title=$data['emailTitle'];
    $content=$data['emailContent'];
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
    //Recipients
    // $mail->setFrom('a2957360@gmail.com', 'E-Drive');
    // $stmt = $pdo->prepare("SELECT * FROM `userTable` 
    //                   LEFT JOIN `reservationTable` ON `userTable`.`userId` = `reservationTable`.`userId`
    //                   WHERE `userTable`.`userId` = '$userId' AND `reservationTable`.`reservationState`='1';");
    // $stmt->execute();
    // if($stmt != null){
    //   while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
    //     $userEmail = $row['userLearningEmail'];
    //     $userName = $row['userName'];
    //     $mail->addAddress($userEmail, $userName);     // Add a recipient
    //   }
    // }
    $mail->setFrom('a2957360@gmail.com', 'E-Drive');
    $mail->addAddress('w2957360@gmail.com', 'guest');     // Add a recipient
    // Attachments
    // $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
    // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
  try {
    //Content
    $mail->isHTML(true);                                  // Set email format to HTML
    // $mail->AddEmbeddedImage('static/img/icon.png','logo');
    $mail->Subject = $title;
    $mail->Body    = $content;


    if($mail->send()){
      $stmt = $pdo->prepare("INSERT INTO `emailTable`(`emailTitle`,`emailContent`) VALUES ('$title','$content')");
      $stmt->execute();
      echo json_encode(["message"=>"success"]);
      exit();
    }
  } catch (Exception $e) {
    echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
    echo json_encode(["message"=>"email error"]);
    exit();
  }
}

}

?>