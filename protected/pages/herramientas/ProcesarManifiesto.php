<?php
prado::using("Application.pages.herramientas.General");

class ProcesarManifiesto {    
    
    public function EnviarManifiesto ($cliente, $intOrdDespacho) {
        $arDespacho = new DespachosRecord();
        $arDespacho = DespachosRecord::finder()->FindByPk($intOrdDespacho);
        $intResultado = 0;
        if(count($arDespacho) > 0){                        
            $strXmlManifiesto = $this->GenerarXMLManifiesto($intOrdDespacho);
            if($strXmlManifiesto != "") {
                $strXmlManifiesto = array('' => $strXmlManifiesto);                                        
                try {
                    $respuesta = $cliente->__soapCall('AtenderMensajeRNDC', $strXmlManifiesto);                            
                    $cadena_xml = simplexml_load_string($respuesta);
                    if($cadena_xml->ErrorMSG != "") {
                        if($cadena_xml->ErrorMSG == "Error al solicitar sesiÃ³n para el servicio. PrepareMethod") 
                            $intResultado = 3;                                                                
                        elseif(substr(strtoupper($cadena_xml->ErrorMSG),0,9) == "DUPLICADO") {
                            $intResultado = 1;
                            General::InsertarErrorWS(2, "Guias", $arGuias->Guia, utf8_decode($cadena_xml->ErrorMSG));              
                        }                            
                        else {                                                        
                            General::InsertarErrorWS(2, "Guias", $arGuias->Guia, utf8_decode($cadena_xml->ErrorMSG));              
                            $intResultado = 0;
                        }                    
                    }
                    else
                        $intResultado = 0;

                    if($cadena_xml->ingresoid) {
                        $intResultado = 1;
                        General::InsertarErrorWS(2, "Guias", $arGuias->Guia, utf8_decode($cadena_xml->ingresoid));                                
                    }                        
                } catch (Exception $e) {                            
                    $intResultado = 0;
                    General::InsertarErrorWS(1, "General", "", "Error al enviar parametros guias" . $e);                
                }                                        
            }                                                                                       

        }                           
        return $intResultado;
    }
    
    public function GenerarXMLManifiesto($intOrdDespacho) {
        $arConfiguracion = new ConfiguracionRecord();
        $arConfiguracion = ConfiguracionRecord::finder()->findByPk(1);        
        $strManifiestoXML = "";
        if($this->validarManifiesto($intOrdDespacho) == true) {
            $arGuia = new GuiasRecord();
            $arGuia = GuiasRecord::finder()->with_ClienteRemitente()->FindByPk($intGuia);     
            $dateFechaCargue = substr($arGuia->FhEntradaBodega, 8, 2) . "/" . substr($arGuia->FhEntradaBodega, 5, 2) . "/" . substr($arGuia->FhEntradaBodega, 0, 4);
            if(count($arGuia) > 0) {
                $strManifiestoXML = "<?xml version='1.0' encoding='ISO-8859-1' ?>
                                <root>
                                    <acceso>
                                        <username>$arConfiguracion->UsuarioWS</username>
                                        <password>$arConfiguracion->ClaveWS</password>
                                    </acceso>
                                    <solicitud>
                                        <tipo>1</tipo>
                                        <procesoid>1</procesoid>
                                    </solicitud>
                                    <variables>
                                        <NUMNITEMPRESATRANSPORTE>$arConfiguracion->EmpresaWS</NUMNITEMPRESATRANSPORTE>
                                        <CONSECUTIVOINFORMACIONVIAJE>0001</CONSECUTIVOINFORMACIONVIAJE>
                                        <CODIDCONDUCTOR>C</CODIDCONDUCTOR>
                                        <NUMIDCONDUCTOR>79616565</NUMIDCONDUCTOR>
                                        <NUMPLACA>WZH111</NUMPLACA>
                                        <NUMPLACAREMOLQUE>R55555</NUMPLACAREMOLQUE>
                                        <CODMUNICIPIOORIGENINFOVIAJE>76001000</CODMUNICIPIOORIGENINFOVIAJE>
                                        <CODMUNICIPIODESTINOINFOVIAJE>11001000</CODMUNICIPIODESTINOINFOVIAJE>
                                        <PREREMESAS procesoid='44'> 
                                        <MANPREREMESA>
                                            <CONSECUTIVOINFORMACIONCARGA>0001</CONSECUTIVOINFORMACIONCARGA>
                                            </MANPREREMESA>
                                            <MANPREREMESA>
                                            < CONSECUTIVOINFORMACIONCARGA >0020</ CONSECUTIVOINFORMACIONCARGA 
                                            </MANPREREMESA>
                                            <MANPREREMESA>
                                            < CONSECUTIVOINFORMACIONCARGA >0035</ CONSECUTIVOINFORMACIONCARGA>
                                            </MANPREREMESA>
                                        </PREREMESAS>
                                        <VALORFLETEPACTADOVIAJE>3200000</VALORFLETEPACTADOVIAJE>
                                    </variables>
                                </root>";             
            }            
        }       
        return $strManifiestoXML;
    }
    
    private function validarManifiesto ($intOrdDespacho) {
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