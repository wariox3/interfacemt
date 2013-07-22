<?
   session_start();

   
   // enviara al webservices la informacin contenida
   // en el vector de remesas $_SESSION['VEC_REMESAS']
   // El envio del vector no se puede hacer con 
   // foreach debido al llamado que se hace a programas
	 // externos.

   if($_SESSION['CONTADOR_REGISTROS_REMESAS']>=count($_SESSION['VEC_REMESAS']))
   {
       // ya se recorrio todo el vector de personas
//       $_SESSION['CONTADOR_REGISTROS_EMP']=0;   		       
			 echo "<script>location.href='15_un_mfto.php';</script>";
   }
   else
   {
 			 // envia el siguiente elemento del vector personas
 			 // al web services
       echo "<script>location.href='14a_select.php';</script>";     
   }
?>