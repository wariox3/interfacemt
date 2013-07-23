<?php
prado::using("Application.pages.herramientas.General");
class ProcesarPersonas{    
    public function ProcesarPersonasDespacho ($cliente, $intOrdDespacho) {                
        $strResultado = "";
        $arDespacho = new DespachosRecord();
        $arDespacho = DespachosRecord::finder()->FindByPk($intOrdDespacho);        
        $arVehiculo = new VehiculosRecord();
        $arVehiculo = VehiculosRecord::finder()->findByPk($arDespacho->IdVehiculo);
        
        $strTenedor = $this->GenerarXMLPersona($arVehiculo->IdTenedor);
        //$strTenedor = $this->GenerarXMLPersona(70143086);
        $strResultado = $this->EnviarTercero($cliente, $strTenedor);
        /*if($strResultado == "") {
            $strPropietario = $this->GenerarXMLPersona($arVehiculo->IdPropietario);
            $strResultado = $this->EnviarTercero($cliente, $strPropietario);            
        }*/
        return $strResultado;
    }
    
    private function EnviarTercero ($cliente, $strTercero) {
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
    
    private function GenerarXMLPersona($strCodigoPersona) {
        
        $arTercero = new TercerosRecord();
        $arTercero = TercerosRecord::finder()->with_Ciudad()->FindByPk($strCodigoPersona);
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
                                    <CODTIPOIDTERCERO>C</CODTIPOIDTERCERO>
                                    <NUMIDTERCERO>70143086</NUMIDTERCERO>
                                    <NOMIDTERCERO>MARIO</NOMIDTERCERO>
                                    <PRIMERAPELLIDOIDTERCERO>ESTRADA</PRIMERAPELLIDOIDTERCERO>
                                    <SEGUNDOAPELLIDOIDTERCERO>ZULUAGA</SEGUNDOAPELLIDOIDTERCERO>
                                    <CODSEDETERCERO>0</CODSEDETERCERO>
                                    <NUMTELEFONOCONTACTO>5112441</NUMTELEFONOCONTACTO>                                    
                                    <NOMENCLATURADIRECCION>CRA 58 N 9-40</NOMENCLATURADIRECCION>
                                    <CODMUNICIPIORNDC>5001000</CODMUNICIPIORNDC>       
                                </variables>
                            </root>";             
        }
        /*
         *                                     <NUMNITEMPRESATRANSPORTE>9004861213</NUMNITEMPRESATRANSPORTE>
                                    <CODTIPOIDTERCERO>C</CODTIPOIDTERCERO>
                                    <NUMIDTERCERO>70143086</NUMIDTERCERO>
                                    <NOMIDTERCERO>MARIO</NOMIDTERCERO>
                                    <PRIMERAPELLIDOIDTERCERO>ESTRADA</PRIMERAPELLIDOIDTERCERO>
                                    <SEGUNDOAPELLIDOIDTERCERO>ZULUAGA</SEGUNDOAPELLIDOIDTERCERO>
                                    <CODSEDETERCERO>0</CODSEDETERCERO>
                                    <NUMTELEFONOCONTACTO>5112441</NUMTELEFONOCONTACTO>                                    
                                    <NOMENCLATURADIRECCION>CRA 58 N 9-40</NOMENCLATURADIRECCION>
                                    <CODMUNICIPIORNDC>5001000</CODMUNICIPIORNDC>
         * 
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
*/
        return $strTerceroXML;
    }
}

?>