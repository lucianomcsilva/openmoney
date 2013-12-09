<?php
   session_start();
   foreach ($_SESSION as $key => $value) {
      echo "<li> {$key} :: {$value}</li>";
   }
?>