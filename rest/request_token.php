<?php
   session_start();
   include "basico.php";
   if( !cURLcheckBasicFunctions() ) die("UNAVAILABLE: cURL Basic Functions");
   include "TOAuthClient.php";  
   $url = "http://www.meusgastos.com.br/api/v1/request_token";
   
   $objOauth = new TOAuthClient($consumerKey, $consumerSecret);
   $objOauth->AddParam("oauth_callback", "http://localhost/labs/money/request_token_ready.php");   
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
   parse_str($response);
   
   //Verifica se já tem um dado de usuario em processo e limpa
   ExecutaQuery("DELETE FROM `u_meusgastos_request_tokens` WHERE CD_USUARIO = {$_SESSION['cd_usuario']}");
   
   //Salva o novo processo para consulta futura
   $sql = "INSERT INTO `u_meusgastos_request_tokens`  (`CD_USUARIO`,             `VL_TOKEN`,       `VL_TOKEN_SECRET`,      `VL_VERIFIER`) 
                 VALUES                               ({$_SESSION['cd_usuario']}, '$oauth_token',   '$oauth_token_secret',  NULL        )";
   $rs    = ExecutaQuery($sql);
   
   
   header("location: http://www.meusgastos.com.br/api/v1/authorize?oauth_token=$oauth_token");
   //echo "<ul><li>oauth_token: $oauth_token</li>";
   //echo "<li>oauth_token_secret: $oauth_token_secret</li></ul>";
   
   // close cURL resource, and free up system resources
   curl_close($ch);
?>
