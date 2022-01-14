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
    //结单
    if (isset($data['isFinish'])) {
      $reservationId = $data['reservationId'];
      $userId = $data['userId'];
      $stmt = $pdo->prepare("UPDATE `reservationTable` SET `reservationState` = '$reservationState' WHERE `reservationId` = '$reservationId' AND `userId` = '$userId'");
      $stmt->execute();
      if($stmt != null){
        echo json_encode(["message"=>"success"]);
        exit();
      }else{
          echo json_encode(["message"=>"database error"]);
          exit();
      }
    }
    //评分
    if (isset($data['isRate'])) {
      $reservationId = $data['reservationId'];
      $reservationRateList=json_encode($data['reservationRateList'], JSON_UNESCAPED_UNICODE);
      $stmt = $pdo->prepare("UPDATE `reservationTable` SET `reservationRateList` = '$reservationRateList'
                             WHERE `reservationId` = '$reservationId'");
      $stmt->execute();
      if($stmt != null){
        echo json_encode(["message"=>"success"]);
        exit();
      }else{
          echo json_encode(["message"=>"database error"]);
          exit();
      }
    }
    //修改期望考试时间
    if (isset($data['isExceptExam']) && $data['isExceptExam'] == 1) {
      $reservationId = $data['reservationId'];
      $expectExamDate=$data['expectExamDate'];
      $stmt = $pdo->prepare("UPDATE `reservationTable` SET `expectExamDate` = '$expectExamDate'
                             WHERE `reservationId` = '$reservationId'");
      $stmt->execute();

      if($stmt != null){
        echo json_encode(["message"=>"success","data"=>null]);
        exit();
      }else{
          echo json_encode(["message"=>"database error"]);
          exit();
      }
    }
    //分配教练
    if (isset($data['isCoach']) && $data['isCoach'] == 1) {
      $reservationId = $data['reservationId'];
      $coachId=$data['coachId'];
      $stmt = $pdo->prepare("UPDATE `reservationTable` SET `coachId` = '$coachId'
                             WHERE `reservationId` = '$reservationId'");
      $stmt->execute();
      $stmt = $pdo->prepare("UPDATE `userTable` SET `userBelong` = '0'
                             WHERE `userId` = (SELECT `userId` FROM `reservationTable` WHERE `reservationId` = '$reservationId')");
      $stmt->execute();
      if($stmt != null){
        echo json_encode(["message"=>"success"]);
        exit();
      }else{
          echo json_encode(["message"=>"database error"]);
          exit();
      }
    }

    //理论课时间
    if (isset($data['isDDT']) && $data['isDDT'] == 1) {
      $reservationId = $data['reservationId'];
      $finishedCourseTime=$data['finishedCourseTime'];
      $stmt = $pdo->prepare("UPDATE `reservationTable` SET `finishedCourseTime` = '$finishedCourseTime'
                             WHERE `reservationId` = '$reservationId'");
      $stmt->execute();
      if($stmt != null){
        echo json_encode(["message"=>"success"]);
        exit();
      }else{
          echo json_encode(["message"=>"database error"]);
          exit();
      }
    }
    //添加模拟考试单
    if (isset($_POST['isExamReport']) && $_POST['isExamReport'] == 1) {
      $date= date('YmdHis');
      $reservationId = $_POST['reservationId'];
      if($_FILES['examReportImage']['name'] != null){
        $File_type = strrchr($_FILES['examReportImage']['name'], '.'); 
        $examReportImage = '../include/pic/examReportImage/'.$date.$reservationId.$coachId.$File_type;
      }
      $stmt = $pdo->prepare("UPDATE `reservationTable` SET `examReportImage` = '$examReportImage'
                             WHERE `reservationId` = '$reservationId'");
      $stmt->execute();
      if($stmt != null){
        if($_FILES['examReportImage']['name'] != null){
          move_uploaded_file($_FILES['examReportImage']['tmp_name'], $examReportImage);
        }
        echo json_encode(["message"=>"UPDATE `reservationTable` SET `examReportImage` = '$examReportImage'
                             WHERE `reservationId` = '$reservationId'"]);
        exit();
      }else{
          echo json_encode(["message"=>"database error"]);
          exit();
      }
    }
    //查询
    if (isset($data['isGet'])) {
      $sql .= isset($data['userId'])?"AND `reservationTable`.`userId` = '".$data['userId']."'":"";
      $sql .= isset($data['coachId'])?"AND `reservationTable`.`coachId` = '".$data['coachId']."'":"";
      $addresslist = array();
      $inprice=0;
      $outprice=0;
      $stmt = $pdo->prepare("SELECT `reservationTable`.*,`examTable`.`examDate`,`examTable`.`examLocation`,`examTable`.`examImage`,
      						`licenseTable`.`licensePickupAddress`,`licenseTable`.`licenseName`,`coachLicense`.`licenseName` AS `coachName`
      						From `reservationTable` 
                            LEFT JOIN `licenseTable` ON `licenseTable`.`userId` = `reservationTable`.`userId`
                            LEFT JOIN `licenseTable` `coachLicense` ON `coachLicense`.`userId` = `reservationTable`.`coachId`
                            LEFT JOIN `examTable` ON `examTable`.`orderId` = `reservationTable`.`reservationId` AND `examTable`.`examResult` = 0
                            WHERE `reservationTable`.`reservationState` != '0' ".$sql." GROUP BY `reservationTable`.`reservationId` ORDER BY `examTable`.`examDate`");
      $stmt->execute();
      if($stmt != null){
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          $row["id"]=$row["reservationId"];
          // $row["courserate"] = round((float)$row["finishedCourseTime"] / (float)$row["courseTime"]) * 100;
          // $row["carrate"] = round((float)$row["finishedCarTime"] / (float)$row["carTime"]) * 100;
          $row["createTime"]=substr($row["createTime"],0,10);
          $row["examDate"]=substr($row["examDate"],0,16);
          $row["carrate"]=($row["carTime"] != 0)?(float)$row["finishedCarTime"]/(float)$row["carTime"] * 100:100;
          $row["courserate"]= ($row["courseTime"] != 0)?(float)$row["finishedCourseTime"]/(float)$row["courseTime"] * 100:100;
          $row["carTimeShow"]=$row["finishedCarTime"]."/".(float)$row["carTime"];
          $row["courseTimeShow"]=$row["finishedCourseTime"]."/".(float)$row["courseTime"];
          $row["examTimeShow"]=$row["finishedExamTime"]."/".(float)$row["courseExamTime"];
          $row["reservationTypeName"]=$row["reservationType"] == 0?"驾校分配":"教练代收";
          $row["examReportImageUrl"] = $row["examReportImage"] != "" ?'http://'.$_SERVER['SERVER_NAME']."/".$row["examReportImage"] : 'http://'.$_SERVER['SERVER_NAME']."/images/demo2.png";
          $row["examApplyImageUrl"] = $row["examImage"] != "" ?'http://'.$_SERVER['SERVER_NAME']."/".$row["examImage"] : 'http://'.$_SERVER['SERVER_NAME']."/images/demo2.png";
          $addresslist['list'][] = $row;
          if($row['reservationType'] == 0){
            $inprice+=$row['coursePrice'];
          }else{
            $outprice+=$row['coursePrice'];
          }
        }
      }else{
          echo json_encode(["message"=>"database error"]);
          exit();
      }
      $addresslist['inprice'] = $inprice;
      $addresslist['outprice'] = $outprice;
      $addresslist['totalprice'] = $inprice - $outprice;
      echo json_encode(["message"=>"success","data"=>$addresslist]);
      exit();
    }
  	//添加/修改
	  $date= date('YmdHis');
    $reservationId=$data['reservationId'];
    $courseName=$data['courseName'];
    $courseExamLevel=$data['courseExamLevel'];
    $userId=$data['userId'];
    $coachId=isset($data['coachId'])?$data['coachId']:0;
    $carTime=$data['carTime'];
    $courseTime=$data['courseTime'];
    $homeTime=$data['homeTime'];
    $isBDE=$data['isBDE'];
    $courseExamTime=$data['courseExamTime'];
    $coursePrice=$data['coursePrice'];
    $courseTotalPrice=$data['courseTotalPrice'];
    $couponPrice=$data['couponPrice'];
    $couponId=$data['couponId'];
    $reservationArea=$data['reservationArea'];
    $reservationLocation=$data['reservationLocation'];
    $reservationState=$data['reservationState'];
    $reservationUserReview=$data['reservationUserReview'];
    $reservationCoachReview=$data['reservationCoachReview'];
    $reservationState=isset($data['reservationState'])?$data['reservationState']:0;
    $coursePayment=$data['coursePayment'];
    $reservationType=$data['reservationType'];
    $getCashUserId=$data['getCashUserId'];
    $reservationRateList=json_encode($data['reservationRateList'], JSON_UNESCAPED_UNICODE);
    // if($_FILES['commonReplyImage']['name'] != null){
    //   $File_type = strrchr($_FILES['commonReplyImage']['name'], '.'); 
    //   $picture = '../include/pic/commonReplyImage/'.$date.$File_type;
    //   $picsql .= ",`commonReplyImage`='".$picture."'";
    // }
    //判断有没有评分
    if($data['reservationRateList'] == ""){
      $reservationRateList = array();
      $stmt = $pdo->prepare("SELECT * From `rateListTable`");
      $stmt->execute();
      if($stmt != null){
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          $rateScore = [0,0,0,0,0,0,0,0,0,0];
          // $rateScore = [["score"=>0],["score"=>0],["score"=>0],["score"=>0],["score"=>0],["score"=>0],["score"=>0],["score"=>0],["score"=>0],["score"=>0]];
          $reservationRateList[] = ["rateName"=>$row["rateName"],"rateNameEng"=>$row["rateNameEng"],"rateScore"=>$rateScore];
        }
      }else{
          echo json_encode(["message"=>"database error"]);
          exit();
      }
    }

    $reservationRateList=json_encode($reservationRateList, JSON_UNESCAPED_UNICODE);

    if(isset($reservationId) && $reservationId !== ""){
	    $stmt = $pdo->prepare("UPDATE `reservationTable` SET `userId` = '$userId', `carTime` = '$carTime', `courseTime` = '$courseTime', `homeTime` = '$homeTime', `isBDE` = '$isBDE',
                            `courseExamTime` = '$courseExamTime', `coursePrice` = '$coursePrice',`courseTotalPrice` = '$courseTotalPrice', `reservationArea` = '$reservationArea', 
                            `reservationState` = '$reservationState', `reservationUserReview` = '$reservationUserReview', `reservationCoachReview` = '$reservationCoachReview', `couponPrice` = '$couponPrice', `couponId` = '$couponId',
                            `reservationRateList` = '$reservationRateList', `reservationState` = '$reservationState',`getCashUserId` = '$getCashUserId', `coursePayment` = '$coursePayment'
                             WHERE `reservationId` = '$reservationId'");
	    $stmt->execute();
      if($stmt != null){
        $stmt = $pdo->prepare("UPDATE `couponTable` SET `couponState` = '3' WHERE `couponId` = '$couponId'");
        $stmt->execute();
        // if($_FILES['commonReplyImage']['name'] != null){
        //   move_uploaded_file($_FILES['commonReplyImage']['tmp_name'], $picture);
        // }
        echo json_encode(["message"=>"success"]);
        exit();
	    }

    }

    //删除没有支付的订单
    $stmt = $pdo->prepare("DELETE FROM `reservationTable` WHERE `userId` = '$userId' AND `reservationState` = '0'");
    $stmt->execute();

    $stmt = $pdo->prepare("INSERT INTO `reservationTable`(`userId`,`coachId`,`reservationName`,`reservationLevel`, `carTime`, `courseTime`, `homeTime`, `isBDE`, `courseExamTime`,
                           `coursePrice`,`courseTotalPrice`, `reservationArea`, `reservationRateList`, `reservationState`, `reservationType`, `getCashUserId`) 
                          VALUES ('$userId','$coachId','$courseName','$courseExamLevel','$carTime','$courseTime','$homeTime','$isBDE','$courseExamTime','$coursePrice','$courseTotalPrice','$reservationArea','$reservationRateList','$reservationState','$reservationType','$getCashUserId')");
    $stmt->execute();
    if($stmt != null){
      $adddata["reservationId"] = $pdo->lastinsertid();
      // if($_FILES['commonReplyImage']['name'] != null){
      //   move_uploaded_file($_FILES['commonReplyImage']['tmp_name'], $picture);
      // }
      echo json_encode(["message"=>"success","data"=>$adddata]);
    }


  }




