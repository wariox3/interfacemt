<?php
prado::using("Application.pages.herramientas.ProcesarPersonas");
prado::using("Application.pages.herramientas.ProcesarManifiesto");
prado::using("Application.pages.herramientas.General");
class Despachos extends TPage {
    public function OnInit($param) {
        parent::OnInit($param);
        if (!$this->IsPostBack) {
            $this->cargarDespachos();
            $this->cargarErrores();
        }
    }
    
    public function procesarDespachoUnico($sender, $param) {
        $registro = $param->Item;   
        $intOrdDespacho = $this->DGDespachos->Datakeys[$registro->ItemIndex];                
        $this->procesarDespacho($intOrdDespacho);
    }
    
    public function procesarDespacho($intOrdDespacho) {        
        $intResultados = 3;
        $intNumeroIntentos = 30;
        $objGeneral = new General();                        
        $objProcesarManifiesto = new ProcesarManifiesto;        
        $cliente = $objGeneral->CrearConexion();
        $arDespacho = new DespachosRecord();
        $arDespacho = DespachosRecord::finder()->FindByPk($intOrdDespacho);        
        $arDespachoControMT = new DespachosControlMTRecord();
        $arDespachoControMT = DespachosControlMTRecord::finder()->findByPk($intOrdDespacho);
        $arVehiculo = new VehiculosRecord();
        $arVehiculo = VehiculosRecord::finder()->findByPk($arDespacho->IdVehiculo);
        
        if(!is_object($cliente))
            $this->LblMensaje->text = $cliente;
        else {
            //Procesar personas          
            if($arDespachoControMT->EnvioPersona == 0) {
                $intResultados = $this->enviarPersonasDespacho($cliente, $intOrdDespacho);
                if($intResultados == 1)
                    $arDespachoControMT->EnvioPersona = 1;
            }

            $arDespachoControMT->save();
            
            
            /*$strTenedor = $objProcesarPersonas->GenerarXMLPersona($arVehiculo->IdPropietario);            
            $strResultado = $objProcesarPersonas->EnviarTercero($cliente, $strTenedor);            
            if($strResultados != "") {
                $this->LblMensaje->text = $this->LblMensaje->text .  "Despacho: " . $intOrdDespacho . " " . $strResultados . "<br/>";
            }*/   
            
            //Procesar manifiesto
            //$strManifiesto = $objProcesarManifiesto->GenerarXMLManfiesto($arDespacho);            
            
            
        }
        $this->cargarErrores();
        $this->cargarDespachos();
    }    

    public function procesarDespachoVarios() {
        $NumItems = $this->DGDespachos->ItemCount;
        for ($i = 0; $i < $NumItems; $i++) {
            if ($this->DGDespachos->Items[$i]->ClmSeleccionar->Check->Value === 'on') {
                $intOrdDespacho = $this->DGDespachos->Items[$i]->ClmOrdDespacho->Text;
                $this->procesarDespacho($intOrdDespacho);
            }
        }
    }    
            
    public function cargarErrores() {
        $arErrores = new ErroresWSRecord();
        $criteria = new TActiveRecordCriteria;            
        $criteria->OrdersBy['codigo'] = 'desc';
        $arErrores = ErroresWSRecord::finder()->FindAll($criteria);
        $this->DGErrores->DataSource = $arErrores;
        $this->DGErrores->DataBind();        
    }
    
    public function cargarDespachos() {
        $arDespachos = new DespachosRecord();
        $arDespachos = $arDespachos->DevDespachosPendientes();
        $this->DGDespachos->DataSource = $arDespachos;
        $this->DGDespachos->DataBind();        
    }
    
    public function enviarPersonasDespacho($cliente, $intOrdDespacho) {
        $intNumeroIntentos = 30;
        $objProcesarPersonas = new ProcesarPersonas();        
        //$arDespacho = new DespachosRecord();
        $arDespacho = DespachosRecord::finder()->FindByPk($intOrdDespacho);        
        //$arVehiculo = new VehiculosRecord();
        $arVehiculo = VehiculosRecord::finder()->findByPk($arDespacho->IdVehiculo);
        $intResultados = 3;
        $intIntentos = 0;        
        while ($intResultados == 3 && $intIntentos <= $intNumeroIntentos) { 
            $intResultados = $objProcesarPersonas->EnviarTercero($cliente, $arVehiculo->IdTenedor);            
            $intIntentos++;
        }
        if($intResultados == 3) 
            General::InsertarErrorWS(1, "Personas", $arVehiculo->IdTenedor, "Al insertar tenedor " . $intIntentos. " intentos, error de coneccion con el servidor del ministerio");
        return $intResultados;
    }
}

?>