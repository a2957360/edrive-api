<?php
  include("../include/sql.php");
  http_response_code(200);
  header('content-type:application/json;charset=utf8');
  header('Access-Control-Allow-Origin: *');
  header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

  $data = file_get_contents('php://input');
  $data = json_decode($data,true);

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
  	//删除
  	if(isset($data['isDelete']) && isset($data['messageId'])){
   		$messageId=$data['messageId'];
   		foreach ($messageId as $key => $value) {
   			$data = $value;
   			$stmt = $pdo->prepare("DELETE FROM `messageTable`WHERE `messageId` = '$value'");
	    	$stmt->execute();
   		}
	    echo json_encode(["message"=>"success"]);
	    exit();
  	}
    //查询
    if(isset($data['isGet']) && isset($data['userId'])){
      $userId=$data['userId'];
      $addresslist = array();
      $stmt = $pdo->prepare("SELECT * From `messageTable` WHERE `userId` = '$userId' ORDER BY `uploadTime` DESC");
      $stmt->execute();
      if($stmt != null){
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          $addresslist[] = $row;
        }
      }else{
          echo json_encode(["message"=>"database error"]);
          exit();
      }
      echo json_encode(["message"=>"success","data"=>$addresslist]);
      exit();
    }
  	// //添加/修改
	  // $date= date('YmdHis');
   //  $userId=$data['userId'];
   //  $messageContent=$data['messageContent'];

   //  $stmt = $pdo->prepare("INSERT INTO `messageTable`(`userId`,`messageContent`) VALUES ('$userId','$messageContent')");
   //  $stmt->execute();
   //  if($stmt != null){
   //    echo json_encode(["message"=>"success"]);
   //  }
  }

