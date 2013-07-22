<?
   session_start();

   include("abrebd.php");               

   $cadenaparaconsulta_un_mfto="select `despachos`.`IdManifiesto` AS NUMMANIFIESTOCARGA
				   , 'P' AS CODOPERACIONTRANSPORTE
				   , date_format(`despachos`.`FhExpedicion`, '%d/%m/%Y'  ) AS FECHAEXPEDICIONMANIFIESTO
				   ,CASE 
				       WHEN LENGTH(TRIM(CAST(ciuorigen.codmintrans AS CHAR))) = 7 THEN CONCAT('0',CAST(ciuorigen.codmintrans AS CHAR))
				       ELSE CAST(ciuorigen.codmintrans AS CHAR)
				    END AS CODMUNICIPIOORIGENMANIFIESTO
				   ,CASE 
				       WHEN LENGTH(TRIM(CAST(`ciudestino`.`CodMinTrans` AS CHAR))) = 7 THEN CONCAT('0',CAST(`ciudestino`.`CodMinTrans` AS CHAR))
				       ELSE CAST(`ciudestino`.`CodMinTrans` AS CHAR)
				    END AS CODMUNICIPIODESTINOMANIFIESTO
				   , `titular`.`TpDoc`  AS CODIDTITULARMANIFIESTO
				   , `vehiculos`.`IdTenedor` AS NUMIDTITULARMANIFIESTO
				   , `despachos`.`IdVehiculo`  AS NUMPLACA
				   , `vehiculos`.`PlacaRemolque`  AS NUMPLACAREMOLQUE
				   , `conductores`.`TpIdConductor` AS CODIDCONDUCTOR
				   , `conductores`.`IdConductor` AS NUMIDCONDUCTOR
					 ,case 
						    when ws_propios.placa_propio is null then `despachos`.`VrFlete`
						    else 0
					  end AS VALORFLETEPACTADOVIAJE
					 ,case 
						    when ws_propios.placa_propio is null then `despachos`.`VrDctoRteFte` 
						    else 0
					  end AS RETENCIONFUENTEMANIFIESTO
					 ,case 
						    when ws_propios.placa_propio is null then (`despachos`.`VrDctoIndCom`/`despachos`.`VrFlete`)*1000 
						    else 0
						end AS RETENCIONICAMANIFIESTOCARGA
					 ,case 
						    when ws_propios.placa_propio is null then `despachos`.`VrAnticipo` 
						    else 0
					  end AS VALORANTICIPOMANIFIESTO
				   , date_format(`despachos`.`FhPagoSaldo`, '%d/%m/%Y'  ) AS FECHAPAGOSALDOMANIFIESTO
			     ,CASE
				       WHEN `despachos`.`PagoCargue` = 'TRANSPORTES CUARTAS S.A' THEN 'E'
				       ELSE 'VERIF'
				    END AS CODRESPONSABLEPAGOCARGUE
				   ,CASE
				       WHEN `despachos`.`PagoDescargue` = 'TRANSPORTES CUARTAS S.A' THEN 'E'
				       ELSE 'VERIF'
				    END AS CODRESPONSABLEPAGODESCARGUE	
				   , `despachos`.`Observaciones` AS OBSERVACIONES
				   ,CASE
				       WHEN `despachos`.`LugarPago` = 'MEDELLIN' THEN '05001000'
				       ELSE 'VERIF'
				    END AS CODMUNICIPIOPAGOSALDO
				from (((((((`despachos` 
				   left join `informacionempresa` on((`despachos`.`IdEmpresa` = `informacionempresa`.`Id`))) 
				   join `ciudades` `ciuorigen` on((`despachos`.`IdCiudadOrigen` = `ciuorigen`.`IdCiudad`))) 
				   join `ciudades` `ciudestino` on((`despachos`.`IdCiudadDestino` = `ciudestino`.`IdCiudad`))) 
				   join `conductores` on((`despachos`.`IdConductor` = `conductores`.`IdConductor`))) 
				   join `vehiculos` on((`despachos`.`IdVehiculo` = `vehiculos`.`IdPlaca`))) 
				   join `carrocerias` on((`vehiculos`.`IdCarroceria` = `carrocerias`.`IdCarroceria`))) 
				   join `terceros` `titular` on((`vehiculos`.`IdTenedor` = `titular`.`IDTercero`))) 
        left join ws_propios on ws_propios.placa_propio = despachos.idvehiculo
				where `despachos`.`idManifiesto` =".$_SESSION['MFTO'];
	 // el anterior select debe devolver un único registro:
	 $matrizdatos_un_mfto=mysql_query($cadenaparaconsulta_un_mfto,$conexion)
				    or die ("Error: No se pudo ejecutar consulta _un_mfto");	 
	 $_SESSION['VEC_UN_MFTO']= mysql_fetch_assoc($matrizdatos_un_mfto);
   $_SESSION['NOMBRES_CAMPOS_MFTO']=array_keys($_SESSION['VEC_UN_MFTO']);

   echo "<script>location.href='15b_ini_contadores.php';</script>";     					 

?>