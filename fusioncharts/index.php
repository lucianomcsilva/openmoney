<?php
  # Include FusionCharts PHP Class
  include('php/FusionCharts_Gen.php');

  # Create Column3D chart Object
  $FC = new FusionCharts("Line","800","400");
  # set the relative path of the swf file
  $FC->setSWFPath("library/");

  # Set chart attributes
  $strParam="caption=Cotacao do Dolar;xAxisName=Dia;yAxisName=Cotacao;numberPrefix=R$;decimalPrecision=2;formatNumberScale=1;showValues='0';chartRightMargin=100;chartLeftMargin=100;chartBottomMargin=50;";
  $FC->setChartParams($strParam);
  include_once '../../meusgastos3/extras/basico.php';
  $rs = ExecutaQuery("SELECT DT_COTACAO as DT_COTACAO, TX_VENDA FROM ii_cotacoes WHERE CD_MOEDA = 220 AND YEAR(DT_COTACAO) > 1994 ORDER BY DT_COTACAO DESC LIMIT 0,1000");
  for ($i = 0; $i < mysql_num_rows($rs); $i++) 
  {
    $row      = mysql_fetch_array($rs);
    $data     = $row['DT_COTACAO'];
    $tx_venda = $row['TX_VENDA'];
    $FC->addChartData("$tx_venda","name=$data");
    //echo "<hr>";
    //echo "<br> $data -> $tx_venda";
    	
  }
  # add chart values and category names
  /*
  $FC->addChartData("40800123","name=Week 1");
  $FC->addChartData("31400123","name=Week 2");
  $FC->addChartData("26700123","name=Week 3");
  $FC->addChartData("54400123","name=Week 4");
  */
?>
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>First Chart Using FusionCharts PHP Class</title>
    <script language='javascript' src='library/FusionCharts.js'></script>
  </head>
  <body>

  <?
    # Render Chart
    $FC->renderChart();
  ?>

  </body>
</html>

