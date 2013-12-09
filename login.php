 <form action="processa_login.php">
   <div class="login_box">
      <div class="login_box_cabecalho">
         
         <img src="<?=$base_url?>/images/lock.png" align="left">
         <h2>Login</h2>
         <p style="margin: 0px;">Esta página serve apenas para testar as funcionalidades da ferramenta Open Money e não deve ser utilizada para controlar seus dados. A conta abaixo é utilizada apenas para demonstração e é reiniciada todos os dias de madrugada. Fique a vontade para testar todas as funcionalidades da ferramenta.</p>
         <p>Utilize o usuário <b>openmoney</b> e a senha <b>openmoney</b></p>
      </div>
      <div class="login_box_inputs">
         <div id="erroLogin" class="error"> Usuário ou senha inválidos.</div>
         <table style="margin-left: 20px">
            <tr>
               <td><label>Login:</label></td>
               <td><input type="text" id="login" name="login"></td>
            </tr>
            <tr>
               <td><label>Senha:</label></td>
               <td><input type="password" id="senha" name="senha"></td>
            </tr>            
         </table>
      </div>
      <br/>
      <button id="btEntrar" type="button">Entrar</button>
      <button id="btCancelar" type="reset">Cancelar</button>
   </div>
   <script>
      $( "#btCancelar" ).button();
      $( "#btCancelar" ).click(function()
      {
         $("#erroLogin").hide();   
         $(".login_box").height(375);
      });
      $( "#btEntrar" ).button();
      
      $( "#btEntrar" ).click(function()
      {
         var request = $.ajax(
         {
            type: 'POST',
            url: "<?="http://{$_SERVER['HTTP_HOST']}{$base_url}";?>/processa_login.php",
            context: document.body,
            data: 
            {
               login:     $("#login").val(),
               senha:     $("#senha").val()
            },
            success: function(data, textStatus, jqXHR)
            {
               if(data['status'] == 0)
               {
                  $("#erroLogin").show(200, "linear");
                  $(".login_box").height(410);
               }
               else
               {
                  window.location = window.location;   
               }
               
            },
            error: function(data, textStatus, jqXHR) 
            {
               alert("ocorreu um erro inesperado. Tente novamente.")
            } 
         });
      });
      
   </script>
</form>   
<style>
   .login_box_inputs
   {
      background-color: #E0E0E0;
      padding-top: 15px;
      padding-bottom: 15px;
   }
   .login_box_inputs label
   {
      display: inline-block;
      width: 100px;
      font-size: 24px;
   }
   .login_box_inputs input
   {
      width: 600px;
      font-size: 24px;
      margin-top: 10px;
   }
   .login_box_cabecalho
   {
      background-color: #606060;
      color: #fff;
      min-height: 100px;
      padding: 10px;
      text-align: left;
      font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
      font-size: 14px;
      font-weight: normal;
   }
   .login_box
   {
      border: 3px solid #000;
      margin-top:20px;
      margin-left: auto;
      margin-right: auto;
      width: 750px;
      height: 375px;
      background-color: #C0C0C0;
      -webkit-border-radius: 10px;
      -moz-border-radius: 10px;
      border-radius: 10px;      
   }
   #erroLogin
   {
      margin-left: 20px;
      margin-right: 20px;
      margin-top: -5px;
      margin-bottom: -5px;
      display: none;
   }
</style>