<?
   session_start();
   
   echo "Procesando el manifiesto número ".$_SESSION['MFTO']."<br><br>";
   echo "-----------------------------------------------------------------------------------------------------------<br>";
   echo "Enviando información de la empresa: ".$_SESSION['VEC_UN_TERCERO_EMP']['NUMIDTERCERO']." (".$_SESSION['VEC_UN_TERCERO_EMP']['NOMIDTERCERO'].")<br>";
   echo "-----------------------------------------------------------------------------------------------------------<br><br>";

   
   $_SESSION['CUENTA_INTENTOS_11']=1;  
   $_SESSION['TIEMPO_INICIO']=time();
   $_SESSION['DURACION_ESPERA']=0;   	  
 
   $_SESSION['ERROR_WEBSERVICES']=" ... <br>";
	 echo "<script>location.href='11c_proceso.php';</script>";

?>