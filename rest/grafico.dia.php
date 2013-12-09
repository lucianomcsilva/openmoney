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
  	      require_once '../fusioncharts/php/FusionCharts_Gen.php';
  	      # Create Column3D chart Object
   	   $FC = new FusionCharts("MSColumn2DLineDY","780","400", "mg_mensal", 1);
	    
         # Set chart attributes
         $strParam="showAlternateHGridColor=1;numberScaleUnit=mil,mm;caption=Valores em Reais;numberPrefix=R$;decimalPrecision=0;formatNumberScale=0;showValues=1;showNames=1;chartRightMargin=25;chartLeftMargin=25;chartBottomMargin=0;showPercentageValues=0;pieSliceDepth=20;pieFillAlpha=50;showPercentageInLabel=1;pieYScale=60;pieRadius=200;animation=1";
         $FC->setChartParams($strParam);
      	  
         $dia     = array();
         $credito = array();
         $debito  = array();
         $saldo   = array();
         $total   = 0;
         foreach($array["data"] as $data => $evento)
         {
            
            //$id           = $evento["DT_EVENTO"];
            list($ano, $mes, $dia)  = explode("-", $evento["DT_EVENTO"]);
            $id = date("j", mktime(0,0,0,$mes,$dia,$ano));
            //$semana[$id]    = $id; 
            //$nome[$id]    = $evento["CATEGORIA"]["NM_CATEGORIA"]; 
            if($evento["IN_EVENTO"] == 1)
            {
               $credito[$id] += $evento["VL_EVENTO"];
               $saldo[$id]   += $evento["VL_EVENTO"];
            }
            else 
            {
               $debito[$id] += $evento["VL_EVENTO"];
               $saldo[$id]  += $evento["VL_EVENTO"];
            } 
        }
        $nm_semana = array(0 => "Domingo", "Segunda", "Terça", "Quarta", "Quinta", "Sexta", "Sábado");
        $data = array();
        //foreach($semana as $id => $value)
        
        for($id = 1; $id < 32; $id++)
        {
           $data[] = array(
                                  "nm_semana"      => $id,
                                   "vl_credito"    => Coalesce($credito[$id] , 0),
                                   "vl_debito"     => Coalesce($debito[$id]  , 0),
                                   "vl_saldo"      => Coalesce($saldo[$id]   , 0)
                           );
        }
        
        $acumulado = array();
        $acumulado[0] = 0;
        for($id = 1; $id < 32; $id++) { $acumulado[$id] = $acumulado[$id - 1] + $saldo[$id]; }
        
        for ($i = 1; $i < 32 ; $i++ ) $FC->addCategory($i);
            	  
   	  $FC->addDataset("Diário","showValues=0;color=909090");
   	  foreach($data as $id => $value) { $FC->addChartData($saldo[$id]); }
   	  
        $FC->addDataset("Diário Acumulado","parentYAxis=S;showValues=0;color=303030");
        for($id = 1; $id < 32; $id++)
        {
           $FC->addChartData($acumulado[$id]);
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