<?
   session_start();

   include("abrebd.php");               

   $cadenaparaconsulta_una_remesa="select guias.`Guia` as CONSECUTIVOREMESA
			   , 'P' AS CODOPERACIONTRANSPORTE
			   , '0' AS CODTIPOEMPAQUE
			   , '1' AS CODNATURALEZACARGA
			   , 'PAQUETES VARIOS' AS DESCRIPCIONCORTAPRODUCTO
			   , '009880' AS MERCANCIAREMESA
			   , `guias`.`KilosReales` AS CANTIDADCARGADA
			   , '1' AS UNIDADMEDIDACAPACIDAD
			   , remitente.`TpDoc` AS CODTIPOIDREMITENTE
			   , remitente.`IDTercero` AS NUMIDREMITENTE
         , CASE
              WHEN remitente.`TpDoc`='N' then 'PPAL'
              ELSE '0' 
			     END AS CODSEDEREMITENTE
			   , 'C' AS CODTIPOIDDESTINATARIO
			   , '22222' AS NUMIDDESTINATARIO
         , 'PPAL' AS CODSEDEDESTINATARIO
			   , remitente.`TpDoc` AS CODTIPOIDPROPIETARIO
			   , remitente.`IDTercero` AS NUMIDPROPIETARIO
         , CASE
              WHEN remitente.`TpDoc`='N' then 'PPAL'
              ELSE '0' 
			     END AS CODSEDEPROPIETARIO
			   , 'E' AS DUENOPOLIZA
			   , `informacionempresa`.`NroPoliza` AS NUMPOLIZATRANSPORTE
			   , date_format(`informacionempresa`.`VencePoliza`, '%d/%m/%Y'  ) AS FECHAVENCIMIENTOPOLIZACARGA
         , `informacionempresa`.`NitAseguradora` AS COMPANIASEGURO			   
			   , '24' AS HORASPACTOCARGA
			   , '00' AS MINUTOSPACTOCARGA
-- OJO: para fechas anteriores a 2 meses, hay que arreglarlos
-- (modificacion que está pendiente
			   , date_format(`despachos`.`FhExpedicion`, '%d/%m/%Y'  ) AS FECHACITAPACTADACARGUE
--			   , '15/04/2013' AS FECHACITAPACTADACARGUE
			   , '22:00' AS HORACITAPACTADACARGUE
			   , '72' AS HORASPACTODESCARGUE
			   , '00' AS MINUTOSPACTODESCARGUE
			   , date_format(DATE_ADD(`despachos`.`FhExpedicion`,INTERVAL 4 DAY), '%d/%m/%Y'  ) AS FECHACITAPACTADADESCARGUE
--			   , '19/04/2013' AS FECHACITAPACTADADESCARGUE
			   , '08:00' AS HORACITAPACTADADESCARGUEREMESA
			from `guias` 
			   left join `informacionempresa` on `guias`.`IdEmpresa` = `informacionempresa`.`Id` 
			   left join `ciudades` `ciudestino` on `guias`.`IdCiuDestino` = `ciudestino`.`IdCiudad` 
			   left join `ciudades` `ciuorigen` on `guias`.`IdCiuOrigen` = `ciuorigen`.`IdCiudad` 
			   left join `despachos` on `guias`.`IdDespacho` = `despachos`.`OrdDespacho` 
			   left join terceros as remitente on guias.`Cuenta` = remitente.`IDTercero` 
			where guias.`Guia` = ".$_SESSION['VEC_REMESAS'][$_SESSION['CONTADOR_REGISTROS_REMESAS']];
		// el anterior select debe devolver un único registro:

		$matrizdatos_una_remesa=mysql_query($cadenaparaconsulta_una_remesa,$conexion)
				    or die ("Error: No se pudo ejecutar consulta _una_remesa");	 
		$_SESSION['VEC_UNA_REMESA']= mysql_fetch_assoc($matrizdatos_una_remesa);
    $_SESSION['NOMBRES_CAMPOS_REMESA']=array_keys($_SESSION['VEC_UNA_REMESA']);

    echo "<script>location.href='14b_ini_contadores.php';</script>";     					 
?>