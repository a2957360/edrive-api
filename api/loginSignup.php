<?php
  include("../include/sql.php");
  require_once "sendemail.php";

  http_response_code(200);
  header('content-type:application/json;charset=utf8');
  header('Access-Control-Allow-Origin: *');
  header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

  $data = file_get_contents('php://input');
  $data = json_decode($data,true);
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if(isset($data['isSignup']) && $data['isSignup'] == 1){
      $userName       =  isset($data['userName'])?$data['userName']:"";
      $userEmail      =  isset($data['userEmail'])?$data['userEmail']:"";
      $userPhone      =  isset($data['userPhone'])?$data['userPhone']:"";
      $userBelong     =  isset($data['userBelong'])?$data['userBelong']:"";
      $userRole     =  isset($data['userRole'])?$data['userRole']:0;
      $userPassword   =  password_hash(isset($data['userPassword'])?$data['userPassword']:"", PASSWORD_DEFAULT);

      $stmt = $pdo->prepare("INSERT INTO `userTable`(`userName`,`userNumber`, `userPassword`,`userEmail`,`userPhone`,`userBelong`,`userRole`) 
                              VALUES ('$userName','$userNumber', '$userPassword','$userEmail','$userPhone','$userBelong','$userRole')");
      $stmt->execute();
      if($stmt != null){
        $userId =  $pdo->lastInsertId();
        if(!isset($userId) || $userId == 0){
          $message=["message"=>"email error"];
          echo json_encode($message);
          exit();
        }
        $data['userId'] = $userId;
        $userNumber = "ED".date("y").str_pad($userId,4,"0",STR_PAD_LEFT);
        $stmt = $pdo->prepare("UPDATE `userTable` SET `userNumber` = '$userNumber' WHERE `userId`= '$userId'");
        $stmt->execute();
        $message=["message"=>"success","data"=>$data];
        echo json_encode($message);
        exit();
      }else{
        echo json_encode(["message"=>"database error"]);
        exit();
      }
    }

    if(isset($data['isCoachSignup']) && $data['isCoachSignup'] == 1){
      $userName       =  isset($data['userName'])?$data['userName']:"";
      $userEmail      =  isset($data['userEmail'])?$data['userEmail']:"";
      $userPhone      =  isset($data['userPhone'])?$data['userPhone']:"";
      $userBelong     =  isset($data['userBelong'])?$data['userBelong']:"";
      $userPassword   =  password_hash(isset($data['userPassword'])?$data['userPassword']:"", PASSWORD_DEFAULT);

      $stmt = $pdo->prepare("INSERT INTO `userTable`(`userName`,`userNumber`, `userPassword`,`userEmail`,`userPhone`,`userRole`) 
                              VALUES ('$userName','$userNumber', '$userPassword','$userEmail','$userPhone','1')");
      $stmt->execute();
      if($stmt != null){
        $data['userId'] = $pdo->lastInsertId();
        if($data['userId'] == "0"){
          echo json_encode(["message"=>"fail","data"=>$data]);
          exit();
        }
        $userId =  $pdo->lastInsertId();
        $userNumber = "ED".date("y").str_pad($userId,4,"0",STR_PAD_LEFT);
        $stmt = $pdo->prepare("UPDATE `userTable` SET `userNumber` = '$userNumber' WHERE `userId`= '$userId'");
        $stmt->execute();
        $message=["message"=>"coach success","data"=>$data];
        echo json_encode($message);
      }else{
        echo json_encode(["message"=>"database error"]);
        exit();
      }
    }

    if(isset($data['isSignupCoach']) && $data['isSignupCoach'] == 1){
      $userName       =  isset($data['userName'])?$data['userName']:"";
      $userRole       =  isset($data['userRole'])?$data['userRole']:"";
      $userPassword   =  password_hash("123456", PASSWORD_DEFAULT);

      $stmt = $pdo->prepare("INSERT INTO `userTable`(`userName`,`userPassword`,`userRole`) 
                              VALUES ('$userName','$userPassword','$userRole')");
      $stmt->execute();
      if($stmt != null){
        $data['userId'] = $pdo->lastInsertId();
        if($data['userId'] == "0"){
          echo json_encode(["message"=>"fail"]);
          exit();
        }
        $userId =  $pdo->lastInsertId();
        $userNumber = "ED".date("y").str_pad($userId,4,"0",STR_PAD_LEFT);
        $userEmail = date("y").str_pad($userId,4,"0",STR_PAD_LEFT)."@gmail.com";
        $stmt = $pdo->prepare("UPDATE `userTable` SET `userNumber` = '$userNumber',`userEmail` = '$userEmail' WHERE `userId`= '$userId'");
        $stmt->execute();
        $message=["message"=>"success","data"=>$data];
        echo json_encode($message);
      }else{
        echo json_encode(["message"=>"database error"]);
        exit();
      }
    }
    
    if(isset($data['isLogin']) && $data['isLogin'] == 1){
      $userEmail       =  isset($data['userEmail'])?$data['userEmail']:"";
      $userPassword    =  isset($data['userPassword'])?$data['userPassword']:"";
      $userRole        =  isset($data['userRole'])?$data['userRole']:"";

      $stmt = $pdo->prepare("SELECT * FROM `userTable` WHERE `userEmail` = '$userEmail' AND `userRole` = '$userRole';");
      $stmt->execute();
      if($stmt != null){
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $userId = $row['userId'];
        if($userId == null){
          echo json_encode(["message"=>"nouser"]);
          exit();
        }
        if($row['userState'] == 0 && $userRole == 1){
          echo json_encode(["message"=>"notallow"]);
          exit();
        }
        if(password_verify($userPassword,$row['userPassword'])){
          echo json_encode(["message"=>"success","data"=>$row]);
          exit();
        }else{
          echo json_encode(["message"=>"wrongpassword"]);
          exit();
        }
      }

    }
    if(isset($data['isForget']) && $data['isForget'] == 1){
      $userEmail = isset($data['userEmail'])?$data['userEmail']:"";

      $stmt = $pdo->prepare("SELECT count(*) AS `num` FROM `userTable` WHERE `userEmail` = '$userEmail'");
      $stmt->execute();
      if($stmt != null){
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row['num'] > 0){
            $tmpuserPassword = substr(md5(time()), 0, 6);
            $userPassword   =  password_hash($tmpuserPassword, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("UPDATE `userTable` SET `userPassword` = '$userPassword' WHERE `userEmail` = '$userEmail'");
            $stmt->execute();
            $mail->addAddress($userEmail, 'E-drive');     // Add a recipient
            try {
              //Content
              $mail->isHTML(true);                                  // Set email format to HTML
              // $mail->AddEmbeddedImage('static/img/icon.png','logo');
              $mail->Subject = 'Edrive 密码找回';
              $mail->Body    = "您的新密码是".$tmpuserPassword."。 请登录后去我的E-Drive修改密码";

              if($mail->send()){
                echo json_encode(["message"=>"send"]);
                exit();
              }
              } catch (Exception $e) {
                  echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
            }
        }else{
          echo json_encode(["message"=>"nouser"]);
          exit();
        }
      }else{
        echo json_encode(["message"=>"database error"]);
        exit();
      }
    }



  }
?>