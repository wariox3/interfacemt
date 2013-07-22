<?
   session_start();
   // enviara al webservices la informacin correspondiente
   // a la variable $_SESSION['CONDUCTOR']
   
   if($_SESSION['CONDUCTOR']=='@@@@@@')
   {
      echo "<script>location.href='13_un_vehiculo.php';</script>";        
   }
   else
   {
      echo "<script>location.href='12a_select.php';</script>";     
   }
   


?>