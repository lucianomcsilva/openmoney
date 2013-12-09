         <table class="MenuPrincipal" style="border: 0px;">
            <tr style="height:20px;">
               <th onclick="goTo('')" <?php if($page == 'home') echo "class=\"MenuItemSelected\""; else echo "class=\"MenuItem\""; ?>>Início </th>
               <td rowspan="10" class="Conteudo">
               <?php
                  switch ($page) 
                  {
                     case 'home':
                        include "pages/home.inc.php";      
                        break;
                     case 'categorias':
                        include "pages/categorias.inc.php";      
                        break;
                     case 'eventos':
                        include "pages/eventos.inc.php";      
                        break;
                     case 'relatorios-fluxo':
                        include "pages/relatorio.fluxo.inc.php";      
                        break;
                     case 'relatorios-categorias':
                        include "pages/relatorio.mensal.inc.php";      
                        break;
                     case 'relatorios-semana':
                        include "pages/relatorio.semana.inc.php";
                        break;
                     case 'relatorios-dia':
                        include "pages/relatorio.dia.inc.php";
                        break;
                     case 'relatorios-comparativo':
                        include "pages/relatorio.comparativo.inc.php";
                        break;
                     case 'sair':
                        include "pages/sair.inc.php";      
                        break;                     
                     default:
                        include "pages/erro.inc.php";
                        break;
                  }
               
               ?>
               </td>
            </tr>
            <tr style="height:20px;">
               <th onclick="goTo('categorias/')" <?php if($page == 'categorias') echo "class=\"MenuItemSelected\""; else echo "class=\"MenuItem\"";  ?>>Categorias</th>
            </tr>
            <tr style="height:20px;">
               <th onclick="goTo('eventos/')" <?php if($page == 'eventos') echo "class=\"MenuItemSelected\""; else echo "class=\"MenuItem\"";  ?>>Eventos</th>
            </tr>
            <tr style="height:20px;">
               <th class="MenuItem nohover">Relatórios</th>
            </tr>            
            <tr style="height:20px;">
               <th style="padding-left: 20px" onclick="goTo('relatorios/fluxo/')" <?php if($page == 'relatorios-fluxo') echo "class=\"MenuItemSelected\""; else echo "class=\"MenuItem\"";  ?>>Fluxo de Caixa</th>
            </tr>
            <tr style="height:20px;">
               <th style="padding-left: 20px" onclick="goTo('relatorios/categorias/')" <?php if($page == 'relatorios-categorias') echo "class=\"MenuItemSelected\""; else echo "class=\"MenuItem\"";  ?>>Categorias</th>
            </tr>            
            <tr style="height:20px;">
               <th style="padding-left: 20px" onclick="goTo('relatorios/dia/')" <?php if($page == 'relatorios-dia') echo "class=\"MenuItemSelected\""; else echo "class=\"MenuItem\"";  ?>>Dia do Mês</th>
            </tr>            
            <tr style="height:20px;">
               <th style="padding-left: 20px" onclick="goTo('relatorios/semana/')" <?php if($page == 'relatorios-semana') echo "class=\"MenuItemSelected\""; else echo "class=\"MenuItem\"";  ?>>Dia da Semana</th>
            </tr>                       
            <tr style="height:20px;">
               <th onclick="goTo('sair/')" <?php if($page == 'sair') echo "class=\"MenuItemSelected\""; else echo "class=\"MenuItem\"";  ?>>Sair</th>
            </tr>  
            <tr>
               <th class="Vazio" style="border: 0px;"> </th>
            </tr> 
         </table>