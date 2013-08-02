<?php
prado::using("Application.pages.herramientas.General");

class ExpedirRemesas {    
    
    public function EnviarExpedirRemesas ($cliente, $intOrdDespacho) {
        // 1 - Registro insertado
        // 3 - Problema de conexion
        $arGuias = new GuiasRecord();
        $arGuias = GuiasRecord::finder()->FindAllBy_IdDespacho_AND_ExpedirRemesaWS($intOrdDespacho, 0);
        if(count($arGuias) > 0){
            $intResultado = 0;
            $boolErrorExpedirRemesa = FALSE;
            foreach ($arGuias as $arGuias) {                       
                $strXmlExpedirRemesa = $this->GenerarXMLExpedirRemesa($arGuias->Guia);
                if($strXmlExpedirRemesa != "") {
                    $strXmlExpedirRemesa = array('' => $strXmlExpedirRemesa);                                        
                    try {
                        $respuesta = $cliente->__soapCall('AtenderMensajeRNDC', $strXmlExpedirRemesa);                            
                        $cadena_xml = simplexml_load_string($respuesta);
                        if($cadena_xml->ErrorMSG != "") {
                            if($cadena_xml->ErrorMSG == "Error al solicitar sesiÃ³n para el servicio. PrepareMethod") 
                                $intResultado = 3;                                                                
                            elseif(substr(strtoupper($cadena_xml->ErrorMSG),0,9) == "DUPLICADO") {
                                $this->actualizarGuia($arGuias->Guia);
                                General::InsertarErrorWS(2, "Expedir remesa", $arGuias->Guia, utf8_decode($cadena_xml->ErrorMSG));              
                            }                            
                            else {                                                        
                                General::InsertarErrorWS(2, "Expedir remesa", $arGuias->Guia, utf8_decode($cadena_xml->ErrorMSG));              
                                $boolErrorExpedirRemesa = TRUE;
                            }                    
                        }
                        else
                            $boolErrorExpedirRemesa = TRUE;

                        if($cadena_xml->ingresoid) {
                            $this->actualizarGuia($arGuias->Guia);
                            General::InsertarErrorWS(2, "Expedir remesa", $arGuias->Guia, utf8_decode($cadena_xml->ingresoid));                                
                        }                        
                    } catch (Exception $e) {                            
                        $boolErrorExpedirRemesa = TRUE;
                        General::InsertarErrorWS(1, "Expedir remesa", "", "Error al enviar parametros guias" . $e);                
                    }                                        
                }                                                                                       
            }
            if($boolErrorExpedirRemesa == TRUE)
                $intResultado = 0;
        }                    
        else
            $intResultado = 1;        
        return $intResultado;
    }
    
    public function GenerarXMLExpedirRemesa($intGuia) {
        $arConfiguracion = new ConfiguracionRecord();
        $arConfiguracion = ConfiguracionRecord::finder()->findByPk(1); 
        $arInformacionEmpresa = new InformacionEmpresaRecord();
        $arInformacionEmpresa = InformacionEmpresaRecord::finder()->findByPk(1);
        $strExpedirRemesaXML = "";
        if($this->validarExpedirRemesa($intGuia) == true) {
            $arGuia = new GuiasRecord();
            $arGuia = GuiasRecord::finder()->with_ClienteRemitente()->FindByPk($intGuia);     
            $dateFechaVencePoliza = substr($arInformacionEmpresa->VencePoliza, 8, 2) . "/" . substr($arInformacionEmpresa->VencePoliza, 5, 2) . "/" . substr($arInformacionEmpresa->VencePoliza, 0, 4);
            $dateFechaCargue = substr($arGuia->FhEntradaBodega, 8, 2) . "/" . substr($arGuia->FhEntradaBodega, 5, 2) . "/" . substr($arGuia->FhEntradaBodega, 0, 4);
            if(count($arGuia) > 0) {
                $strExpedirRemesaXML = "<?xml version='1.0' encoding='ISO-8859-1' ?>
                                <root>
                                    <acceso>
                                        <username>$arConfiguracion->UsuarioWS</username>
                                        <password>$arConfiguracion->ClaveWS</password>
                                    </acceso>
                                    <solicitud>
                                        <tipo>1</tipo>
                                        <procesoid>3</procesoid>
                                    </solicitud>
                                    <variables>
                                        <NUMNITEMPRESATRANSPORTE>$arConfiguracion->EmpresaWS</NUMNITEMPRESATRANSPORTE>
                                        <CONSECUTIVOREMESA>$arGuia->Guia</CONSECUTIVOREMESA>
                                        <CODOPERACIONTRANSPORTE>P</CODOPERACIONTRANSPORTE>
                                        <CODTIPOIDREMITENTE>" . $arGuia->ClienteRemitente->TpDoc . "</CODTIPOIDREMITENTE>
                                        <NUMIDREMITENTE>" . $arGuia->ClienteRemitente->IDTercero . "</NUMIDREMITENTE>
                                        <CODSEDEREMITENTE>1</CODSEDEREMITENTE>
                                        <CONSECUTIVOINFORMACIONCARGA>$arGuia->Guia</CONSECUTIVOINFORMACIONCARGA> 
                                        <CANTIDADCARGADA>$arGuia->Unidades</CANTIDADCARGADA>
                                        <DUENOPOLIZA>E</DUENOPOLIZA>
                                        <NUMPOLIZATRANSPORTE>$arInformacionEmpresa->NroPoliza</NUMPOLIZATRANSPORTE>
                                        <FECHAVENCIMIENTOPOLIZACARGA>$dateFechaVencePoliza</FECHAVENCIMIENTOPOLIZACARGA>
                                        <COMPANIASEGURO>$arInformacionEmpresa->NitAseguradora</COMPANIASEGURO>
                                        <FECHALLEGADACARGUE>$dateFechaCargue</FECHALLEGADACARGUE>
                                        <HORALLEGADACARGUEREMESA>11:00</HORALLEGADACARGUEREMESA>
                                        <FECHAENTRADACARGUE>$dateFechaCargue</FECHAENTRADACARGUE>
                                        <HORAENTRADACARGUEREMESA>11:15</HORAENTRADACARGUEREMESA>
                                        <FECHASALIDACARGUE>$dateFechaCargue</FECHASALIDACARGUE>
                                        <HORASALIDACARGUEREMESA>11:50</HORASALIDACARGUEREMESA>
                                    </variables>
                                </root>";             
            }            
        }       
        return $strExpedirRemesaXML;
    }
    
    private function validarExpedirRemesa ($intGuia) {
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
   
    private function actualizarGuia($intGuia) {
        $arGuiaAct = GuiasRecord::finder()->findByPk($intGuia);
        $arGuiaAct->ExpedirRemesaWS = 1;
        $arGuiaAct->save();        
    }    
}

?>