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
   
   //Preparando o argumento get
   $get_args = "";
   foreach ($_GET as $key => $value)  $get_args .= "{$key}={$value}&";
   $get_args = substr($get_args, 0, strlen($get_args) - 1);
   
   if(!function_exists("FullPathFile")) include "../basico.php";
   if(!class_exists("TOAuthClient"))    include "TOAuthClient.php";

   //Primeiro o saldo acumulado, para isso preciso voltar 1 dia na data final
   
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
   
   $objOauth = new TOAuthClient($consumerKey, $consumerSecret);   
   $objOauth->setToken($_SESSION['VL_TOKEN'], $_SESSION['VL_TOKEN_SECRET']);
   $objOauth->setApiUrl("http://www.meusgastos.com.br/api/v1/transaction/select?{$args_previous}&type=2");
   $oauthHeaders['oauth_signature'] =  $objOauth->signedString();
   
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
   /*
   echo "Response puro: <pre>";
   var_dump($response_previous);
   echo "</pre>Response object: <pre>";
   var_dump(json_decode($response_previous));
   echo "</pre>Response array: <pre>";
   var_dump(json_decode($response_previous, true));
   echo "</pre>";
    */
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
      //header("content-type: application/json");   
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
      require_once '../fusioncharts/php/FusionCharts_Gen.php';
      # Create Column3D chart Object
      //$FC = new FusionCharts("MSColumn2DLineDY","780","400", "mg_mensal", 1);
      $FC = new FusionCharts("FCF_Line","780","400", "mg_mensal", 1);
      
      # Set chart attributes
      $strParam="showAlternateHGridColor=1;numberScaleUnit=mil,mm;caption=Valores em Reais;numberPrefix=R$;decimalPrecision=0;formatNumberScale=0;showValues=0;showNames=1;chartRightMargin=25;chartLeftMargin=25;chartBottomMargin=0;animation=1;rotateNames=1";
      $FC->setChartParams($strParam);
      
      $saldo_acumulado  = array();
      $saldo            = array();
      $min_data         = 999999999;
      $max_data         = -1 * $min_data;
      
      foreach($array["data"] as $data => $evento)
      {	        
         //$id           = $evento["DT_EVENTO"];
         list($ano, $mes, $dia)  = explode("-", $evento["DT_EVENTO"]);
         $id = mktime(0,0,0,$mes,$dia,$ano)/(24*60*60);
         $min_data = ($min_data > $id) ? $id : $min_data;
         $max_data = ($max_data < $id) ? $id : $max_data;
         if($evento["IN_EVENTO"] == 1)
         {
            $saldo[$id]   += $evento["VL_EVENTO"];
         }
         else 
         {
            $saldo[$id]  -= $evento["VL_EVENTO"];
         } 
      }
      $saldo_acumulado[$min_data] = $saldo[$min_data] + $saldo_anterior;
      for($id = $min_data+1; $id <= $max_data; $id++)
      {
         $saldo_acumulado[$id] = $saldo_acumulado[$id - 1] + $saldo[$id];
      }

      $FC->addDataset("Saldo Acumulado","showValues=No;color=303030");
      foreach($saldo_acumulado as $id => $value) $FC->addCategory($id);
      foreach($saldo_acumulado as $id => $valor)
      {
         $strData = date('d/m/Y', $id*24*60*60);
         $FC->addChartData($valor, "name={$strData}");
      }    	  
      $xml = $FC->getXML();	
      echo $xml;
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
