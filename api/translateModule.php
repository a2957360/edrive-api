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
  	if(isset($_POST['isDelete']) && isset($_POST['questionId'])){
   		$questionId=$_POST['questionId'];
   		foreach ($questionId as $key => $value) {
   			$stmt = $pdo->prepare("DELETE FROM `questionTable`WHERE `questionId` = '$value'");
	    	$stmt->execute();
   		}
	    echo json_encode(["message"=>"success"]);
	    exit();
  	}

    if(isset($data['isFiniesh']) && isset($data['translateId'])){
      $translateId=$data['translateId'];
      $translateState=$data['translateState'];
      $stmt = $pdo->prepare("UPDATE `translateTable` SET `translateState` = '$translateState' WHERE `translateId` = '$translateId'");
      $stmt->execute();

      echo json_encode(["message"=>"success"]);
      exit();
    }

    if (isset($data['isGet'])) {
      $sql .= isset($data['userId'])?"AND `userId` = '".$data['userId']."'":"";
      $userId=$data['userId'];
      $translateStateList = [1=>"未完成",2=>"已完成",3=>"拒绝"];

      $addresslist = array();
      $stmt = $pdo->prepare("SELECT * From `translateTable` WHERE `translateState` != '0' ".$sql);
      $stmt->execute();
      if($stmt != null){
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          $row["id"]=$row["translateId"];
          $row["createTime"]=substr($row["createTime"],0,10);
          $imagelist = explode(',',$row["translateImage"]);
          foreach ($imagelist as $key => $value) {
            $row["translateImageList"][] = 'http://'.$_SERVER['SERVER_NAME']."/".$value;
          }
          $row["translateState"] = $translateStateList[$row["translateState"]];
          $addresslist[] = $row;
        }
      }else{
          echo json_encode(["message"=>"database error"]);
          exit();
      }
      echo json_encode(["message"=>"success","data"=>$addresslist]);
      exit();
    }
  	//添加
	   $date= date('YmdHis');

    $translateId=$_POST['translateId'];
    $name=$_POST['name'];
    $userId=$_POST['userId'];
    $phone=$_POST['phone'];
    $email=$_POST['email'];
    $address=$_POST['address'];
    $hurry=$_POST['hurry'];
    $type=$_POST['type'];
    $purpose=$_POST['purpose'];
    $translatePrice=$_POST['translatePrice'];
    $couponPrice=$_POST['couponPrice'];
    $couponId=$_POST['couponId'];
    $translatePayment=$_POST['translatePayment'];
    $purpose=$_POST['purpose'];
    $translateName=$_POST['translateName'];
    $translateState=$_POST['translateState'];
    $picture = "";

    if($_FILES['translateImage']['name'] != null){
      $File_type = strrchr($_FILES['translateImage']['name'], '.'); 
      $picture .= $translateImage= '../include/pic/translate/'.$date.$userId."0".$File_type;
    }
    if($_FILES['frontImage1']['name'] != null){
      $File_type = strrchr($_FILES['frontImage1']['name'], '.'); 
      $picture .= $frontImage1= '../include/pic/translate/'.$date.$userId."1".$File_type;
    }
    if($_FILES['frontImage2']['name'] != null){
      $File_type = strrchr($_FILES['frontImage2']['name'], '.'); 
      $picture .= ','.$frontImage2= '../include/pic/translate/'.$date.$userId."2".$File_type;
      $picsql .= ",`translatePic`='".$picture."'";
    }
    if($_FILES['sideImage1']['name'] != null){
      $File_type = strrchr($_FILES['sideImage1']['name'], '.'); 
      $picture .= ','.$sideImage1= '../include/pic/translate/'.$date.$userId."3".$File_type;
    }
    if($_FILES['sideImage2']['name'] != null){
      $File_type = strrchr($_FILES['sideImage2']['name'], '.'); 
      $picture .= ','.$sideImage2= '../include/pic/translate/'.$date.$userId."4".$File_type;
    }

    if(isset($translateId) && $translateId !== ""){
	    $stmt = $pdo->prepare("UPDATE `translateTable` SET `translateName` = '$translateName', `translateUserName` = '$name',
	     `translateUserPhone` = '$phone', `translateUserEmail` = '$email', `trasnslateUserAddress` = '$address', `couponPrice` = '$couponPrice', `couponId` = '$couponId', 
	     `translateType` = '$type',`translateHurry` = '$hurry', `translatePurpose` = '$purpose', `translatePayment` = '$translatePayment', `translateState` = '$translateState'
	      WHERE `translateId` = '$translateId'");
	    $stmt->execute();

      if($stmt != null){
        $stmt = $pdo->prepare("UPDATE `couponTable` SET `couponState` = '3' WHERE `couponId` = '$couponId'");
        $stmt->execute();
	      if($_FILES['translateImage']['name'] != null){
	        move_uploaded_file($_FILES['translateImage']['tmp_name'], $translateImage);
	      }
	      if($_FILES['frontImage1']['name'] != null){
	        move_uploaded_file($_FILES['frontImage1']['tmp_name'], $frontImage1);
	      }      
	      if($_FILES['frontImage2']['name'] != null){
	        move_uploaded_file($_FILES['frontImage2']['tmp_name'], $frontImage2);
	      }      
	      if($_FILES['sideImage1']['name'] != null){
	        move_uploaded_file($_FILES['sideImage1']['tmp_name'], $sideImage1);
	      }      
	      if($_FILES['sideImage2']['name'] != null){
	        move_uploaded_file($_FILES['sideImage2']['tmp_name'], $sideImage2);
	      }
	    }
	    echo json_encode(["message"=>null]);
	    exit();
    }

    $stmt = $pdo->prepare("INSERT INTO `translateTable`(`translateImage`,`userId`, `translateName`, `translateUserName`, `translateUserPhone`,`translateUserEmail`, 
                          `trasnslateUserAddress`, `translateType`,`translateHurry`, `translatePurpose`, `translatePrice`, `translatePayment`)  
    						            VALUES ('$picture','$userId','$translateName','$name','$phone','$email','$address','$type','$hurry','$purpose','$translatePrice','$translatePayment')");
    $stmt->execute();

    if($stmt != null){
    	$data["translateId"] = $pdo->lastinsertid();
      $upload = array();
      $upload = $data;
      if($_FILES['translateImage']['name'] != null){
        move_uploaded_file($_FILES['translateImage']['tmp_name'], $translateImage);
      }
      if($_FILES['frontImage1']['name'] != null){
        move_uploaded_file($_FILES['frontImage1']['tmp_name'], $frontImage1);

      }      
      if($_FILES['frontImage2']['name'] != null){
        move_uploaded_file($_FILES['frontImage2']['tmp_name'], $frontImage2);
      }      
      if($_FILES['sideImage1']['name'] != null){
        move_uploaded_file($_FILES['sideImage1']['tmp_name'], $sideImage1);
      }      
      if($_FILES['sideImage2']['name'] != null){
        move_uploaded_file($_FILES['sideImage2']['tmp_name'], $sideImage2);
      }
    }
    echo json_encode(["message"=>"success","data"=>$upload]);
    // if($stmt != null){
    //   $lasid = $pdo->lastinsertid();
    // 	$message=["message"=>"success"];
    //   $_POST['orderState'] = "待接单";
    //   $_POST['orderPrice'] = $orderPrice;
    //   $_POST['orderTax'] = $orderTax;
    //   $_POST['orderId'] = $lasid;
    //   $message["_POST"] = $_POST;
    // 	echo json_encode($message);
    // }else{
    //     echo json_encode(["message"=>"_POSTbase error"]);
    //     exit();
    // } 
  }




