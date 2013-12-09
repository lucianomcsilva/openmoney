<?php
   session_start();
   if(!isset($_SESSION['cd_usuario_money']))
   {
      $status = 401;
      $detail = "Not connected on Open Money";
      header("HTTP/1.0 {$status} {$detail}");
      header("Status: {$status} {$detail}");
      header("content-type: application/json");   
      die(json_encode(array( "status" => $status, 
                              "detail" => $detail, 
                              "data"   => "Connect on Open Money first")));
   }
   //verificando parametros de entrada
   $cd_evento     = $_GET['cd_evento'];
   $dt_evento     = $_GET['dt_evento'];
   $dc_evento     = utf8_encode($_GET['dc_evento']);
   $cd_categoria  = $_GET['cd_categoria'];
   $vl_evento     = $_GET['vl_evento'];
   $in_evento     = $_GET['in_evento'];
   $tp_evento     = $_GET['tp_evento'];
   $qt_eventos    = $_GET['qt_eventos'];
   $tp_recorrente = $_GET['tp_recorrente'];
   $cd_recorrente = $_GET['cd_recorrente'];
   $tp_update     = $_GET['tp_update'];
   
   if(!function_exists("FullPathFile")) include "../basico.php";
   if(!class_exists("TOAuthClient"))    include "TOAuthClient.php";

   
   $objOauth = new TOAuthClient($consumerKey, $consumerSecret);   
   $objOauth->setToken($_SESSION['VL_TOKEN'], $_SESSION['VL_TOKEN_SECRET']);
      
   if($cd_evento == -1)      
   {
      $api_url = "http://www.meusgastos.com.br/api/v1/transaction/insert";
      $objOauth->AddParam("dt_evento",      $dt_evento);
      $objOauth->AddParam("dc_evento",      $dc_evento);
      $objOauth->AddParam("cd_categoria",    $cd_categoria);
      $objOauth->AddParam("vl_evento",      $vl_evento);
      $objOauth->AddParam("in_evento",      $in_evento);
      $objOauth->AddParam("tp_evento",      $tp_evento);
      $objOauth->AddParam("qt_eventos",     $qt_eventos);
      $objOauth->AddParam("tp_recorrente",   $tp_recorrente);

   }
   else 
   {
      $api_url = "http://www.meusgastos.com.br/api/v1/transaction/update";
      //var_dump($dt_evento);
      if($cd_evento !== "")     $objOauth->AddParam("cd_evento",      $cd_evento);
      if($dt_evento !== "")     $objOauth->AddParam("dt_evento",      $dt_evento);
      if($dc_evento !== "")     $objOauth->AddParam("dc_evento",      $dc_evento);
      if($cd_categoria !== "")  $objOauth->AddParam("cd_categoria",   $cd_categoria);
      if($vl_evento !== "")     $objOauth->AddParam("vl_evento",      $vl_evento);
      if($in_evento !== "")     $objOauth->AddParam("in_evento",      $in_evento);
      if($tp_update !== "")     $objOauth->AddParam("tp_update",      $tp_update);
      if($cd_recorrente !== "") $objOauth->AddParam("cd_recorrente",   $cd_recorrente);

   }
   $objOauth->setApiUrl($api_url);
   $oauthHeaders['oauth_signature'] =  $objOauth->signedString();
   //echo $objOauth;
   //die();
   $_h = array('Expect:');
   $_h[] = $objOauth->getHeader();
   $ch = curl_init();
   
   // set URL and other appropriate options
   curl_setopt($ch, CURLOPT_URL,       $api_url);
   curl_setopt($ch, CURLOPT_HEADER,       0);
   curl_setopt($ch, CURLOPT_HTTPHEADER, $_h);
   
   // grab URL and pass it to the browser
   ob_start();
   curl_exec($ch);
   $response = ob_get_clean();
   
   curl_close($ch);      

   $array = json_decode($response, true);
   //var_dump($array);
   //echo "[$cd_categoria, $nm_categoria, $cd_superior]";
   if($array["status"] == 200)
   {
      echo json_encode($array['data']);   
   }
   else
   {
      $status = $array['status'];
      $detail = $array['detail'];
      header("HTTP/1.0 {$status} {$detail}");
      header("Status: {$status} {$detail}");
      header("content-type: application/json");   
       echo json_encode($array['data']['error_code']);    
   }
   
?>
