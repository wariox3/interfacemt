<?
   session_start();
   
   echo "Procesando el manifiesto número ".$_SESSION['MFTO']."<br><br>";
   echo "-----------------------------------------------------------------------------------------------------------<br>";
   echo "Enviando información del conductor: ".$_SESSION['VEC_UN_TERCERO_CONDU']['NUMIDTERCERO']." ( ".$_SESSION['VEC_UN_TERCERO_CONDU']['NOMIDTERCERO']." ".$_SESSION['VEC_UN_TERCERO_CONDU']['PRIMERAPELLIDOIDTERCERO']." )<br>";
   echo "-----------------------------------------------------------------------------------------------------------<br>";   

   
   $_SESSION['CUENTA_INTENTOS_12']=1;  
   $_SESSION['TIEMPO_INICIO']=time();
   $_SESSION['DURACION_ESPERA']=0;   	  
 
   $_SESSION['ERROR_WEBSERVICES']=" ... <br>";
	 echo "<script>location.href='12c_proceso.php';</script>";

?>