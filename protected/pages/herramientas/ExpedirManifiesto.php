<?php
prado::using("Application.pages.herramientas.General");

class ExpedirManifiesto {

    public function EnviarManifiesto ($cliente, $intOrdDespacho) {
        $arDespacho = new DespachosRecord();
        $arDespacho = DespachosRecord::finder()->FindByPk($intOrdDespacho);
        $intResultado = 0;
        if(count($arDespacho) > 0){
            $strXmlExpedirManifiesto = $this->GenerarXMLExpedirManifiesto($intOrdDespacho);
            if($strXmlExpedirManifiesto != "") {
                $strXmlExpedirManifiesto = array('' => $strXmlExpedirManifiesto);
                try {
                    $respuesta = $cliente->__soapCall('AtenderMensajeRNDC', $strXmlExpedirManifiesto);
                    $cadena_xml = simplexml_load_string($respuesta);
                    if($cadena_xml->ErrorMSG != "") {
                        if($cadena_xml->ErrorMSG == "Error al solicitar sesiÃ³n para el servicio. PrepareMethod")
                            $intResultado = 3;
                        elseif(substr(strtoupper($cadena_xml->ErrorMSG),0,9) == "DUPLICADO") {
                            $intResultado = 1;
                            General::InsertarErrorWS(2, "Expedir Manifiesto", $intOrdDespacho, utf8_decode($cadena_xml->ErrorMSG));
                        }
                        else {
                            General::InsertarErrorWS(2, "Expedir Manifiesto", $intOrdDespacho, utf8_decode($cadena_xml->ErrorMSG));
                            $intResultado = 0;
                        }
                    }
                    else
                        $intResultado = 0;

                    if($cadena_xml->ingresoid) {
                        $intResultado = 1;
                        General::InsertarErrorWS(2, "Expedir Manifiesto", $intOrdDespacho, utf8_decode($cadena_xml->ingresoid));
                    }
                } catch (Exception $e) {
                    $intResultado = 0;
                    General::InsertarErrorWS(1, "General", "", "Error al enviar parametros manifiesto" . $e);
                }
            }

        }
        return $intResultado;
    }

    public function GenerarXMLExpedirManifiesto($intOrdDespacho) {
        $arConfiguracion = new ConfiguracionRecord();
        $arConfiguracion = ConfiguracionRecord::finder()->findByPk(1);
        $strExpedirManifiestoXML = "";
        if($this->validarExpedirManifiesto($intOrdDespacho) == true) {
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
                                                    <FECHAEXPEDICIONMANIFIESTO>06/08/2013</FECHAEXPEDICIONMANIFIESTO>
                                                    <CODMUNICIPIOORIGENMANIFIESTO>05001000</CODMUNICIPIOORIGENMANIFIESTO>	
                                                    <CODMUNICIPIODESTINOMANIFIESTO>05237000</CODMUNICIPIODESTINOMANIFIESTO>
                                                    <CODIDTITULARMANIFIESTO>N</CODIDTITULARMANIFIESTO>
                                                    <NUMIDTITULARMANIFIESTO>9004861213</NUMIDTITULARMANIFIESTO>
                                                    <NUMPLACA>TNG911</NUMPLACA>
                                                    <CODIDCONDUCTOR>C</CODIDCONDUCTOR>
                                                    <NUMIDCONDUCTOR>71732204</NUMIDCONDUCTOR>
                                                    <VALORFLETEPACTADOVIAJE>0</VALORFLETEPACTADOVIAJE>
                                                    <RETENCIONFUENTEMANIFIESTO>0</RETENCIONFUENTEMANIFIESTO>
                                                    <RETENCIONICAMANIFIESTOCARGA>0</RETENCIONICAMANIFIESTOCARGA>
                                                    <VALORANTICIPOMANIFIESTO>0</VALORANTICIPOMANIFIESTO>
                                                    <FECHAPAGOSALDOMANIFIESTO>16/08/2013</FECHAPAGOSALDOMANIFIESTO>
                                                    <CODRESPONSABLEPAGOCARGUE>E</CODRESPONSABLEPAGOCARGUE>
                                                    <CODRESPONSABLEPAGODESCARGUE>E</CODRESPONSABLEPAGODESCARGUE>
                                                    <OBSERVACIONES>RUTA LECHERA AUXILIAR GUSTAVO SURITA</OBSERVACIONES>
                                                    <CODMUNICIPIOPAGOSALDO>05001000</CODMUNICIPIOPAGOSALDO>
						<REMESASMAN procesoid='43'>";
                                                    foreach ($arGuias as $arGuias) {
                                                        $strExpedirManifiestoXML .= "
                                                        <REMESA>
                                                            <CONSECUTIVOREMESA>" . $arGuias->Guia . "</CONSECUTIVOREMESA>
                                                        </REMESA>";
                                                    }
                                                    $strExpedirManifiestoXML .= "
						</REMESASMAN>
					</variables>
	  		</root>";
                /*$strExpedirManifiestoXML = "<?xml version='1.0' encoding='ISO-8859-1' ?>
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
                                        <FECHAEXPEDICIONMANIFIESTO>$dateFechaExpedicion</FECHAEXPEDICIONMANIFIESTO>
                                        <CODMUNICIPIOORIGENMANIFIESTO>" . $arDespacho->CiudadOrigen->CodMinTrans . "</CODMUNICIPIOORIGENMANIFIESTO>
                                        <CODMUNICIPIODESTMANIFIESTO>" . $arDespacho->CiudadDestino->CodMinTrans . "</CODMUNICIPIODESTMANIFIESTO>
                                        <CODIDTITULARMANIFIESTO>$arTerceroConductor->TpDoc</CODIDTITULARMANIFIESTO>
                                        <NUMIDTITULARMANIFIESTO>$arTerceroConductor->IDTercero</NUMIDTITULARMANIFIESTO>                                        
                                        <CODIDCONDUCTOR>$arTerceroConductor->TpDoc</CODIDCONDUCTOR>
                                        <NUMIDCONDUCTOR>$arTerceroConductor->IDTercero</NUMIDCONDUCTOR>
                                        <VALORFLETEPACTADOVIAJE>" . $arDespacho->VrFlete . "</VALORFLETEPACTADOVIAJE>
                                        <RETENCIONICAMANIFIESTOCARGA>" . $arDespacho->VrDctoRteFte . "</RETENCIONICAMANIFIESTOCARGA>
                                        <VALORANTICIPOMANIFIESTO>" . $arDespacho->VrAnticipo . "</VALORANTICIPOMANIFIESTO>
                                        <FECHAPAGOSALDOMANIFIESTO>$dateFechaPagoSaldo</FECHAPAGOSALDOMANIFIESTO>
                                        <CODRESPONSABLEPAGOCARGUE>E</CODRESPONSABLEPAGOCARGUE>
                                        <CODRESPONSABLEPAGODESCARGUE>E</CODRESPONSABLEPAGODESCARGUE>
                                        <REMESASMAN procesoid='43'>";
                                        foreach ($arGuias as $arGuias) {
                                            $strExpedirManifiestoXML .= "
                                            <REMESA>
                                                <CONSECUTIVOREMESA>" . $arGuias->Guia . "</CONSECUTIVOREMESA>
                                            </REMESA>";
                                        }

                                        $strExpedirManifiestoXML .=
                                       "</REMESASMAN>

                                    </variables>
                                </root>";*/
            }
        }
        return $strExpedirManifiestoXML;
    }

    private function validarExpedirManifiesto ($intOrdDespacho) {
        $intResultado = TRUE;
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