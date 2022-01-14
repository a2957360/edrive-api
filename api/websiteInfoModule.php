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
  	if(isset($data['isDelete']) && isset($data['infoId'])){
   		$infoId=$data['infoId'];
   		foreach ($infoId as $key => $value) {
   			$data = $value;
   			$stmt = $pdo->prepare("DELETE FROM `websiteInfoTable`WHERE `infoId` = '$value'");
	    	$stmt->execute();
   		}
	    echo json_encode(["message"=>"success"]);
	    exit();
  	}

  	//添加/修改
    $date= date('YmdHis');
    $infoId=$_POST['infoId'];
    $infoName=$_POST['infoName'];
    $infoContent=$_POST['infoContent'];
    $infoImage=$_POST['infoImage'];
    if($_FILES['infoImage']['name'] != null){
      $File_type = strrchr($_FILES['infoImage']['name'], '.'); 
      $infoImage= '../include/pic/websiteImage/'.$date."0".$File_type;
      $sql = ",`infoImage` = '".$infoImage."'";
    }
    if(isset($_POST['infoId']) && $_POST['infoId'] != ""){
      $stmt = $pdo->prepare("UPDATE `websiteInfoTable` SET `infoName`='$infoName',`infoContent`='$infoContent'".$sql." WHERE `infoId` = '$infoId'");
      $stmt->execute();
      if($stmt != null){
        if($_FILES['infoImage']['name'] != null){
          move_uploaded_file($_FILES['infoImage']['tmp_name'], $infoImage);
        }
        echo json_encode(["message"=>"success"]);
        exit();
      }
    }

    $stmt = $pdo->prepare("INSERT INTO `websiteInfoTable`(`infoName`,`infoContent`,`infoImage`) VALUES ('$infoName','$infoContent','$infoImage')");
    $stmt->execute();
    if($stmt != null){
      if($_FILES['infoImage']['name'] != null){
        move_uploaded_file($_FILES['infoImage']['tmp_name'], $infoImage);
      }
      echo json_encode(["message"=>"success"]);
    }
  }

  if ($_SERVER["REQUEST_METHOD"] == "GET") {

    $addresslist = array();
    $stmt = $pdo->prepare("SELECT * From `websiteInfoTable`");
    $stmt->execute();
    if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        $row["id"]=$row["infoId"];
        $row["infoImageurl"] = 'http://'.$_SERVER['SERVER_NAME']."/".$row["infoImage"];
        $addresslist[] = $row;
        $showlist[$row["infoName"]] = $row;
      }
    }else{
        echo json_encode(["message"=>"database error"]);
        exit();
    }

    echo json_encode(["message"=>"success","data"=>$addresslist,"usedata"=>$showlist]);
  }


