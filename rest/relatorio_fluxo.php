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
                              "session", $_SESSION,
                              "data"   => "Connect on Open Money first")));
   }
   
   //Preparando o argumento get
   $get_args = "";
   foreach ($_GET as $key => $value)  $get_args .= "{$key}={$value}&";
   $get_args = substr($get_args, 0, strlen($get_args) - 1);
   
   if(!function_exists("FullPathFile")) include "../basico.php";
   if(!class_exists("TOAuthClient"))    include "TOAuthClient.php";
   //Primeiro o saldo acumulado, para isso preciso voltar 1 dia na data final
   //echo "<hr>".$get_args."<hr>";
   foreach ($_GET as $key => $value)
   {
      if($key == "startDate")       $new_arg[$key] = "0000-01-01";
      elseif($key == "endDate")     $new_arg[$key] = date('Y-m-d', strtotime("-1 day",  strtotime($_GET["startDate"])));
      else                          $new_arg[$key] = $value;
   }
   $new_arg["total"] = 1;
   $args_previous = "";
   foreach ($new_arg as $key => $value)  $args_previous .= "{$key}={$value}&";
   $args_previous = substr($args_previous, 0, strlen($args_previous) - 1);
   //echo "<hr>".$args_previous."<hr>";
   $objOauth = new TOAuthClient($consumerKey, $consumerSecret);   
   $objOauth->setToken($_SESSION['VL_TOKEN'], $_SESSION['VL_TOKEN_SECRET']);
   $objOauth->setApiUrl("http://www.meusgastos.com.br/api/v1/transaction/select?{$args_previous}&type=2");
   $oauthHeaders['oauth_signature'] =  $objOauth->signedString();
   //echo "<hr>".$objOauth."<hr>";
   $_h = array('Expect:');
   $_h[] = $objOauth->getHeader();
   $ch = curl_init();
   
   // set URL and other appropriate options
   curl_setopt($ch, CURLOPT_URL,       "http://www.meusgastos.com.br/api/v1/transaction/select?{$args_previous}&type=2");
   curl_setopt($ch, CURLOPT_HEADER,       0);
   curl_setopt($ch, CURLOPT_HTTPHEADER, $_h);
   
   // grab URL and pass it to the browser
   ob_start();
   curl_exec($ch);
   $response_previous = ob_get_clean();
   curl_close($ch);         
   //echo "<hr>".$response_previous."<hr>";
   $array_previous = json_decode($response_previous, true);
   if($array_previous["status"] == 200)
   { 
      $saldo_anterior = ($array_previous["data"][0]["IN_EVENTO"] == 1) ? $array_previous["data"][0]["VL_EVENTO"] : -$array_previous["data"][0]["VL_EVENTO"];   
   }
   else 
   {
      $status = $array_previous['status'];
      $detail = $array_previous['detail'];
      $status = 401;
      $detail = "Forbidden";
      header("HTTP/1.0 {$status} {$detail}");
      header("Status: {$status} {$detail}");
      header("content-type: application/json");   
      echo $response_previous;
      die;
   }
   //Agora puxa o fluxo de caixa existente      
   $objOauth = new TOAuthClient($consumerKey, $consumerSecret);   
   $objOauth->setToken($_SESSION['VL_TOKEN'], $_SESSION['VL_TOKEN_SECRET']);
   $objOauth->setApiUrl("http://www.meusgastos.com.br/api/v1/transaction/select?{$get_args}&type=2");
   $oauthHeaders['oauth_signature'] =  $objOauth->signedString();
 
   $_h = array('Expect:');
   $_h[] = $objOauth->getHeader();
   $ch = curl_init();
   
   // set URL and other appropriate options
   curl_setopt($ch, CURLOPT_URL,       "http://www.meusgastos.com.br/api/v1/transaction/select?{$get_args}&type=2");
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
      //echo "<hr>".$response."<hr>";
      $anterior =  array( 
                           "CD_EVENTO"        => 0, 
                           "DC_EVENTO"        => "Saldo acumulado anteriormente", 
                           "DT_EVENTO"        => "", 
                           "IN_EVENTO"        => $array_previous["data"][0]["IN_EVENTO"],
                           "VL_EVENTO"        => $array_previous["data"][0]["VL_EVENTO"],
                           "TP_EVENTO"        => $array_previous["data"][0]["TP_EVENTO"],
                           "CATEGORIA"        => array
                           											 (
                           											 "CD_CATEGORIA"     => 0,
                           											 "NM_CATEGORIA"     => "",
                           											 "CD_SUPERIOR"      => 0,
                           											 "NM_SUPERIOR"      => ""
                           											 ),
                           "CD_RECORRENTE"    => $row["CD_RECORRENTE"],  
                         );
      if(!empty($array['data']))
      {
         array_unshift($array['data'], $anterior);
      }
      else 
      {
         $array['data'] = array(0 => $anterior);
      }
      echo json_encode($array);
   }
   else 
   {
      $status = $array['status'];
      $detail = $array['detail'];
      $status = 401;
      $detail = "Forbidden";
      header("HTTP/1.0 {$status} {$detail}");
      header("Status: {$status} {$detail}");
      header("content-type: application/json");   
      echo $response;
   }
?>
