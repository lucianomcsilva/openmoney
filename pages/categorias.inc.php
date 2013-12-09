<?php
   if(!isset($_SESSION['cd_usuario_money']))
   {
      header("location: index.php");
   }

   if(!function_exists("FullPathFile")) include "basico.php";
   if(!class_exists("TOAuthClient"))    FullPathFile('rest/TOAuthClient.php');
       
   ob_start();
   include FullPathFile('rest/categorias_select.php');
   $response = ob_get_clean();
   $array = json_decode($response, true);
?>
   <style>
      .tabelaZeetha
      {
         border: 1px solid #606060;
      }
      .tabelaZeetha th
      {
         background: #606060;
         color: #FFFFFF;
      }

      .tabelaZeetha tr:nth-child(even)
      {
         background: #E0E0E0;
      }
      .tabelaZeetha .edit 
      {
         cursor: pointer;
         text-align: center;
      }
      .ui-dialog-content p 
      {
         text-align: justify;
      }
      .ui-dialog-content p #iNomeCategoriaDelete
      {
         color: #DD3333;
         font-weight: bold;   
      }
		#btNovaCategoria {padding: .4em 1em .4em 20px;text-decoration: none;position: relative;}
		#btNovaCategoria span.ui-icon {margin: 0 5px 0 0;position: absolute;left: .2em;top: 50%;margin-top: -8px;}
   </style>
   <script> 
      var cd_categoria_apagar = -1;
      //-----------------------------------------------------------------------
      var IniciaCategorias = function ()
      {
			// Dialog			
			$('#dialog').dialog({
				autoOpen: false,
				modal: true,
				resizable: false,
				width: 480,
				top: 200,
				buttons: 
				{
					"Ok": function() 
					{ 
					   if( $('#iDescricao').val() == '') 
					   {
					      $("#dialog-vazio").dialog("open");
					      return;
					   }
                  var request = $.ajax(
                  {
                     url: "<?=$base_url_rest?>rest/save",
                     context: document.body,
                     data: 
                     {
                        cd_categoria: $("#iCodigoCategoria").val(),
                        nm_categoria: $("#iDescricao").val(),
                        cd_superior: $("#selCategoriaPai").val()
                     },
                     success: function(data, textStatus, jqXHR)
                     {
                        window.location = window.location;
                     },
                     error: function(data, textStatus, jqXHR) 
                     {
                        panel_wait.hide();
                        alert("ocorreu um erro inesperado. Tente novamente.")
                     } 
                  });
					}, 
					"Cancel": function() 
					{ 
						$(this).dialog("close"); 
					} 
				}
			});
         
         $("#dialog-delete" ).dialog
         ({
   			resizable: false,
   			autoOpen: false,
   			height:240,
   			width: 500,
   			modal: true,
   			buttons: {
   				"Apagar": function() 
   				{
   					$( this ).dialog( "close" );
                  var request = $.ajax(
                  {
                     url: "<?=$base_url_rest?>rest/delete",
                     context: document.body,
                     data: 
                     {
                        cd_categoria:  cd_categoria_apagar
                     },
                     success: function(data, textStatus, jqXHR)
                     {
                        //alert(data);
                        window.location = window.location;
                     },
                     error: function(data, textStatus, jqXHR) 
                     {
                        
                        panel_wait.hide();
                        alert(data.responseText);
                        //alert("ocorreu um erro inesperado. Tente novamente.")
                     } 
                  });
   					
   				},
   				Cancel: function() 
   				{
   				   cd_categoria_apagar = -1; // evite acidentes!
   					$( this ).dialog( "close" );
   				}
   			}
   		}); 
   		
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
            
            
			//hover states on the static widgets
			$('#btNovaCategoria').hover(
				function() { $(this).addClass('ui-state-hover'); }, 
				function() { $(this).removeClass('ui-state-hover'); }
			);
			
			//Criando a lista de categorias superior possiveis
         fillSelectCategoriaSuperior();
		}
      //-----------------------------------------------------------------------
      var fillSelectCategoriaSuperior = function()
      {
         $('#selCategoriaPai').empty() 
         $('#selCategoriaPai')
            .append(
               $("<option></option>")
                  .attr("value", 0)
                  .text("-- Nenhuma --")
            ); 
         for (var i=0; i < lisCategorias.length; i++) 
         {
            if(lisCategorias[i].CD_SUPERIOR == 0)
            {
               $('#selCategoriaPai')
                  .append(
                     $("<option></option>")
                        .attr("value", lisCategorias[i].CD_CATEGORIA)
                        .text(lisCategorias[i].NM_CATEGORIA)
                  ); 
            }
         };         
      }
      //-----------------------------------------------------------------------
      var dialogoCategoriaApaga = function(cd_categoria)
		{
		   
         $('#iNomeCategoriaDelete').html(getNomeById(cd_categoria));
         $('#dialog-delete').dialog('open');
         cd_categoria_apagar = cd_categoria;
         return false;
      }
      //-----------------------------------------------------------------------
      var dialogoCategoriaEdita = function(cd_categoria)
		{
		   $("#dialog").dialog({ title: 'Entre com os dados que deseja atualizar' });
		   $("#iCodigoCategoria").val(cd_categoria);
		   $('#iDescricao').val(getNomeById(cd_categoria));
		   $('#selCategoriaPai').val(getSuperiorById(cd_categoria));
         $('#dialog').dialog('open');
         return false;
      }
      //-----------------------------------------------------------------------
      var dialogoCategoriaNovo = function()
		{
		   $("#dialog").dialog({ title: 'Entre com os dados da nova categoria' });
         $("#iCodigoCategoria").val(-1);
         $('#iDescricao').val("");
         $('#selCategoriaPai').val(-1);
         $('#dialog').dialog('open');
         return false;
      }
		//-----------------------------------------------------------------------
		function getNomeById(CdCategoria)
		{
         for (var i=0; i < lisCategorias.length; i++) 
         {
            if(lisCategorias[i].CD_CATEGORIA == CdCategoria)
               return lisCategorias[i].NM_CATEGORIA;
         };
         return false;	
		}
		//-----------------------------------------------------------------------
      function getSuperiorById(CdCategoria)
      {
         for (var i=0; i < lisCategorias.length; i++) 
         {
            if(lisCategorias[i].CD_CATEGORIA == CdCategoria)
               return lisCategorias[i].CD_SUPERIOR;
         };
         return false;
      }
		//-----------------------------------------------------------------------	
      <?php
		   echo "lisCategorias = ".json_encode($array);
         echo "\n";
      ?>
		//-----------------------------------------------------------------------
		setTimeout("IniciaCategorias()", 200);
   </script>
	
	

   <!-- ui-dialog -->
	<div id="dialog" title="Placeholder">
      <input id="iCodigoCategoria" type="hidden" value="">
      <table >
         <tr>
            <th style="width: 220px">Nome da categoria</th>
            <th style="width: 220px">Categoria Superior</th>
         </tr>
         <tr>
            <th><input id="iDescricao" type="text" value="" style="width: 200px"></th>
            <th><div id="divCategoriaPai">
               <select id="selCategoriaPai" style="width: 200px">
               </select>
            </div></th>
         </tr>
      </table>    
	</div>

   <!-- ui-dialog apagar -->
   <div id="dialog-vazio" title="Descrição vazia">
      <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Digite o nome da categoria antes de continuar</p>
   </div>
	
	<!-- ui-dialog apagar -->
   <div id="dialog-delete" title="Apagar a categorias">
   	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Você está prestes a apagar categoria <span id="iNomeCategoriaDelete">placeholder</span> e esta ação não pode ser desfeita. Tem certeza que deseja continuar?</p>
   </div>
	
		
   <h1>Edição de Categorias</h1>
   <p class="Explica">Aqui você pode criar, apagar ou editar cada uma das categorias e subcategorias utilizadas no site. Ninguém melhor do que você para determinar qual a melhor forma de classificar seus gastos! Fique a vontade.</p>
   <p class="Explica">Atualmente você possui <span id=pQtd><?php echo sizeof($array);?></span> itens entre categorias e subcatergorias, todas listadas abaixo. </p>
   <p id="btNovaCategoria" class="ui-state-default ui-corner-all" style="cursor:pointer" onclick="dialogoCategoriaNovo()"><span class="ui-icon ui-icon-newwin"></span>Clique aqui para adicionar uma nova categoria.</p>
   <br>

      <table class="tabelaZeetha" style="width:100%">
         <tr>
            <th colspan="2">Nome da Categoria</th>
            <th style="width:20px"> <img src="http://www.meusgastos.com.br/img/b_edit.png" /></th>
            <th style="width:20px"> <img src="http://www.meusgastos.com.br/img/b_drop.png" /></th>
         </tr>
      <?php
         //echo $response;
         //var_dump(json_decode($response, true));
         foreach ($array as $key => $value) :
            if($value['CD_SUPERIOR'] > 0) continue; 
      ?>
         <tr>
            <!-- <td><?=$value['CD_CATEGORIA']?></td> -->
            <td colspan="2" style="font-weight: bold;"><?=$value['NM_CATEGORIA']?></td>
            <td class="edit"> <img src="http://www.meusgastos.com.br/img/b_edit.png" onclick="dialogoCategoriaEdita(<?=$value['CD_CATEGORIA']?>)"/></td>
            <td class="edit"> <img src="http://www.meusgastos.com.br/img/b_drop.png" onclick="dialogoCategoriaApaga(<?=$value['CD_CATEGORIA']?>)"/></td>
         </tr>
      <?php
            foreach ($array as $subkey => $subvalue) :
                if($value['CD_CATEGORIA'] != $subvalue['CD_SUPERIOR']) continue;
      ?>
         <tr>
            <!-- <td><?=$subvalue['CD_CATEGORIA']?></td> -->
            <td style="width: 20px">&nbsp;</td>
            <td><?=$subvalue['NM_CATEGORIA']?></td>
            <td class="edit"> <img src="http://www.meusgastos.com.br/img/b_edit.png" onclick="dialogoCategoriaEdita(<?=$subvalue['CD_CATEGORIA']?>)"/></td>
            <td class="edit"> <img src="http://www.meusgastos.com.br/img/b_drop.png" onclick="dialogoCategoriaApaga(<?=$subvalue['CD_CATEGORIA']?>)"/></td>
         </tr>
      <?php
            endforeach;
         endforeach;
      ?>
      </table>