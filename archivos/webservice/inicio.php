<?
   session_start();
   $_SESSION['MFTO']=$_GET['mfto'];
   $_SESSION['TIEMPO_LIMITE_ESPERA_SEGUNDOS']=$_GET['espera'];
   
   // ========================================================
   // PRIMERO: Variables de uso recurrente
	 // ========================================================
   $_SESSION['NOMBRE_WEB_SERVICE']="http://72.167.52.87:8080/ws/svr008w.dll/wsdl/IBPMServices";
   $_SESSION['NIT_LOGI']="9004861213";
   $_SESSION['USU_WEB']="LOGI@2446LOGICUARTAS";
   $_SESSION['ACCESO_WEB']="Lo15me_A24";
   
   
// echo "<script>location.href='15_un_mfto.php';</script>";   
   
   // ========================================================
   // SEGUNDO: Recopilación del titular, conductor y placa
	 //          del manifiesto.
	 // ========================================================
   include("abrebd.php");
   // TITULAR, CONDUCTOR, PLACA:
   $cadenaparaconsulta_premftos="select despachos.orddespacho
			   , `titular`.`TpDoc`  AS CODIDTITULARMANIFIESTO
			   , `vehiculos`.`IdTenedor` AS NUMIDTITULARMANIFIESTO
			   , `despachos`.`IdVehiculo`  AS NUMPLACA
			   , `conductores`.`IdConductor` AS NUMIDCONDUCTOR
			from (((((((`despachos` 
			   left join `informacionempresa` on((`despachos`.`IdEmpresa` = `informacionempresa`.`Id`))) 
			   join `ciudades` `ciuorigen` on((`despachos`.`IdCiudadOrigen` = `ciuorigen`.`IdCiudad`))) 
			   join `ciudades` `ciudestino` on((`despachos`.`IdCiudadDestino` = `ciudestino`.`IdCiudad`))) 
			   join `conductores` on((`despachos`.`IdConductor` = `conductores`.`IdConductor`))) 
			   join `vehiculos` on((`despachos`.`IdVehiculo` = `vehiculos`.`IdPlaca`))) 
			   join `carrocerias` on((`vehiculos`.`IdCarroceria` = `carrocerias`.`IdCarroceria`))) 
			   join `terceros` `titular` on((`vehiculos`.`IdTenedor` = `titular`.`IDTercero`))) 
			where (`despachos`.`idManifiesto` = ".$_SESSION['MFTO'].")";
		// el anterior select debe devolver un único registro:
		$matrizdatos_premftos=mysql_query($cadenaparaconsulta_premftos,$conexion)
				    or die ("Error: No se pudo ejecutar consulta premftos");	 
		$vec_per_ini=array();
		$vec_emp_ini=array();
    $conta_per=0;
    $conta_emp=0;
		$registro= mysql_fetch_assoc($matrizdatos_premftos);

    $_SESSION['ORDEN_DESPACHO']=$registro['orddespacho'];
		$ced_conductor=$registro['NUMIDCONDUCTOR'];    
		$placa=$registro['NUMPLACA'];
			
		if($registro['CODIDTITULARMANIFIESTO']=='C')
		{
		          $vec_per_ini[$conta_per]=$registro['NUMIDTITULARMANIFIESTO'];
              $conta_per++;
		}
		elseif($registro['CODIDTITULARMANIFIESTO']=='N')
		{
		          $vec_emp_ini[$conta_per]=$registro['NUMIDTITULARMANIFIESTO'];       
              $conta_emp++;
		}
		else
		{
		          echo "error indeterminado en if titular. El programa suspenderá";
		          exit;
		}

		
   // ========================================================
   // TERCERO: Recopilación de los remitentes
	 // ========================================================
    $cadenaparaconsulta_preremitentes="select remitente.`TpDoc` AS CODTIPOIDREMITENTE
				   , remitente.`IDTercero` AS NUMIDREMITENTE
				from `guias` 
				   left join `informacionempresa` on `guias`.`IdEmpresa` = `informacionempresa`.`Id` 
				   left join `ciudades` `ciudestino` on `guias`.`IdCiuDestino` = `ciudestino`.`IdCiudad` 
				   left join `ciudades` `ciuorigen` on `guias`.`IdCiuOrigen` = `ciuorigen`.`IdCiudad` 
				   left join `despachos` on `guias`.`IdDespacho` = `despachos`.`OrdDespacho` 
				   left join terceros as remitente on guias.`Cuenta` = remitente.`IDTercero` 
				where (`guias`.`IdDespacho` = ".$_SESSION['ORDEN_DESPACHO'].")";

		// el anterior select puede devolver varios registros:
		$matrizdatos_preremitentes=mysql_query($cadenaparaconsulta_preremitentes,$conexion)
				    or die ("Error: No se pudo ejecutar consulta _preremitentes");	 
		while($registro_preremitentes= mysql_fetch_assoc($matrizdatos_preremitentes))
		{
				if($registro_preremitentes['CODTIPOIDREMITENTE']=='C')
				{
				          $vec_per_ini[$conta_per]=$registro_preremitentes['NUMIDREMITENTE'];
                  $conta_per++;
				}
				elseif($registro_preremitentes['CODTIPOIDREMITENTE']=='N')
				{
				          $vec_emp_ini[$conta_emp]=$registro_preremitentes['NUMIDREMITENTE'];      
                  $conta_emp++;
				}
				else
				{
				          echo "error indeterminado en if remitente remesa. El programa suspenderá";
				          exit;
				}
		}		
		
   // ========================================================
   // CUARTO: Recopilación de tenedor y propietario del vehículo
	 // ========================================================
   $cadenaparaconsulta_prevehi="select `propietario`.`TpDoc` AS CODTIPOIDPROPIETARIO
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
				where `vehiculos`.`IdPlaca` = '".$placa."'";

		// el anterior select debe devolver un único registro:
		$matrizdatos_prevehi=mysql_query($cadenaparaconsulta_prevehi,$conexion)
				    or die ("Error: No se pudo ejecutar consulta _prevehi");	 
		$registro_prevehi= mysql_fetch_assoc($matrizdatos_prevehi);
		if($registro_prevehi['CODTIPOIDPROPIETARIO']=='C')
		{
		          $vec_per_ini[$conta_per]=$registro_prevehi['NUMIDPROPIETARIO'];
              $conta_per++;
		}
		elseif($registro_prevehi['CODTIPOIDTENEDOR']=='N')
		{
		          $vec_emp_ini[$conta_emp]=$registro['NUMIDTENEDOR'];       
              $conta_emp++;
		}
		else
		{
		          echo "error indeterminado en if tenedor-propiet. El programa suspenderá";
		          exit;
		}
		
   // ========================================================
   // QUINTO: Recopilación de remesas
	 // ========================================================
	 $vec_remesas_ini=array();
   $conta_remesas=0;
   $cadenaparaconsulta_preremesas="select guias.`Guia` as CONSECUTIVOREMESA
				from `guias` 
				   left join `informacionempresa` on `guias`.`IdEmpresa` = `informacionempresa`.`Id` 
				   left join `ciudades` `ciudestino` on `guias`.`IdCiuDestino` = `ciudestino`.`IdCiudad` 
				   left join `ciudades` `ciuorigen` on `guias`.`IdCiuOrigen` = `ciuorigen`.`IdCiudad` 
				   left join `despachos` on `guias`.`IdDespacho` = `despachos`.`OrdDespacho` 
				   left join terceros as remitente on guias.`Cuenta` = remitente.`IDTercero` 
				where (`guias`.`IdDespacho` = ".$_SESSION['ORDEN_DESPACHO'].")";

		// el anterior select puede devolver varios registros:
		$matrizdatos_preremesas=mysql_query($cadenaparaconsulta_preremesas,$conexion)
				    or die ("Error: No se pudo ejecutar consulta _preremesas");	 
		while($registro_preremesas= mysql_fetch_assoc($matrizdatos_preremesas))
		{
        $vec_remesas_ini[$conta_remesas]=$registro_preremesas['CONSECUTIVOREMESA'];
        $conta_remesas++;
		}	
		
		
   // ========================================================
   // SEXTO: Elimina los elementos null y los elementos duplicados 
	 //        de los vectores per, emp y rem 
	 // ========================================================
   // a) eliminar elementos null
   $vec_per_ini2 = array_values(array_diff($vec_per_ini, array('')));
   $vec_emp_ini2 = array_values(array_diff($vec_emp_ini, array('')));
   $vec_remesas_ini2 = array_values(array_diff($vec_remesas_ini, array('')));
   // b) eliminar elementos duplicados y re-indexar
	 $vec_per2=array_values(array_unique($vec_per_ini2));
	 $vec_emp2=array_values(array_unique($vec_emp_ini2));		
	 $vec_remesas2=array_values(array_unique($vec_remesas_ini2));		

   // ========================================================
   // SÉPTIMO: Los terceros no conductores que ya
	 //          hayan sido enviados al web services no se volverán
	 //          a enviar (serán eliminados del vector)
	 // ========================================================
   // colocar marca para borrado
	 $conta_per2=0;
   while($conta_per2 < count($vec_per2))
   {
      $cadenaparaconsulta_existe_per="SELECT * 
				   FROM ws_autoriz
				   where tipo='ter_per' and numero=".$vec_per2[$conta_per2];
		  $matrizdatos_existe_per=mysql_query($cadenaparaconsulta_existe_per,$conexion)
				    or die ("Error: No se pudo ejecutar consulta _existe_per");	 
		  if(mysql_num_rows($matrizdatos_existe_per)!=0)
		  {
          $vec_per2[$conta_per2]="@@@@@";
		  }
		  $conta_per2++;
   }
   // pasar a un tercer vector los elementos no marcados:
   $vec_per3=array();
	 $conta_per3=0;
	 foreach($vec_per2 as $ced_per2)
	 {
	    if($ced_per2!="@@@@@")
	    {
	       $vec_per3[$conta_per3]=$ced_per2;
	       $conta_per3++;
	    }
	 }
 
   // ========================================================
   // OCTAVO:  La empresas que ya
	 //          hayan sido enviadas al web services no se volverán
	 //          a enviar (serán eliminadas del vector)
	 // ========================================================
   // colocar marca para borrado
	 $conta_emp2=0;
   while($conta_emp2 < count($vec_emp2))
   {
      $cadenaparaconsulta_existe_emp="SELECT * 
				   FROM ws_autoriz
				   where tipo='ter_emp' and numero=".$vec_emp2[$conta_emp2];
		  $matrizdatos_existe_emp=mysql_query($cadenaparaconsulta_existe_emp,$conexion)
				    or die ("Error: No se pudo ejecutar consulta _existe_emp");	 
		  if(mysql_num_rows($matrizdatos_existe_emp)!=0)
		  {
          $vec_emp2[$conta_emp2]="@@@@@";
		  }
		  $conta_emp2++;
   }
   // pasar a un tercer vector los elementos no marcados:
   $vec_emp3=array();
	 $conta_emp3=0;
	 foreach($vec_emp2 as $nit_emp2)
	 {
	    if($nit_emp2!="@@@@@")
	    {
	       $vec_emp3[$conta_emp3]=$nit_emp2;
	       $conta_emp3++;
	    }
	 }
	 
   // ========================================================
   // NOVENO:  Las remesas que ya
	 //          hayan sido enviadas al web services no se volverán
	 //          a enviar (serán eliminadas del vector)
	 // ========================================================
   // colocar marca para borrado
	 $conta_remesas2=0;
   while($conta_remesas2 < count($vec_remesas2))
   {
      $cadenaparaconsulta_existe_remesa="SELECT * 
				   FROM ws_autoriz
				   where tipo='rem_env' and numero=".$vec_remesas2[$conta_remesas2];
		  $matrizdatos_existe_remesa=mysql_query($cadenaparaconsulta_existe_remesa,$conexion)
				    or die ("Error: No se pudo ejecutar consulta _existe_remesa");	 
		  if(mysql_num_rows($matrizdatos_existe_remesa)!=0)
		  {
          $vec_remesas2[$conta_remesas2]="@@@@@";
		  }
		  $conta_remesas2++;
   }
   // pasar a un tercer vector los elementos no marcados:
   $vec_remesas3=array();
	 $conta_remesas3=0;
	 foreach($vec_remesas2 as $una_remesa2)
	 {
	    if($una_remesa2!="@@@@@")
	    {
	       $vec_remesas3[$conta_remesas3]=$una_remesa2;
	       $conta_remesas3++;
	    }
	 }

   // ========================================================
   // 10):  Si el conductor ya fué enviado al webservices, 
	 //          lo marca para no volverlo a enviar en 12_un_conduc...php
	 // ========================================================
   $cadenaparaconsulta_existe_condu="SELECT * 
      FROM ws_autoriz
      where tipo='ter_con' and numero="
			.$ced_conductor." order by id desc limit 0,1";
   // este select solamente devuelve un registro, el mas nuevo de todos				   
	 $matrizdatos_existe_condu=mysql_query($cadenaparaconsulta_existe_condu,$conexion)
	    or die ("Error: No se pudo ejecutar consulta _existe_condu");	 
	 if(mysql_num_rows($matrizdatos_existe_condu)!=0)
	 {
	    $rgto_existe_condu= mysql_fetch_assoc($matrizdatos_existe_condu);
	    // se sabe entonces que el conductor ya está en el web services,
	    // pero hay que averiguar si la fecha de vencimiento de la licencia
	    // del conductor que hay en el web services (y que está grabado
	    // en el campo fec_vto de ws_autoriz) es la misma que está en la
	    // tabla 'conductores' campo 'FhVenceLic'. Si es la misma no hay
	    // que enviar el conductor, pero si no, significa que hace poco 
	    // actualizaron la fecha en la tabla conductores y en el web
	    // services sigue la fecha vieja, por eso hay que enviarlo para 
	    // que el web services no responda 'Fecha de vencimiento vencida':
			$cadenaparaconsulta_fvto_licencia="select `conductores`.`FhVenceLic` AS FECHAVENCIMIENTOLICENCIA
			   from `ciudades` 
			   join `conductores` on `ciudades`.`IdCiudad` = `conductores`.`IdCiudad` 
			   where conductores . IdConductor = ".$ced_conductor;
	    $matrizdatos_fvto_licencia=mysql_query($cadenaparaconsulta_fvto_licencia,$conexion)
	       or die ("Error: No se pudo ejecutar consulta _existe_condu");	 
	    $rgto_fvto_licencia= mysql_fetch_assoc($matrizdatos_fvto_licencia);
      if($rgto_fvto_licencia['FECHAVENCIMIENTOLICENCIA']==$rgto_existe_condu['fec_vto'])
      {
         // las fechas son iguales, no debe enviar el conductor al web services
			   $ced_conductor="@@@@@@";      
      }
	 }

   // ========================================================
   // 11):  Si el vehículo ya fué enviado al webservices, 
	 //          lo marca para no volverlo a enviar en 13_un_veh...php
	 // ========================================================
   $cadenaparaconsulta_existe_vehi="SELECT * 
				   FROM ws_autoriz
				   where tipo='veh' and numero='".$placa."' order by id desc limit 0,1";;
	 $matrizdatos_existe_vehi=mysql_query($cadenaparaconsulta_existe_vehi,$conexion)
	    or die ("Error: No se pudo ejecutar consulta _existe_vehi");	 
	 if(mysql_num_rows($matrizdatos_existe_vehi)!=0)
	 {
	    $rgto_existe_vehi= mysql_fetch_assoc($matrizdatos_existe_vehi);
	    // ver comentario al conductor (inmediatamente arriba de estas lineas) 
			$cadenaparaconsulta_fvto_soat="select `vehiculos`.`VenceSoat` AS FECHAVENCIMIENTOSOAT
          from `vehiculos` 
          where `vehiculos`.`IdPlaca` = '".$placa."'";
	    $matrizdatos_fvto_soat=mysql_query($cadenaparaconsulta_fvto_soat,$conexion)
	       or die ("Error: No se pudo ejecutar consulta _soat");	 
	    $rgto_fvto_soat= mysql_fetch_assoc($matrizdatos_fvto_soat);
      if($rgto_fvto_soat['FECHAVENCIMIENTOSOAT']==$rgto_existe_vehi['fec_vto'])
      {
         // las fechas son iguales, no debe enviar el conductor al web services
         $placa="@@@@@@";
      }
	 }
	 
   // ========================================================
   // 12):  Paso a vbles de session
	 // ========================================================
	 $_SESSION['VEC_PER']=$vec_per3;
	 $_SESSION['VEC_EMP']=$vec_emp3;		
   $_SESSION['VEC_REMESAS']=$vec_remesas3;  
   $_SESSION['VEC_REMESAS_INI']=$vec_remesas_ini;
	 $_SESSION['CONDUCTOR']=$ced_conductor;   
	 $_SESSION['PLACA']=$placa;
	 
				
/*
echo "<br>vec per: ".count($_SESSION['VEC_PER']);
print_r($_SESSION['VEC_PER']);
echo "<br>vec emp: ".$_SESSION['VEC_EMP'].count($_SESSION['VEC_EMP']);		
print_r($_SESSION['VEC_EMP']);
echo "<br>vec remesas: ".count($_SESSION['VEC_REMESAS']);  
print_r($_SESSION['VEC_REMESAS']);
echo "<br>vec conductor: ".$_SESSION['CONDUCTOR'];   
echo "<br>vec placa: ".$_SESSION['PLACA']=$placa;
exit;
*/

    // sigue el recorrer cada vector, el primero será el de personas:
    $_SESSION['CONTADOR_REGISTROS_PER']=0;   		
		echo "<script>location.href='10_recorre_vec_per.php';</script>";
				

?>