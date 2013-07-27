<?php
prado::using("Application.pages.herramientas.General");
class ProcesarManifiesto{        
       
    
    public function GenerarXMLManfiesto($arDespachoParam) {   
        $arDespacho = new DespachosRecord();
        $arDespacho = $arDespachoParam;
        $strDespachoXML = "";
        if(count($arDespacho) > 0) {
            $strDespachoXML = 
"<?xml version='1.0' encoding='ISO-8859-1' ?>
    <root>
        <acceso>
            <username>LOGI@2446LOGICUARTAS</username>
            <password>Lo15me_A24</password>
	</acceso>
	<solicitud>
            <tipo>1</tipo>
            <procesoid>4</procesoid>
	</solicitud>
        <variables>
            <NUMNITEMPRESATRANSPORTE>9004861213</NUMNITEMPRESATRANSPORTE>
            <NUMMANIFIESTOCARGA>1962</NUMMANIFIESTOCARGA>
            <CODOPERACIONTRANSPORTE>P</CODOPERACIONTRANSPORTE>
            <FECHAEXPEDICIONMANIFIESTO>15/06/2013</FECHAEXPEDICIONMANIFIESTO>
            <CODMUNICIPIOORIGENMANIFIESTO>05001000</CODMUNICIPIOORIGENMANIFIESTO>
            <CODMUNICIPIODESTINOMANIFIESTO>05887000</CODMUNICIPIODESTINOMANIFIESTO>
            <CODIDTITULARMANIFIESTO>N</CODIDTITULARMANIFIESTO>
            <NUMIDTITULARMANIFIESTO>9004861213</NUMIDTITULARMANIFIESTO>
            <NUMPLACA>TMY281</NUMPLACA>
            <CODIDCONDUCTOR>C</CODIDCONDUCTOR>
            <NUMIDCONDUCTOR>70516596</NUMIDCONDUCTOR>
            <VALORFLETEPACTADOVIAJE>0</VALORFLETEPACTADOVIAJE>
            <RETENCIONFUENTEMANIFIESTO>0</RETENCIONFUENTEMANIFIESTO>
            <RETENCIONICAMANIFIESTOCARGA>0</RETENCIONICAMANIFIESTOCARGA>
            <VALORANTICIPOMANIFIESTO>0</VALORANTICIPOMANIFIESTO>
            <FECHAPAGOSALDOMANIFIESTO>25/06/2013</FECHAPAGOSALDOMANIFIESTO>
            <CODRESPONSABLEPAGOCARGUE>E</CODRESPONSABLEPAGOCARGUE>
            <CODRESPONSABLEPAGODESCARGUE>E</CODRESPONSABLEPAGODESCARGUE>
            <OBSERVACIONES>COSTA</OBSERVACIONES>
            <CODMUNICIPIOPAGOSALDO>05001000</CODMUNICIPIOPAGOSALDO>
            <REMESASMAN procesoid='43'>
                <REMESA><CONSECUTIVOREMESA>10026623</CONSECUTIVOREMESA></REMESA>
                <REMESA><CONSECUTIVOREMESA>10052094</CONSECUTIVOREMESA></REMESA>
                <REMESA><CONSECUTIVOREMESA>10062277</CONSECUTIVOREMESA></REMESA>
            </REMESASMAN>
        </variables>
    </root>";             
        }
       
        return $strDespachoXML;
    }
}

?>