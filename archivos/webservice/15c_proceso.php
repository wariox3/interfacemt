<?
   session_start();
   date_default_timezone_set("America/Bogota");   
   error_reporting(0);
   set_time_limit(0);
   
   // =======================================================================
   // 1)   Mostrar información del mfto que se está enviando
   // =======================================================================   
   echo "Procesando el manifiesto número ".$_SESSION['MFTO']."<br><br>";
   echo "-----------------------------------------------------------------------------------------------------------<br>";
   echo "Enviando el manifiesto: ".$_SESSION['MFTO']."<br>";
   echo "-----------------------------------------------------------------------------------------------------------<br><br>";

   // =======================================================================
   // 2)   Si llegó a este programa por un error de conexion (else) muestra
   //      el texto del error.
   // =======================================================================   
	 if(strlen(trim($_SESSION['ERROR_WEBSERVICES']))==0)
   {
      echo "Error de conexión. El Servidor del Ministerio no entregó ninguna respuesta.";
   }
   else
   {
      echo "<br><br>".$_SESSION['ERROR_WEBSERVICES']; 
   }
   
   // =======================================================================
   // 3)   Muestra la cantidad de intentos. Si la vble que cuenta la duración
   //      no es cero, muestra cuantos segundos de proceso van
   // =======================================================================   
   echo "<br><br>Intento número: ".$_SESSION['CUENTA_INTENTOS_15'];
   if($_SESSION['DURACION_ESPERA']!=0)
   {
      echo "<br><br>Tiempo transcurrido (min seg) ".date("i s",$_SESSION['DURACION_ESPERA']);		   
   }   

   // =======================================================================
   // 4)   Conexion al Web Services. Si hay error habrán dos posibilidades: O
   //      vuelve a ejecutar este script o si el ministerio ha demorado mucho
   //      termina el proceso.
   // =======================================================================   
	 try 
	 {
	 		$cliente = new SoapClient($_SESSION['NOMBRE_WEB_SERVICE']);
	 } 
	 catch (Exception $e) 
	 {
	     // SI hubo un error al crear new_soap:
			 $tiempo_fin=time();
			 $_SESSION['DURACION_ESPERA']=$tiempo_fin-$_SESSION['TIEMPO_INICIO'];
			 if($_SESSION['DURACION_ESPERA'] > $_SESSION['TIEMPO_LIMITE_ESPERA_SEGUNDOS'])
			 {
			 		 $minutos_mensaje=number_format($_SESSION['TIEMPO_LIMITE_ESPERA_SEGUNDOS']/60,2,'.',',');
           $aux_error="El Ministerio ha tardado más de ".$minutos_mensaje." minuto(s) en responder. <br><br>Por favor intente más tarde o presione <a href='15b_ini_contadores.php'>aqui</a> para intentarlo ya. <br><br>El programa ha suspendido su ejecución.";   
					 echo $aux_error;
					 exit;
			 }
			 else
			 {
           $_SESSION['ERROR_WEBSERVICES']="Intentando conexión con el Servidor del Ministerio.";
					 $_SESSION['CUENTA_INTENTOS_15']++;
           echo "<script>location.href='15c_proceso.php';</script>";								 
			 }
	 }

   // =======================================================================
   // 5)   Arma el cuerpo central del XML (Los campos a enviar)
   // =======================================================================   
   $aux_vbles="<variables>
			  <NUMNITEMPRESATRANSPORTE>".$_SESSION['NIT_LOGI']."</NUMNITEMPRESATRANSPORTE>";
   $conta_nombres=0;
	 foreach($_SESSION['VEC_UN_MFTO'] as $campo)
	 {
			    if(strlen(trim($campo))==0)
			    {
			       // si está en blanco, no debe enviar:
             $conta_nombres++;			       
			    }
			    else
			    {
						  $aux_vbles=$aux_vbles
							   ."<".$_SESSION['NOMBRES_CAMPOS_MFTO'][$conta_nombres]
								 .">"
								 .utf8_decode($campo)
								 ."</"
								 .$_SESSION['NOMBRES_CAMPOS_MFTO'][$conta_nombres]
								 .">";
		          $conta_nombres++;
			    }
	 }
	 // Al manifiesto hay que agregar el TAG que contiene las
	 // remesas: (REMESASMAN que va después de OBSERVACIONES):
	 $aux_vbles=$aux_vbles."<REMESASMAN procesoid=\"43\">";
	 foreach($_SESSION['VEC_REMESAS_INI'] as $una_remesa)
	 {
			    $aux_vbles=$aux_vbles."<REMESA>
						<CONSECUTIVOREMESA>".$una_remesa."</CONSECUTIVOREMESA>
						</REMESA>";
	 }			
	 $aux_vbles=$aux_vbles."</REMESASMAN>";

   // =======================================================================
   // 6)   Arma el encabezado y el cierre del XML, y luego la variable TODA
   //      a enviar al web service
   // =======================================================================   
   include("encab_xml_mfto.php");  // arma $aux_encabezado
   $aux_cierre="</variables>
	  		</root>";
   $aux_toda=$aux_encabezado.$aux_vbles.$aux_cierre;			

   // =======================================================================
   // 7)   Envia el XML al webservice. Si hay error en el envio, habrán dos 
	 //      posibilidades: O vuelve a ejecutar este script o si el ministerio 
	 //      ha demorado mucho termina el proceso.
   // =======================================================================   
   $parametros = array('' => $aux_toda);
   try 
	 {
	    $respuesta=$cliente->__soapCall('AtenderMensajeRNDC',$parametros);
	 } 
	 catch (Exception $e) 
	 {
			       echo utf8_decode($respuesta);
						 $tiempo_fin=time();
						 $_SESSION['DURACION_ESPERA']=$tiempo_fin-$_SESSION['TIEMPO_INICIO'];
						 if($_SESSION['DURACION_ESPERA'] > $_SESSION['TIEMPO_LIMITE_ESPERA_SEGUNDOS'])
						 {
						 		 $minutos_mensaje=number_format($_SESSION['TIEMPO_LIMITE_ESPERA_SEGUNDOS']/60,2,'.',',');
			           $aux_error="El Ministerio ha tardado más de ".$minutos_mensaje." minuto(s) en responder. <br><br>Por favor intente más tarde o presione <a href='15b_ini_contadores.php'>aqui</a> para intentarlo ya. <br><br>El programa ha suspendido su ejecución.";   
								 echo $aux_error;
								 exit;
						 }
						 else
						 {
			           $_SESSION['ERROR_WEBSERVICES']="Error de conexión. El Ministerio no ha entregado ninguna respuesta.";
								 $_SESSION['CUENTA_INTENTOS_15']++;
			           echo "<script>location.href='15c_proceso.php';</script>";								 
						 }
		}

	  // =========================================================
    // 8)   Recibir respuesta del web service
		// =========================================================
		$cadena_xml = simplexml_load_string($respuesta);

	  // =========================================================
    // 9)   Averiguar si se recibio un ErrorMSG, si es asi habrán 
		//      dos posibilidades: O vuelve a ejecutar este script 
		//      o si el ministerio ha demorado mucho termina el programa
		// =========================================================
    $_SESSION['ERROR_WEBSERVICES']=utf8_decode(utf8_decode($cadena_xml->ErrorMSG));
		if(substr(trim($cadena_xml->ErrorMSG),0,18)=="Error al solicitar"
            or substr(trim($cadena_xml->ErrorMSG),0,25)=="Invalid pointer operation"	 
            or substr(trim($cadena_xml->ErrorMSG),0,24)=="Access violation at addr"	 
            or substr(trim($cadena_xml->ErrorMSG),0,28)=="Connection Closed Gracefully"	 
				    or substr(trim($cadena_xml->ErrorMSG),0,16)=="Access violation"
				    )	 
    {
			       echo utf8_decode($respuesta);
						 $tiempo_fin=time();
						 $_SESSION['DURACION_ESPERA']=$tiempo_fin-$_SESSION['TIEMPO_INICIO'];
						 if($_SESSION['DURACION_ESPERA'] > $_SESSION['TIEMPO_LIMITE_ESPERA_SEGUNDOS'])
						 {
						 		 $minutos_mensaje=number_format($_SESSION['TIEMPO_LIMITE_ESPERA_SEGUNDOS']/60,2,'.',',');
			           $aux_error="El Ministerio ha tardado más de ".$minutos_mensaje." minuto(s) en responder. <br><br>Por favor intente más tarde o presione <a href='15b_ini_contadores.php'>aqui</a> para intentarlo ya. <br><br>El programa ha suspendido su ejecución.";   
								 echo $aux_error;
								 exit;
						 }
						 else
						 {
								 $_SESSION['CUENTA_INTENTOS_14']++;
			           echo "<script>location.href='15c_proceso.php';</script>";								 
						 }
		}		    

	  // =========================================================
    // 10)   Averiguar, en caso de que se haya recibido un mensaje de
		//      error, si corresponde a un DUPLICADO, si es asi se debe
		//      buscar el num de autorización en el RNDC para proceder
		//      a grabarlo en la tabla ws_autoriz. 
		// =========================================================
    $pos_duplicado = strpos($cadena_xml->ErrorMSG,"DUPLICADO");
		if ($pos_duplicado === false) 
		{
		   // no es un duplicado
		} 
		else 
		{
		   // si es un duplicado:
	     // 1) debe buscar el num de autorizacion que hay en el
		   //    web service para este manifiesto y grabarlo en ws_autoriz
       // 2) grabar el numero de autorizacion en la tabla ws_autoriz
       // 3) grabar el numero de autorizacion en la tabla 
       //    'despachos', campo 'ManElectronico' de la BD logicuartas
       // 4) Llevar el indicador 1 a la tabla 'despachos',
       //    campo 'EnviadoMT' de la BD logicuartas. Y al campo
       //    ManElectronico el num de autorizacion
		   // 5) mostrar en el navegador la autorización, con
			 //    esto termina el envio del mfto
		   
			 // 1) 
		   $num_autoriz_existe=obtiene_num_autoriz_mfto($_SESSION['MFTO']);
		   // en caso de que el web service no haya devuelto un valor
		   // correcto el programa no llegara a la siguiente linea sino
	     // que empezara nuevamente este 15c_.....php

		   // 2) 
		   include("abrebd.php");  
       $aux_tipo="man_env";
       $aux_numero=$_SESSION['MFTO'];
		   $aux_autoriz=$num_autoriz_existe;
 			 // hora actual (now() de mysql no se puede usar
			 // debido al problema de hora de verano:
			 $aux_fecha=date("Y-m-d H:i:s",time());	
 			 $cadenaparaagregar=
				  "insert into ws_autoriz (tipo,numero,autoriz
				  ,fecha)
					values ('".$aux_tipo."'
					   ,'".$aux_numero."'
					   ,'".$aux_autoriz."'
					   ,'".$aux_fecha."'
					)";
		   $consultaagregar=mysql_query($cadenaparaagregar,$conexion)
				    or die ("Error: No se pudo agregar el número de autoriz.");	 
		   // 3) y 4)
 			 $cadenaparamodificar=
				  "update despachos
					   set ManElectronico='".$aux_autoriz."',EnviadoMT=1 
             where `despachos`.`idManifiesto` = ".$_SESSION['MFTO'];
		   $consultamodificar=mysql_query($cadenaparamodificar,$conexion)
				    or die ("Error: No se pudo colocar el nro autoriz en la tabla despachos");	 
			 
			 // 5) 
			 echo "<br>Se envió correctamente el manifiesto al Ministerio. Número de Autorización: ".$aux_autoriz;
       exit;
		}    
    
	  // =========================================================
    // 11)   Si la respuesta no es un duplicado, verifica si se entregó
		//       un num de autorización. Si no fue asi termina el programa
		// =========================================================
    if(strlen(trim($cadena_xml->ingresoid))==0)
		{
		   // no se recibió num de autorización:
	     echo utf8_decode($respuesta);
			 echo "<br><br>Por favor proceda a corregir la inconsistencia en la información.";   
       $aux_error="<br><br>Una vez hecha la corrección, puede presionar <a href='15b_ini_contadores.php'>aqui</a> para re-enviar.";   
		   echo $aux_error;
       exit;
    }
		else
		{
		   // el envio fué exitoso:
       // 1) grabar el numero de autorizacion en la tabla ws_autoriz
       // 2) grabar el numero de autorizacion en la tabla 
       //    'despachos', campo 'ManElectronico' de la BD logicuartas
       // 3) Llevar el indicador 1 a la tabla 'despachos',
       //    campo 'EnviadoMT' de la BD logicuartas. Y al campo
       //    ManElectronico el num de autorizacion
		   // 4) mostrar en el navegador la autorización, con
			 //    esto termina el envio del mfto
		   
		   // 1) 
		   include("abrebd.php");  
       $aux_tipo="man_env";
       $aux_numero=$_SESSION['MFTO'];
		   $aux_autoriz=$cadena_xml->ingresoid;
 			 // hora actual (now() de mysql no se puede usar
			 // debido al problema de hora de verano:
			 $aux_fecha=date("Y-m-d H:i:s",time());	
 			 $cadenaparaagregar=
				  "insert into ws_autoriz (tipo,numero,autoriz
				  ,fecha)
					values ('".$aux_tipo."'
					   ,'".$aux_numero."'
					   ,'".$aux_autoriz."'
					   ,'".$aux_fecha."'
					)";
		   $consultaagregar=mysql_query($cadenaparaagregar,$conexion)
				    or die ("Error: No se pudo agregar el número de autoriz.");	 
		   // 2) y 3)
 			 $cadenaparamodificar=
				  "update despachos
					   set ManElectronico='".$aux_autoriz."',EnviadoMT=1 
             where `despachos`.`idManifiesto` = ".$_SESSION['MFTO'];
		   $consultamodificar=mysql_query($cadenaparamodificar,$conexion)
				    or die ("Error: No se pudo colocar el nro autoriz en la tabla despachos");	 
			 
			 // 4) 
			 echo "<br>Se envió correctamente el manifiesto al Ministerio. Número de Autorización: ".$cadena_xml->ingresoid;
       exit;
    } // fin del if código respuesta del web service
    
function obtiene_num_autoriz_mfto($num_mfto)
{
   // ==========================================================
   // a) new soap:
   // ==========================================================   
	 try 
	 {
	 		$cliente = new SoapClient($_SESSION['NOMBRE_WEB_SERVICE']);
	 } 
	 catch (Exception $e) 
	 {
           echo "<script>location.href='15c_proceso.php';</script>";								 
	 }
	 
   // ==========================================================
   // b) envio de solicitud de consulta al web services
   // ==========================================================   
   include("xml_mfto_duplicado.php");  
	 // alli se arma la vble $aux_buscar_autoriz
	 $parametros_autoriz = array('' => $aux_buscar_autoriz);
   try 
	 {
		    $respuesta_autoriz=$cliente->__soapCall('AtenderMensajeRNDC',$parametros_autoriz);
	 } 
	 catch (Exception $e) 
	 {
			       echo utf8_decode($respuesta);
	           echo "<script>location.href='15c_proceso.php';</script>";								 
	 }

   // ==========================================================
   // c) Recepción de la respuesta
   // ==========================================================   
	 $cadena_xml = simplexml_load_string($respuesta_autoriz);
	 
   // ==========================================================
   // d) Determinar si se recibio un error de conexion. Si es asi
	 //    se volverá a ejecutar este script desde el principio.
   // ==========================================================   
   $aux_ya_autoriz=$cadena_xml->documento->ingresoid;
	 if(substr(trim($cadena_xml->ErrorMSG),0,18)=="Error al solicitar"
            or substr(trim($cadena_xml->ErrorMSG),0,25)=="Invalid pointer operation"	 
            or substr(trim($cadena_xml->ErrorMSG),0,24)=="Access violation at addr"	 
            or substr(trim($cadena_xml->ErrorMSG),0,28)=="Connection Closed Gracefully"	 
				    or substr(trim($cadena_xml->ErrorMSG),0,16)=="Access violation"
				    )	 
	 {
	           echo "<script>location.href='15c_proceso.php';</script>";								 
	           return 0;
	 }
	 		    
   // ==========================================================
   // e) Si el webservice retorno un num de autorizacion cero, 
	 //    se volverá a ejecutar este script desde el principio. En
	 //    caso contrario se retornará el num de autorización para 
	 //    que sea grabado en la tabla ws_autoriz
   // ==========================================================   
   if(strlen(trim($aux_ya_autoriz))==0)
	 {
			       echo utf8_decode($respuesta);
	           echo "<script>location.href='15c_proceso.php';</script>";								 
	           return 0;
   }
   else
   {
       return $aux_ya_autoriz;
	 }
}
    
?>