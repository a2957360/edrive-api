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
  	if(isset($data['isDelete']) && isset($data['rateId'])){
   		$rateId=$data['rateId'];
   		foreach ($rateId as $key => $value) {
   			$data = $value;
   			$stmt = $pdo->prepare("DELETE FROM `rateListTable`WHERE `rateId` = '$value'");
	    	$stmt->execute();
   		}
	    echo json_encode(["message"=>"success"]);
	    exit();
  	}

  	//添加/修改
    $rateId=$_POST['rateId'];
    $rateName=$_POST['rateName'];
    $rateNameEng=$_POST['rateNameEng'];

    if(isset($_POST['rateId']) && $_POST['rateId'] != ""){
      $stmt = $pdo->prepare("UPDATE `rateListTable` SET `rateName`='$rateName',`rateNameEng`='$rateNameEng' WHERE `rateId` = '$rateId'");
      $stmt->execute();
      if($stmt != null){
        echo json_encode(["message"=>"success"]);
        exit();
      }
    }

    $stmt = $pdo->prepare("INSERT INTO `rateListTable`(`rateName`,`rateNameEng`) VALUES ('$rateName','$rateNameEng')");
    $stmt->execute();
    if($stmt != null){

      echo json_encode(["message"=>"success"]);
    }
  }

  if ($_SERVER["REQUEST_METHOD"] == "GET") {

    $addresslist = array();
    $stmt = $pdo->prepare("SELECT * From `rateListTable`");
    $stmt->execute();
    if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        $row["id"]=$row["rateId"];
        $addresslist[] = $row;
      }
    }else{
        echo json_encode(["message"=>"database error"]);
        exit();
    }

    echo json_encode(["message"=>"success","data"=>$addresslist]);
  }


