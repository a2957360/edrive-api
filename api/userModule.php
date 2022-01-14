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
      $date= date('YmdHis');

    if(isset($data['isPassword']) && isset($data['userId'])){
      $userId=$data['userId'];
      $userPassword = password_hash(isset($data['userPassword'])?$data['userPassword']:"", PASSWORD_DEFAULT);
      $stmt = $pdo->prepare("UPDATE `userTable` SET `userPassword` = '$userPassword' WHERE `userId` = '$userId'");
      $stmt->execute();
      echo json_encode(["message"=>"success"]);
      exit();
    }

    if(isset($data['isDelete']) && isset($data['userId'])){
      $userId=$data['userId'];
      foreach ($userId as $key => $value) {
        $stmt = $pdo->prepare("DELETE FROM `userTable`WHERE `userId` = '$value'");
        $stmt->execute();
      }
      echo json_encode(["message"=>"success"]);
      exit();
    }

    if(isset($data['isGet']) && $data['isGet'] == 1){
      $userId       =  isset($data['userId'])?$data['userId']:"";
      $stmt = $pdo->prepare("SELECT `userTable`.*,`licenseTable`.*,`reservationTable`.*,`timeTable`.`timeList`,`timeTable`.`timeId` FROM `userTable` 
                              LEFT JOIN `licenseTable` ON `licenseTable`.`userId` = `userTable`.`userId`
                              LEFT JOIN `reservationTable` ON `userTable`.`userId` = `reservationTable`.`userId` AND `reservationTable`.`reservationState`='1'
                              LEFT JOIN `timeTable` ON `timeTable`.`userId` = `reservationTable`.`userId` AND `timeTable`.`timeType` = '2'
                              WHERE `userTable`.`userId` = '$userId';");
      //有考试日期
      // $stmt = $pdo->prepare("SELECT `userTable`.*,`licenseTable`.*,`reservationTable`.*,`timeTable`.`timeList`,`timeTable`.`timeId`,`examTable`.`examDate` FROM `userTable` 
      //                   LEFT JOIN `licenseTable` ON `licenseTable`.`userId` = `userTable`.`userId`
      //                   LEFT JOIN `reservationTable` ON `userTable`.`userId` = `reservationTable`.`userId` AND `reservationTable`.`reservationState`='1'
      //                   LEFT JOIN `timeTable` ON `timeTable`.`userId` = `reservationTable`.`userId` AND `timeTable`.`timeType` = '2'
      //                   LEFT JOIN `examTable` ON `examTable`.`userId` = `userTable`.`userId` AND `examTable`.`examResult` = '0'
      //                   WHERE `userTable`.`userId` = '$userId';");
      
      // $stmt = $pdo->prepare("SELECT * FROM `userTable` 
      //                   LEFT JOIN `licenseTable` ON `licenseTable`.`userId` = `userTable`.`userId`
      //                   LEFT JOIN `reservationTable` ON `userTable`.`userId` = `reservationTable`.`userId` AND `reservationTable`.`reservationState`='1'

      //                   WHERE `userTable`.`userId` = '$userId';");
      $stmt->execute();
      if($stmt != null){
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $row['userId']=$userId;
        $row["userImageurl"] = $row["userImage"] != "" ?'http://'.$_SERVER['SERVER_NAME']."/".$row["userImage"] : 'http://'.$_SERVER['SERVER_NAME']."/images/upload.png";
        $row["userImageshowurl"] = $row["userImage"] != "" ?'http://'.$_SERVER['SERVER_NAME']."/".$row["userImage"] : 'http://'.$_SERVER['SERVER_NAME']."/images/avatar.png";
        $row["examReportImageurl"] = $row["examReportImage"] != "" ?'http://'.$_SERVER['SERVER_NAME']."/".$row["examReportImage"] : 'http://'.$_SERVER['SERVER_NAME']."/images/upload.png";
        $row["examReportImage"] = null;
        $row["qrCodeurl"] = $row["qrCode"] != "" ?'http://'.$_SERVER['SERVER_NAME']."/".$row["qrCode"] : 'http://'.$_SERVER['SERVER_NAME']."/images/upload.png";
        $row["licenseImageurl"] = $row["licenseImage"] != "" ?'http://'.$_SERVER['SERVER_NAME']."/".$row["licenseImage"] : 'http://'.$_SERVER['SERVER_NAME']."/images/upload.png";
        $row["tmplicenseImage"] = $row["licenseImage"] != "" ?$row["licenseImage"]:"";
        $row["reservationRateList"] = $row['reservationRateList'] != null?json_decode($row['reservationRateList'], true):"";
        $row['timeList'] = $row['timeList'] != null?json_decode($row['timeList'], true):"";
        foreach ($row as $key => $value) {
        	if($row[$key] == null){
				    $row[$key] = "";
        	}
        }
        echo json_encode(["message"=>"success","data"=>$row]);
        exit();
      }
    }
    if(isset($data['isGetCoach']) && $data['isGetCoach'] == 1){
      $userId       =  isset($data['userId'])?$data['userId']:"";
      $stmt = $pdo->prepare("SELECT * FROM `coachInfoTable` LEFT JOIN `licenseTable` ON `licenseTable`.`userId` = `coachInfoTable`.`coachId` WHERE `coachId` = '$userId';");
      $stmt->execute();
      if($stmt != null){
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $row['userId']=$userId;
        //教练端
        $row["coachLicenseImageurl"] = $row["coachLicenseImage"] != "" ?'http://'.$_SERVER['SERVER_NAME']."/".$row["coachLicenseImage"] :
         'http://'.$_SERVER['SERVER_NAME']."/images/upload.png";
        $row["coachCityLicenseImageurl"] = $row["coachCityLicenseImage"] != "" ?'http://'.$_SERVER['SERVER_NAME']."/".$row["coachCityLicenseImage"] : 
        'http://'.$_SERVER['SERVER_NAME']."/images/upload.png";
        $row["coachYearLicenseImageurl"] = $row["coachYearLicenseImage"] != "" ?'http://'.$_SERVER['SERVER_NAME']."/".$row["coachYearLicenseImage"] : 
        'http://'.$_SERVER['SERVER_NAME']."/images/upload.png";
        $row["coachInsuranceLicenseImageurl"] = $row["coachInsuranceLicenseImage"] != "" ?'http://'.$_SERVER['SERVER_NAME']."/".$row["coachInsuranceLicenseImage"] : 
        'http://'.$_SERVER['SERVER_NAME']."/images/upload.png";
        $row["coachOtherLicenseImageurl"] = $row["coachOtherLicenseImage"] != "" ?'http://'.$_SERVER['SERVER_NAME']."/".$row["coachOtherLicenseImage"] : 
        'http://'.$_SERVER['SERVER_NAME']."/images/upload.png";
        $row["carImage1url"] = $row["carImage1"] != "" ?'http://'.$_SERVER['SERVER_NAME']."/".$row["carImage1"] : 'http://'.$_SERVER['SERVER_NAME']."/images/upload.png";
        $row["carImage2url"] = $row["carImage2"] != "" ?'http://'.$_SERVER['SERVER_NAME']."/".$row["carImage2"] : 'http://'.$_SERVER['SERVER_NAME']."/images/upload.png";

        echo json_encode(["message"=>"success","data"=>$row]);
        exit();
      }
    }
    if(isset($_POST['isChange']) && $_POST['isChange'] == 1){
      $userId =  isset($_POST['userId'])?$_POST['userId']:"";
      $userName =  isset($_POST['userName'])?$_POST['userName']:"";
      $userEmail =  isset($_POST['userEmail'])?$_POST['userEmail']:"";
      $userLearningEmail = isset($_POST['userLearningEmail'])?$_POST['userLearningEmail']:"";
      $userPhone = isset($_POST['userPhone'])?$_POST['userPhone']:"";
      $serverLocation = isset($_POST['serverLocation'])?$_POST['serverLocation']:"";
      $backupServerLocation = isset($_POST['backupServerLocation'])?$_POST['backupServerLocation']:"";
      $userRole = isset($_POST['userRole'])?$_POST['userRole']:"";
      $userState = isset($_POST['userState'])?$_POST['userState']:"";
      // $userPassword = password_hash(isset($data['userPassword'])?$data['userPassword']:"", PASSWORD_DEFAULT);
      if($_FILES['userImage']['name'] != null){
        $File_type = strrchr($_FILES['userImage']['name'], '.'); 
        $userImage = '../include/pic/userImage/'.$date.$userId.$File_type;
        $imagesql .=",`userImage` = '$userImage'";
      }
      if($_FILES['qrCode']['name'] != null){
        $File_type = strrchr($_FILES['qrCode']['name'], '.'); 
        $qrCode = '../include/pic/userImage/'."qr".$date.$userId.$File_type;
        $imagesql .=",`qrCode` = '$qrCode' ";
      }
      $stmt = $pdo->prepare("UPDATE `userTable` SET `userName` = '$userName',`userEmail` = '$userEmail',`userLearningEmail` = '$userLearningEmail',
                            `userPhone` = '$userPhone' ,`serverLocation` = '$serverLocation' ,`backupServerLocation` = '$backupServerLocation',`userRole` = '$userRole',`userState` = '$userState'".$imagesql."WHERE `userId` = '$userId'");
      $result = $stmt->execute();
      if($stmt != null){
        if(!$result){
          echo json_encode(["message"=>"fail"]);
          exit();
        }
        if($_FILES['userImage']['name'] != null){
          move_uploaded_file($_FILES['userImage']['tmp_name'], $userImage);
        }
        if($_FILES['qrCode']['name'] != null){
          move_uploaded_file($_FILES['qrCode']['tmp_name'], $qrCode);
        }
        $message=["message"=>"success","data"=>$data];
        echo json_encode($message);
      }else{
        echo json_encode(["message"=>"database error"]);
        exit();
      } 
    }

    if(isset($_POST['isLicense']) && $_POST['isLicense'] == 1){
      $userId = $_POST['userId'];
      $picture = $_POST['tmplicenseImage'];
      $licenseName = $_POST['licenseName'];
      $licenseGender = $_POST['licenseGender'];
      $licenseBirthday = $_POST['licenseBirthday'];
      $licensePhone = $_POST['licensePhone'];
      $licenseEmail = $_POST['licenseEmail'];
      $licenseNumber = $_POST['licenseNumber'];
      $licenseExpireDate = $_POST['licenseExpireDate'];
      $licenseIssueDate = $_POST['licenseIssueDate'];
      $licenseEye = $_POST['licenseEye'];
      $licenseAddress = $_POST['licenseAddress'];
      $licensePickupAddress = $_POST['licensePickupAddress'];
      $licenseLanguage = $_POST['licenseLanguage'];
      $licenseYear = $_POST['licenseYear'];
      
      $date= date('YmdHis');
      if($_FILES['licenseImage']['name'] != null){
        $File_type = strrchr($_FILES['licenseImage']['name'], '.'); 
        $picture = '../include/pic/licenseImage/'.$date.$userId.$File_type;
        // $picture = "1111111111111111111111";
        $picsql .= ",`licenseImage`='".$picture."'";
      }

      $stmt = $pdo->prepare("INSERT INTO `licenseTable`(`userId`, `licenseImage`, `licenseName`, `licenseGender`, `licenseBirthday`, `licensePhone`, 
                                                              `licenseEmail`, `licenseNumber`, `licenseExpireDate`, `licenseIssueDate`, `licenseEye`, 
                                                              `licenseAddress`, `licensePickupAddress`, `licenseLanguage`, `licenseYear`) 
                            VALUES ('$userId','$picture','$licenseName','$licenseGender','$licenseBirthday','$licensePhone','$licenseEmail','$licenseNumber','$licenseExpireDate',
                            '$licenseIssueDate','$licenseEye','$licenseAddress','$licensePickupAddress','$licenseLanguage','$licenseYear')
                             ON DUPLICATE KEY UPDATE `userId`='$userId',`licenseImage`='$picture',`licenseName`='$licenseName',`licenseGender`='$licenseGender',
                             `licenseBirthday`='$licenseBirthday',`licensePhone`='$licensePhone',`licenseEmail`='$licenseEmail',`licenseNumber`='$licenseNumber',
                             `licenseExpireDate`='$licenseExpireDate',`licenseIssueDate`='$licenseIssueDate',`licenseEye`='$licenseEye',`licenseAddress`='$licenseAddress',
                             `licensePickupAddress`='$licensePickupAddress',`licenseLanguage`='$licenseLanguage',`licenseYear`='$licenseYear';");
      $stmt->execute();
      if($stmt != null){
        if($_FILES['licenseImage']['name'] != null){
          move_uploaded_file($_FILES['licenseImage']['tmp_name'], $picture);
        }
        $message=["message"=>"success","data"=>null];
        echo json_encode($message);
      }else{
        echo json_encode(["message"=>"database error"]);
        exit();
      } 
    }

    if(isset($_POST['isCoachLicense']) && $_POST['isCoachLicense'] == 1){
      $coachId = $_POST['userId'];
      $coachLicenseImage = "`coachLicenseImage`";
      $coachCityLicenseImage = "`coachCityLicenseImage`";
      $coachYearLicenseImage = "`coachYearLicenseImage`";
      $coachInsuranceLicenseImage = "`coachInsuranceLicenseImage`";
      $coachOtherLicenseImage = "`coachOtherLicenseImage`";
      $coachLicenseNumber = $_POST['coachLicenseNumber'];
      $coachLicenseIssueData = $_POST['coachLicenseIssueData'];
      $coachLicenseExpireDate = $_POST['coachLicenseExpireDate'];
      $cityLicenseNumber = $_POST['cityLicenseNumber'];
      $cityLicenseIssueData = $_POST['cityLicenseIssueData'];
      $cityLicenseCity = $_POST['cityLicenseCity'];
      $insuranceCompany = $_POST['insuranceCompany'];
      $insuranceNumber = $_POST['insuranceNumber'];
      
      $date= date('YmdHis');
      if($_FILES['coachLicenseImage']['name'] != null){
        $File_type = strrchr($_FILES['coachLicenseImage']['name'], '.'); 
        $coachLicenseImage = "'../include/pic/licenseImage/".$date.$userId."1".$File_type."'";
        $coachLicenseImageupload = '../include/pic/licenseImage/'.$date.$userId."1".$File_type;
      }
      if($_FILES['coachCityLicenseImage']['name'] != null){
        $File_type = strrchr($_FILES['coachCityLicenseImage']['name'], '.'); 
        $coachCityLicenseImage = "'../include/pic/licenseImage/".$date.$userId."2".$File_type."'";
        $coachCityLicenseImageupload = '../include/pic/licenseImage/'.$date.$userId."2".$File_type;
      }
      if($_FILES['coachYearLicenseImage']['name'] != null){
        $File_type = strrchr($_FILES['coachYearLicenseImage']['name'], '.'); 
        $coachYearLicenseImage = "'../include/pic/licenseImage/".$date.$userId."3".$File_type."'";
        $coachYearLicenseImageupload = '../include/pic/licenseImage/'.$date.$userId."3".$File_type;
      }
      if($_FILES['coachInsuranceLicenseImage']['name'] != null){
        $File_type = strrchr($_FILES['coachInsuranceLicenseImage']['name'], '.'); 
        $coachInsuranceLicenseImage = "'../include/pic/licenseImage/".$date.$userId."4".$File_type."'";
        $coachInsuranceLicenseImageupload = '../include/pic/licenseImage/'.$date.$userId."4".$File_type;
      }
      if($_FILES['coachOtherLicenseImage']['name'] != null){
        $File_type = strrchr($_FILES['coachOtherLicenseImage']['name'], '.'); 
        $coachOtherLicenseImage = "'../include/pic/licenseImage/".$date.$userId."5".$File_type."'";
        $coachOtherLicenseImageupload = '../include/pic/licenseImage/'.$date.$userId."5".$File_type;
      }


      $stmt = $pdo->prepare("INSERT INTO `coachInfoTable`(`coachId`, `coachLicenseImage`, `coachCityLicenseImage`, `coachYearLicenseImage`, `coachInsuranceLicenseImage`, 
                  `coachOtherLicenseImage`, `coachLicenseNumber`, `coachLicenseIssueData`, `coachLicenseExpireDate`, `cityLicenseNumber`, `cityLicenseIssueData`, 
                  `cityLicenseCity`, `insuranceCompany`, `insuranceNumber`) 
                            VALUES ('$coachId',$coachLicenseImage,$coachCityLicenseImage,$coachYearLicenseImage,$coachInsuranceLicenseImage,$coachOtherLicenseImage,'$coachLicenseNumber','$coachLicenseIssueData','$coachLicenseExpireDate','$cityLicenseNumber','$cityLicenseIssueData','$cityLicenseCity','$insuranceCompany','$insuranceNumber')
                            ON DUPLICATE KEY UPDATE `coachLicenseImage`=$coachLicenseImage,`coachCityLicenseImage`=$coachCityLicenseImage,`coachYearLicenseImage`=$coachYearLicenseImage,
                            `coachInsuranceLicenseImage`=$coachInsuranceLicenseImage,`coachOtherLicenseImage`=$coachOtherLicenseImage,`coachLicenseNumber`='$coachLicenseNumber',
                            `coachLicenseIssueData`='$coachLicenseIssueData',`coachLicenseExpireDate`='$coachLicenseExpireDate',`cityLicenseNumber`='$cityLicenseNumber',
                            `cityLicenseIssueData`='$cityLicenseIssueData',`cityLicenseCity`='$cityLicenseCity',`insuranceCompany`='$insuranceCompany',`insuranceNumber`='$insuranceNumber'");
      $stmt->execute();
      if($stmt != null){
        if($_FILES['coachLicenseImage']['name'] != null){
          move_uploaded_file($_FILES['coachLicenseImage']['tmp_name'], $coachLicenseImageupload);
        }
        if($_FILES['coachCityLicenseImage']['name'] != null){
          move_uploaded_file($_FILES['coachCityLicenseImage']['tmp_name'], $coachCityLicenseImageupload);
        }
        if($_FILES['coachYearLicenseImage']['name'] != null){
          move_uploaded_file($_FILES['coachYearLicenseImage']['tmp_name'], $coachYearLicenseImageupload);
        }
        if($_FILES['coachInsuranceLicenseImage']['name'] != null){
          move_uploaded_file($_FILES['coachInsuranceLicenseImage']['tmp_name'], $coachInsuranceLicenseImageupload);
        }
        if($_FILES['coachOtherLicenseImage']['name'] != null){
          move_uploaded_file($_FILES['coachOtherLicenseImage']['tmp_name'], $coachOtherLicenseImageupload);
        }
        $message=["message"=>"success"];
        echo json_encode($message);
      }else{
        echo json_encode(["message"=>"database error"]);
        exit();
      } 
    }

     if(isset($_POST['isCoachCar']) && $_POST['isCoachCar'] == 1){
      $coachId = $_POST['userId'];
      $carImage1 = "`carImage1`";
      $carImage2 = "`carImage2`";
      $carBrand = $_POST['carBrand'];
      $carModel = $_POST['carModel'];
      $carDriveDistense = $_POST['carDriveDistense'];
      $carPlate = $_POST['carPlate'];
      $coachPlate = $_POST['coachPlate'];
      $coachProvincePlate = $_POST['coachProvincePlate'];
      $coachDescription = $_POST['coachDescription'];
      
      $date= date('YmdHis');
      if($_FILES['carImage1']['name'] != null){
        $File_type = strrchr($_FILES['carImage1']['name'], '.'); 
        $carImage1 = "'../include/pic/licenseImage/".$date.$userId."c1".$File_type."'";
        $carImage1upload = '../include/pic/licenseImage/'.$date.$userId."c1".$File_type;
      }
      if($_FILES['carImage2']['name'] != null){
        $File_type = strrchr($_FILES['carImage2']['name'], '.'); 
        $carImage2 = "'../include/pic/licenseImage/".$date.$userId."c2".$File_type."'";;
        $carImage2upload = '../include/pic/licenseImage/'.$date.$userId."c2".$File_type;
      }
      $stmt = $pdo->prepare("INSERT INTO `coachInfoTable`(`coachId`,`carImage1`, `carImage2`, `carBrand`, `carModel`, `carDriveDistense`, 
                  `carPlate`, `coachPlate`, `coachProvincePlate`, `coachDescription`) 
                            VALUES ('$coachId',$carImage1,$carImage2,'$carBrand','$carModel','$carDriveDistense','$carPlate','$coachPlate','$coachProvincePlate','$coachDescription')
                            ON DUPLICATE KEY UPDATE `carImage1`=$carImage1,`carImage2`=$carImage2,`carBrand`='$carBrand',`carModel`='$carModel',`carDriveDistense`='$carDriveDistense',
                            `carPlate`='$carPlate',`coachPlate`='$coachPlate',`coachProvincePlate`='$coachProvincePlate',`coachDescription`='$coachDescription'");
      $stmt->execute();
      if($stmt != null){
        if($_FILES['carImage1']['name'] != null){
          move_uploaded_file($_FILES['carImage1']['tmp_name'], $carImage1upload);
        }
        if($_FILES['carImage2']['name'] != null){
          move_uploaded_file($_FILES['carImage2']['tmp_name'], $carImage2upload);
        }
        $message=["message"=>"INSERT INTO `coachInfoTable`(`coachId`,`carImage1`, `carImage2`, `carBrand`, `carModel`, `carDriveDistense`, 
                  `carPlate`, `coachPlate`, `coachProvincePlate`, `coachDescription`) 
                            VALUES ('$coachId',$carImage1,$carImage2,'$carBrand','$carModel','$carDriveDistense','$carPlate','$coachPlate','$coachProvincePlate','$coachDescription')
                            ON DUPLICATE KEY UPDATE `carImage1`=$carImage1,`carImage2`=$carImage2,`carBrand`='$carBrand',`carModel`='$carModel',`carDriveDistense`='$carDriveDistense',
                            `carPlate`='$carPlate',`coachPlate`='$coachPlate',`coachProvincePlate`='$coachProvincePlate',`coachDescription`='$coachDescription'"];
        echo json_encode($message);
      }else{
        echo json_encode(["message"=>"database error"]);
        exit();
      } 
    }
}
?>