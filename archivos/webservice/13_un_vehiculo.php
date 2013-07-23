<?
   session_start();
   // enviara al webservices la informacin correspondiente
   // al vehículo de la variable  $_SESSION['PLACA']

   if($_SESSION['PLACA']=='@@@@@@')
   {
      $_SESSION['CONTADOR_REGISTROS_REMESAS']=0;
      echo "<script>location.href='14_recorre_vec_remesas.php';</script>";        
   }
   else
   {
      echo "<script>location.href='13a_select.php';</script>";     
   }
   
   
   


?>