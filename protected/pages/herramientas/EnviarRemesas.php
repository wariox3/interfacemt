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
                                <CODTIPOEMPAQUE>17</CODTIPOEMPAQUE> 
                                <CODNATURALEZACARGA>1</CODNATURALEZACARGA>
                                <DESCRIPCIONCORTAPRODUCTO>PAQUETES VARIOS</DESCRIPCIONCORTAPRODUCTO> 
                                <MERCANCIAREMESA>009880</MERCANCIAREMESA>
                                <CANTIDADESTIMADA>$arGuia->KilosReales</CANTIDADESTIMADA>
                                <CANTIDADCARGADA>$arGuia->KilosReales</CANTIDADCARGADA>
                                <UNIDADMEDIDACAPACIDAD>1</UNIDADMEDIDACAPACIDAD>                                            
                                <CODTIPOIDPROPIETARIO>" . $arGuia->ClienteRemitente->TpDoc . "</CODTIPOIDPROPIETARIO>
                                <NUMIDPROPIETARIO>" . $arGuia->Cuenta . "</NUMIDPROPIETARIO>
                                <CODSEDEPROPIETARIO>PRINCIPAL</CODSEDEPROPIETARIO>                                 
                                <CODTIPOIDREMITENTE>" . $arGuia->ClienteRemitente->TpDoc . "</CODTIPOIDREMITENTE>
                                <NUMIDREMITENTE>" . $arGuia->Cuenta . "</NUMIDREMITENTE>
                                <CODSEDEREMITENTE>PRINCIPAL</CODSEDEREMITENTE>                                  
                                <CODTIPOIDDESTINATARIO>C</CODTIPOIDDESTINATARIO>
                                <NUMIDDESTINATARIO>22222</NUMIDDESTINATARIO>
                                <CODSEDEDESTINATARIO>PPAL</CODSEDEDESTINATARIO>                                                                               
                                <DUENOPOLIZA>E</DUENOPOLIZA>
                                <NUMPOLIZATRANSPORTE>$arInformacionEmpresa->NroPoliza</NUMPOLIZATRANSPORTE>
                                <FECHAVENCIMIENTOPOLIZACARGA>$dateFechaVencePoliza</FECHAVENCIMIENTOPOLIZACARGA>
                                <COMPANIASEGURO>$arInformacionEmpresa->NitAseguradora</COMPANIASEGURO>                                        
                                
                                <FECHACITAPACTADACARGUE>$dateFechaPactadaCargue</FECHACITAPACTADACARGUE>                                                                        
                                <HORACITAPACTADACARGUE>10:00</HORACITAPACTADACARGUE>                                   
                                <FECHALLEGADACARGUE>$dateFechaPactadaCargue</FECHALLEGADACARGUE>
                                <HORALLEGADACARGUEREMESA>10:10</HORALLEGADACARGUEREMESA> 
                                <FECHAENTRADACARGUE>$dateFechaPactadaCargue</FECHAENTRADACARGUE>
                                <HORAENTRADACARGUEREMESA>10:20</HORAENTRADACARGUEREMESA>
                                <FECHASALIDACARGUE>$dateFechaPactadaCargue</FECHASALIDACARGUE>
                                <HORASALIDACARGUEREMESA>10:30</HORASALIDACARGUEREMESA>

                                <HORASPACTOCARGUE>1</HORASPACTOCARGUE>
                                <MINUTOSPACTOCARGA>0</MINUTOSPACTOCARGA>
                                                                
                                <HORASPACTODESCARGUE>3</HORASPACTODESCARGUE>
                                <MINUTOSPACTODESCARGUE>1</MINUTOSPACTODESCARGUE>
                                
                                <FECHACITAPACTADADESCARGUE>$dateFechaPactadaCargue</FECHACITAPACTADADESCARGUE>
                                <HORACITAPACTADADESCARGUEREMESA>15:00</HORACITAPACTADADESCARGUEREMESA>                                        
                                <OBSERVACIONES>TRANSPORTES VARIOS PAQUETEO</OBSERVACIONES>
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