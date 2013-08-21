<?php
prado::using("Application.pages.herramientas.General");
class EnviarRemesas {
    public function EnviarRemesasManifiesto($intOrdDespacho) {
        $booResultados = TRUE;
        $arDespacho = new DespachosRecord();
        $arDespacho = DespachosRecord::finder()->FindByPk($intOrdDespacho);        
        $strSql = "SELECT Guia FROM guias where IdDespacho = " . $intOrdDespacho;
        $arGuias = new GuiasRecord();
        $arGuias = GuiasRecord::finder()->FindAllBySql($strSql);
        foreach ($arGuias as $arGuias) {
            if($this->EnviarGuiaWebServices($arGuias->Guia, $arDespacho) == false){
                $booResultados = false;
            }  
        }                              
        return $booResultados;
    }
    
    public function EnviarGuiaWebServices($intGuia, $arDespacho){
        $objGeneral = new General();                                       
        $cliente = $objGeneral->CrearConexion();
        $boolResultadosEnvio = False;                
        $arGuia = new GuiasRecord();
        $arGuia = GuiasRecord::finder()->with_ClienteRemitente()->FindByPk($intGuia);
        if($arGuia->ActualizadoWebServices == 1)
            $boolResultadosEnvio = true;
        else {
            if($this->ValidarDatosGuia($arGuia) == true) {
                $strXmlGuia = array('' => $this->GenerarXMLGuia($arGuia, $arDespacho));
                $respuesta = "";
                try {
                    $respuesta = $cliente->__soapCall('AtenderMensajeRNDC', $strXmlGuia);
                    $cadena_xml = simplexml_load_string($respuesta);
                    if($cadena_xml->ErrorMSG != "") {
                        if(substr(strtoupper($cadena_xml->ErrorMSG),0,9) == "DUPLICADO") 
                            $boolResultadosEnvio = TRUE;                                                    
                        else
                            General::InsertarErrorWS(2, "Remesas", $arGuia->Guia, utf8_decode($cadena_xml->ErrorMSG));                            
                    }
                    if($cadena_xml->ingresoid) {
                        General::InsertarErrorWS(2, "Remesas", $arGuia->Guia, utf8_decode($cadena_xml->ingresoid));                        
                        $boolResultadosEnvio = true;
                    }                    
                } catch (Exception $e) {
                    General::InsertarErrorWS(1, "General", "", "Error al enviar parametros" . $e);
                }
            }
            else
                $boolResultadosEnvio = false; 
            
            if($boolResultadosEnvio == true) {
                $this->ActualizarGuia($intGuia);
            }            
        }            
        return $boolResultadosEnvio;
    }
    
    public function ValidarDatosGuia ($arGuia) {
        $intResultadoValidacion = TRUE;
        return $intResultadoValidacion;            
    }
    
    public function GenerarXMLGuia($arGuia, $arDespacho) {
        $arConfiguracion = new ConfiguracionRecord();
        $arConfiguracion = ConfiguracionRecord::finder()->findByPk(1); 
        $arInformacionEmpresa = new InformacionEmpresaRecord();
        $arInformacionEmpresa = InformacionEmpresaRecord::finder()->findByPk(1);
        $strExpedirRemesaXML = "";
        $dateFechaVencePoliza = substr($arInformacionEmpresa->VencePoliza, 8, 2) . "/" . substr($arInformacionEmpresa->VencePoliza, 5, 2) . "/" . substr($arInformacionEmpresa->VencePoliza, 0, 4);
        $dateFechaCargue = substr($arGuia->FhEntradaBodega, 8, 2) . "/" . substr($arGuia->FhEntradaBodega, 5, 2) . "/" . substr($arGuia->FhEntradaBodega, 0, 4);
        $dateFechaPactadaCargue = substr($arDespacho->FhExpedicion, 8, 2) . "/" . substr($arDespacho->FhExpedicion, 5, 2) . "/" . substr($arDespacho->FhExpedicion, 0, 4);
        $dateFechaPactadaDescargueCargue = substr($arDespacho->FhPagoSaldo, 8, 2) . "/" . substr($arDespacho->FhPagoSaldo, 5, 2) . "/" . substr($arDespacho->FhPagoSaldo, 0, 4);

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
                                <CONSECUTIVOREMESA>" . $arGuia->Guia . "</CONSECUTIVOREMESA>
                                <CODOPERACIONTRANSPORTE>P</CODOPERACIONTRANSPORTE>      
                                <CODTIPOEMPAQUE>0</CODTIPOEMPAQUE> 
                                <CODNATURALEZACARGA>1</CODNATURALEZACARGA>
                                <DESCRIPCIONCORTAPRODUCTO>PAQUETES VARIOS</DESCRIPCIONCORTAPRODUCTO> 
                                <MERCANCIAREMESA>009980</MERCANCIAREMESA>
                                <CANTIDADCARGADA>$arGuia->Unidades</CANTIDADCARGADA>
                                <UNIDADMEDIDACAPACIDAD>1</UNIDADMEDIDACAPACIDAD>                                            
                                <CODTIPOIDREMITENTE>" . $arGuia->ClienteRemitente->TpDoc . "</CODTIPOIDREMITENTE>
                                <NUMIDREMITENTE>" . $arGuia->Cuenta . "</NUMIDREMITENTE>
                                <CODSEDEREMITENTE>PPAL</CODSEDEREMITENTE>                                        
                                <CODTIPOIDDESTINATARIO>C</CODTIPOIDDESTINATARIO>
                                <NUMIDDESTINATARIO>22222</NUMIDDESTINATARIO>
                                <CODSEDEDESTINATARIO>PPAL</CODSEDEDESTINATARIO>                                        
                                <CODTIPOIDPROPIETARIO>" . $arGuia->ClienteRemitente->TpDoc . "</CODTIPOIDPROPIETARIO>
                                <NUMIDPROPIETARIO>" . $arGuia->Cuenta . "</NUMIDPROPIETARIO>
                                <CODSEDEPROPIETARIO>PPAL</CODSEDEPROPIETARIO>                                        
                                <DUENOPOLIZA>E</DUENOPOLIZA>
                                <NUMPOLIZATRANSPORTE>$arInformacionEmpresa->NroPoliza</NUMPOLIZATRANSPORTE>
                                <FECHAVENCIMIENTOPOLIZACARGA>$dateFechaVencePoliza</FECHAVENCIMIENTOPOLIZACARGA>
                                <COMPANIASEGURO>$arInformacionEmpresa->NitAseguradora</COMPANIASEGURO>                                        
                                <HORASPACTOCARGUE>24</HORASPACTOCARGUE>
                                <MINUTOSPACTOCARGA>00</MINUTOSPACTOCARGA>
                                <FECHACITAPACTADACARGUE>$dateFechaPactadaCargue</FECHACITAPACTADACARGUE>                                        
                                <HORACITAPACTADACARGUE>22:00</HORACITAPACTADACARGUE>   
                                <HORASPACTODESCARGUE>72</HORASPACTODESCARGUE>
                                <MINUTOSPACTODESCARGUE>00</MINUTOSPACTODESCARGUE>
                                <FECHACITAPACTADADESCARGUE>$dateFechaPactadaDescargueCargue</FECHACITAPACTADADESCARGUE>
                                <HORACITAPACTADADESCARGUEREMESA>08:00</HORACITAPACTADADESCARGUEREMESA>                                        
                            </variables>
                        </root>";               
            
        return $strExpedirRemesaXML;
    }  
    
    public function ActualizarGuia($intGuia) {
        $arGuia = new GuiasRecord();
        $arGuia = GuiasRecord::finder()->FindByPk($intGuia);
        $arGuia->ActualizadoWebServices = 1;
        $arGuia->save();
    }
}
?>