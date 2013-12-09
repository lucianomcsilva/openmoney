<?php
   session_start();
   $usuario = $_POST['login'];
   $senha   = $_POST['senha'];
   header("content-type: application/json");  
   /** Execute sua validação aqui, e guarde nas váriaveis abaixo as informações
    *    para login. cd_usuario_money é utilizado localmente caso queira
    *    integrar com outros recursos seus. TOKENs sao necessários para o
    *    script funcionar. NM_USUARIO_MONEY serve para escrever o nome do usuario
    *    na tela.
   */
   if($usuario == "openmoney" & $senha == "openmoney")
   {//Logar
      $_SESSION['cd_usuario_money'] = 1;
      $_SESSION['VL_TOKEN']         = 'TOKEN_USUARIO';
      $_SESSION['VL_TOKEN_SECRET']  = 'SECRET_USUARIO';
      $_SESSION['NM_USUARIO_MONEY'] = "Open Money";
      echo json_encode(array("status" => 1));
   } 
   else 
   {//Avisar que deu erro
      echo json_encode(array("status" => 0));   
   }   
?>