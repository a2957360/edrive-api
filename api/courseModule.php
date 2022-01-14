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
  	if(isset($data['isDelete']) && isset($data['courseId'])){
   		$courseId=$data['courseId'];
   		foreach ($courseId as $key => $value) {
   			$data = $value;
   			$stmt = $pdo->prepare("DELETE FROM `courseTable`WHERE `courseId` = '$value'");
	    	$stmt->execute();
   		}
	    echo json_encode(["message"=>"success"]);
	    exit();
  	}
  	//添加/修改
    $courseId=$_POST['courseId'];
    $courseName=$_POST['courseName'];
    $courseTarget=$_POST['courseTarget'];
    $courseExamLevel=$_POST['courseExamLevel'];
    $carTime=$_POST['carTime'];    
    $courseTime=$_POST['courseTime'];
    $homeTime=$_POST['homeTime'];
    $isBDE=$_POST['isBDE'];
    $courseExamTime=$_POST['courseExamTime'];
    $coursePrice=$_POST['coursePrice'];
    $courseArea=$_POST['courseArea'];

    if(isset($courseId) && $courseId !== ""){
	    $stmt = $pdo->prepare("UPDATE `courseTable` SET `courseName` = '$courseName', `courseTarget` = '$courseTarget' , `courseExamLevel` = '$courseExamLevel' , `carTime` = '$carTime' 
                            , `courseTime` = '$courseTime' , `homeTime` = '$homeTime' , `isBDE` = '$isBDE' , `courseExamTime` = '$courseExamTime' , `coursePrice` = '$coursePrice', `courseArea` = '$courseArea' 
                            WHERE `courseId` = '$courseId'");
	    $stmt->execute();
      if($stmt != null){
        echo json_encode(["message"=>"success"]);
	    }
	    exit();
    }
    $stmt = $pdo->prepare("INSERT INTO `courseTable`(`courseName`,`courseTarget`,`courseExamLevel`,`carTime`,`courseTime`,`homeTime`,`isBDE`,`courseExamTime`,`coursePrice`,`courseArea`) 
                                              VALUES ('$courseName','$courseTarget','$courseExamLevel','$carTime','$courseTime','$homeTime','$isBDE','$courseExamTime','$coursePrice','$courseArea')");
    $stmt->execute();
    if($stmt != null){
      echo json_encode(["message"=>"success"]);
    }
  }


  if ($_SERVER["REQUEST_METHOD"] == "GET") {

    $addresslist = array();
    $stmt = $pdo->prepare("SELECT * From `courseTable`");
    $stmt->execute();
    $sum = ["iconsum"=>0,"normalsum"=>0];

    $bdecourse = [0=>"没有",1=>"有"];
    // $courseArealist = [0=>"区域一",1=>"区域二",2=>"区域三"];

    $questionSectionList = array();
    if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
      	$row["id"]=$row["courseId"];
        $row["isBDEName"]=$bdecourse[$row["isBDE"]];
        // $row["courseArea"]=$courseArealist[$row["courseArea"]];
        $addresslist[] = $row;
      }
    }else{
        echo json_encode(["message"=>"database error"]);
        exit();
    }

    echo json_encode(["message"=>"success","data"=>$addresslist]);
  }

