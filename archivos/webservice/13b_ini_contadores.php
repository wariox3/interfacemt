<?
   session_start();
   
   echo "Procesando el manifiesto n�mero ".$_SESSION['MFTO']."<br><br>";
   echo "-----------------------------------------------------------------------------------------------------------<br>";
   echo "Enviando informaci�n del veh�culo ".$_SESSION['VEC_UN_VEHICULO']['NUMPLACA']."<br>";
   echo "-----------------------------------------------------------------------------------------------------------<br>";

   
   $_SESSION['CUENTA_INTENTOS_13']=1;  
   $_SESSION['TIEMPO_INICIO']=time();
   $_SESSION['DURACION_ESPERA']=0;   	  
 
   $_SESSION['ERROR_WEBSERVICES']=" ... <br>";
	 echo "<script>location.href='13c_proceso.php';</script>";

?>