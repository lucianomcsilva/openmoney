<?php
   session_start();
   include "basico.php";
   include "TOAuthClient.php";
   $page = $_GET['page'];
   $base_url = dirname($_SERVER['SCRIPT_NAME']);
   $restricted_pages = array("home", "categorias", "eventos", "relatorios-categorias", "relatorios-fluxo", "relatorios-dia", "relatorios-semana", "relatorios-comparativo", "sair");
   if(!in_array($page, $restricted_pages))
   {
      echo ("Página não encontrada ($page)");
      return;
   }
   if(!isset($_SESSION['cd_usuario_money']))
   {
      $page = "login";
   }
   if($page == "sair")
   {
      session_destroy();
      header("location:http://{$_SERVER['HTTP_HOST']}{$base_url}/");
      $page = "login";   
      return; 
   }   
   
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head> 
		<title>Open Money - Projeto Open Source de controle financeiro</title>
      <link rel="shortcut icon" href="<?="http://{$_SERVER['HTTP_HOST']}{$base_url}/"?>favicon.ico" type="image/x-icon" />
      <!-- YUI 2.9.0 -->
	   <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/combo?2.9.0/build/grids/grids-min.css&2.9.0/build/button/assets/skins/sam/button.css&2.9.0/build/container/assets/skins/sam/container.css&2.9.0/build/datatable/assets/skins/sam/datatable.css">
      <script type="text/javascript" src="http://yui.yahooapis.com/combo?2.9.0/build/yahoo/yahoo-min.js&2.9.0/build/dom/dom-min.js&2.9.0/build/event/event-min.js&2.9.0/build/event-delegate/event-delegate-min.js&2.9.0/build/event-mouseenter/event-mouseenter-min.js&2.9.0/build/element/element-min.js&2.9.0/build/button/button-min.js&2.9.0/build/connection/connection-min.js&2.9.0/build/container/container-min.js&2.9.0/build/datasource/datasource-min.js&2.9.0/build/datatable/datatable-min.js&2.9.0/build/element-delegate/element-delegate-min.js&2.9.0/build/event-simulate/event-simulate-min.js&2.9.0/build/json/json-min.js"></script>
	   
      <link type="text/css" href="<?=$base_url;?>/open_money.css" rel="stylesheet" />
      <link type="text/css" href="<?=$base_url;?>/open_money_site.css" rel="stylesheet" />
		<link type="text/css" href="<?=$base_url;?>/jquery-ui-1.8.17.custom/css/custom-theme/jquery-ui-1.8.17.custom.css" rel="stylesheet" />
		<script src="<?=$base_url;?>/jquery-ui-1.8.17.custom/js/jquery-1.7.1.min.js"></script>
      <script src="<?=$base_url;?>/jquery-ui-1.8.17.custom/js/jquery-ui-1.8.17.custom.min.js"></script>
      <script type="text/javascript" src="<?=$base_url;?>/jquery.ui.datepicker-pt-BR.js"></script>
      
      <meta http-equiv="content-type" content="text/html; charset=utf-8">
	</head>
	
	<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" class="yui-skin-sam" >
      <div style="margin-bottom:50px; background-color: #306030; width:100%; color: #ccc; font-size: 14px; text-align: left; padding-top: 10px; padding-bottom: 10px; border-bottom: 5px solid #000000;">
         <div style="float:left; margin-left: 20px;">
            <img src="http://www.zeetha.com/open/money/images/open_money_logo.png" width=128>
         </div>
         <div style="float:left; margin-top: 10px; margin-left: 20px;">
            <p class="titulo_cabecalho">Open Money</p>
            <p class="frase_cabecalho">Projeto Open Source de controle financeiro</p>
            <div class="divmenu_cabecalho">
               <a href="http://www.zeetha.com/open/money/" class="menu_cabecalho">Home</a>  &#8226;  
               <a href="http://www.zeetha.com/open/money/sobre/" class="menu_cabecalho">Sobre o Projeto</a>  &#8226;
               <a href="http://www.zeetha.com/open/money/trial/" class="menu_cabecalho menu_cabecalho_selected">Demonstração</a>  &#8226;
               <a href="http://www.zeetha.com/open/money/cadastro/" class="menu_cabecalho">Cadastro</a>  &#8226;
               <a href="http://www.zeetha.com/open/money/doc/" class="menu_cabecalho">Documentação</a>  &#8226;
               <a href="http://www.zeetha.com/open/money/contato/" class="menu_cabecalho">Contato</a>
            </div>
         </div>
         <div style="clear:both"></div>      
      </div>	   
		<div style="width:1000px; min-height: 500px; margin-left: auto; margin-right: auto;">		
         <?php
		      if($page == "login") 
            {
               include 'login.php';
            }
            else
            {
               include 'webapp.php';
            } 
		   ?>
      </div>  
      <div style="margin-top:50px; border-top: 5px solid #000; background-color: #606060; width:100%; color: #ccc; font-size: 14px; text-align: center; padding-top: 10px; padding-bottom: 10px;">
         Zeetha&trade; 2010-2012  - 
         New generation apps <br>
         <a href='http://www.meusgastos.com.br'>www.meusgastos.com.br</a> &#8226;  <a href='http://www.meusgastos.com.br'>www.meuspostos.com.br</a>
      </div>
			
      <!-- Barra de Progresso -->
      <div id="PanelWait">
         <div class="hd" style="font-size: 12px;">Processando as informações...</div>
         <div class="bd">
            <img src="http://l.yimg.com/a/i/us/per/gr/gp/rel_interstitial_loading.gif">      
         </div>
      </div>  
      <script>
         var panel_wait;         
         var IniciaPanelWait = function()
         {  
            //IniciaPanelWait
            panel_wait = new YAHOO.widget.SimpleDialog("PanelWait", 
            { 
              width:"240px",
              height: "60px", 
              visible:true,
              fixedcenter: true,
              modal:true,
              close: false,        
              constraintoviewport:true            
            });
            document.getElementById("PanelWait").style.display = "block";
            panel_wait.render();
            panel_wait.hide();
            
         };
         
         YAHOO.util.Event.addListener(window, "load", function() 
         {
             IniciaPanelWait();
         });   
         
         function goTo(url)
         {
            location.href = "<?=$base_url;?>/" + url;
         }         
      </script>
      <script type="text/javascript">
      
        var _gaq = _gaq || [];
        _gaq.push(['_setAccount', 'UA-33886764-1']);
        _gaq.push(['_setDomainName', 'zeetha.com']);
        _gaq.push(['_trackPageview']);
      
        (function() {
          var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
          ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
          var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
        })();
      
      </script>      
   </body>
</html>