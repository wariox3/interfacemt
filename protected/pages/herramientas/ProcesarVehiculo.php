<?php
prado::using("Application.pages.herramientas.General");

class ProcesarVehiculo {    
    
    public function EnviarVehiculo ($cliente, $strPlaca) {
        $arVehiculo = new VehiculosRecord();
        $arVehiculo = VehiculosRecord::finder()->FindByPk($strPlaca);
        
        $intResultado = 0;
        if($arVehiculo->ActualizadoWebServices == 1)
            $intResultado = 1;            
        
        if($strPlaca != "" && $intResultado != 1){
            $strXmlVehiculo = $this->GenerarXMLVehiculo($strPlaca);  
            if($strXmlVehiculo != "") {
                $strXmlVehiculo = array('' => $strXmlVehiculo);                    
                $respuesta = "";
                try {
                    $respuesta = $cliente->__soapCall('AtenderMensajeRNDC', $strXmlVehiculo);
                    $intResultado = 1;
                } catch (Exception $e) {
                    $intResultado = 0;
                    General::InsertarErrorWS(1, "General", "", "Error al enviar parametros" . $e);                
                }                    
                $cadena_xml = simplexml_load_string($respuesta);
                if($intResultado == 1) {
                    if($cadena_xml->ErrorMSG != "") {
                        if($cadena_xml->ErrorMSG == "Error al solicitar sesiÃ³n para el servicio. PrepareMethod") {
                            $intResultado = 3;
                        }
                        elseif(substr(strtoupper($cadena_xml->ErrorMSG),0,9) == "DUPLICADO") {
                            $intResultado = 1;
                            General::InsertarErrorWS(2, "Vehiculos", $strPlaca, utf8_decode($cadena_xml->ErrorMSG));              
                        }
                        else {                        
                            $intResultado = 0;
                            General::InsertarErrorWS(2, "Vehiculos", $strPlaca, utf8_decode($cadena_xml->ErrorMSG));              
                        }                    
                    }
                    else
                        $intResultado = 0;
                    
                    if($cadena_xml->ingresoid) {                                                
                        General::InsertarErrorWS(2, "Vehiculos", $strPlaca, utf8_decode($cadena_xml->ingresoid));
                        $intResultado = 1;
                    }
                    
                }                
            }
            if($intResultado == 1) {
                $arVehiculo->ActualizadoWebServices = 1;
                $arVehiculo->save();
            }
        }
        return $intResultado;
    }
    
    public function GenerarXMLVehiculo($strPlaca) {
        $arConfiguracion = new ConfiguracionRecord();
        $arConfiguracion = ConfiguracionRecord::finder()->findByPk(1);        
        $strVehiculoXML = "";
        if($this->validarVehiculo($strPlaca) == true) {
            $arVehiculo = new VehiculosRecord();
            $arVehiculo = VehiculosRecord::finder()->with_Tenedor()->with_Propietario()->FindByPk($strPlaca);    
            $dateFechaVenceSoat = substr($arVehiculo->VenceSoat, 8, 2) . "/" . substr($arVehiculo->VenceSoat, 5, 2) . "/" . substr($arVehiculo->VenceSoat, 0, 4);            
            if(count($arVehiculo) > 0) {
                $strVehiculoXML = "<?xml version='1.0' encoding='ISO-8859-1' ?>
                                <root>
                                    <acceso>
                                        <username>$arConfiguracion->UsuarioWS</username>
                                        <password>$arConfiguracion->ClaveWS</password>
                                    </acceso>
                                    <solicitud>
                                        <tipo>1</tipo>
                                        <procesoid>12</procesoid>
                                    </solicitud>
                                    <variables>
                                        <NUMNITEMPRESATRANSPORTE>$arConfiguracion->EmpresaWS</NUMNITEMPRESATRANSPORTE>
                                        <NUMPLACA>" . $arVehiculo->IdPlaca  . "</NUMPLACA>
                                        <CODCONFIGURACIONUNIDADCARGA>" . $arVehiculo->VehConfiguracion . "</CODCONFIGURACIONUNIDADCARGA>
                                        <NUMEJES>" . $arVehiculo->NroEjes . "</NUMEJES>
                                        <CODMARCAVEHICULOCARGA>1</CODMARCAVEHICULOCARGA>
                                        <CODLINEAVEHICULOCARGA>373</CODLINEAVEHICULOCARGA>
                                        <ANOFABRICACIONVEHICULOCARGA>" . $arVehiculo->Modelo . "</ANOFABRICACIONVEHICULOCARGA>
                                        <CODTIPOCOMBUSTIBLE>1</CODTIPOCOMBUSTIBLE>
                                        <PESOVEHICULOVACIO>" . $arVehiculo->PesoVacio . "</PESOVEHICULOVACIO>
                                        <CODCOLORVEHICULOCARGA>" . $arVehiculo->IdColor . "</CODCOLORVEHICULOCARGA>
                                        <CODTIPOCARROCERIA>" . $arVehiculo->IdCarroceria . "</CODTIPOCARROCERIA>
                                        <CODTIPOIDPROPIETARIO>" . $arVehiculo->Propietario->TpDoc . "</CODTIPOIDPROPIETARIO>
                                        <NUMIDPROPIETARIO>" . $arVehiculo->Propietario->IDTercero . "</NUMIDPROPIETARIO>
                                        <CODTIPOIDTENEDOR>" . $arVehiculo->Tenedor->TpDoc . "</CODTIPOIDTENEDOR>
                                        <NUMIDTENEDOR>" . $arVehiculo->Tenedor->IDTercero . "</NUMIDTENEDOR> 
                                        <NUMSEGUROSOAT>" . $arVehiculo->Soat . "</NUMSEGUROSOAT> 
                                        <FECHAVENCIMIENTOSOAT>" . $dateFechaVenceSoat . "</FECHAVENCIMIENTOSOAT>
                                        <NUMNITASEGURADORASOAT>" . $arVehiculo->IdAseguradora . $this->calcularDV($arVehiculo->IdAseguradora) . "</NUMNITASEGURADORASOAT>
                                        <CAPACIDADUNIDADCARGA>$arVehiculo->Capkilos</CAPACIDADUNIDADCARGA>
                                        <UNIDADMEDIDACAPACIDAD>1</UNIDADMEDIDACAPACIDAD>
                                    </variables>
                                </root>";             
            }            
        }       
        return $strVehiculoXML;
    }
    
    private function validarVehiculo ($strPlaca) {
        $intResultado = TRUE;
        //$arTercero = new TercerosRecord();
        //$arTercero = TercerosRecord::finder()->with_Ciudad()->FindByPk($strCodigoPersona);        
        /*if($arTercero->Telefono != "") {
            if(strlen($arTercero->Telefono) != 7) {
                $intResultado = FALSE;
                General::InsertarErrorWS(3, "Personas", $strCodigoPersona, "El numero de telefono debe ser de 7 digitos");
            }                
        }*/               
        return $intResultado;
    }
    
    function calcularDV($nit) {
        if (!is_numeric($nit)) {
            return false;
        }

        $arr = array(1 => 3, 4 => 17, 7 => 29, 10 => 43, 13 => 59, 2 => 7, 5 => 19,
            8 => 37, 11 => 47, 14 => 67, 3 => 13, 6 => 23, 9 => 41, 12 => 53, 15 => 71);
        $x = 0;
        $y = 0;
        $z = strlen($nit);
        $dv = '';

        for ($i = 0; $i < $z; $i++) {
            $y = substr($nit, $i, 1);
            $x += ($y * $arr[$z - $i]);
        }

        $y = $x % 11;

        if ($y > 1) {
            $dv = 11 - $y;
            return $dv;
        } else {
            $dv = $y;
            return $dv;
        }
    }    
}

?>