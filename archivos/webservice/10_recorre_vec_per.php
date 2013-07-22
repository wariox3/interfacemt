<?
   session_start();
   
//echo "<script>location.href='13_un_vehiculo.php';</script>";   
 
   // enviara al webservices la informacin contenida
   // en el vector de personas $_SESSION['VEC_PER']
   // El envio del vector no se puede hacer con 
   // foreach debido al llamado que se hace a programas
	 // externos.

   if($_SESSION['CONTADOR_REGISTROS_PER']>=count($_SESSION['VEC_PER']))
   {
       // ya se recorrio todo el vector de personas
       $_SESSION['CONTADOR_REGISTROS_EMP']=0;   		       
			 echo "<script>location.href='11_recorre_vec_emp.php';</script>";
   }
   else
   {
 			 // envia el siguiente elemento del vector personas
 			 // al web services
       echo "<script>location.href='10a_select.php';</script>";     
   }
?>