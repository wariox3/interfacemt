<?
   session_start();
   
   echo "Procesando el manifiesto número ".$_SESSION['MFTO']."<br><br>";
   echo "-----------------------------------------------------------------------------------------------------------<br>";
   echo "Enviando el manifiesto: ".$_SESSION['MFTO']."<br>";
   echo "-----------------------------------------------------------------------------------------------------------<br>";

   
   $_SESSION['CUENTA_INTENTOS_15']=1;  
   $_SESSION['TIEMPO_INICIO']=time();
   $_SESSION['DURACION_ESPERA']=0;   	  
 
   $_SESSION['ERROR_WEBSERVICES']=" ... <br>";
	 echo "<script>location.href='15c_proceso.php';</script>";

?>