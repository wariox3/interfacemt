<?php
prado::using("Application.pages.herramientas.ProcesarPersonas");
prado::using("Application.pages.herramientas.General");
class Despachos extends TPage {
    public function OnInit($param) {
        parent::OnInit($param);
        if (!$this->IsPostBack) {
            $arDespachos = new DespachosRecord();
            $arDespachos = $arDespachos->DevDespachosPendientes();
            $this->DGDespachos->DataSource = $arDespachos;
            $this->DGDespachos->DataBind();
        }
    }
    
    public function procesarDespachoUnico($sender, $param) {
        $registro = $param->Item;   
        $intOrdDespacho = $this->DGDespachos->Datakeys[$registro->ItemIndex];                
        $this->procesarDespacho($intOrdDespacho);
    }
    
    public function procesarDespacho($intOrdDespacho) {        
        $strResultados = "";
        $objGeneral = new General();                
        $cliente = $objGeneral->CrearConexion();
        if(!is_object($cliente))
            $this->LblMensaje->text = $cliente;
        else {
            //Procesar personas
            $objProcesarPersonas = new ProcesarPersonas();
            $strResultados = $objProcesarPersonas->ProcesarPersonasDespacho($cliente, $intOrdDespacho);                    
            if($strResultados != "") {
                $this->LblMensaje->text = "Despacho: " . $intOrdDespacho . " " . $strResultados;
            }                                    
        }               
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
    
}

?>