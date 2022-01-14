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
      $isStu = $data['isStu'];
      $userId = $data['userId'];
      $coachId = $data['coachId'];
      $sql = isset($data['coachId']) ? " OR (`userId` = '$coachId' AND `timeType` = '0')":"";

      $addresslist = [0=>["timeList"=>[]],1=>["timeList"=>[]],2=>["timeList"=>[]]];
      $stmt = $pdo->prepare("SELECT * From `timeTable` WHERE `userId` = '$userId'".$sql);
      $stmt->execute();
      if($stmt != null){
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          $row['timeList'] = json_decode($row['timeList'], true);
          $tmptimeList = array();
          //学生不能看见教练的不能上课原因
          if($row["timeType"] == "0" && $isStu == 1){
            foreach ($row['timeList'] as $key => $value) {
               $value['title'] = "Unavailable";
               $tmptimeList = $value;
               $row['timeList'][$key] = $tmptimeList;
            }
            $row['timeList'] = array_values($row['timeList']);
          }
          $addresslist[$row["timeType"]] = $row;
        }
      }else{
          echo json_encode(["message"=>"database error"]);
          exit();
      }

      $tmplist = array_merge($addresslist[1]['timeList'],$addresslist[2]['timeList'],$addresslist[0]['timeList']);
      $addresslist["studentList"] =$tmplist;
      echo json_encode(["message"=>"success","data"=>$addresslist]);
      exit();
    }

    //查询教练所有学生的时间
    if (isset($data['isGetCoach'])) {
      $userId = $data['userId'];
      $coachId = $data['coachId'];

      $timelist = array(0=>["timeList"=>[]],1=>["timeList"=>[]],2=>["timeList"=>[]]);
      $stulist = array();
      $stmt = $pdo->prepare("SELECT * From `timeTable` 
                            WHERE (`userId` = '$coachId' AND `timeType` = '0') 
                            OR (`coachId` = '$coachId' AND `timeType` = '2') 
                            OR (`userId` = '$userId' AND `timeType` = '1')");
      $stmt->execute();
      if($stmt != null){
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          $row['timeList'] = json_decode($row['timeList'], true);
          if($row['timeType'] == '2'){
            if($row['userId'] == $userId){
              $stulist = $row['timeList'];
            }else{
              $tmptimelist = array();
              foreach ($row['timeList'] as $key => $value) {
                $value['backgroundColor']="#60a8ef";
                $tmptimelist[] = $value;
              }
              $row['timeList'] = $tmptimelist;
            }
          }
          if(count($timelist[$row["timeType"]]['timeList']) != 0){
            $tmpmergelist = array_merge($timelist[$row["timeType"]]['timeList'],$row['timeList']);
            $timelist[$row["timeType"]]['timeList'] = $tmpmergelist;
          }else{
            $timelist[$row["timeType"]] = $row;
          }
        }
      }else{
          echo json_encode(["message"=>"database error"]);
          exit();
      }
      $timelist["all"] = array_merge($timelist[1]['timeList'],$timelist[2]['timeList'],$timelist[0]['timeList']);
      $timelist[2]['timeList'] = $stulist;
      echo json_encode(["message"=>"success","data"=>$timelist]);
      exit();
    }

    // //给课程评分
    // if (isset($data['isReview'])) {
    //   $timeList=json_encode($data['timeList'], JSON_UNESCAPED_UNICODE);
    //   $timeId = $data['timeId'];
    //   $stmt = $pdo->prepare("UPDATE `timeTable` SET `timeList` = '$timeList' WHERE `timeId` = '$timeId'");
    //   $stmt->execute();
    //   if($stmt != null){
    //     echo json_encode(["message"=>"success"]);
    //     exit();
    //   }else{
    //       echo json_encode(["message"=>"database error"]);
    //       exit();
    //   }
    // }

    //给课程评分
    if (isset($_POST['isReview'])) {
      $timeList=json_decode($_POST["timeList"], true);
      $index = $_POST['index'];
      $timeId = $_POST['timeId'];
      $signImg = $_POST['signImg'];
      $role = $_POST['role'];

      $duration = $timeList[$index]["duration"];

      //把base64转换成图片
      $signImg = str_replace('data:image/png;base64,', '', $signImg); 
      $signImg = base64_decode($signImg);
      
      $file = '../include/pic/signImage/'.date('YmdHis').$timeId.'.png'; 
      $success = file_put_contents($file, $signImg); 
      //0是学生 1是教练
      if($role == 0){
        $timeList[$index]["stuSign"] = 'http://'.$_SERVER['SERVER_NAME'].str_replace("..","",$file);
      }else{
        $timeList[$index]["coaSign"] = 'http://'.$_SERVER['SERVER_NAME'].str_replace("..","",$file);
      }
      $timeList = json_encode($timeList,JSON_UNESCAPED_UNICODE);
      if($success){
        $stmt = $pdo->prepare("UPDATE `timeTable` SET `timeList` = '$timeList' WHERE `timeId` = '$timeId'");
        $stmt->execute();
        if($stmt != null){
          if($role == 0){
            $stmt = $pdo->prepare("UPDATE `reservationTable` SET `finishedCarTime` = `finishedCarTime` + '$duration' 
                                  WHERE `reservationId` = (SELECT `reservationId` FROM `timeTable` WHERE `timeId` = '$timeId')");
            $stmt->execute(); 
          }
          echo json_encode(["message"=>"UPDATE `reservationTable` SET `finishedCarTime` = `finishedCarTime` + '$duration' 
                                  WHERE `reservationId` = (SELECT `reservationId` FROM `timeTable` WHERE `timeId` = '$timeId')"]);
          exit();
        }else{
            echo json_encode(["message"=>"database error"]);
            exit();
        }
        echo json_encode(["message"=>"success"]);
        exit();  
      }else{
        echo json_encode(["message"=>"fail"]);
        exit(); 
      }


    }

  	//查询
	  $date= date('YmdHis');
    $userId=$data['userId'];
    $coachId=$data['coachId'];
    $reservationId=$data['reservationId'];
    $timeList=json_encode($data['timeList'], JSON_UNESCAPED_UNICODE);
    $timeType=$data['timeType'];
    $data[$timeType] = ["userId"=>$userId,"timeList"=>$data['timeList'],"timeType"=>$timeType];
    // if($_FILES['commonReplyImage']['name'] != null){
    //   $File_type = strrchr($_FILES['commonReplyImage']['name'], '.'); 
    //   $picture = '../include/pic/commonReplyImage/'.$date.$File_type;
    //   $picsql .= ",`commonReplyImage`='".$picture."'";
    // }
    if(isset($data['Duration'])){
      $Duration=$data['Duration'];
      $stmt = $pdo->prepare("SELECT * From `reservationTable` WHERE `userId` = '$userId' AND `reservationState` = '1'");
      $stmt->execute();
      if($stmt != null){
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          $reservationId = $row['reservationId'];
          $row['carTime'] = $row['carTime'];
          $row['finishedCarTime'] = $row['finishedCarTime'];
          if($row['carTime'] < $row['finishedCarTime']+$Duration && $data['changetype'] != 3){
            echo json_encode(["message"=>"notime","data"=>null,"time"=>$row['carTime']-$row['finishedCarTime']]);
            exit();
          }
        }
      }else{
        echo json_encode(["message"=>"database error"]);
        exit();
      }
    }

    //添加练车时间的情况
    if($timeType == 2){
      // if($data['changetype'] == 1){
      //   $stmt = $pdo->prepare("UPDATE `reservationTable` SET `finishedCarTime` = `finishedCarTime` + '$Duration' WHERE `reservationId` = '$reservationId'");
      //   $stmt->execute(); 
      // }
      // if($data['changetype'] == 3){
      //   $stmt = $pdo->prepare("UPDATE `reservationTable` SET `finishedCarTime` = `finishedCarTime` - '$Duration' WHERE `reservationId` = '$reservationId'");
      //   $stmt->execute(); 
      // }

      $changetypeList = [1=>"新练车时间",2=>"练车时间修改",3=>"练车时间删除"];
      $classtime = date("m-d h:i", strtotime($data['startTime']));
      $messageContent = $changetypeList[$data['changetype']]." ".$classtime;
      $stmt = $pdo->prepare("INSERT INTO `messageTable`(`userId`, `messageContent`) VALUES ('$userId','$messageContent')");
      $stmt->execute();
    }

    $stmt = $pdo->prepare("INSERT INTO `timeTable`(`userId`,`coachId`,`timeList`,`timeType`) 
                            VALUES ('$userId','$coachId','$timeList','$timeType')
                           ON DUPLICATE KEY UPDATE `reservationId`='$reservationId',`timeList`='$timeList'");
    $stmt->execute();
    if($stmt != null){
      // if($_FILES['commonReplyImage']['name'] != null){
      //   move_uploaded_file($_FILES['commonReplyImage']['tmp_name'], $picture);
      // }
      // echo json_encode(["message"=>"success","data"=>null]);
      echo json_encode(["message"=>"success","data"=>null]);
      exit();

    }


  }




