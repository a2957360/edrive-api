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
    if($_FILES['file']['name'] != null){
      $File_type = strrchr($_FILES['file']['name'], '.'); 
      $picture = '/include/pic/commonReplyImage/'.$date.$File_type;
      $url = 'http://'.$_SERVER['SERVER_NAME'].$picture ;
      move_uploaded_file($_FILES['file']['tmp_name'], "..".$picture);
    }

    echo $url;
  }
?>


