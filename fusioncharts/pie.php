<?php
   session_start();
  # Include FusionCharts PHP Class
  include('php/FusionCharts_Gen.php');

  # Create Column3D chart Object
  $FC = new FusionCharts("Pie3d","590","400", "mg_mensal", 1);
  # set the relative path of the swf file
  $FC->setSWFPath("library/");

  # Set chart attributes
  $strParam="caption=% por Categoria;numberPrefix=R$;decimalPrecision=0;formatNumberScale=1;showValues=1;showNames=1;chartRightMargin=25;chartLeftMargin=25;chartBottomMargin=0;showPercentageValues=0;pieSliceDepth=20;pieFillAlpha=50;showPercentageInLabel=1;pieYScale=60;pieRadius=200;animation=1";
  $FC->setChartParams($strParam);
  include_once '../../meusgastos3/extras/basico.php';
$sql = "
SELECT
    c.NM_CATEGORIA AS CATEGORIA,
    sum(a.VL_EVENTO) AS VALOR
FROM
    (    
    SELECT
       CASE
            WHEN CD_SUPERIOR = 0 then c.CD_CATEGORIA
            ELSE CD_SUPERIOR
          END as CD_CAT,
					sum(VL_EVENTO) as VL_EVENTO
    		FROM
    			u_eventos d
				RIGHT JOIN
    		  u_categorias c
    		ON
    		  c.CD_CATEGORIA = d.CD_CATEGORIA
    		WHERE
    		  d.CD_CONTA  =  {$_SESSION['cd_grupo']} AND
    		  MONTH(DT_EVENTO) =  $mes           AND
          YEAR(DT_EVENTO)  =  $ano AND
          IN_DEBITO = 0
				GROUP BY
					c.cd_CATEGORIA
			 ) AS a
INNER JOIN
       u_categorias c
ON
       a.cd_CAT = c.cd_CATEGORIA
WHERE
    	c.CD_CONTA  =  {$_SESSION['cd_grupo']}  AND
    	c.CD_SUPERIOR = 0
GROUP BY
    c.NM_CATEGORIA
order by
    c.NM_CATEGORIA
        ";          
  $rs = ExecutaQuery($sql);
  for ($i = 0; $i < mysql_num_rows($rs); $i++) 
  {
    $row      = mysql_fetch_array($rs);
    $data     = $row['DT_COTACAO'];
    $tx_venda = $row['TX_VENDA'];
    $FC->addChartData($row['VALOR'],"name=".utf8_encode($row['CATEGORIA']));
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
  <style>
  <!--
      #mg_mensalDiv
      {
         border: 1px solid black; 
      }
  //-->
  </style>
  <body>

  <?
    # Render Chart
    $FC->renderChart();
    $cachekiller = md5(time());
  ?>
  <img alt='Carregando...' width=740 height=400 src='../../meusgastos3/conteudo/fechado/graficos/img/mensal.php?cachekiller=?<?=$cachekiller?>&Data=200808' border=0>
  </body>
</html>

