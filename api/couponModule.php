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
  	if(isset($data['isDelete']) && isset($data['couponId'])){
   		$commonReplyId=$data['couponId'];
   		foreach ($commonReplyId as $key => $value) {
   			$data = $value;
   			$stmt = $pdo->prepare("DELETE FROM `couponTable`WHERE `couponId` = '$value'");
	    	$stmt->execute();
   		}
	    echo json_encode(["message"=>"success"]);
	    exit();
  	}
    //查询
    if (isset($data['isGet'])) {
      $stateList=[0=>"未批准",1=>"已批准",2=>"已拒绝",3=>"已使用"];
      $sql .= isset($data['userId'])?"AND `couponTable`.`userId` = '".$data['userId']."'":"";

      $addresslist = array();
      $stmt = $pdo->prepare("SELECT * From `couponTable` 
                              LEFT JOIN  `userTable` ON `userTable`.`userId` = `couponTable`.`userId` WHERE 1 ".$sql." ORDER BY `couponTable`.`couponState`");
      $stmt->execute();
      if($stmt != null){
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          $row["id"]=$row["couponId"];
          $row["couponImageurl"] = $row["couponImage"] != "" ?'http://'.$_SERVER['SERVER_NAME']."/".$row["couponImage"] : "";
          $row["couponStateName"]=$stateList[$row["couponState"]];
          $addresslist[] = $row;
        }
      }else{
          echo json_encode(["message"=>"database error"]);
          exit();
      }
      echo json_encode(["message"=>"success","data"=>$addresslist]);
      exit();
    }
  	//添加/修改
    $couponPrice=$data['couponImage'];
    $userId=$_POST['userId'];
    $couponPrice=$data['couponPrice'];
    $couponState=$data['couponState'];
    $couponId=$data['couponId'];

    if(isset($data['couponId'])){
      $couponCode = $userId.date("hi").$couponId;

      $stmt = $pdo->prepare("UPDATE `couponTable` SET `couponCode`='$couponCode',`couponPrice`='$couponPrice',`couponState`='$couponState' WHERE `couponId` = '$couponId'");
      $stmt->execute();
      if($stmt != null){
        echo json_encode(["message"=>"success"]);
          exit();
      }
    }


	  $date= date('YmdHis');
    if($_FILES['couponImage']['name'] != null){
      $File_type = strrchr($_FILES['couponImage']['name'], '.'); 
      $picture = '../include/pic/couponImage/'.$date.$File_type;
      $picsql .= ",`couponImage`='".$picture."'";
    }

    $stmt = $pdo->prepare("INSERT INTO `couponTable`(`couponImage`,`userId`) VALUES ('$picture','$userId')");
    $stmt->execute();
    if($stmt != null){
      if($_FILES['couponImage']['name'] != null){
        move_uploaded_file($_FILES['couponImage']['tmp_name'], $picture);
      }
      $addresslist = array();
      $stmt = $pdo->prepare("SELECT * From `couponTable` WHERE `userId` = '$userId'");
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
    }
  }




