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
    if(isset($data['isDelete']) && isset($data['examId'])){
      $examId=$data['examId'];
      $stmt = $pdo->prepare("DELETE FROM `examTable` WHERE `examId` = '$examId'");
      $stmt->execute();
      echo json_encode(["message"=>"success"]);
      exit();
    }

    if(isset($data['isExchange'])){
      $firstexamId=$data['0']['examId'];
      $secondexamId=$data['1']['examId'];
      $firststudentId=$data['0']['userId'];
      $secondstudentId=$data['1']['userId'];
      $firstorderId=$data['0']['orderId'];
      $secondorderId=$data['1']['orderId'];

      $stmt = $pdo->prepare("UPDATE `examTable` SET `userId` = '$secondstudentId',`orderId` = '$secondorderId' WHERE `examId` = '$firstexamId'");
      $stmt->execute();

      $stmt = $pdo->prepare("UPDATE `examTable` SET `userId` = '$firststudentId',`orderId` = '$firstorderId' WHERE `examId` = '$secondexamId'");
      $stmt->execute();

      echo json_encode(["message"=>"success"]);
      exit();
    }
    if(isset($_POST['isResult'])){
      $examId=$_POST['examId'];
      $examResult=$_POST['examResult'];
      $date= date('YmdHis');

      if($_FILES['examResultImage']['name'] != null){
        $File_type = strrchr($_FILES['examResultImage']['name'], '.'); 
        $examResultImage= '../include/pic/examImage/'.$date.$examId.$File_type;
      }
      $stmt = $pdo->prepare("UPDATE `examTable` SET `examResult` = '$examResult',`examResultImage` = '$examResultImage' WHERE `examId` = '$examId'");
      $stmt->execute();
      if($_FILES['examResultImage']['name'] != null){
        move_uploaded_file($_FILES['examResultImage']['tmp_name'], $examResultImage);
      }

      echo json_encode(["message"=>"success"]);
      exit();
    }
    //查询
    if (isset($data['isGet'])) {
      //已经有课程的学员
      $sql .= isset($data['isRegister'])?"AND `reservationTable`.`userId` = `userTable`.`userId`":"";
      $sql .= isset($data['coachId'])?" AND `examTable`.`coachId` = '".$data['coachId']."'":"";
      $sql .= isset($data['userId'])?" AND `examTable`.`userId` = '".$data['userId']."'":"";

      $examlist = [0=>"未开始",1=>"成功",2=>"失败"];
      $addresslist = array();
      $stmt = $pdo->prepare("SELECT * From `examTable` 
                            LEFT JOIN `licenseTable` ON `examTable`.`userId` = `licenseTable`.`userId`
                            LEFT JOIN `reservationTable` ON `examTable`.`orderId` = `reservationTable`.`reservationId`
                            WHERE 1 ".$sql." ORDER BY `examDate` DESC");
      $stmt->execute();

      if($stmt != null){
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          // $row["userId"]=$row["studentId"];
          // $row["courserate"] = round((float)$row["finishedCourseTime"] / (float)$row["courseTime"]) * 100;
          // $row["carrate"] = round((float)$row["finishedCarTime"] / (float)$row["carTime"]) * 100;
          $row["examResultImageurl"] = $row["examResultImage"] != "" ?'http://'.$_SERVER['SERVER_NAME']."/".$row["examResultImage"] : 'http://'.$_SERVER['SERVER_NAME']."/images/upload.png";
          $row["examImageurl"] = $row["examImage"] != "" ?'http://'.$_SERVER['SERVER_NAME']."/".$row["examImage"] : "";
          $row["examResultText"] = $examlist[$row["examResult"]];
          $row["examDate"]=substr($row["examDate"],0,16);
          $addresslist[] = $row;
        }
      }else{
          echo json_encode(["message"=>"database error"]);
          exit();
      }
      echo json_encode(["message"=>"success","data"=>$addresslist]);
      exit();
    }

    if(isset($data['stuConfirm']) && $data['stuConfirm'] == 1){
      $examId=$data['examId'];
      $reservationId=$data['orderId'];

      $stmt = $pdo->prepare("UPDATE `examTable` SET `studentConfirm` = '1' WHERE `examId` = '$examId'");
      $stmt->execute();

      $stmt = $pdo->prepare("UPDATE `reservationTable` SET `finishedExamTime` = `finishedExamTime`+'1' WHERE `reservationId` = '$reservationId'");
      $stmt->execute();

      echo json_encode(["message"=>"success"]);
      exit();
    }

    //添加/修改
  $date= date('YmdHis');
    $examId=$_POST['examId'];
    $userId=$_POST['userId'];
    $coachId=$_POST['coachId'];
    $orderId=$_POST['reservationId'];
    $examType=$_POST['examType'];
    $examLocation=$_POST['examLocation'];
    $examGovernment=$_POST['examGovernment'];
    $examDate=$_POST['examDate'];
    $examDate = date("Y-m-d H:i:s",strtotime($examDate));

    if($_FILES['examImage']['name'] != null){
      $File_type = strrchr($_FILES['examImage']['name'], '.'); 
      $examImage = '../include/pic/examImage/'.$date.$File_type;
      $picsql .= ",`examImage`='".$examImage."'";
    }
    if(isset($examId) && $examId !== ""){
      $stmt = $pdo->prepare("UPDATE `examTable` SET `examType` = '$examType',`examLocation` = '$examLocation',`examGovernment` = '$examGovernment',`examDate` = '$examDate' ".$picsql."
                             WHERE `examId` = '$examId'");
      $stmt->execute();
      if($stmt != null){
        if($_FILES['examImage']['name'] != null){
          move_uploaded_file($_FILES['examImage']['tmp_name'], $examImage);
        }
        echo json_encode(["message"=>"success"]);
        exit();
      }
    }
    
    $stmt = $pdo->prepare("INSERT INTO `examTable`(`userId`,`coachId`,`orderId`,`examImage`,`examType`, `examLocation`,`examGovernment`, `examDate`) 
                                    VALUES ('$userId','$coachId','$orderId','$examImage','$examType','$examLocation','$examGovernment','$examDate')");
    $stmt->execute();
    if($stmt != null){
      $stmt = $pdo->prepare("UPDATE `reservationTable` SET `finishedExamTime` = `finishedExamTime` + 1 WHERE `reservationId` = '$orderId'");
      $stmt->execute();
      if($_FILES['examImage']['name'] != null){
        move_uploaded_file($_FILES['examImage']['tmp_name'], $examImage);
      }
      echo json_encode(["message"=>"success","data"=>null]);
    }
  }
