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
  	if(isset($data['isDelete']) && isset($data['questionId'])){
   		$questionId=$data['questionId'];
   		foreach ($questionId as $key => $value) {
   			$data = $value;
   			$stmt = $pdo->prepare("DELETE FROM `questionTable`WHERE `questionId` = '$value'");
	    	$stmt->execute();
   		}
	    echo json_encode(["message"=>$data]);
	    exit();
  	}
  	//添加
	$date= date('YmdHis');

    $questionId=$_POST['questionId'];
    $questionTitle=$_POST['questionTitle'];
    $questionTip=$_POST['questionTip'];
    $answer1=$_POST['answer1'];
    $answer2=$_POST['answer2'];
    $answer3=$_POST['answer3'];
    $answer4=$_POST['answer4'];
    $rightAnswer=$_POST['rightAnswer'];
    $questionType=$_POST['questionType'];
    $picture = "";
    if($_FILES['questionImage']['name'] != null){
      $File_type = strrchr($_FILES['questionImage']['name'], '.'); 
      $picture = '../include/pic/questionpic/'.$date.$File_type;
      $picsql .= ",`questionImage`='".$picture."'";
    }
    if(isset($questionId) && $questionId !== ""){
	    $stmt = $pdo->prepare("UPDATE `questionTable` SET `questionTitle` = '$questionTitle'".$picsql.", `questionTip` = '$questionTip', `answer1` = '$answer1',
	    				 `answer2` = '$answer2', `answer3` = '$answer3', `answer4` = '$answer4', `rightAnswer` = '$rightAnswer', `questionType` = '$questionType' WHERE `questionId` = '$questionId'");
	    $stmt->execute();
        if($stmt != null){
	      if($_FILES['questionImage']['name'] != null){
	        move_uploaded_file($_FILES['questionImage']['tmp_name'], $picture);
	      }
	    }
	    echo json_encode(["message"=>"success"]);
	    exit();
    }
    $stmt = $pdo->prepare("INSERT INTO `questionTable`(`questionTitle`,`questionImage`, `questionTip`, `answer1`, `answer2`, `answer3`, `answer4`, `rightAnswer`, `questionType`)  
    						            VALUES ('$questionTitle','$picture','$questionTip','$answer1','$answer2','$answer3','$answer4','$rightAnswer','$questionType')");
    $stmt->execute();

    if($stmt != null){
      if($_FILES['questionImage']['name'] != null){
        move_uploaded_file($_FILES['questionImage']['tmp_name'], $picture);
      }
    }
    echo json_encode(["message"=>"success"]);
    // if($stmt != null){
    //   $lasid = $pdo->lastinsertid();
    // 	$message=["message"=>"success"];
    //   $data['orderState'] = "待接单";
    //   $data['orderPrice'] = $orderPrice;
    //   $data['orderTax'] = $orderTax;
    //   $data['orderId'] = $lasid;
    //   $message["data"] = $data;
    // 	echo json_encode($message);
    // }else{
    //     echo json_encode(["message"=>"database error"]);
    //     exit();
    // } 
  }


  if ($_SERVER["REQUEST_METHOD"] == "GET") {

    if(isset($_GET['exam']) && $_GET['exam'] ==1){
      $stmt = $pdo->prepare("SELECT * FROM `questionTable` WHERE `questionType` = 0 ORDER BY RAND() LIMIT 20");
      $stmt->execute();
      if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          $row['userAnswer'] = "";
          $row["questionImage"] = $row["questionImage"] != "" ?'http://'.$_SERVER['SERVER_NAME']."/".$row["questionImage"] : "";
          $addresslist["icon"][] = $row;
        }
      }
      $stmt = $pdo->prepare("SELECT * FROM `questionTable` WHERE `questionType` = 1 ORDER BY RAND() LIMIT 20");
      $stmt->execute();
      if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          $row['userAnswer'] = "";
          $addresslist["normal"][] = $row;
        }
      }
      echo json_encode(["message"=>"success","data"=>$addresslist]);
      exit();
    }
    $questionTypeList = [0=>"标识题",1=>"理论题"];

    $addresslist = array();
    $stmt = $pdo->prepare("SELECT * From `questionTable`");
    $stmt->execute();
    $sum = ["iconsum"=>0,"normalsum"=>0];

    //题库计算
    $questionPerSection=40;
    $icountnum=0;
    $isectionnum=1;
    $ncountnum=0;
    $nsectionnum=1;
    $questionSectionList = array();
    $tmpexamList = array();
    $examList = array();
    $iconidList = array();
    $normalidList = array();
    if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
      	$row["id"]=$row["questionId"];
        $row["questionTypeName"]=$questionTypeList[$row["questionType"]];
      	$row["questionImage"] = $row["questionImage"] != "" ?'http://'.$_SERVER['SERVER_NAME']."/".$row["questionImage"] : "";
        $addresslist[] = $row;
        $questionnum = count($addresslist) - 1;
        if($row["questionType"] == 0){
          $sum["iconsum"]++;
          $iconidList[$questionnum]=$questionnum;
          $questionSectionList["icon"][$isectionnum][$icountnum] = ["questionId"=>$questionnum,"rightAnswer"=>$row["rightAnswer"],"userAnswer"=>""];
            $icountnum++;
          if($icountnum < $questionPerSection){
          }else{
            $icountnum=0;
            $isectionnum++;
          }
        }else{
          $sum["normalsum"]++;
          $normalidList[$questionnum]=$questionnum;
          $questionSectionList["normal"][$nsectionnum][$ncountnum] = ["questionId"=>$questionnum,"rightAnswer"=>$row["rightAnswer"],"userAnswer"=>""];
            $ncountnum++;
          if($ncountnum < $questionPerSection){
          }else{
            $ncountnum=0;
            $nsectionnum++;
          }
        }
      }
    }else{
        echo json_encode(["message"=>"database error"]);
        exit();
    }
    $tmpexamList = array_merge(array_rand($iconidList,20), array_rand($normalidList,20));
    foreach ($tmpexamList as $key => $value) {
      $examList[] = $addresslist[$value];
    }

    echo json_encode(["message"=>"success","data"=>$addresslist,"number"=>$sum,"questionlist"=>$questionSectionList]);
  }

