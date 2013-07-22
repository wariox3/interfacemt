<?
   session_start();
   date_default_timezone_set("America/Bogota");   
   error_reporting(0);
   set_time_limit(0);
   
   echo "Procesando el manifiesto número ".$_SESSION['MFTO']."<br><br>";
   echo "-----------------------------------------------------------------------------------------------------------<br>";
   echo "Enviando información del conductor: ".$_SESSION['VEC_UN_TERCERO_CONDU']['NUMIDTERCERO']." ( ".$_SESSION['VEC_UN_TERCERO_CONDU']['NOMIDTERCERO']." ".$_SESSION['VEC_UN_TERCERO_CONDU']['PRIMERAPELLIDOIDTERCERO']." )<br>";
   echo "-----------------------------------------------------------------------------------------------------------<br><br>";

	 if(strlen(trim($_SESSION['ERROR_WEBSERVICES']))==0)
   {
      echo "Error de conexión. El Servidor del Ministerio no entregó ninguna respuesta.";
   }
   else
   {
      echo "<br><br>".$_SESSION['ERROR_WEBSERVICES']; 
   }
   
   echo "<br><br>Intento número: ".$_SESSION['CUENTA_INTENTOS_12'];
   if($_SESSION['DURACION_ESPERA']!=0)
   {
      echo "<br><br>Tiempo transcurrido (min seg) ".date("i s",$_SESSION['DURACION_ESPERA']);		   
   }   

   

	 $error_new_soap="no error al crear soap";
	 try 
	 {
	 		$cliente = new SoapClient($_SESSION['NOMBRE_WEB_SERVICE']);
	 } 
	 catch (Exception $e) 
	 {
	    $error_new_soap="SI error al crear soap";
	 }
	 if($error_new_soap=="no error al crear soap")
	 {
	     $aux_vbles="<variables><NUMNITEMPRESATRANSPORTE>".$_SESSION['NIT_LOGI']."</NUMNITEMPRESATRANSPORTE>";
       $conta_nombres=0;
			 foreach($_SESSION['VEC_UN_TERCERO_CONDU'] as $campo)
			 {
			    if(strlen(trim($campo))==0)
			    {
			       // si está en blanco, no debe enviar:
             $conta_nombres++;			       
			    }
			    else
			    {
						  $aux_vbles=$aux_vbles
							   ."<".$_SESSION['NOMBRES_CAMPOS_CONDU'][$conta_nombres].">".utf8_decode($campo)."</".$_SESSION['NOMBRES_CAMPOS_CONDU'][$conta_nombres].">";
		          $conta_nombres++;
			    }
			 }		

       include("encab_xml_tercero.php");  // arma $aux_encabezado
		   $aux_cierre="</variables></root>";
		   $aux_toda=$aux_encabezado.$aux_vbles.$aux_cierre;			

			 $parametros = array('' => $aux_toda);

			 $error_envio_param="no error al enviar parametros";
			 try 
			 {
				    $respuesta=$cliente->__soapCall('AtenderMensajeRNDC',$parametros);
			 } 
			 catch (Exception $e) 
			 {
            $_SESSION['ERROR_WEBSERVICES']="Los datos no se enviaron correctamente al Web Services. Por favor revise";
				    $error_envio_param="SI error al enviar parametros";
			 }
	  }  // fin del if error_new_soap
	  else
	  {
	     // SI hubo un error al crear new_soap:
			 $tiempo_fin=time();
			 $_SESSION['DURACION_ESPERA']=$tiempo_fin-$_SESSION['TIEMPO_INICIO'];
			 if($_SESSION['DURACION_ESPERA'] > $_SESSION['TIEMPO_LIMITE_ESPERA_SEGUNDOS'])
			 {
			 		 $minutos_mensaje=number_format($_SESSION['TIEMPO_LIMITE_ESPERA_SEGUNDOS']/60,2,'.',',');
           $aux_error="El Ministerio ha tardado más de ".$minutos_mensaje." minuto(s) en responder. <br><br>Por favor intente más tarde o presione <a href='12a_select.php'>aqui</a> para intentarlo ya. <br><br>El programa ha suspendido su ejecución.";   
					 echo $aux_error;
					 exit;
			 }
			 else
			 {
           $_SESSION['ERROR_WEBSERVICES']="Intentando conexión con el Servidor del Ministerio.";
					 $_SESSION['CUENTA_INTENTOS_12']++;
           echo "<script>location.href='12c_proceso.php';</script>";								 
			 }
	  }
		
	  // =========================================================
		// TERCERO: Procesamiento de la respuesta del webservices
		// =========================================================
		$cadena_xml = simplexml_load_string($respuesta);
    $_SESSION['ERROR_WEBSERVICES']=utf8_decode(utf8_decode($cadena_xml->ErrorMSG));
		if(substr(trim($cadena_xml->ErrorMSG),0,18)=="Error al solicitar"
            or substr(trim($cadena_xml->ErrorMSG),0,25)=="Invalid pointer operation"	 
            or substr(trim($cadena_xml->ErrorMSG),0,24)=="Access violation at addr"	 
	          or $error_envio_param=='SI error al enviar parametros' 
				    or $error_new_soap=="SI error al crear soap"
				    or substr(trim($cadena_xml->ErrorMSG),0,16)=="Access violation"
				    )	 
    {
	       $error_conexion="si_existe";
    }   
    else
    {
			   $error_conexion="no_existe";	    
    }
	    
    // sigue el proceso de la respuesta que dió el webservice
    if(strlen(trim($cadena_xml->ingresoid))==0
		        and $error_conexion=="si_existe")
		{
       echo utf8_decode($respuesta);		
			 $tiempo_fin=time();
			 $_SESSION['DURACION_ESPERA']=$tiempo_fin-$_SESSION['TIEMPO_INICIO'];
			 if($_SESSION['DURACION_ESPERA'] > $_SESSION['TIEMPO_LIMITE_ESPERA_SEGUNDOS'])
			 {
			 		 $minutos_mensaje=number_format($_SESSION['TIEMPO_LIMITE_ESPERA_SEGUNDOS']/60,2,'.',',');
           $aux_error="El Ministerio ha tardado más de ".$minutos_mensaje." minuto(s) en responder. <br><br>Por favor intente más tarde o presione <a href='1a_select.php'>aqui</a> para intentarlo ya. <br><br>El programa ha suspendido su ejecución.";   
					 echo $aux_error;
					 exit;
			 }
			 else
			 {
           $_SESSION['ERROR_WEBSERVICES']="Error de conexión. El Ministerio no ha entregado ninguna respuesta.";
					 $_SESSION['CUENTA_INTENTOS_12']++;
           echo "<script>location.href='12c_proceso.php';</script>";								 
			 }
    }
		elseif(strlen(trim($cadena_xml->ingresoid))==0
		            and $error_conexion=="no_existe")
		{
       echo utf8_decode($respuesta);
			 echo "<br><br>Por favor proceda a corregir la inconsistencia en la información.";   
       $aux_error="<br><br>Una vez hecha la corrección, puede presionar <a href='12a_select.php'>aqui</a> para re-enviar.";   
		   echo $aux_error;
       exit;
    }  // fin del if còdigo nombre de error delweb service
		else
		{
		   // el envio fué exitoso:
		   // 1) grabar el numero de autorizacion en la tabla
	     //    autorizaciones de mysql
		   // 2) mostrar en el navegador la autorización, incrementar
		   //    el contador del vector para seguir recorriendo ese vector
		   //    mediante la llamada a 10a_......php
		   // 1)
       include("abrebd.php");  
       $aux_tipo="ter_con";
       $aux_numero=$_SESSION['VEC_UN_TERCERO_CONDU']['NUMIDTERCERO'];
       $aux_autoriz=$cadena_xml->ingresoid;
 			 // hora actual (now() de mysql no se puede usar
			 // debido al problema de hora de verano:
			 $aux_fecha=date("Y-m-d H:i:s",time());	

			 // para convertir de dd/mm/aaaa a aaaa-mm-dd
			 $vto_cambiar=$_SESSION['VEC_UN_TERCERO_CONDU']['FECHAVENCIMIENTOLICENCIA'];
       $vto_cambiar2=substr($vto_cambiar,6,4)."-".substr($vto_cambiar,3,2)."-".substr($vto_cambiar,0,2);
			 $aux_fec_vto=str_replace("/","-",$vto_cambiar2);
 			 $cadenaparaagregar=
				  "insert into ws_autoriz (tipo,numero,autoriz
				  ,fecha,fec_vto)
					values ('".$aux_tipo."'
					   ,'".$aux_numero."'
					   ,'".$aux_autoriz."'
					   ,'".$aux_fecha."'
					   ,'".$aux_fec_vto."'
					)";

		   $consultaagregar=mysql_query($cadenaparaagregar,$conexion)
				    or die ("Error: No se pudo agregar el número de autoriz.");	 
		
       // 2)
       echo "Se envió correctamente el conductor al Ministerio. Número de Autorización: ".$cadena_xml->ingresoid;
       echo "<script>location.href='13_un_vehiculo.php';</script>";
   } // fin del if código respuesta del web service


    
?>