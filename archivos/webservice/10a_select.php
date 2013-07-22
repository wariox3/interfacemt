<?
   session_start();

   include("abrebd.php");               

   $cadenaparaconsulta_terce_per="select `terceros`.`TpDoc` AS CODTIPOIDTERCERO
				   , `terceros`.`IDTercero` AS NUMIDTERCERO
				   , `terceros`.`Nombre` AS NOMIDTERCERO
				   , `terceros`.`Apellido1` AS PRIMERAPELLIDOIDTERCERO
				   , `terceros`.`Apellido2` AS SEGUNDOAPELLIDOIDTERCERO
,'0' AS CODSEDETERCERO
				 ,CASE
   WHEN LENGTH(TRIM(`terceros`.`Telefono`))<=7 THEN `terceros`.`Telefono`
   ELSE ''
 END AS NUMTELEFONOCONTACTO
,CASE
   WHEN LENGTH(TRIM(`terceros`.`Telefono`))=10 THEN `terceros`.`Telefono`
   ELSE ''
 END AS NUMCELULARPERSONA
				   , `terceros`.`Direccion` AS NOMENCLATURADIRECCION
				   ,CASE 
				       WHEN LENGTH(TRIM(CAST(`ciudades`.`CodMinTrans` AS CHAR))) = 7 THEN CONCAT('0',CAST(`ciudades`.`CodMinTrans` AS CHAR))
				       ELSE CAST(`ciudades`.`CodMinTrans` AS CHAR)
				    END AS CODMUNICIPIORNDC
				from `terceros` 
				   left join `ciudades` on `terceros`.`IdCiudad` = `ciudades`.`IdCiudad` 
				where `terceros`.`IDTercero` =  ".$_SESSION['VEC_PER'][$_SESSION['CONTADOR_REGISTROS_PER']];
		// el anterior select debe devolver un único registro:

		$matrizdatos_terce_per=mysql_query($cadenaparaconsulta_terce_per,$conexion)
				    or die ("Error: No se pudo ejecutar consulta _terce_per");	 
		$_SESSION['VEC_UN_TERCERO_PER']= mysql_fetch_assoc($matrizdatos_terce_per);
    $_SESSION['NOMBRES_CAMPOS_PER']=array_keys($_SESSION['VEC_UN_TERCERO_PER']);

    echo "<script>location.href='10b_ini_contadores.php';</script>";     					 
?>