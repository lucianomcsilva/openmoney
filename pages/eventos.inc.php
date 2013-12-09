<?php
   $base_url_rest = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
?>
<h1>Adicionar Eventos</h1>
<p>Para manter um bom controle das suas receitas e despesas escolha em qual categoria classificá-las.  Caso seja uma despesa recorrente (se repete ao longo do tempo) marque a caixa abaixo e deixe o sistema controla-las por você. </p>
<script type="text/javascript" src="http://jquery-ui.googlecode.com/svn/trunk/ui/i18n/jquery.ui.datepicker-pt-BR.js"></script>
<script src="../jquery.numeric/jquery.numeric.js"></script>
<style>
   .tdEvento
   {
      height: 20px;
      padding: 8px;
   }
   .titleEvento
   {
      font-family: Verdana;
      font-size: 12px;
      font-weight: bold;
      margin-bottom: 5px;      
   }
    #divRecorrente { display: none; }
</style>
<script>
   $(function() 
   {
      $( "#datepicker" ).datepicker();

      $( "#btCancelar" ).button();
      $( "#btProcessar" ).button();
      
      $( "#cbCredito" ).button();
      $(".cb-enable").click(function()
      {
           var parent = $(this).parents('.switch');
           $('.cb-disable',parent).removeClass('selected');
           $(this).addClass('selected');
           $('.checkbox',parent).attr('checked', true);
           $("#cbCredito").attr("checked", true);
       });
       $(".cb-disable").click(function()
       {
           var parent = $(this).parents('.switch');
           $('.cb-enable',parent).removeClass('selected');
           $(this).addClass('selected');
           $('.checkbox',parent).attr('checked', false);
           $("#cbCredito").attr("checked", false);
       })
       $(".numeric").numeric({ decimal : ",", negative : false  });
       $("#iQuantidadeRepeticao").numeric({ decimal : false, negative : false  });
       
       var show_recorrente = function() 
       {
          if( $('#cbAtivaRecorrente').attr("checked") == "checked")
          {
             $('#divRecorrente').toggle("fast");
             $('html, body').animate({scrollTop: 2000}, 800);
          }
          else
          {
             $('#divRecorrente').toggle("fast");
             $('html, body').animate({scrollTop: 2000}, 800);
          }
       }
       $('#cbAtivaRecorrente').click(show_recorrente);
       $('#sAtivaRecorrente').click(show_recorrente);
       
      //Mensagens de aviso
      $("#dialog-vazio").dialog
      ({
         resizable: false,
         autoOpen: false,
         height:150,
         width: 360,
         modal: true,
         buttons: {
            "OK": function() {
               $( this ).dialog( "close" );
            } 
         }
      });        
      
      $("#dialog-valor").dialog
      ({
         resizable: false,
         autoOpen: false,
         height:150,
         width: 420,
         modal: true,
         buttons: {
            "OK": function() {
               $( this ).dialog( "close" );
            } 
         }
      });  
      
      $("#dialog-frequencia").dialog
      ({
         resizable: false,
         autoOpen: false,
         height:180,
         width: 400,
         modal: true,
         buttons: {
            "OK": function() {
               $( this ).dialog( "close" );
            } 
         }
      });      
   });
   //--------------------------------------------------------------------------
   var processa_evento = function()
   {
      //$.datepicker.dateFormat = 'mm/dd/yy';
      $( "#datepicker" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
      var dt_evento     = $("#datepicker").val();
      //var dt_evento     = $.datepicker.formatDate('yy-mm-dd', $("#datepicker"));
      var dc_evento     = $("#iDescricao").val();
      var cd_categoria  = $("#sCategoria").val();
      var in_evento     = ($('#cbCredito').attr("checked") == "checked") ? 1 : 0;
      var vl_evento     = parseFloat($("#iValor").val().replace(',', '.')); 
      
      //Verifica se é recorrente
      if( $('#cbAtivaRecorrente').attr("checked") == "checked")
      {
         var qt_eventos    = $("#iQuantidadeRepeticao").val();
         var tp_recorrente = $("#selTipo").val(); 
         var tp_evento     = 1;
      }
      else
      {
         var qt_eventos    = 1;
         var tp_recorrente = -1;
         var tp_evento     = 0; 
      }
      
      //Checagem de campos obrigatórios
      if(dc_evento == "" )
      {
         $("#dialog-vazio").dialog("open");
         return;         
      }
      
      if(vl_evento == 0.00 )
      {
         $("#dialog-valor").dialog("open");
         return;         
      }
      if( $('#cbAtivaRecorrente').attr("checked") == "checked" & (qt_eventos <= 1 || qt_eventos > 100))
      {
         $("#dialog-frequencia").dialog("open");
         return;  
      }
      panel_wait.show();
      var request = $.ajax(
      {
         url: "<?=$base_url_rest?>rest/save",
         context: document.body,
         data: 
         {
            cd_evento:     -1,
            dt_evento:     dt_evento,
            dc_evento:     dc_evento,
            cd_categoria:  cd_categoria,
            vl_evento:     vl_evento,
            in_evento:     in_evento,
            qt_eventos:    qt_eventos,
            tp_evento:     tp_evento,
            tp_recorrente: tp_recorrente
         },
         success: function(data, textStatus, jqXHR)
         {
            $("#formEventos").get(0).reset();
            clean_form();
            panel_wait.hide();
         },
         error: function(data, textStatus, jqXHR) 
         {
            panel_wait.hide();
            alert("ocorreu um erro inesperado. Tente novamente.")
         } 
      });

   }
   //--------------------------------------------------------------------------
   var clean_form = function()
   {
      if( $('#cbAtivaRecorrente').attr("checked") == "checked")
      {
          $('#cbAtivaRecorrente').attr('checked', !$('#cbAtivaRecorrente').attr('checked'))
          $('#divRecorrente').toggle("fast");
          $('html, body').animate({scrollTop: 2000}, 800);
      }      
   }
</script>
   
   <!-- ui-dialog descrição vazia -->
   <div id="dialog-vazio" title="Descrição vazia">
      <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Digite a descrição do evento antes de continuar</p>
   </div>
   
   <!-- ui-dialog nome categoria -->
   <div id="dialog-valor" title="Descrição vazia">
      <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Digite o valor do evento. Ele deve ser maior que zero.</p>
   </div>
   
   <!-- ui-dialog nome categoria -->
   <div id="dialog-frequencia" title="Descrição vazia">
      <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Eventos recorrentes devem se repetir um mínimo de 2 até um máximo de 100 eventos por inserção. </p>
   </div>
   
<form id="formEventos" onsubmit="return false;" onreset="clean_form();">
<table border=1 width="100%" class="tabelaEvento">
   <tr>
      <td rowspan="4" style="width:200px"><div id="datepicker"></div></td>
      <td class="tdEvento">
         <p class="titleEvento">Descrição</p>
         <input type="text" id="iDescricao" value="" style="width:430px; height: 30px; font-size: 14px; font-weight: bold;">
      </td>
   </tr>
   <tr>
      <td class="tdEvento">
         <p class="titleEvento">Categoria</p>
         <select id="sCategoria" style="width:430px; height: 30px;">
         <?php
            ob_start();
            include FullPathFile('rest/categorias_select.php');
            $response = ob_get_clean();

            $array = json_decode($response, true);
            foreach ($array as $key => $value) :
               if($value['CD_SUPERIOR'] > 0) continue; 
         ?>
            <option style="height: 30px;" value="<?=$value['CD_CATEGORIA']?>"><?=$value['NM_CATEGORIA']?></option>
         <?php
               foreach ($array as $subkey => $subvalue) :
                   if($value['CD_CATEGORIA'] != $subvalue['CD_SUPERIOR']) continue;
         ?>
            <option value="<?=$subvalue['CD_CATEGORIA']?>">&nbsp;&nbsp;&nbsp;<?=$subvalue['NM_CATEGORIA']?></option>
         <?php
               endforeach;
            endforeach;
         ?>
         ></select>
      </td>
   </tr>
   <tr>
      <td class="tdEvento">
         <p class="titleEvento">Tipo de Evento</p>
         <p class="field switch" style="margin:0px; padding:0px;">
             <label class="cb-enable"><span style="width:195px">Receitas (Crédito)</span></label>
             <label class="cb-disable selected"><span style="width:195px">Despesas (Débito)</span></label>
             <input type="checkbox" id="cbCredito" class="checkbox" name="cbCredito" />
         </p>
      </td>
   </tr>
     <tr>
      <td class="tdEvento">
         <p class="titleEvento">Valor <span style="font-weight: normal;">(use virgula como separador decimal)</span></p>
         <input class="numeric" type="text" style="width:430px; height: 30px; font-size: 14px; font-weight: bold;" id="iValor" value="0,00">
      </td>
   </tr>
</table> 
<div id="divRecorrente">
   <table width="100%" border=1>
      <tr>
         <td class="tdEvento" style="width:266px">
            <p class="titleEvento">Tipo de Repetição</p>
            <select id="selTipo" style="width:255px;">
               <option value="0">Diário</option>
               <option value="1">Semanal</option>
               <option value="2" selected>Mensal</option>
               <option value="3">Anual</option>               
            </select>    
         </td>
         <td class="tdEvento">
            <p class="titleEvento">Quantidade de Repetições (2 a 100)</p>
            <input type="text" id="iQuantidadeRepeticao" value="3"  style="width:430px; height: 30px; font-size: 14px; font-weight: bold;"  class="numeric">
         </td>
      </tr>
   </table>
</div>
<input type="checkbox" id="cbAtivaRecorrente"> <span style="cursor:pointer" id="sAtivaRecorrente" onclick="$('#cbAtivaRecorrente').attr('checked', !$('#cbAtivaRecorrente').attr('checked'))";>Este evento é recorrente.</span>
<div style="text-align: center; clear:both;">
   <input  id="btCancelar" value="Cancelar" type="reset"/>
   <button id="btProcessar" onclick="processa_evento(); return false;">Processar</button>
</div>
</form>

