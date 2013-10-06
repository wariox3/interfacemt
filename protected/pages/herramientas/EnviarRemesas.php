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
                        if(substr(strtoupper($cadena_xml->ErrorMSG),0,9) == "DUPLICADO") {
                            $boolResultadosEnvio = true;                          
                        } elseif(substr($cadena_xml->ErrorMSG, 0, 23 ) == "Error al solicitar sesi") {
                            sleep(4);
                            $this->EnviarGuiaWebServices($intGuia, $arDespacho);
                        }                        
                        else {
                            General::InsertarErrorWS(2, "Remesas", $arGuia->Guia, utf8_decode($cadena_xml->ErrorMSG));                            
                        }
                    }
                    if($cadena_xml->ingresoid) {
                        General::InsertarErrorWS(2, "Remesas", $arGuia->Guia, utf8_decode($cadena_xml->ingresoid));                        
                        $boolResultadosEnvio = true;
                    }                    
                } catch (Exception $e) {
                    if(substr($e, 0, 19 ) == "SoapFault exception") {
                        $this->EnviarGuiaWebServices($intGuia, $arDespacho);
                    } else { 
                        General::InsertarErrorWS(1, "General", "", "Error al enviar parametros" . $e);
                    }                                         
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
        $strExpedirRemesaXML ="<?xml version='1.0' encoding='ISO-8859-1' ?>
                                <root>
                                    <acceso>
                                        <username>entregandomed@0841</username>
                                        <password>TKLLUVTPHT</password>
                                    </acceso>
                                    <solicitud>
                                        <tipo>1</tipo>
                                        <procesoid>3</procesoid>
                                    </solicitud>
                                    <variables>
                                        <NUMNITEMPRESATRANSPORTE>8300379211</NUMNITEMPRESATRANSPORTE>
                                        <CONSECUTIVOREMESA>$arGuia->Guia</CONSECUTIVOREMESA>
                                        <CODOPERACIONTRANSPORTE>P</CODOPERACIONTRANSPORTE>
                                        <CODTIPOEMPAQUE>0</CODTIPOEMPAQUE>
                                        <CODNATURALEZACARGA>1</CODNATURALEZACARGA>                                                  
                                        <DESCRIPCIONCORTAPRODUCTO>PAQUETES VARIOS</DESCRIPCIONCORTAPRODUCTO>
                                        <MERCANCIAREMESA>009880</MERCANCIAREMESA>
                                        <CANTIDADCARGADA>$arGuia->KilosReales</CANTIDADCARGADA>
                                        <UNIDADMEDIDACAPACIDAD>1</UNIDADMEDIDACAPACIDAD>
                                        <CODTIPOIDREMITENTE>" . $arGuia->ClienteRemitente->TpDoc . "</CODTIPOIDREMITENTE>
                                        <NUMIDREMITENTE>$arGuia->Cuenta</NUMIDREMITENTE>
                                        <CODSEDEREMITENTE>1</CODSEDEREMITENTE>
                                        <CODTIPOIDDESTINATARIO>C</CODTIPOIDDESTINATARIO>
                                        <NUMIDDESTINATARIO>22222</NUMIDDESTINATARIO>
                                        <CODSEDEDESTINATARIO>1</CODSEDEDESTINATARIO>
                                        <CODTIPOIDPROPIETARIO>" . $arGuia->ClienteRemitente->TpDoc . "</CODTIPOIDPROPIETARIO>
                                        <NUMIDPROPIETARIO>$arGuia->Cuenta</NUMIDPROPIETARIO>
                                        <CODSEDEPROPIETARIO>1</CODSEDEPROPIETARIO>
                                        <DUENOPOLIZA>E</DUENOPOLIZA>
                                        <NUMPOLIZATRANSPORTE>$arInformacionEmpresa->NroPoliza</NUMPOLIZATRANSPORTE>
                                        <FECHAVENCIMIENTOPOLIZACARGA>$dateFechaVencePoliza</FECHAVENCIMIENTOPOLIZACARGA>
                                        <COMPANIASEGURO>$arInformacionEmpresa->NitAseguradora</COMPANIASEGURO>
                                        <HORASPACTOCARGA>24</HORASPACTOCARGA>
                                        <MINUTOSPACTOCARGA>00</MINUTOSPACTOCARGA>
                                        <FECHACITAPACTADACARGUE>21/08/2013</FECHACITAPACTADACARGUE>
                                        <HORACITAPACTADACARGUE>22:00</HORACITAPACTADACARGUE>
                                        <HORASPACTODESCARGUE>72</HORASPACTODESCARGUE>
                                        <MINUTOSPACTODESCARGUE>00</MINUTOSPACTODESCARGUE>
                                        <FECHACITAPACTADADESCARGUE>25/08/2013</FECHACITAPACTADADESCARGUE>
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