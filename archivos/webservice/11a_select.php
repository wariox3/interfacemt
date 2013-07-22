<?
   session_start();

   include("abrebd.php");               

   $cadenaparaconsulta_terce_emp="select `terceros`.`TpDoc` AS CODTIPOIDTERCERO
				   , `terceros`.`IDTercero` AS NUMIDTERCERO
				   , replace(`terceros`.`RazonSocial`,'&','Y') AS NOMIDTERCERO
,'PPAL' AS CODSEDETERCERO
,'PRINCIPAL' AS NOMSEDETERCERO
,CASE
   WHEN LENGTH(TRIM(`terceros`.`Telefono`))<=7 THEN `terceros`.`Telefono`
   ELSE ''
 END AS NUMTELEFONOCONTACTO
,CASE
   WHEN LENGTH(TRIM(`terceros`.`Telefono`))>7 THEN `terceros`.`Telefono`
   ELSE ''
 END AS NUMCELULARPERSONA
				   , `terceros`.`Direccion` AS NOMENCLATURADIRECCION
				   ,CASE 
				       WHEN LENGTH(TRIM(CAST(`ciudades`.`CodMinTrans` AS CHAR))) = 7 THEN CONCAT('0',CAST(`ciudades`.`CodMinTrans` AS CHAR))
				       ELSE CAST(`ciudades`.`CodMinTrans` AS CHAR)
				    END AS CODMUNICIPIORNDC 

				from `terceros` 
				   left join `ciudades` on `terceros`.`IdCiudad` = `ciudades`.`IdCiudad` 
				where `terceros`.`IDTercero` = '".$_SESSION['VEC_EMP'][$_SESSION['CONTADOR_REGISTROS_EMP']]."'";
		$matrizdatos_terce_emp=mysql_query($cadenaparaconsulta_terce_emp,$conexion)
 				    or die ("Error: No se pudo ejecutar consulta _terce_per");	 
		$_SESSION['VEC_UN_TERCERO_EMP']= mysql_fetch_assoc($matrizdatos_terce_emp);
    $_SESSION['NOMBRES_CAMPOS_EMP']=array_keys($_SESSION['VEC_UN_TERCERO_EMP']);


    echo "<script>location.href='11b_ini_contadores.php';</script>";     					 
?>