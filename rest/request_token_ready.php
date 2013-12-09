<?php
   session_start();
   include "basico.php";
   if( !cURLcheckBasicFunctions() ) die("UNAVAILABLE: cURL Basic Functions");
   include "TOAuthClient.php";  

   $sql   = "SELECT * FROM `u_meusgastos_request_tokens` WHERE VL_TOKEN = '{$_GET['oauth_token']}'";
   $rs    = ExecutaQuery($sql);  
   
   $row   = mysql_fetch_array($rs);
   $oauth_token_secret  = $row['VL_TOKEN_SECRET'];
   $oauth_token         = $_GET['oauth_token'];
   $oauth_verifier      = $_GET['oauth_verifier']; 
   $url                 = "http://www.meusgastos.com.br/api/v1/access_token";
   
   $objOauth = new TOAuthClient($consumerKey, $consumerSecret);
   $objOauth->setToken("{$oauth_token}", "{$oauth_token_secret}");
   $objOauth->AddParam("oauth_verifier", "{$oauth_verifier}");
   $objOauth->setApiUrl($url);
   $oauthHeaders['oauth_signature'] =  $objOauth->signedString();
   
   $_h = array('Expect:');
   $_h[] = $objOauth->getHeader();
   $ch = curl_init();
   
   // set URL and other appropriate options
   curl_setopt($ch, CURLOPT_URL,       $url);
   curl_setopt($ch, CURLOPT_HEADER,       0);
   curl_setopt($ch, CURLOPT_HTTPHEADER, $_h);
   
   // grab URL and pass it to the browser
   ob_start();
   curl_exec($ch);
   $response = ob_get_clean();
   unset($oauth_token);
   unset($oauth_token_secret);
   
   parse_str($response);   
   $rs    = ExecutaQuery("INSERT INTO `u_meusgastos_tokens` (`CD_USUARIO`,             `VL_TOKEN`,    `VL_TOKEN_SECRET`) 
                               VALUES                       ({$_SESSION['cd_usuario']}, '$oauth_token', '$oauth_token_secret')");
   
   // close cURL resource, and free up system resources
   curl_close($ch);
   
   header("location: webapp.php");
   ?> 
