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
            $arVehiculo = VehiculosRecord::finder()->with_Tenedor()->FindByPk($strPlaca);            
            
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
                                        <CODCONFIGURACIONUNIDADCARGA>55</CODCONFIGURACIONUNIDADCARGA>
                                        <CODMARCAVEHICULOCARGA>1</CODMARCAVEHICULOCARGA>
                                        <CODLINEAVEHICULOCARGA>373</CODLINEAVEHICULOCARGA>
                                        <ANOFABRICACIONVEHICULOCARGA>2010</ANOFABRICACIONVEHICULOCARGA>
                                        <CODTIPOCOMBUSTIBLE>1</CODTIPOCOMBUSTIBLE>
                                        <PESOVEHICULOVACIO>8000</PESOVEHICULOVACIO>
                                        <CODCOLORVEHICULOCARGA>9439</CODCOLORVEHICULOCARGA>
                                        <CODTIPOCARROCERIA>0</CODTIPOCARROCERIA>
                                        <CODTIPOIDPROPIETARIO>C</CODTIPOIDPROPIETARIO>
                                        <NUMIDPROPIETARIO>51760125</NUMIDPROPIETARIO>
                                        <CODTIPOIDTENEDOR>C</CODTIPOIDTENEDOR>
                                        <NUMIDTENEDOR>51760125</NUMIDTENEDOR> 
                                        <NUMSEGUROSOAT>AT131811151729</NUMSEGUROSOAT> 
                                        <FECHAVENCIMIENTOSOAT>14/10/2011</FECHAVENCIMIENTOSOAT>
                                        <NUMNITASEGURADORASOAT>8110191907</NUMNITASEGURADORASOAT>
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
}

?>