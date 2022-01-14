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

    $userId               =  isset($_POST['userId'])?$_POST['userId']:"";
    $translateUserName    =  isset($_POST['translateUserName'])?$_POST['translateUserName']:"";
    $translateUserPhone   =  isset($_POST['translateUserPhone'])?$_POST['translateUserPhone']:"";

    $date= date('YmdHis');
    $picsql = "";

    if($_FILES['translatePic']['name'] != null){
      $File_type = strrchr($_FILES['translatePic']['name'], '.'); 
      $translatePic = 'include/pic/'.$userPhone."/".$date.$File_type;
      $picsql .= "`translatePic`='".$picture."'";
    }else{
      $message=["message"=>"fail"];
      echo json_encode($message);
      exit();
    }
    $stmt = $pdo->prepare("INSERT INTO `translateTable`(`userId`, `translatePic`, `translateUserName`, `translateUserPhone`) 
    									           VALUES ('$userId', '$translatePic', '$translateUserName', '$translateUserPhone')");
    $stmt->execute();
    if($stmt != null){
      if($_FILES['userPic']['name'] != null){
        if (!is_dir('include/pic/'.$userPhone)) {
          mkdir('include/pic/'.$userPhone);
        }
        move_uploaded_file($_FILES['translatePic']['tmp_name'], $translatePic);
      }
      $message=["message"=>"success"];
      echo json_encode($message);
      }else{
        echo json_encode(["message"=>"database error"]);
        exit();
      }   

  }
?>