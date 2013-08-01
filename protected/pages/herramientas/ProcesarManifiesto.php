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
                            General::InsertarErrorWS(2, "Manifiesto", $intOrdDespacho, utf8_decode($cadena_xml->ErrorMSG));              
                        }                            
                        else {                                                        
                            General::InsertarErrorWS(2, "Manifiesto", $intOrdDespacho, utf8_decode($cadena_xml->ErrorMSG));              
                            $intResultado = 0;
                        }                    
                    }
                    else
                        $intResultado = 0;

                    if($cadena_xml->ingresoid) {
                        $intResultado = 1;
                        General::InsertarErrorWS(2, "Manifiesto", $intOrdDespacho, utf8_decode($cadena_xml->ingresoid));                                
                    }                        
                } catch (Exception $e) {                            
                    $intResultado = 0;
                    General::InsertarErrorWS(1, "General", "", "Error al enviar parametros manifiesto" . $e);                
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
            $arDespacho = new DespachosRecord();
            $arDespacho = DespachosRecord::finder()->with_CiudadOrigen()->with_CiudadDestino()->FindByPk($intOrdDespacho); 
            $arTerceroConductor = new TercerosRecord();
            $arTerceroConductor = TercerosRecord::finder()->FindByPk($arDespacho->IdConductor);     
            $arGuias = new GuiasRecord();
            $arGuias = GuiasRecord::finder()->FindAllBy_IdDespacho_AND_ActualizadoWebServices($intOrdDespacho, 1);
            if(count($arDespacho) > 0) {
                $strManifiestoXML = "<?xml version='1.0' encoding='ISO-8859-1' ?>
                                <root>
                                    <acceso>
                                        <username>$arConfiguracion->UsuarioWS</username>
                                        <password>$arConfiguracion->ClaveWS</password>
                                    </acceso>
                                    <solicitud>
                                        <tipo>1</tipo>
                                        <procesoid>2</procesoid>
                                    </solicitud>
                                    <variables>
                                        <NUMNITEMPRESATRANSPORTE>$arConfiguracion->EmpresaWS</NUMNITEMPRESATRANSPORTE>
                                        <CONSECUTIVOINFORMACIONVIAJE>$arDespacho->IdManifiesto</CONSECUTIVOINFORMACIONVIAJE>
                                        <CODIDCONDUCTOR>$arTerceroConductor->TpDoc</CODIDCONDUCTOR>
                                        <NUMIDCONDUCTOR>$arTerceroConductor->IDTercero</NUMIDCONDUCTOR>
                                        <NUMPLACA>$arDespacho->IdVehiculo</NUMPLACA>                                        
                                        <CODMUNICIPIOORIGENINFOVIAJE>" . $arDespacho->CiudadOrigen->CodMinTrans . "</CODMUNICIPIOORIGENINFOVIAJE>
                                        <CODMUNICIPIODESTINOINFOVIAJE>" . $arDespacho->CiudadDestino->CodMinTrans . "</CODMUNICIPIODESTINOINFOVIAJE>
                                        <PREREMESAS procesoid='44'>";
                                        foreach ($arGuias as $arGuias) {
                                            $strManifiestoXML .= " 
                                            <MANPREREMESA>
                                                <CONSECUTIVOINFORMACIONCARGA>" . $arGuias->Guia . "</CONSECUTIVOINFORMACIONCARGA>
                                            </MANPREREMESA>";                                            
                                        }

                                        $strManifiestoXML .= 
                                       "</PREREMESAS>
                                        <VALORFLETEPACTADOVIAJE>" . $arDespacho->VrFlete . "</VALORFLETEPACTADOVIAJE>
                                    </variables>
                                </root>";             
            }            
        }       
        return $strManifiestoXML;
    }
    
    private function validarManifiesto ($intOrdDespacho) {
        $intResultado = TRUE;
        $arDespacho = new DespachosRecord();
        $arDespacho = DespachosRecord::finder()->FindByPk($intOrdDespacho);         
        $arTercero = new TercerosRecord();
        $arTercero = TercerosRecord::finder()->FindByPk($arDespacho->IdConductor);        
        if(count($arTercero) <= 0) {            
            $intResultado = FALSE;
            General::InsertarErrorWS(3, "Manifiestos", $arDespacho->IdConductor, "El conductor debe estar en terceros");            
        }        
        /*if($arTercero->Telefono != "") {
            if(strlen($arTercero->Telefono) != 7) {
                $intResultado = FALSE;
                General::InsertarErrorWS(3, "Personas", $strCodigoPersona, "El numero de telefono debe ser de 7 digitos");
            }                
        } */              
        return $intResultado;
    }    
}

?>