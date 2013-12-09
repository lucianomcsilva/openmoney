<?php
   $base_url_rest = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
?>
<script language='javascript' src='../../fusioncharts/library/FusionCharts.js'></script>
<script type="text/javascript" src="http://jquery-ui.googlecode.com/svn/trunk/ui/i18n/jquery.ui.datepicker-pt-BR.js"></script>
<script src="../../jquery.numeric/jquery.numeric.js"></script>
<style>
      .ui-dialog-content p #iEventoSimplesDelete, .ui-dialog-content p #iEventoRecorrenteDelete
      {
         color: #DD3333;
         font-weight: bold;   
      }
      .ui-dialog-content p 
      {
         text-align: justify;
      }
      .divWarning
      {
         background-color: #FFFFCC;
         border: 2px solid #B8B800;
         color: #00366B;
         text-align: left;
      }
         
</style>

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
<script type="text/javascript">
var myDataTable;
var myDataSource;
var myColumnDefs;
var callbackObj;
var global_CdRecorrente;
var global_CdEvento;
var global_DtEvento;
var global_InEvento;
var global_DcEvento;
var global_VlEvento;
var global_CdCategoria;
//-----------------------------------------------------------------------------
var reset_global = function()
{
   global_CdRecorrente = -1;
   global_CdEvento     = -1;
   global_DtEvento     = -1;
   global_InEvento     = -1;
   global_DcEvento     = -1;
   global_VlEvento     = -1;
   global_CdCategoria  = -1;
}
//-----------------------------------------------------------------------------
//Formatters  -----------------------------------------------------------------
//-----------------------------------------------------------------------------
var formatEdit = function(elCell, oRecord, oColumn, sData) 
{
    if(oRecord.getData("DC_EVENTO") == "Saldo acumulado anteriormente") 
      elCell.innerHTML = "";
    else
      elCell.innerHTML = "<span style='cursor:pointer' onclick='dialogoEventoEdita(" +  oRecord.getData("CD_EVENTO")  + ", " +  oRecord.getData("CD_RECORRENTE")  + ")'><img src='http://meusgastos.com.br/img/b_edit.png'></span>";
};
//-----------------------------------------------------------------------------  
var formatDelete = function(elCell, oRecord, oColumn, sData) 
{
    if(oRecord.getData("DC_EVENTO") == "Saldo acumulado anteriormente") 
      elCell.innerHTML = "";
    else
       elCell.innerHTML = "<span style='cursor:pointer' onclick='dialogoEventoApaga(\"" +  oRecord.getData("DC_EVENTO")  + "\", " +  oRecord.getData("CD_EVENTO")  + ", " +  oRecord.getData("CD_RECORRENTE")  + ")'><img src='http://meusgastos.com.br/img/b_drop.png'></span>";
};
//-----------------------------------------------------------------------------
var formatData = function(elCell, oRecord, oColumn, sData) 
{
    if(oRecord.getData("DC_EVENTO") == "Saldo acumulado anteriormente") 
      elCell.innerHTML = "Anterior";
    else
    {
       var datePart  = sData.match(/\d+/g);
       year          = datePart[0]; // get only two digits
       month         = datePart[1];
       day           = datePart[2];
       
       in_recorrente = (oRecord.getData("CD_RECORRENTE") > 0) ? "*" : "";
       
       elCell.innerHTML = day+'/'+month+'/'+year+in_recorrente;
    }
};
//-----------------------------------------------------------------------------
var formatCredito = function(elCell, oRecord, oColumn, sData) 
{
    elCell.innerHTML = (oRecord.getData("IN_EVENTO") == 0) ? "" : "<table style='width:100%; border:0px;' border=0 ><tr><td style='width:25px; border:0px;'>R$</td><td style='border:0px; text-align:right'>" + parseFloat(oRecord.getData("VL_EVENTO")).toFixed(2) + "</td></tr></table>";
};
var formatDebito = function(elCell, oRecord, oColumn, sData) 
{
    elCell.innerHTML = (oRecord.getData("IN_EVENTO") == 1) ? "" : "<table style='width:100%; border:0px;' border=0 ><tr><td style='width:25px; border:0px;'>R$</td><td style='border:0px; text-align:right'>" + parseFloat(oRecord.getData("VL_EVENTO")).toFixed(2) + "</td></tr></table>";
};
var formatSaldo = function(elCell, oRecord, oColumn, sData) 
{
    var records = this.getRecordSet();  
    var saldo   = 0;
   
    for (var i=0; i < records._records.length; i++) 
    {
      if(records._records[i]._oData.IN_EVENTO == 1) saldo += parseFloat(records._records[i]._oData.VL_EVENTO);
      else saldo -= parseFloat(records._records[i]._oData.VL_EVENTO);
      if(records._records[i].getId() == oRecord.getId())
      {
         break;
      };
    };
    elCell.innerHTML = "<table style='width:100%; border:0px;' border=0 ><tr><td style='width:25px; border:0px;'>R$</td><td style='border:0px; text-align:right'>" + parseFloat(saldo).toFixed(2) + "</td></tr></table>";
};
//-----------------------------------------------------------------------------
// Fim formatters  ------------------------------------------------------------
//-----------------------------------------------------------------------------

//-----------------------------------------------------------------------------
// Inicio Sortes --------------------------------------------------------------
//-----------------------------------------------------------------------------
var mySortCredito = function(a, b, desc, field) 
{
    var comp = YAHOO.util.Sort.compare;
    
    // Deal with empty values
    if(!YAHOO.lang.isValue(a))      { return (!YAHOO.lang.isValue(b)) ? 0 : 1; } 
    else if(!YAHOO.lang.isValue(b)) { return -1; }
    
    //Garante sempre a ordem do saldo acumulado em primeiro lugar
    if(a.getData("DC_EVENTO") == "Saldo acumulado anteriormente") { return -1; }
    if(b.getData("DC_EVENTO") == "Saldo acumulado anteriormente") { return  1; }
   
    //Ordena o crédito   
    if(a.getData("IN_EVENTO") == b.getData("IN_EVENTO"))
    {
      var compState = comp(a.getData("VL_EVENTO"), b.getData("VL_EVENTO"), desc);
    }
    else if(a.getData("IN_EVENTO") == 1)
    {
      var compState = comp(a.getData("VL_EVENTO"), 100000000*b.getData("VL_EVENTO"), false);   
    }
    else
    {
       var compState = comp(100000000*a.getData("VL_EVENTO"), b.getData("VL_EVENTO"), false);
    }
    return compState;
};
//-----------------------------------------------------------------------------
var mySortDebito = function(a, b, desc, field) 
{
    var comp = YAHOO.util.Sort.compare;
    // Deal with empty values
    if(!YAHOO.lang.isValue(a))      { return (!YAHOO.lang.isValue(b)) ? 0 : 1; } 
    else if(!YAHOO.lang.isValue(b)) { return -1; }
    
    //Garante sempre a ordem do saldo acumulado em primeiro lugar
    if(a.getData("DC_EVENTO") == "Saldo acumulado anteriormente") { return -1; }
    if(b.getData("DC_EVENTO") == "Saldo acumulado anteriormente") { return  1; }
   
    //Ordena o crédito   
    if(a.getData("IN_EVENTO") == b.getData("IN_EVENTO"))
    {
      var compState = comp(a.getData("VL_EVENTO"), b.getData("VL_EVENTO"), desc);
    }
    else if(a.getData("IN_EVENTO") == 1)
    {
      var compState = comp(100000000*a.getData("VL_EVENTO"), b.getData("VL_EVENTO"), false);   
    }
    else
    {
       var compState = comp(a.getData("VL_EVENTO"), 100000000*b.getData("VL_EVENTO"), false);
    }

    return compState;
};
//-----------------------------------------------------------------------------
var mySort = function(a, b, desc, field) 
{
    var comp = YAHOO.util.Sort.compare;
    
    // Deal with empty values
    if(!YAHOO.lang.isValue(a))      { return (!YAHOO.lang.isValue(b)) ? 0 : 1; } 
    else if(!YAHOO.lang.isValue(b)) { return -1; }
    
    //Garante sempre a ordem do saldo acumulado em primeiro lugar
    if(a.getData("DC_EVENTO") == "Saldo acumulado anteriormente") { return -1; }
    if(b.getData("DC_EVENTO") == "Saldo acumulado anteriormente") { return  1; }

           
    //Ordena 
    var compState = comp(a.getData(field), b.getData(field), desc);
    return compState;
};
//-----------------------------------------------------------------------------
// Fim Sortes -----------------------------------------------------------------
//-----------------------------------------------------------------------------
var dialogoEventoEdita = function(CdEvento, CdRecorrente)
{
   global_CdEvento      = CdEvento;
   global_CdRecorrente  = CdRecorrente;
   if(CdRecorrente == 0)
   {
      //alert("Evento Simples: "+ CdEvento);
      $('#dialog-edita').dialog({height: 400}); 
      $("#dialog-edita").dialog("option", "buttons", 
      [ 
         {text: "Salvar",         click: function() { salvaEvento(0); $( this ).dialog( "close" ); }},
         {text: "Cancelar",       click: function() { reset_global(); $( this ).dialog( "close" ); }}     
      ]);            
      $('#warningEditaRecorrente').hide();    
   }
   else
   {
      $('#dialog-edita').dialog({height: 480});
      $("#dialog-edita").dialog("option", "buttons", 
      [ 
         {text: "Apenas esse",         click: function() { salvaEvento(0); $( this ).dialog( "close" ); }},
         {text: "Todos eventos",       click: function() { salvaEvento(1); $( this ).dialog( "close" ); }},
         {text: "Eventos futuros",     click: function() { salvaEvento(2); $( this ).dialog( "close" ); }},
         {text: "Eventos passados",    click: function() { salvaEvento(3); $( this ).dialog( "close" ); }},
         {text: "Cancelar",            click: function() { reset_global(); $( this ).dialog( "close" ); }}     
      ]);      
      $('#warningEditaRecorrente').show();     
   }   
   $('#dialog-edita').dialog('open');
   panel_wait.show();
   var request = $.ajax(
   {
      url: "<?=$base_url_rest?>../../eventos/rest/get",
      context: document.body,
      dataType: "json",
      data: 
      {
         cd_evento:     global_CdEvento,
      },
      success: function(data, textStatus, jqXHR)
      {
         global_DtEvento     = data[0]["DT_EVENTO"];
         global_InEvento     = data[0]["IN_EVENTO"];
         global_DcEvento     = data[0]["DC_EVENTO"];
         global_VlEvento     = data[0]["VL_EVENTO"];
         global_CdCategoria  = data[0]["CATEGORIA"]["CD_CATEGORIA"];

         var parsedDate = $.datepicker.parseDate('yy-mm-dd', global_DtEvento);
         $("#datepicker").datepicker('setDate', parsedDate); 
         $("#iDescricao").val(global_DcEvento);
         $("#sCategoria").val(global_CdCategoria);
         if(global_InEvento == 1)
         { 
            var parent = $('.cb-enable').parents('.switch');
            $('.cb-disable',parent).removeClass('selected');
            $('.cb-enable').addClass('selected');
            $('.checkbox',parent).attr('checked', true);
            $("#cbCredito").attr("checked", true);            
         } 
         else 
         {
            var parent = $('.cb-disable').parents('.switch');
            $('.cb-enable',parent).removeClass('selected');
            $('.cb-disable').addClass('selected');
            $('.checkbox',parent).attr('checked', false);
            $("#cbCredito").attr("checked", false);            
         }
         $("#iValor").val(global_VlEvento.replace('.', ',')); 
         panel_wait.hide();
      },
      error: function(data, textStatus, jqXHR) 
      {
         panel_wait.hide();
         alert("ocorreu um erro inesperado. Tente novamente.")
      } 
   });     
} 
//-----------------------------------------------------------------------
var dialogoEventoApaga = function(DcEvento, CdEvento, CdRecorrente)
{
   global_CdEvento      = CdEvento;
   global_CdRecorrente  = CdRecorrente;
   if(CdRecorrente > 0)
   {
      $('#iEventoRecorrenteDelete').html(DcEvento);
      $('#dialog-delete-recorrente').dialog('open');      
   }
   else
   {
      $('#iEventoSimplesDelete').html(DcEvento);
      $('#dialog-delete-simples').dialog('open');     
   }
   return false;
}
//-----------------------------------------------------------------------------
var prepareLoadData = function()
{
    panel_wait.show();
    //Prepara nova query
    var from_date_parts = $("#from").val().match(/\d+/g);
    year         = from_date_parts[2]; // get only two digits
    month        = from_date_parts[1];
    day          = from_date_parts[0];
    var from_date = year+'-'+month+'-'+day;
    
    var to_date_parts = $("#to").val().match(/\d+/g);
    year         = to_date_parts[2]; // get only two digits
    month        = to_date_parts[1];
    day          = to_date_parts[0];
    
    var to_date = year+'-'+month+'-'+day;
    var in_evento     = ($('#cbCredito').attr("checked") == "checked") ? 1 : 0;
    
    //var query_search = "startDate="+from_date+"&endDate="+to_date+"&debit="+in_evento;
    var query_search = "startDate="+from_date+"&endDate="+to_date;
    return query_search;      
}
//-----------------------------------------------------------------------------
var loadData = function(query_search)
{
   $.ajax({
            type: "GET",
            url: "../../rest/grafico.fluxo.php?"+query_search,
            dataType: "text",
            success: function(xml) { RenderChart(xml); }
   });      
}
//------------------------------------------------------------------------
var changeEditaData = function()
{
   if(global_CdRecorrente > 0)
   {
      $( "#datepicker" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
      var dt_evento     = $("#datepicker").val();
      if(dt_evento == global_DtEvento)
      {
         $("#dialog-edita").dialog("option", "buttons", 
         [ 
            {text: "Apenas esse",                          click: function() { salvaEvento(0); $( this ).dialog( "close" ); }},
            {text: "Todos eventos",       disabled: false,  click: function() { salvaEvento(1); $( this ).dialog( "close" ); }},
            {text: "Eventos futuros",     disabled: false,  click: function() { salvaEvento(2); $( this ).dialog( "close" ); }},
            {text: "Eventos passados",    disabled: false,  click: function() { salvaEvento(3); $( this ).dialog( "close" ); }},
            {text: "Cancelar",                             click: function() { reset_global(); $( this ).dialog( "close" ); }}     
         ]);         
      }
      else
      {
         $("#dialog-edita").dialog("option", "buttons", 
         [ 
            {text: "Apenas esse",                          click: function() { salvaEvento(0); $( this ).dialog( "close" ); }},
            {text: "Todos eventos",       disabled: true,  click: function() { salvaEvento(1); $( this ).dialog( "close" ); }},
            {text: "Eventos futuros",     disabled: true,  click: function() { salvaEvento(2); $( this ).dialog( "close" ); }},
            {text: "Eventos passados",    disabled: true,  click: function() { salvaEvento(3); $( this ).dialog( "close" ); }},
            {text: "Cancelar",                             click: function() { reset_global(); $( this ).dialog( "close" ); }}     
         ]);
      }
   }      
}
//------------------------------------------------------------------------
var montaDialogoEdita = function()
{ // Iniciar dialogo de edição
      $( "#datepicker" ).datepicker({onSelect: changeEditaData});

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
}
//------------------------------------------------------------------------
$(document).ready(function()
{
   loadData("startDate=<?=date("Y-m-d", mktime(0, 0, 0, date("m")-1, date("d"),   date("Y")))?>&endDate=<?=date("Y-m-d")?>");   
    YAHOO.example.XHR_JSON = function() 
    {
        var myColumnDefs = [
            {key:"DT_EVENTO",                width: 80,  label:"Data",        sortable:true,                formatter: formatData,    sortOptions: { sortFunction: mySort }},
            {key:"DC_EVENTO",                width: 150, label:"Descrição",   sortable:true,                                          sortOptions: { sortFunction: mySort }},
            {key:"CATEGORIA.NM_CATEGORIA",   width: 110, label:"Categoria",   sortable:true,                                          sortOptions: { sortFunction: mySort }},
            {key:"vl_credito",               width: 80,  label:"Crédito",     sortable:true,                formatter: formatCredito, sortOptions: { sortFunction: mySortCredito } },
            {key:"vl_debito",                width: 80,  label:"Débito",      sortable:true,                formatter: formatDebito,  sortOptions: { sortFunction: mySortDebito }},
            {key:"vl_saldo",                 width: 80,  label:"Saldo",       sortable:false,               formatter: formatSaldo},
            {key:"CD_EVENTO",                width: 15,  label: "E",                                        formatter: formatEdit},
            {key:"X",                        width: 15,                                                     formatter: formatDelete}
        ];
         //http://localhost/incorporativa/relatorio/fluxo/rest/?startDate=2007-09-01&endDate=2007-09-30
        myDataSource = new YAHOO.util.DataSource("<?=$base_url_rest?>rest/?");
        myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;
        myDataSource.connXhrMode = "queueRequests";
        myDataSource.responseSchema = {
                                          resultsList: "data",
                                          fields: [
                                                      {key:"CD_EVENTO", parser:"number"}, 
                                                      {key:"VL_EVENTO", parser:"number"}, 
                                                      "DC_EVENTO", 
                                                      "DT_EVENTO", 
                                                      "IN_EVENTO",
                                                      {key:"CATEGORIA.NM_CATEGORIA"},
                                                      {key:"CD_RECORRENTE", parser:"number"}
                                                  ]
                                      };

        myDataTable = new YAHOO.widget.DataTable("json", myColumnDefs,
        myDataSource, {initialRequest:"startDate=<?=date("Y-m-d", mktime(0, 0, 0, date("m")-1, date("d"),   date("Y")))?>&endDate=<?=date("Y-m-d")?>"});

        var mySuccessHandler = function() 
        {
            this.set("sortedBy", "DT_EVENTO");
            this.deleteRows(0, 1000000);
            this.onDataReturnAppendRows.apply(this,arguments);
            panel_wait.hide();
            
        };
        var myFailureHandler = function() 
        {
            this.showTableMessage(YAHOO.widget.DataTable.MSG_ERROR, YAHOO.widget.DataTable.CLASS_ERROR);
            this.onDataReturnAppendRows.apply(this,arguments);
            panel_wait.hide();
        };
        
        var callbackObj = 
        {
            success : mySuccessHandler,
            failure : myFailureHandler,
            scope : myDataTable
        };
        myDataSource.sendRequest("startDate=<?=date("Y-m-d", mktime(0, 0, 0, date("m")-1, date("d"),   date("Y")))?>&endDate=<?=date("Y-m-d")?>", callbackObj);
    
         $(function() 
         {
            var dates = $( "#from, #to" ).datepicker
            ({
               defaultDate: "+1w",
               changeMonth: true,
               changeYear: true,
               numberOfMonths: 2,
               onSelect: function( selectedDate ) 
               {
                  var option = this.id == "from" ? "minDate" : "maxDate",
                     instance = $( this ).data( "datepicker" ),
                     date = $.datepicker.parseDate(
                        instance.settings.dateFormat ||
                        $.datepicker._defaults.dateFormat,
                        selectedDate, instance.settings );
                  dates.not( this ).datepicker( "option", option, date );
                  
                  
               }
            });
         });
         $.datepicker.setDefaults(
           $.extend(
             $.datepicker.regional['pt-BR']
           )
         );
         $("#from, #to").attr("readonly","readonly");
         $("#btFiltrar").button()
                        .click(function() 
                        { 
                           panel_wait.show();
                           loadData(prepareLoadData());
                           //Override parameters
                           myDataSource.sendRequest(prepareLoadData(), callbackObj);
                        });
         //--------------------------------------------------------------------               
         $("#dialog-delete-simples").dialog
         ({
            resizable: false,
            autoOpen: false,
            height:240,
            width: 460,
            modal: true,
            buttons: {
               "Apagar": function(){ apagaEvento(0); $( this ).dialog( "close" );},
               Cancel:  function() { reset_global(); $( this ).dialog( "close" );}
            }
         }); 
         //--------------------------------------------------------------------
         $("#dialog-delete-recorrente" ).dialog 
         ({
            resizable: false,
            autoOpen: false,
            height:240,
            width: 860,
            modal: true,
            buttons: {
               "Apenas esse":       function() { apagaEvento(0); $( this ).dialog( "close" );},
               "Todos eventos":     function() { apagaEvento(1); $( this ).dialog( "close" );},
               "Eventos futuros":   function() { apagaEvento(2); $( this ).dialog( "close" );},
               "Eventos passados":  function() { apagaEvento(3); $( this ).dialog( "close" );},
               Cancel:              function() { reset_global(); $( this ).dialog( "close" );}
            }
         }); 
         //--------------------------------------------------------------------
         $("#dialog-edita" ).dialog
         ({
            resizable: false,
            autoOpen: false,
            height:400,
            width: 800,
            modal: true
         }); 
         return {oDS: myDataSource, oDT: myDataTable };
    }();
    montaDialogoEdita();
});
//-----------------------------------------------------------------------------
function salvaEvento(TpUpdate)
{
   panel_wait.show();
   $( "#datepicker" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
   var dt_evento     = $("#datepicker").val();
   var dc_evento     = $("#iDescricao").val();
   var cd_categoria  = $("#sCategoria").val();
   var in_evento     = ($('#cbCredito').attr("checked") == "checked") ? 1 : 0;
   var vl_evento     = parseFloat($("#iValor").val().replace(',', '.'));  
     
   var request = $.ajax(
   {
      url: "<?=$base_url_rest?>../../eventos/rest/save",
      context: document.body,
      data: 
      {
         cd_evento:     global_CdEvento,
         cd_recorrente: global_CdRecorrente,
         tp_update:     TpUpdate,
         dt_evento:     ((dt_evento     != global_DtEvento)    ? dt_evento     : ""),
         dc_evento:     ((dc_evento     != global_DcEvento)    ? dc_evento     : ""),
         cd_categoria:  ((cd_categoria  != global_CdCategoria) ? cd_categoria  : ""),
         vl_evento:     ((vl_evento     != global_VlEvento)    ? vl_evento     : ""),
         in_evento:     ((in_evento     != global_InEvento)    ? in_evento     : "")       
      },
      success: function(data, textStatus, jqXHR)
      {
         //alert(data);
         $("#btFiltrar").click();
      },
      error: function(data, textStatus, jqXHR) 
      {
         alert(data);
         panel_wait.hide();
         alert("ocorreu um erro inesperado. Tente novamente.")
      } 
   });
}
//-----------------------------------------------------------------------------
function apagaEvento(TpDelete)
{
   panel_wait.show();
   var request = $.ajax(
   {
      url: "<?=$base_url_rest?>../../eventos/rest/delete",
      context: document.body,
      data: 
      {
         cd_evento:     global_CdEvento,
         cd_recorrente: global_CdRecorrente,
         tp_delete:     TpDelete
      },
      success: function(data, textStatus, jqXHR)
      {
         //alert(data);
         $("#btFiltrar").click();
      },
      error: function(data, textStatus, jqXHR) 
      {
         panel_wait.hide();
         alert("ocorreu um erro inesperado. Tente novamente.")
      } 
   });
}
//-----------------------------------------------------------------------------
function RenderChart(strXML)
{
   var chart = new FusionCharts("../../fusioncharts/library/FCF_Line.swf", "mg_chart", "780", "400", "0", "1", "","noScale","EN");
   chart.setDataXML(strXML);
   chart.render("divChartBody");
}
//-----------------------------------------------------------------------------
var chart_visible = true;
var hideChart = function()
{
   if(chart_visible)
   {
      $("#pHideChart").html("&#9658; Mostrar Gráfico");
      $("#divChartBody").toggle("fast");   
      
   }
   else
   {
     $("#pHideChart").html("&#9660; Esconder Gráfico");
     $("#divChartBody").toggle("fast");
   }
   chart_visible = !chart_visible;
}
</script>


<h1>Relatório de Fluxo de Caixa</h1>

<label for="from">Início</label>
<input type="text" id="from" name="from" value="<?=date("d/m/Y", mktime(0, 0, 0, date("m")-1, date("d"),   date("Y")));?>">
<label for="to">Fim</label>
<input type="text" id="to" name="to" value="<?=date("d/m/Y");?>"/>
<button id="btFiltrar">Filtrar</button>
<br/>
<br/>

<p id="pHideChart" class="hideChart" onclick="hideChart()">&#9660; Esconder Gráfico</p>
<div id="divChartBody">
   teste
</div>

<div id="json"></div>

<!-- Dialog Excluir Simples -->
<div id="dialog-delete-simples" title="Apagar o Evento">
   <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Você está prestes a apagar o evento <span id="iEventoSimplesDelete">placeholder</span> e esta ação não pode ser desfeita. Tem certeza que deseja continuar?</p>
</div>
   
   
<!-- Dialogo Excluir Recorrente -->
<div id="dialog-delete-recorrente" title="Apagar o Evento">
   <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Você está prestes a apgar um evento marcado como recorrente (<span id="iEventoRecorrenteDelete">placeholder</span>) e esta ação não pode ser desfeita. Isto significa que é bem provável que haja outros registros como este em outras datas. Selecione abaixo os eventos ligados a este que você deseja excluir.</p>
</div>


<!-- Dialog Edita Evento -->
<div id="dialog-edita" title="Salvar Evento">
   <div id="warningEditaRecorrente" class="warning"> Você está editando um série recorrente de eventos, mas apenas o evento selecionado é mostrado. Para editar a data você deve desmembrar o elemento de sua série.</div>
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
</div>
