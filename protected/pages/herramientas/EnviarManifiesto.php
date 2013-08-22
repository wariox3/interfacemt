<?php
prado::using("Application.pages.herramientas.General");
class EnviarManifiesto {
    public function EnviarManifiestoLocal($intOrdDespacho) {
        $booResultados = TRUE;
        $arDespacho = new DespachosRecord();
        $arDespacho = DespachosRecord::finder()->FindByPk($intOrdDespacho);        
        if($this->EnviarManifiestoWebServices($intOrdDespacho) == false){
            $booResultados = false;
        }  
        return $booResultados;
    }
    
    public function EnviarManifiestoWebServices($intOrdDespacho){
        $objGeneral = new General();                                       
        $cliente = $objGeneral->CrearConexion();
        $boolResultadosEnvio = False;        
        $arDespacho = new DespachosRecord();
        $arDespacho = DespachosRecord::finder()->FindByPk($intOrdDespacho);
        if($arDespacho->EnviadoMT == 1){
            $boolResultadosEnvio = true;            
        }            
        else {
            if($this->ValidarDatosManifiesto($arDespacho) == true) {
                $strXmlManifiesto = array('' => $this->GenerarXMLManifiesto($intOrdDespacho));
                $respuesta = "";
                try {
                    $respuesta = $cliente->__soapCall('AtenderMensajeRNDC', $strXmlManifiesto);
                    $cadena_xml = simplexml_load_string($respuesta);
                    if($cadena_xml->ErrorMSG != "") {
                        if(substr(strtoupper($cadena_xml->ErrorMSG),0,9) == "DUPLICADO") 
                            $boolResultadosEnvio = TRUE;                                                    
                        else
                            General::InsertarErrorWS(2, "Manifiesto", $arDespacho->OrdDespacho, utf8_decode($cadena_xml->ErrorMSG));                            
                    }
                    if($cadena_xml->ingresoid) {
                        General::InsertarErrorWS(2, "Manifiesto", $arDespacho->OrdDespacho, utf8_decode($cadena_xml->ingresoid));                        
                        $boolResultadosEnvio = true;
                    }                    
                } catch (Exception $e) {
                    General::InsertarErrorWS(1, "General", "", "Error al enviar parametros" . $e);
                }
            }
            else
                $boolResultadosEnvio = false; 
            
            if($boolResultadosEnvio == true) {
                $this->ActualizarManifiesto($intOrdDespacho);
            }            
        }              
        return $boolResultadosEnvio;
    }
    
    public function ValidarDatosManifiesto ($arDespacho) {
        $intResultadoValidacion = TRUE;
        return $intResultadoValidacion;            
    }
    
    public function GenerarXMLManifiesto($intOrdDespacho) {
        $arConfiguracion = new ConfiguracionRecord();
        $arConfiguracion = ConfiguracionRecord::finder()->findByPk(1);
        $strExpedirManifiestoXML = "";
        $arDespacho = new DespachosRecord();
        $arDespacho = DespachosRecord::finder()->with_CiudadOrigen()->with_CiudadDestino()->FindByPk($intOrdDespacho);
        $arTerceroConductor = new TercerosRecord();
        $arTerceroConductor = TercerosRecord::finder()->FindByPk($arDespacho->IdConductor);
        $arGuias = new GuiasRecord();
        $arGuias = GuiasRecord::finder()->FindAllBy_IdDespacho_AND_ExpedirRemesaWS($intOrdDespacho, 1);
        $dateFechaExpedicion = substr($arDespacho->FhExpedicion, 8, 2) . "/" . substr($arDespacho->FhExpedicion, 5, 2) . "/" . substr($arDespacho->FhExpedicion, 0, 4);
        $dateFechaPagoSaldo = substr($arDespacho->FhPagoSaldo, 8, 2) . "/" . substr($arDespacho->FhPagoSaldo, 5, 2) . "/" . substr($arDespacho->FhPagoSaldo, 0, 4);
        if(count($arDespacho) > 0) {

            $strExpedirManifiestoXML = "<?xml version='1.0' encoding='ISO-8859-1' ?>
                                            <root>
                                             <acceso>
                                              <username>$arConfiguracion->UsuarioWS</username>
                                              <password>$arConfiguracion->ClaveWS</password>
                                             </acceso>
                                             <solicitud>
                                              <tipo>1</tipo>
                                              <procesoid>4</procesoid>
                                             </solicitud>
                                             <variables>
                                                <NUMNITEMPRESATRANSPORTE>$arConfiguracion->EmpresaWS</NUMNITEMPRESATRANSPORTE>
                                                <NUMMANIFIESTOCARGA>$arDespacho->IdManifiesto</NUMMANIFIESTOCARGA>
                                                <CODOPERACIONTRANSPORTE>P</CODOPERACIONTRANSPORTE>
                                                <FECHAEXPEDICIONMANIFIESTO>21/08/2013</FECHAEXPEDICIONMANIFIESTO>
                                                <CODMUNICIPIOORIGENMANIFIESTO>05001000</CODMUNICIPIOORIGENMANIFIESTO>	
                                                <CODMUNICIPIODESTINOMANIFIESTO>05237000</CODMUNICIPIODESTINOMANIFIESTO>
                                                <CODIDTITULARMANIFIESTO>C</CODIDTITULARMANIFIESTO>
                                                <NUMIDTITULARMANIFIESTO>70143086</NUMIDTITULARMANIFIESTO>
                                                <NUMPLACA>AAA111</NUMPLACA>
                                                <CODIDCONDUCTOR>C</CODIDCONDUCTOR>
                                                <NUMIDCONDUCTOR>70143086</NUMIDCONDUCTOR>
                                                <VALORFLETEPACTADOVIAJE>100000</VALORFLETEPACTADOVIAJE>
                                                <RETENCIONFUENTEMANIFIESTO>0</RETENCIONFUENTEMANIFIESTO>
                                                <RETENCIONICAMANIFIESTOCARGA>0</RETENCIONICAMANIFIESTOCARGA>
                                                <VALORANTICIPOMANIFIESTO>0</VALORANTICIPOMANIFIESTO>
                                                <FECHAPAGOSALDOMANIFIESTO>25/08/2013</FECHAPAGOSALDOMANIFIESTO>
                                                <CODRESPONSABLEPAGOCARGUE>E</CODRESPONSABLEPAGOCARGUE>
                                                <CODRESPONSABLEPAGODESCARGUE>E</CODRESPONSABLEPAGODESCARGUE>
                                                <OBSERVACIONES>PRUEBA</OBSERVACIONES>
                                                <CODMUNICIPIOPAGOSALDO>05001000</CODMUNICIPIOPAGOSALDO>
						<REMESASMAN procesoid='43'>
							<REMESA>
								<CONSECUTIVOREMESA>1002</CONSECUTIVOREMESA>
							</REMESA>
							<REMESA>
								<CONSECUTIVOREMESA>1003</CONSECUTIVOREMESA>
							</REMESA>
						</REMESASMAN>                                                
                                    </variables>
                    </root>";
                						/*<REMESASMAN procesoid='43'>";
                                                    foreach ($arGuias as $arGuias) {
                                                        $strExpedirManifiestoXML .= "
                                                        <REMESA>
                                                            <CONSECUTIVOREMESA>" . $arGuias->Guia . "</CONSECUTIVOREMESA>
                                                        </REMESA>";
                                                    }
                                                    $strExpedirManifiestoXML .= "
						</REMESASMAN>*/
            }
        
        return $strExpedirManifiestoXML;
    }  
    
    public function ActualizarManifiesto($intOrdDespacho) {
        $arDespacho = new DespachosRecord();
        $arDespacho = DespachosRecord::finder()->FindByPk($intOrdDespacho);
        $arDespacho->EnviadoMT = 1;
        $arDespacho->save();
    }        
}
?>