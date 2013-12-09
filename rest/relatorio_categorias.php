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

   //echo $response;
   $array = json_decode($response, true);
   if($array["status"] == 200)
   {
   		$nome    = array();
   		$credito = array();
   		$debito  = array();
   		$saldo   = array();
   		$total = 0;
   		foreach($array["data"] as $data => $evento)
   		{
   			
   			$id 				 = $evento["CATEGORIA"]["CD_CATEGORIA"];
   			$nome[$id] 	 = $evento["CATEGORIA"]["NM_CATEGORIA"]; 
   			if($evento["IN_EVENTO"] == 1)
   			{
   				$credito[$id] += $evento["VL_EVENTO"];
   				$saldo[$id]   += $evento["VL_EVENTO"];
   			}
   			else 
   			{
   				$debito[$id] += $evento["VL_EVENTO"];
   				$saldo[$id]  -= $evento["VL_EVENTO"];
   			} 
   	  }
   	  
   	  $data = array();
   	  foreach($nome as $id => $value)
   	  {
   	     $data[] = array(
   	     								 "nm_categoria" => $nome[$id],
								   	     "vl_credito" 	=> Coalesce($credito[$id] , 0),
								   	     "vl_debito" 		=> Coalesce($debito[$id]  , 0),
								   	     "vl_saldo" 		=> Coalesce($saldo[$id]   , 0)
   	     								);
   	  }
   	  echo json_encode(array("data"=> $data));
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
