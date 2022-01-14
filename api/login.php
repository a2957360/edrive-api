<?php
  include("include/sql.php");
	http_response_code(200);
	header('content-type:application/json;charset=utf8');
	header('Access-Control-Allow-Origin: *');
	header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
	header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = file_get_contents('php://input');
    $data = json_decode($data,true);

    $userPassword   =  password_hash(isset($_POST['userPassword'])?$_POST['userPassword']:"", PASSWORD_DEFAULT);
    $userPhone      =  isset($_POST['userPhone'])?$_POST['userPhone']:"";

    $stmt = $pdo->prepare("SELECT * FROM `userTable` WHERE `userPhone` = '$userPhone'");
    $stmt->execute();
    if($stmt != null){
      if($_FILES['userPic']['name'] != null){
        if(password_verify($userPassword,$row['userPassword'])){
          $message=["message"=>"success"];
          echo json_encode($message);
        }else{
          $message=["message"=>"fail"];
          echo json_encode($message);
        }
      }

      }else{
        echo json_encode(["message"=>"database error"]);
        exit();
      }

  }
?>