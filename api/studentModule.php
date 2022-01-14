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
    //查询
    if (isset($data['isGet'])) {
      //已经有课程的学员
      $sql .= isset($data['noCoach'])?"AND `reservationTable`.`coachId` = '' AND `reservationTable`.`reservationId` != ''":"";
      $sql .= isset($data['coachId'])?"AND `reservationTable`.`coachId` = '".$data['coachId']."' OR `userTable`.`userBelong` = '".$data['coachId']."'":"";
      //所有有课程的学员
      $sql .= isset($data['allStu'])?"AND `reservationTable`.`reservationId` != ''":"";

      $addresslist = array();
      $stmt = $pdo->prepare("SELECT `userTable`.*,`reservationTable`.*,`licenseTable`.*,`userTable`.`userId` AS `studentId`,`coachLicense`.`licenseName` AS `coachName`,`examTable`.`examDate` 
                              From `userTable` 
                              LEFT JOIN `reservationTable` ON `reservationTable`.`userId` = `userTable`.`userId` AND `reservationTable`.`reservationState` = '1'
                              LEFT JOIN `licenseTable` ON `licenseTable`.`userId` = `userTable`.`userId`
                              LEFT JOIN `licenseTable` `coachLicense` ON `coachLicense`.`userId` = `reservationTable`.`coachId`
                              LEFT JOIN `examTable` ON `examTable`.`userId` = `userTable`.`userId` AND `examTable`.`examResult` = '0'
                              WHERE `userTable`.`userRole` = '0'".$sql."GROUP BY `userTable`.`userId` ORDER BY `reservationTable`.`coachId`,`reservationTable`.`createTime` DESC");
      $stmt->execute();
      if($stmt != null){
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          $row["id"]=$row["studentId"];
          $row["userId"]=$row["studentId"];
          $row["userImageurl"] = $row["userImage"] != "" ?'http://'.$_SERVER['SERVER_NAME']."/".$row["userImage"] : 'http://'.$_SERVER['SERVER_NAME']."/images/avatar.png";
          $row["haveCoach"]="无教练";
          if($row["coachId"] != 0){
            $row["haveCoach"]="有教练";
          }
          // $row["courserate"] = round((float)$row["finishedCourseTime"] / (float)$row["courseTime"]) * 100;
          // $row["carrate"] = round((float)$row["finishedCarTime"] / (float)$row["carTime"]) * 100;
          $row["createTime"]=substr($row["createTime"],0,10);
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




