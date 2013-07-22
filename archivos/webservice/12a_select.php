<?
   session_start();

   include("abrebd.php");               

   $cadenaparaconsulta_terce_condu="select `conductores`.`TpIdConductor` AS CODTIPOIDTERCERO
			   , `conductores`.`IdConductor` AS NUMIDTERCERO
			   , `conductores`.`Nombre` AS NOMIDTERCERO
			   , `conductores`.`Apellido1` AS PRIMERAPELLIDOIDTERCERO
			   , `conductores`.`Apellido2` AS SEGUNDOAPELLIDOIDTERCERO
			   , `conductores`.`TelConductor` AS NUMTELEFONOCONTACTO
                           , CASE
                                WHEN LENGTH(`conductores`.`Celular`)=10 THEN `conductores`.`Celular`
                                ELSE ''
                             END AS NUMCELULARPERSONA
			   , `conductores`.`Direccion` AS NOMENCLATURADIRECCION
			   ,CASE 
			       WHEN LENGTH(TRIM(CAST(`ciudades`.`CodMinTrans` AS CHAR))) = 7 THEN CONCAT('0',CAST(`ciudades`.`CodMinTrans` AS CHAR))
			       ELSE CAST(`ciudades`.`CodMinTrans` AS CHAR)
			    END AS CODMUNICIPIORNDC
			   ,case
			      when `conductores`.`Categoria`='4' then '4'
			      when `conductores`.`Categoria`='5' then '5'
			      when `conductores`.`Categoria`='6' then '6'
			      when `conductores`.`Categoria`='7' then 'C1'
			      when `conductores`.`Categoria`='8' then 'C2'
			      when `conductores`.`Categoria`='9' then 'C3' 
			      else `conductores`.`Categoria`                             
			    end AS CODCATEGORIALICENCIACONDUCCION
			   , `conductores`.`LicenciaConductor` AS NUMLICENCIACONDUCCION
   , date_format(`conductores`.`FhVenceLic`, '%d/%m/%Y'  ) AS FECHAVENCIMIENTOLICENCIA
			from `ciudades` 
			   join `conductores` on `ciudades`.`IdCiudad` = `conductores`.`IdCiudad` 
			where conductores . IdConductor =  ".$_SESSION['CONDUCTOR'];
	 // el anterior select debe devolver un único registro:
	 $matrizdatos_terce_condu=mysql_query($cadenaparaconsulta_terce_condu,$conexion)
				    or die ("Error: No se pudo ejecutar consulta _terce_condu");	 
	 $_SESSION['VEC_UN_TERCERO_CONDU']= mysql_fetch_assoc($matrizdatos_terce_condu);
   $_SESSION['NOMBRES_CAMPOS_CONDU']=array_keys($_SESSION['VEC_UN_TERCERO_CONDU']);

echo $_SESSION['VEC_UN_TERCERO_CONDU']['FECHAVENCIMIENTOLICENCIA'];
echo "<br><br>";
//$aux_fec_vto=date("Y-m-d",$_SESSION['VEC_UN_TERCERO_CONDU']['FECHAVENCIMIENTOLICENCIA']);
//$aux_fec_vto=$_SESSION['VEC_UN_TERCERO_CONDU']['FECHAVENCIMIENTOLICENCIA'];
//$aux_fec_vto=date_format($_SESSION['VEC_UN_TERCERO_CONDU']['FECHAVENCIMIENTOLICENCIA'],"Y-m-d");
//$vto_cambiar=$_SESSION['VEC_UN_TERCERO_CONDU']['FECHAVENCIMIENTOLICENCIA'];
//$aux_fec_vto=substr($vto_cambiar,0,2)."-".substr($vto_cambiar,3,2)."-".substr($vto_cambiar,6,4);
//$aux_fec_vto=str_replace("/","-",$_SESSION['VEC_UN_TERCERO_CONDU']['FECHAVENCIMIENTOLICENCIA']);

/**
// para convertir de dd/mm/aaaa a aaaa-mm-dd
$vto_cambiar=$_SESSION['VEC_UN_TERCERO_CONDU']['FECHAVENCIMIENTOLICENCIA'];
$aux_fec_vto=substr($vto_cambiar,6,4)."-".substr($vto_cambiar,3,2)."-".substr($vto_cambiar,0,2);
echo "la vecha vto es: ".$aux_fec_vto;
exit;
**/

   echo "<script>location.href='12b_ini_contadores.php';</script>";     					 

?>