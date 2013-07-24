<?php
prado::using("Application.pages.herramientas.General");
class ProcesarManifiesto{    
    public function ProcesarManifiesto ($cliente, $intOrdDespacho) {                
        $strResultado = "";
        $arDespacho = new DespachosRecord();
        $arDespacho = DespachosRecord::finder()->FindByPk($intOrdDespacho);                
        
        $strManifiesto = $this->GenerarXMLManifiesto($intOrdDespacho);

        //$strResultado = $this->EnviarTercero($cliente, $strTenedor);
        /*if($strResultado == "") {
            $strPropietario = $this->GenerarXMLPersona($arVehiculo->IdPropietario);
            $strResultado = $this->EnviarTercero($cliente, $strPropietario);            
        }*/
        return $strResultado;
    }
    
    private function EnviarManifiesto ($cliente, $strManifiesto) {
        $strResultado = "";
        if($strTercero != ""){  
            $strXmlTercero = $strTercero;
            $strTercero = array('' => $strTercero);        
            $respuesta = "";
            try {
                $respuesta = $cliente->__soapCall('AtenderMensajeRNDC', $strTercero);
            } catch (Exception $e) {            
                $strResultado = "Error al enviar parametros:" . $e;
            }        
            $cadena_xml = simplexml_load_string($respuesta);
            if($strResultado == "") {
                if($cadena_xml->ErrorMSG != "")
                    $strResultado = $strResultado . "ErrorMSG:" . $cadena_xml->ErrorMSG . $strXmlTercero;                                                
            }
        }
        return $strResultado;

    }
    
    private function GenerarXMLManfiesto($intOrdDespacho) {

        $strTerceroXML = "";
        if(count($arTercero) > 0) {
            $strTerceroXML = "<?xml version='1.0' encoding='ISO-8859-1' ?>
                            <root>
                                <acceso>
                                    <username>LOGI@2446LOGICUARTAS</username>
				    <password>Lo15me_A24</password>
                                </acceso>
				<solicitud>
                                    <tipo>1</tipo>
                                    <procesoid>11</procesoid>
				</solicitud>
                                <variables>
                                    <NUMNITEMPRESATRANSPORTE>9004861213</NUMNITEMPRESATRANSPORTE>
                                    <CODTIPOIDTERCERO>". $arTercero->TpDoc ."</CODTIPOIDTERCERO>
                                    <NUMIDTERCERO>" . $arTercero->IDTercero . "</NUMIDTERCERO>
                                    <NOMIDTERCERO>" . utf8_decode($arTercero->Nombre) . "</NOMIDTERCERO>
                                    <PRIMERAPELLIDOIDTERCERO>" . utf8_decode($arTercero->Apellido1) . "</PRIMERAPELLIDOIDTERCERO>
                                    <SEGUNDOAPELLIDOIDTERCERO>" . utf8_decode($arTercero->Apellido2) . "</SEGUNDOAPELLIDOIDTERCERO>
                                    <CODSEDETERCERO>0</CODSEDETERCERO>
                                    <NUMTELEFONOCONTACTO>" . $arTercero->Telefono . "</NUMTELEFONOCONTACTO>
                                    <NUMCELULARPERSONA>" . $arTercero->Celular . "</NUMCELULARPERSONA>
                                    <NOMENCLATURADIRECCION>" . $arTercero->Direccion . "</NOMENCLATURADIRECCION>
                                    <CODMUNICIPIORNDC>" . $arTercero->Ciudad->CodMinTrans . "</CODMUNICIPIORNDC> 
                                </variables>
                            </root>";             
        }
       
        return $strTerceroXML;
    }
}

?>