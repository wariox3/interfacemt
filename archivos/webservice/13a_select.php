<?
   session_start();

   include("abrebd.php");               

   $cadenaparaconsulta_vehiculo="select `vehiculos`.`IdPlaca` AS NUMPLACA
   , `vehiculos`.`VehConfiguracion` AS CODCONFIGURACIONUNIDADCARGA
   , SUBSTRING(ws_marcaslin.actual,1,LOCATE('@', ws_marcaslin.actual)-1) AS CODMARCAVEHICULOCARGA
   , SUBSTRING(ws_marcaslin.actual,LOCATE('@', ws_marcaslin.actual)+1,5) AS CODLINEAVEHICULOCARGA
   , `vehiculos`.`NroEjes` As NUMEJES
   , `vehiculos`.`Modelo` AS ANOFABRICACIONVEHICULOCARGA
   , `vehiculos`.`ModeloRep` AS ANOREPOTENCIACION
   , `colores`.`CodMinTrans` AS CODCOLORVEHICULOCARGA
   , `vehiculos`.`PesoVacio` AS PESOVEHICULOVACIO
   , `vehiculos`.`CapKilos` AS CAPACIDADUNIDADCARGA
   , '1' AS UNIDADMEDIDACAPACIDAD 
   , `carrocerias`.`CodMinTrans` AS CODTIPOCARROCERIA
   , `vehiculos`.`chasis` AS NUMCHASIS
   , `vehiculos`.`TipoCombustible` AS CODTIPOCOMBUSTIBLE
   , `vehiculos`.`Soat` AS NUMSEGUROSOAT
   , date_format(`vehiculos`.`VenceSoat`, '%d/%m/%Y'  ) AS FECHAVENCIMIENTOSOAT
   , CASE
	WHEN `aseguradora`.`IDTercero` = '8600002184' THEN '8600021846'
	WHEN `aseguradora`.`IDTercero` = '860002400' THEN '8600024002'
	WHEN `aseguradora`.`IDTercero` = '860002534' THEN '8600025340'
	WHEN `aseguradora`.`IDTercero` = '860009578' THEN '8600095786'
	WHEN `aseguradora`.`IDTercero` = '860037013' THEN '8600370136'
	WHEN `aseguradora`.`IDTercero` = '86003988' THEN '8600399880'
	WHEN `aseguradora`.`IDTercero` = '860039988' THEN '8600399880'
	WHEN `aseguradora`.`IDTercero` = '890903407' THEN '8909034079'
	ELSE `aseguradora`.`IDTercero`
     END AS NUMNITASEGURADORASOAT
   , `propietario`.`TpDoc` AS CODTIPOIDPROPIETARIO
   , `propietario`.`IDTercero` AS NUMIDPROPIETARIO
   , `tenedor`.`TpDoc` AS CODTIPOIDTENEDOR
   , `tenedor`.`IDTercero` AS NUMIDTENEDOR
from `vehiculos` 
   left join `lineas` on `vehiculos`.`IdLinea` = `lineas`.`IdLinea` 
   left join ws_marcaslin on ws_marcaslin.anterior=concat(lineas.idmarca,'@',lineas.linea)
   left join `colores` on `vehiculos`.`IdColor` = `colores`.`IdColor`
   left join `carrocerias` on `vehiculos`.`IdCarroceria` = `carrocerias`.`IdCarroceria`
   left join `terceros` `aseguradora` on `vehiculos`.`IdAseguradora` = `aseguradora`.`IDTercero` 
   left join `terceros` `propietario` on `vehiculos`.`IdPropietario` = `propietario`.`IDTercero`
   left join `terceros` `tenedor` on `vehiculos`.`IdTenedor` = `tenedor`.`IDTercero`
where `vehiculos`.`IdPlaca` = '".$_SESSION['PLACA']."'";
	 // el anterior select debe devolver un único registro:
	 $matrizdatos_vehiculo=mysql_query($cadenaparaconsulta_vehiculo,$conexion)
				    or die ("Error: No se pudo ejecutar consulta _vehiculo");	 
	 $_SESSION['VEC_UN_VEHICULO']= mysql_fetch_assoc($matrizdatos_vehiculo);
   $_SESSION['NOMBRES_CAMPOS_VEH']=array_keys($_SESSION['VEC_UN_VEHICULO']);

   echo "<script>location.href='13b_ini_contadores.php';</script>";     					 

?>