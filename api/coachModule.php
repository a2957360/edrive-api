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
    $userStateList=[0=>"未审批",1=>"审批通过"];
  	//删除
  	if(isset($data['isDelete']) && isset($data['reservationId'])){
   		$commonReplyId=$data['commonReplyId'];
   		foreach ($commonReplyId as $key => $value) {
   			$data = $value;
   			$stmt = $pdo->prepare("DELETE FROM `commonReplyTable`WHERE `commonReplyId` = '$value'");
	    	$stmt->execute();
   		}
	    echo json_encode(["message"=>"success"]);
	    exit();
  	}
    //查询
    if (isset($data['isGet'])) {
      //已经有课程的学员
      // $sql .= isset($data['noCoach'])?"AND `reservationTable`.`coachId` = '' AND `reservationTable`.`reservationId` != ''":"";
      // $sql .= isset($data['coachId'])?"AND `reservationTable`.`coachId` = '".$data['coachId']."' OR `userTable`.`userBelong` = '".$data['coachId']."'":"";
      $addresslist = array();
      $stmt = $pdo->prepare("SELECT *,`userTable`.`userId` AS `coachId`,count(`reservationTable`.`reservationId`) AS `reservationTotal`,
                                count(if(`reservationTable`.`reservationState`=1,true,null)) AS `reservationNow` From `userTable` 
                              LEFT JOIN `coachInfoTable` ON `coachInfoTable`.`coachId` = `userTable`.`userId`
                              LEFT JOIN `licenseTable` ON `licenseTable`.`userId` = `userTable`.`userId`
                              LEFT JOIN `reservationTable` ON `reservationTable`.`coachId` = `userTable`.`userId`
                              WHERE `userTable`.`userRole` = '1' GROUP BY `userTable`.`userId`");
      $stmt->execute();
      if($stmt != null){
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          $row["id"]=$row["coachId"];
          $row["userId"]=$row["coachId"];
          // $row["courserate"] = round((float)$row["finishedCourseTime"] / (float)$row["courseTime"]) * 100;
          // $row["carrate"] = round((float)$row["finishedCarTime"] / (float)$row["carTime"]) * 100;
          $row["userImageshowurl"] = $row["userImage"] != "" ?'http://'.$_SERVER['SERVER_NAME']."/".$row["userImage"] : 'http://'.$_SERVER['SERVER_NAME']."/images/avatar.png";
          $row["reservationShow"]=$row["reservationNow"]."/".$row["reservationTotal"];
          $row["createTime"]=substr($row["createTime"],0,10);
          $row["userStateShow"]=$userStateList[$row["userState"]];

          $addresslist[] = $row;
        }
      }else{
          echo json_encode(["message"=>"database error"]);
          exit();
      }
      echo json_encode(["message"=>"success","data"=>$addresslist]);
      exit();
    }
    //查询教练积分
    if (isset($data['isGetPoint'])) {
      $priceTable = array();
      $stmt = $pdo->prepare("SELECT * FROM `priceTable` WHERE `priceType`='0' ");
      $stmt->execute();
      if($stmt != null){
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          $priceTable[$row['priceTitle']] = $row['priceAmount'];
        }
      }else{
          echo json_encode(["message"=>"database error"]);
          exit();
      }
      //已经有课程的学员
      $coachId=$data['coachId'];
      $addresslist = array();
      $inamount = 0;
      $outamount = 0;
      //获取练车时间
      $stmt = $pdo->prepare("SELECT `timeTable`.*,`licenseTable`.`licenseName` From `timeTable` 
                              LEFT JOIN `licenseTable` ON `licenseTable`.`userId` = `timeTable`.`userId`
                              WHERE `timeTable`.`coachId` = '$coachId' AND `timeType` = '2'");
      $stmt->execute();
      if($stmt != null){
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          $timeList = json_decode($row['timeList'], true);
          foreach ($timeList as $key => $value) {
            // if(isset($value['stureview'])){
            //   $addresslist[] = $row;
            // }
            if($value["stuSign"] != null ){
              $price= (float)$value['duration'] * (float)$priceTable['练车'];
              $tmpdata = ["createTime"=>date("Y-m-d H:i:s",strtotime($value['start'])),"licenseName"=>$row['licenseName'],"reservationName"=>"练车","carTime"=>$value['duration']."Hr","reservationType"=>0,"reservationTypeDisplay"=>"领取","coursePrice"=>$price];
              $addresslist[] = $tmpdata;
              $inamount += $price;
            }

          }
        }
      }else{
          echo json_encode(["message"=>"database error"]);
          exit();
      }
      //获取考试次数
      $stmt = $pdo->prepare("SELECT `examTable`.*,`licenseTable`.`licenseName` From `examTable` 
                              LEFT JOIN `licenseTable` ON `licenseTable`.`userId` = `examTable`.`userId`
                              WHERE `examTable`.`coachId` = '$coachId' AND `examResult` != '0' AND `studentConfirm` != '0'");
      $stmt->execute();
      if($stmt != null){
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          $tmpdata = ["createTime"=>$row['examDate'],"licenseName"=>$row['licenseName'],"reservationName"=>"考试","carTime"=>1,"reservationType"=>0,"reservationTypeDisplay"=>"领取","coursePrice"=>$priceTable['考试']];
          $addresslist[] = $tmpdata;
          $inamount += (float)$priceTable['考试'];
        }
      }else{
          echo json_encode(["message"=>"database error"]);
          exit();
      }
      //获取代收费用
      $stmt = $pdo->prepare("SELECT `reservationTable`.*,`licenseTable`.`licenseName` From `reservationTable` 
                              LEFT JOIN `licenseTable` ON `licenseTable`.`userId` = `reservationTable`.`userId`
                              LEFT JOIN `userTable` ON `userTable`.`userId` = `reservationTable`.`userId` -- 链接user判断是否是教练自有
                              WHERE `reservationTable`.`coachId` = '$coachId' AND `reservationType` = '1' AND `reservationState` = '1'
                              AND `userTable`.`userRole` = '0'");
      $stmt->execute();
      if($stmt != null){
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          $tmpdata = ["createTime"=>$row['createTime'],"licenseName"=>$row['licenseName'],"reservationName"=>$row['reservationName'],"carTime"=>1,"reservationType"=>1,"reservationTypeDisplay"=>"代收","coursePrice"=>$row['courseTotalPrice']];
          $addresslist[] = $tmpdata;
          $outamount += (float)$row['courseTotalPrice'];
        }
      }else{
          echo json_encode(["message"=>"database error"]);
          exit();
      }
      $data['list'] = $addresslist;
      $data['inprice'] = $inamount;
      $data['outprice'] = $outamount;
      $data['totalprice'] = $inamount - $outamount;
      echo json_encode(["message"=>"success","data"=>$data]);
      exit();
    }
  	//添加/修改
	 $date= date('YmdHis');
    $coachId=$data['coachId'];
    $reservationId=$data['reservationId'];

    // if($_FILES['commonReplyImage']['name'] != null){
    //   $File_type = strrchr($_FILES['commonReplyImage']['name'], '.'); 
    //   $picture = '../include/pic/commonReplyImage/'.$date.$File_type;
    //   $picsql .= ",`commonReplyImage`='".$picture."'";
    // }
    if(isset($reservationId) && $reservationId !== ""){
	    $stmt = $pdo->prepare("UPDATE `reservationTable` SET `coachId` = '$coachId'
                             WHERE `reservationId` = '$reservationId'");
	    $stmt->execute();
      if($stmt != null){
        // if($_FILES['commonReplyImage']['name'] != null){
        //   move_uploaded_file($_FILES['commonReplyImage']['tmp_name'], $picture);
        // }
        echo json_encode(["message"=>"success"]);
        exit();
	    }
    }
    // $stmt = $pdo->prepare("INSERT INTO `reservationTable`(`userId`,`reservationName`,`reservationLevel`, `carTime`, `courseTime`, `homeTime`, `isBDE`, `courseExamTime`, `coursePrice`, `reservationArea`) 
    //                                 VALUES ('$userId','$courseName','$courseExamLevel','$carTime','$courseTime','$homeTime','$isBDE','$courseExamTime','$coursePrice','$reservationArea')");
    // $stmt->execute();
    // if($stmt != null){
    //   // if($_FILES['commonReplyImage']['name'] != null){
    //   //   move_uploaded_file($_FILES['commonReplyImage']['tmp_name'], $picture);
    //   // }
    //   echo json_encode(["message"=>"success"]);
    // }
  }