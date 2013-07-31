<?php
prado::using("Application.pages.herramientas.General");

class ProcesarGuias {    
    
    public function EnviarGuias ($cliente, $intOrdDespacho) {
        // 1 - Registro insertado
        // 3 - Problema de conexion
        $arGuias = new GuiasRecord();
        $arGuias = GuiasRecord::finder()->FindAllBy_IdDespacho_AND_ActualizadoWebServices($intOrdDespacho, 0);
        if(count($arGuias) > 0){
            $intResultado = 0;
            $boolErrorEnGuia = FALSE;
            foreach ($arGuias as $arGuias) {                       
                $strXmlGuia = $this->GenerarXMLGuia($arGuias->Guia);
                if($strXmlGuia != "") {
                    $strXmlGuia = array('' => $strXmlGuia);                                        
                    try {
                        $respuesta = $cliente->__soapCall('AtenderMensajeRNDC', $strXmlGuia);                            
                        $cadena_xml = simplexml_load_string($respuesta);
                        if($cadena_xml->ErrorMSG != "") {
                            if($cadena_xml->ErrorMSG == "Error al solicitar sesiÃ³n para el servicio. PrepareMethod") 
                                $intResultado = 3;                                                                
                            elseif(substr(strtoupper($cadena_xml->ErrorMSG),0,9) == "DUPLICADO") {
                                $this->actualizarGuia($arGuias->Guia);
                                General::InsertarErrorWS(2, "Guias", $arGuias->Guia, utf8_decode($cadena_xml->ErrorMSG));              
                            }                            
                            else {                                                        
                                General::InsertarErrorWS(2, "Guias", $arGuias->Guia, utf8_decode($cadena_xml->ErrorMSG));              
                                $boolErrorEnGuia = TRUE;
                            }                    
                        }
                        else
                            $boolErrorEnGuia = TRUE;

                        if($cadena_xml->ingresoid) {
                            $this->actualizarGuia($arGuias->Guia);
                            General::InsertarErrorWS(2, "Guias", $arGuias->Guia, utf8_decode($cadena_xml->ingresoid));                                
                        }                        
                    } catch (Exception $e) {                            
                        $boolErrorEnGuia = TRUE;
                        General::InsertarErrorWS(1, "General", "", "Error al enviar parametros guias" . $e);                
                    }                                        
                }                                                                                       
            }
            if($boolErrorEnGuia == TRUE)
                $intResultado = 0;
        }                    
        else
            $intResultado = 1;        
        return $intResultado;
    }
    
    public function GenerarXMLGuia($intGuia) {
        $arConfiguracion = new ConfiguracionRecord();
        $arConfiguracion = ConfiguracionRecord::finder()->findByPk(1);        
        $strGuiaXML = "";
        if($this->validarGuia($intGuia) == true) {
            $arGuia = new GuiasRecord();
            $arGuia = GuiasRecord::finder()->with_ClienteRemitente()->FindByPk($intGuia);     
            $dateFechaCargue = substr($arGuia->FhEntradaBodega, 8, 2) . "/" . substr($arGuia->FhEntradaBodega, 5, 2) . "/" . substr($arGuia->FhEntradaBodega, 0, 4);
            if(count($arGuia) > 0) {
                $strGuiaXML = "<?xml version='1.0' encoding='ISO-8859-1' ?>
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
                                        <NUMNITEMPRESATRANSPORTE>" . $arConfiguracion->EmpresaWS . "</NUMNITEMPRESATRANSPORTE>
                                        <CONSECUTIVOINFORMACIONCARGA>" . $arGuia->Guia . "</CONSECUTIVOINFORMACIONCARGA>
                                        <CODOPERACIONTRANSPORTE>P</CODOPERACIONTRANSPORTE>
                                        <CODTIPOEMPAQUE>17</CODTIPOEMPAQUE> 
                                        <CODNATURALEZACARGA>1</CODNATURALEZACARGA>
                                        <DESCRIPCIONCORTAPRODUCTO>VARIOS</DESCRIPCIONCORTAPRODUCTO> 
                                        <MERCANCIAINFORMACIONCARGA>009980</MERCANCIAINFORMACIONCARGA>
                                        <CANTIDADINFORMACIONCARGA>" . $arGuia->Unidades . "</CANTIDADINFORMACIONCARGA>
                                        <UNIDADMEDIDACAPACIDAD>1</UNIDADMEDIDACAPACIDAD>
                                        <CODTIPOIDREMITENTE>" . $arGuia->ClienteRemitente->TpDoc . "</CODTIPOIDREMITENTE>
                                        <NUMIDREMITENTE>" . $arGuia->Cuenta . "</NUMIDREMITENTE>
                                        <CODSEDEREMITENTE>1</CODSEDEREMITENTE>
                                        <PACTOTIEMPOCARGUE>NO</PACTOTIEMPOCARGUE>
                                        <PACTOTIEMPODESCARGUE>NO</PACTOTIEMPODESCARGUE>
                                        <FECHACITAPACTADACARGUE>$dateFechaCargue</FECHACITAPACTADACARGUE>
                                        <HORACITAPACTADACARGUE>12:00</HORACITAPACTADACARGUE>
                                        <FECHACITAPACTADADESCARGUE>$dateFechaCargue</FECHACITAPACTADADESCARGUE>
                                        <HORACITAPACTADADESCARGUEREMESA>15:00</HORACITAPACTADADESCARGUEREMESA>
                                    </variables>
                                </root>";             
            }            
        }       
        return $strGuiaXML;
    }
    
    private function validarGuia ($intGuia) {
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
        $arGuiaAct->ActualizadoWebServices = 1;
        $arGuiaAct->save();        
    }
}

?>