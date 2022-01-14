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
    //查询
    if(isset($data['isGet'])){
      $priceType=$data['priceType'];
      $searchSql .= isset($data['priceType'])?"AND `priceType`=".$priceType:"";
      $pricelist = array();
      $stmt = $pdo->prepare("SELECT * FROM `priceTable` WHERE 1 ".$searchSql);
      $stmt->execute();
      if($stmt != null){
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
          $row['id'] = $row['priceId'];
          $pricelist[$row['priceTitle']] = $row;
        }
      }else{
          echo json_encode(["message"=>"database error"]);
          exit();
      }
      echo json_encode(["message"=>"success","data"=>$pricelist,"list"=>array_values($pricelist)]);
      exit();
    }

    //删除
    if(isset($data['isDelete']) && isset($data['priceId'])){
      $priceId=$data['priceId'];
      foreach ($priceId as $key => $value) {
        $data = $value;
        $stmt = $pdo->prepare("DELETE FROM `priceTable`WHERE `priceId` = '$value'");
        $stmt->execute();
      }
      echo json_encode(["message"=>"success"]);
      exit();
    }
    //添加/修改
    $date= date('YmdHis');
    $priceId=$data['priceId'];
    $priceAmount=$data['priceAmount'];
    // $priceType=$_POST['priceType'];// 0：教练收入；1：翻译价格 

    if(isset($priceId) && $priceId !== ""){
      $stmt = $pdo->prepare("UPDATE `priceTable` SET `priceAmount` = '$priceAmount'WHERE `priceId` = '$priceId'");
      $stmt->execute();
      if($stmt != null){
        echo json_encode(["message"=>"success"]);
        exit();
      }

    }
    $stmt = $pdo->prepare("INSERT INTO `priceTable`(`priceTitle`,`priceAmount`,`pricetype`) 
                            VALUES ('$priceTitle','$priceAmount','$pricetype')");
    $stmt->execute();
    if($stmt != null){
      echo json_encode(["message"=>"success","data"=>null]);
    }
  }
