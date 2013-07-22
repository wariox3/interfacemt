<?
   session_start();
   
   echo "Procesando el manifiesto número ".$_SESSION['MFTO']."<br><br>";
   echo "-----------------------------------------------------------------------------------------------------------<br>";
   $reme_actual=$_SESSION['CONTADOR_REGISTROS_REMESAS']+1;
   echo "Enviando información de la remesa: ".$_SESSION['VEC_UNA_REMESA']['CONSECUTIVOREMESA']."   ( ".$reme_actual." de ".count($_SESSION['VEC_REMESAS'])." )<br>";
   echo "-----------------------------------------------------------------------------------------------------------<br>";

   
   $_SESSION['CUENTA_INTENTOS_14']=1;  
   $_SESSION['TIEMPO_INICIO']=time();
   $_SESSION['DURACION_ESPERA']=0;   	  
 
   $_SESSION['ERROR_WEBSERVICES']=" ... <br>";
	 echo "<script>location.href='14c_proceso.php';</script>";

?>