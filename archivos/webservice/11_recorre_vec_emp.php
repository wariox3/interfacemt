<?
   session_start();
 
   // enviara al webservices la informacin contenida
   // en el vector de empresas $_SESSION['VEC_EMP']
   // El envio del vector no se puede hacer con 
   // foreach debido al llamado que se hace a programas
	 // externos.

   if($_SESSION['CONTADOR_REGISTROS_EMP']>=count($_SESSION['VEC_EMP']))
   {
       // ya se recorrio todo el vector de empresas
			 echo "<script>location.href='12_un_conductor.php';</script>";
   }
   else
   {
 			 // envia el siguiente elemento del vector personas
 			 // al web services
       echo "<script>location.href='11a_select.php';</script>";     
   }
?>