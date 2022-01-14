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
  	if(isset($data['isDelete']) && isset($data['videoId'])){
   		$videoId=$data['videoId'];
   		foreach ($videoId as $key => $value) {
   			$data = $value;
   			$stmt = $pdo->prepare("DELETE FROM `videoTable`WHERE `videoId` = '$value'");
	    	$stmt->execute();
   		}
	    echo json_encode(["message"=>"success"]);
	    exit();
  	}
  	//添加/修改
	  $date= date('YmdHis');
    $videoId=$_POST['videoId'];
    $videoTitle=$_POST['videoTitle'];
    $videoContent=addslashes($_POST['videoContent']);
    $videotype=$_POST['videotype'];

    if(isset($videoId) && $videoId !== ""){
	    $stmt = $pdo->prepare("UPDATE `videoTable` SET `videoTitle` = '$videoTitle', `videoContent` = '$videoContent',
	    														`videotype` = '$videotype' WHERE `videoId` = '$videoId'");
	    $stmt->execute();
      if($stmt != null){
        echo json_encode(["message"=>"success"]);
        exit();
	    }

    }
    $stmt = $pdo->prepare("INSERT INTO `videoTable`(`videoTitle`,`videoContent`,`videotype`) 
    												VALUES ('$videoTitle','$videoContent','$videotype')");
    $stmt->execute();
    if($stmt != null){
      echo json_encode(["message"=>"success"]);
    }
  }


  if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // $typelist = [0=>"路考路线",1=>"G1重点",2=>"常见问题",3=>"驾考收费 区域一",4=>"驾考收费 区域二",5=>"驾考收费 区域三",6=>"教练端文档",7=>"学生端文档",
    //               8=>"学生端文档 笔试题库",9=>"学生端文档 驾考课程",10=>"学生端文档 驾照翻译",11=>"学生端文档 全科网课",12=>"学生端文档 我的EDRIVING"];
    $addresslist = array();
    $stmt = $pdo->prepare("SELECT * From `videoTable`");
    $stmt->execute();
    $questionSectionList = array();
    if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
      	$row["id"]=$row["videoId"];
        // $row["commonReplytypeName"]= $typelist[$row["commonReplytype"]];
        $addresslist[] = $row;
      }
    }else{
        echo json_encode(["message"=>"database error"]);
        exit();
    }

    echo json_encode(["message"=>"success","data"=>$addresslist]);
  }

