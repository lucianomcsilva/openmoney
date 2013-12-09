<?php
   session_destroy();
   header("location:http://{$_SERVER['HTTP_HOST']}"); 
?>
