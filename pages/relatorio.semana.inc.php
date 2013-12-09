<?php
   $base_url_rest = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
?>
<script language='javascript' src='../../fusioncharts/library/FusionCharts.js'></script>


<h1>Relatório por dia da semana</h1>

<table style="width:780px;">
   <tr>
      <td>
<label for="from">Início</label>
<input style="width:80px" type="text" id="from" name="from" value="<?=date("d/m/Y", mktime(0, 0, 0, date("m")-1, date("d"),   date("Y")));?>">
<label for="to">Fim</label>
<input style="width:80px" type="text" id="to" name="to" value="<?=date("d/m/Y");?>"/>
<button id="btFiltrar">Filtrar</button>         
      </td>
      <td>
         <p class="field switch" style="margin:0px; padding:0px;">
             <label class="cb-enable"><span style="width:190px">Receitas (Crédito)</span></label>
             <label class="cb-disable selected"><span style="width:190px">Despesas (Débito)</span></label>
             <input type="checkbox" id="cbCredito" class="checkbox" name="cbCredito" />
         </p>         
      </td>
   </tr>
</table>
      
<script>
var myDataTable;
var myDataSource;
var myColumnDefs;
var callbackObj;


//-----------------------------------------------------------------------------
//Formatters  -----------------------------------------------------------------
//-----------------------------------------------------------------------------
//-----------------------------------------------------------------------------
var formatCredito = function(elCell, oRecord, oColumn, sData) 
{
    elCell.innerHTML = (oRecord.getData("IN_EVENTO") == 0) ? "" : "<table style='width:100%; border:0px;' border=0 ><tr><td style='width:25px; border:0px;'>R$</td><td style='border:0px; text-align:right'>" + parseFloat(oRecord.getData("vl_credito")).toFixed(2) + "</td></tr></table>";
};
var formatDebito = function(elCell, oRecord, oColumn, sData) 
{
    elCell.innerHTML = (oRecord.getData("IN_EVENTO") == 1) ? "" : "<table style='width:100%; border:0px;' border=0 ><tr><td style='width:25px; border:0px;'>R$</td><td style='border:0px; text-align:right'>" + parseFloat(oRecord.getData("vl_debito")).toFixed(2) + "</td></tr></table>";
};
var formatSaldo = function(elCell, oRecord, oColumn, sData) 
{
    elCell.innerHTML = (oRecord.getData("IN_EVENTO") == 1) ? "" : "<table style='width:100%; border:0px;' border=0 ><tr><td style='width:25px; border:0px;'>R$</td><td style='border:0px; text-align:right'>" + parseFloat(oRecord.getData("vl_saldo")).toFixed(2) + "</td></tr></table>";
};
//-----------------------------------------------------------------------------
// Fim formatters  ------------------------------------------------------------
//----------------------------------------------------------------------------- 
//-----------------------------------------------------------------------------
// Panel Pizza
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
    
    var query_search = "startDate="+from_date+"&endDate="+to_date+"&debit="+in_evento;
    return query_search;      
}
//-----------------------------------------------------------------------------
var  loadData = function(query_search)
{
   $.ajax({
            type: "GET",
            url: "../../rest/grafico.semana.php?"+query_search,
            dataType: "text",
            success: function(xml) 
            {
               RenderChart(xml);
               panel_wait.hide();
            }
   });      
} 
//-----------------------------------------------------------------------------
function RenderChart(strXML)
{
   var chart = new FusionCharts("../../fusioncharts/library/FCF_Pie3D.swf", "mg_chart", "780", "400", "0", "1", "","noScale","EN");
   chart.setDataXML(strXML);
   chart.render("divChartBody");
}
//-----------------------------------------------------------------------------
$(document).ready(function(){
      loadData("startDate=<?=date("Y-m-d", mktime(0, 0, 0, date("m")-1, date("d"),   date("Y")))?>&endDate=<?=date("Y-m-d")?>");   
      $( "#cbCredito" ).button();
      $(".cb-enable").click(function(){
           var parent = $(this).parents('.switch');
           $('.cb-disable',parent).removeClass('selected');
           $(this).addClass('selected');
           $('.checkbox',parent).attr('checked', true);
           $("#cbCredito").attr("checked", true);
           loadData(prepareLoadData());
       });
      $(".cb-disable").click(function(){
           var parent = $(this).parents('.switch');
           $('.cb-enable',parent).removeClass('selected');
           $(this).addClass('selected');
           $('.checkbox',parent).attr('checked', false);
           $("#cbCredito").attr("checked", false);
           loadData(prepareLoadData());
       })
 
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
   
   $.datepicker.setDefaults(
     $.extend(
       $.datepicker.regional['pt-BR']
     )
   );
   $("#from, #to").attr("readonly","readonly");
   $("#btFiltrar").button()
                  .click(function() 
                  { 
                     loadData(prepareLoadData());
                     //Override parameters
                     myDataSource.sendRequest(prepareLoadData()+"&debit=2", callbackObj);
                     
                  });
    
   //-----------------------------------------------------------------------------
   // Inicio Tabela --------------------------------------------------------------
   //-----------------------------------------------------------------------------

    YAHOO.example.XHR_JSON = function() 
    {
        myColumnDefs = [
            {key:"nm_semana",      width: 250,  label:"Dia da Semana",    sortable:true                                           },
            {key:"vl_credito",     width: 150,  label:"Crédito",     sortable:true,                formatter: formatCredito  },
            {key:"vl_debito",      width: 150,  label:"Débito",      sortable:true,                formatter: formatDebito   },
            {key:"vl_saldo",       width: 150,  label:"Saldo",       sortable:true,                formatter: formatSaldo}
        ];
         //http://localhost/incorporativa/relatorio/fluxo/rest/?startDate=2007-09-01&endDate=2007-09-30
        myDataSource = new YAHOO.util.DataSource("<?=$base_url_rest?>rest/?");
        myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;
        myDataSource.connXhrMode = "queueRequests";
        myDataSource.responseSchema = {
                                          resultsList: "data",
                                          fields: [
                                                      {key:"nm_semana"},
                                                      "vl_credito", 
                                                      "vl_debito", 
                                                      "vl_saldo",
                                                  ]
                                      };

        myDataTable = new YAHOO.widget.DataTable("divTabelaSemana", myColumnDefs,
        myDataSource, {initialRequest:"startDate=<?=date("Y-m-d", mktime(0, 0, 0, date("m")-1, date("d"),   date("Y")))?>&endDate=<?=date("Y-m-d")?>"});

        var mySuccessHandler = function() 
        {
            //this = datatable
            this.deleteRows(0, 1000000);
            this.onDataReturnAppendRows.apply(this,arguments);
            this.set( "sortedBy", null );
            panel_wait.hide();
        };
        var myFailureHandler = function() 
        {
            this.deleteRows(0, 1000000);
            YAHOO.widget.DataTable.MSG_ERROR = "Nenhuma evento no período selecionado. Escolha outro período";
            this.showTableMessage(YAHOO.widget.DataTable.MSG_ERROR, YAHOO.widget.DataTable.CLASS_ERROR);
            this.onDataReturnAppendRows.apply(this,arguments);
            panel_wait.hide();
        };
        
        callbackObj = 
        {
            success : mySuccessHandler,
            failure : myFailureHandler,
            scope : myDataTable
        };

        myDataSource.sendRequest("startDate=<?=date("Y-m-d", mktime(0, 0, 0, date("m")-1, date("d"),   date("Y")))?>&endDate=<?=date("Y-m-d")?>", callbackObj);
         
        //myDataSource.sendRequest(query_search, callbackObj);
                
        return {oDS: myDataSource, oDT: myDataTable };
    }();
});
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
<p id="pHideChart" class="hideChart" onclick="hideChart()">&#9660; Esconder Gráfico</p>
<div id="divChartBody">
   teste
</div>
<div id="divTabelaSemana"></div>
