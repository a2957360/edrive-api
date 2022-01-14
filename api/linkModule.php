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
  	if(isset($data['isDelete']) && isset($data['linkId'])){
   		$linkId=$data['linkId'];
   		foreach ($linkId as $key => $value) {
   			$data = $value;
   			$stmt = $pdo->prepare("DELETE FROM `linkTable`WHERE `linkId` = '$value'");
	    	$stmt->execute();
   		}
	    echo json_encode(["message"=>"success"]);
	    exit();
  	}
  	//添加/修改
	$date= date('YmdHis');
    $linkId=$_POST['linkId'];
    $linkTitle=$_POST['linkTitle'];
    $linkContent=addslashes($_POST['linkContent']);
    $linkType=$_POST['linkType'];
    if($_FILES['linkImage']['name'] != null){
      $File_type = strrchr($_FILES['linkImage']['name'], '.'); 
      $linkImage= '../include/pic/linkimage/'.$date.$userId."0".$File_type;
      $sql = ",`linkImage` = '".$linkImage."'";
    }
    if(isset($linkId) && $linkId !== ""){
	    $stmt = $pdo->prepare("UPDATE `linkTable` SET `linkTitle` = '$linkTitle', `linkContent` = '$linkContent',
	    														`linkType` = '$linkType' ".$sql." WHERE `linkId` = '$linkId'");
	    $stmt->execute();
      if($stmt != null){
        if($_FILES['linkImage']['name'] != null){
          move_uploaded_file($_FILES['linkImage']['tmp_name'], $linkImage);
        }
        echo json_encode(["message"=>"success"]);
        exit();
	    }

    }
    $stmt = $pdo->prepare("INSERT INTO `linkTable`(`linkTitle`,`linkImage`,`linkContent`,`linkType`) 
    												VALUES ('$linkTitle','$linkImage','$linkContent','$linkType')");
    $stmt->execute();
    if($stmt != null){
      if($_FILES['linkImage']['name'] != null){
        move_uploaded_file($_FILES['linkImage']['tmp_name'], $linkImage);
      }
      echo json_encode(["message"=>"success"]);
    }
  }


  if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $typelist = [0=>"增值服务",1=>"常用网站",2=>"广告"];
    $addresslist = array();
    $showList = array();
    $stmt = $pdo->prepare("SELECT * From `linkTable`");
    $stmt->execute();
    if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
      	$row["id"]=$row["linkId"];
        $row["linkImageurl"] = 'http://'.$_SERVER['SERVER_NAME']."/".$row["linkImage"];
        $row["linkTypeName"] = $typelist[$row["linkType"]];
        $addresslist[] = $row;
        $showList[$row["linkType"]][] = $row;
      }
    }else{
        echo json_encode(["message"=>"database error"]);
        exit();
    }

    echo json_encode(["message"=>"success","data"=>$addresslist,"showData"=>$showList]);
  }

