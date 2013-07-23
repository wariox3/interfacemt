<?
   session_start();
   
   echo "Procesando el manifiesto número ".$_SESSION['MFTO']."<br><br>";
   echo "-----------------------------------------------------------------------------------------------------------<br>";
   echo "Enviando información del tercero: ".$_SESSION['VEC_UN_TERCERO_PER']['NUMIDTERCERO']." ( ".$_SESSION['VEC_UN_TERCERO_PER']['NOMIDTERCERO']." ".$_SESSION['VEC_UN_TERCERO_PER']['PRIMERAPELLIDOIDTERCERO']." )<br>";
   echo "-----------------------------------------------------------------------------------------------------------<br>";

   
   $_SESSION['CUENTA_INTENTOS_10']=1;  
   $_SESSION['TIEMPO_INICIO']=time();
   $_SESSION['DURACION_ESPERA']=0;   	  
 
   $_SESSION['ERROR_WEBSERVICES']=" ... <br>";
	 echo "<script>location.href='10c_proceso.php';</script>";

?>