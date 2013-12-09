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
   $tp_delete     = $_GET['tp_delete'];
   $cd_recorrente = $_GET['cd_recorrente'];

   
   if(!function_exists("FullPathFile")) include "../basico.php";
   if(!class_exists("TOAuthClient"))    include "TOAuthClient.php";

   
      
   $objOauth = new TOAuthClient($consumerKey, $consumerSecret);   
   $objOauth->setToken($_SESSION['VL_TOKEN'], $_SESSION['VL_TOKEN_SECRET']);

   $api_url = "http://www.meusgastos.com.br/api/v1/transaction/delete";
   $objOauth->AddParam("cd_evento",      $cd_evento);
   $objOauth->AddParam("tp_delete",      $tp_delete);
   $objOauth->AddParam("cd_recorrente",  $cd_recorrente);
   $objOauth->setApiUrl($api_url);
   $oauthHeaders['oauth_signature'] =  $objOauth->signedString();
   
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
