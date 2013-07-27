<?php
prado::using("Application.pages.herramientas.General");
class ProcesarPersonas {    
    
    public function EnviarTercero ($cliente, $strTercero) {
        $intResultado = 0;
        if($strTercero != ""){
            $strXmlTercero = $this->GenerarXMLPersona($strTercero);  
            if($strXmlTercero != "") {
                $strXmlTercero = array('' => $strXmlTercero);                    
                $respuesta = "";
                try {
                    $respuesta = $cliente->__soapCall('AtenderMensajeRNDC', $strXmlTercero);
                    $intResultado = 1;
                } catch (Exception $e) {
                    $intResultado = 0;
                    General::InsertarErrorWS(1, "General", "Error al enviar parametros" . $e);                
                }                    
                $cadena_xml = simplexml_load_string($respuesta);
                if($intResultado == 1) {
                    if($cadena_xml->ErrorMSG != "") {
                        if($cadena_xml->ErrorMSG == "Error al solicitar sesiÃ³n para el servicio. PrepareMethod") {
                            $intResultado = 3;
                        }
                        elseif(substr(strtoupper($cadena_xml->ErrorMSG),0,9) == "DUPLICADO") {
                            $intResultado = 1;
                            General::InsertarErrorWS(2, "Personas", $strTercero, utf8_decode($cadena_xml->ErrorMSG));              
                        }
                        else {                        
                            $intResultado = 0;
                            General::InsertarErrorWS(2, "Personas", $strTercero, utf8_decode($cadena_xml->ErrorMSG));              
                        }                    
                    }
                    else
                        $intResultado = 0;
                    
                    if($cadena_xml->ingresoid) {                                                
                        General::InsertarErrorWS(2, "Personas", $strTercero, utf8_decode($cadena_xml->ingresoid));
                        $intResultado = 1;
                    }
                    
                }                
            }            
        }
        return $intResultado;
    }
    
    public function GenerarXMLPersona($strCodigoPersona) {
        $strTerceroXML = "";
        if($this->validarPersona($strCodigoPersona) == true) {
            $arTercero = new TercerosRecord();
            $arTercero = TercerosRecord::finder()->with_Ciudad()->FindByPk($strCodigoPersona);            
            if(count($arTercero) > 0) {
                $strTerceroXML = "<?xml version='1.0' encoding='ISO-8859-1' ?>
                                <root>
                                    <acceso>
                                        <username>entregandomed@0841</username>
                                        <password>TKLLUVTPHT</password>
                                    </acceso>
                                    <solicitud>
                                        <tipo>1</tipo>
                                        <procesoid>11</procesoid>
                                    </solicitud>
                                    <variables>
                                        <NUMNITEMPRESATRANSPORTE>8300379211</NUMNITEMPRESATRANSPORTE>
                                        <CODTIPOIDTERCERO>". $arTercero->TpDoc ."</CODTIPOIDTERCERO>
                                        <NUMIDTERCERO>" . $arTercero->IDTercero . "</NUMIDTERCERO>
                                        <NOMIDTERCERO>" . utf8_decode($arTercero->Nombre) . "</NOMIDTERCERO>
                                        <PRIMERAPELLIDOIDTERCERO>" . utf8_decode($arTercero->Apellido1) . "</PRIMERAPELLIDOIDTERCERO>
                                        <SEGUNDOAPELLIDOIDTERCERO>" . utf8_decode($arTercero->Apellido2) . "</SEGUNDOAPELLIDOIDTERCERO>
                                        <CODSEDETERCERO>0</CODSEDETERCERO>";
                                        if($arTercero->Telefono != "") {
                                            $strTerceroXML .= "<NUMTELEFONOCONTACTO>" . $arTercero->Telefono . "</NUMTELEFONOCONTACTO>";
                                        }
                                        if($arTercero->Celular != "") {
                                            $strTerceroXML .= "<NUMCELULARPERSONA>" . $arTercero->Celular . "</NUMCELULARPERSONA>";
                                        }
                                        $strTerceroXML .= "                                        
                                        <NOMENCLATURADIRECCION>" . $arTercero->Direccion . "</NOMENCLATURADIRECCION>
                                        <CODMUNICIPIORNDC>" . $arTercero->Ciudad->CodMinTrans . "</CODMUNICIPIORNDC> 
                                    </variables>
                                </root>";             
            }            
        }       
        return $strTerceroXML;
    }
    
    private function validarPersona ($strCodigoPersona) {
        $intResultado = TRUE;
        $arTercero = new TercerosRecord();
        $arTercero = TercerosRecord::finder()->with_Ciudad()->FindByPk($strCodigoPersona);        
        if($arTercero->Telefono != "") {
            if(strlen($arTercero->Telefono) != 7) {
                $intResultado = FALSE;
                General::InsertarErrorWS(3, "Personas", $strCodigoPersona, "El numero de telefono debe ser de 7 digitos");
            }                
        }
        return $intResultado;
    }
}

?>