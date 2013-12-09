<?php
//------------------------------------------------------------------------------
// Para conseguir as variaveis abaixo, entre em contato consco no
// http://www.zeetha.com/open/money/contato/
//------------------------------------------------------------------------------
$consumerKey      = "<<YOUR_CONSUMER_KEY>>";
$consumerSecret   = "<<YOUR_CONSUMER_SECRET>>";
error_reporting(E_ERROR | E_WARNING | E_PARSE);
//------------------------------------------------------------------------------
function FormataMoeda($Valor, $Width=100, $style='font-size:13px; font-weight: normal;')
{
   if($Valor == false)
      return "<table border=0 width=".$Width."px style=\"$style\"><tr><td style='text-align:center;'> n/d </td></tr></table>"; 
   else
      return "<table border=0 width=".$Width."px style=\"$style\"><tr><td style='width:15px'>R$</td><td style='text-align:right;'>". number_format($Valor, 2)."</td></tr></table>";
}
//------------------------------------------------------------------------------

function cURLcheckBasicFunctions()
{
  if( !function_exists("curl_init") &&
      !function_exists("curl_setopt") &&
      !function_exists("curl_exec") &&
      !function_exists("curl_close") ) return false;
  else return true;
}   
//-----------------------------------------------------------------------------
function ctype_int($text)
{
   return preg_match('/^-?[0-9]+$/', (string)$text) ? true : false;
}
//-----------------------------------------------------------------------------
function FullPathFile($file)
{
   $path = "";
   $i = 0;
   $max = 10;
   while(!is_file($path.$file) & $i < $max)
   {      
      $path .= "../";
      $i++;
   }
   if($i == $max)
      return -1;
   return $path.$file;   
}  
//------------------------------------------------------------------------------
// Nome: Coalesce
// Resumo: Caso a $Var seja nula, retorna o valor $Value 
// Parametros
//    - $Var     - variavel a ser chegada
//    - $Value   - valor a ser considerado caso $Var seja vazio. default: "NULL"
//------------------------------------------------------------------------------   
function Coalesce($Var, $Value="NULL")
{
   if(empty($Var)) return $Value;
   return $Var;
}
?>
