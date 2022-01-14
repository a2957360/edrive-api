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
  	if(isset($data['isDelete']) && isset($data['commonReplyId'])){
   		$commonReplyId=$data['commonReplyId'];
   		foreach ($commonReplyId as $key => $value) {
   			$data = $value;
   			$stmt = $pdo->prepare("DELETE FROM `commonReplyTable`WHERE `commonReplyId` = '$value'");
	    	$stmt->execute();
   		}
	    echo json_encode(["message"=>"success"]);
	    exit();
  	}
  	//添加/修改
	  $date= date('YmdHis');
    $commonReplyId=$_POST['commonReplyId'];
    $commonReplyTitle=$_POST['commonReplyTitle'];
    $commonReplyContent=addslashes($_POST['commonReplyContent']);
    $commonReplytype=$_POST['commonReplytype'];

    if($_FILES['commonReplyImage']['name'] != null){
      $File_type = strrchr($_FILES['commonReplyImage']['name'], '.'); 
      $picture = '../include/pic/commonReplyImage/'.$date.$File_type;
      $picsql .= ",`commonReplyImage`='".$picture."'";
    }
    if(isset($commonReplyId) && $commonReplyId !== ""){
	    $stmt = $pdo->prepare("UPDATE `commonReplyTable` SET `commonReplyTitle` = '$commonReplyTitle', `commonReplyContent` = '$commonReplyContent',
	    														`commonReplytype` = '$commonReplytype'".$picsql." WHERE `commonReplyId` = '$commonReplyId'");
	    $stmt->execute();
      if($stmt != null){
        if($_FILES['commonReplyImage']['name'] != null){
          move_uploaded_file($_FILES['commonReplyImage']['tmp_name'], $picture);
        }
        echo json_encode(["message"=>"success"]);
        exit();
	    }

    }
    $stmt = $pdo->prepare("INSERT INTO `commonReplyTable`(`commonReplyTitle`,`commonReplyContent`,`commonReplyImage`,`commonReplytype`) 
    												VALUES ('$commonReplyTitle','$commonReplyContent','$picture','$commonReplytype')");
    $stmt->execute();
    if($stmt != null){
      if($_FILES['commonReplyImage']['name'] != null){
        move_uploaded_file($_FILES['commonReplyImage']['tmp_name'], $picture);
      }
      echo json_encode(["message"=>$success]);
    }
  }


  if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $typelist = [0=>"路考路线",1=>"G1重点",2=>"常见问题",3=>"驾考收费 区域一",4=>"驾考收费 区域二",5=>"驾考收费 区域三",6=>"教练端文档",7=>"学生端文档",
                  8=>"学生端文档 笔试题库",9=>"学生端文档 驾考课程",10=>"学生端文档 驾照翻译",11=>"学生端文档 全科网课",12=>"学生端文档 我的EDRIVING"];
    $addresslist = array();
    $stmt = $pdo->prepare("SELECT * From `commonReplyTable`");
    $stmt->execute();
    $sum = ["iconsum"=>0,"normalsum"=>0];

    $questionSectionList = array();
    if($stmt != null){
      while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        $row["commonReplyContenturl"] = "<a href='http://edrive.finestudiodemo.com/test.php?articleId=".$row["commonReplyId"]."' target='view_window'>链接<a/>";
      	$row["id"]=$row["commonReplyId"];
        $row["commonReplytypeName"]= $typelist[$row["commonReplytype"]];
        $addresslist[] = $row;
      }
    }else{
        echo json_encode(["message"=>"database error"]);
        exit();
    }

    echo json_encode(["message"=>"success","data"=>$addresslist]);
  }

