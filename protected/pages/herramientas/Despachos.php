<?php
prado::using("Application.pages.herramientas.EnviarTerceros");
prado::using("Application.pages.herramientas.EnviarVehiculo");
prado::using("Application.pages.herramientas.EnviarRemesas");
prado::using("Application.pages.herramientas.EnviarManifiesto");

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
        $this->EnviarDespacho($intOrdDespacho);
    }
    
    public function procesarDespacho($intOrdDespacho) {        
        $intResultados = 3;
        $intNumeroIntentos = 30;
        $objGeneral = new General();                                       
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
            else
                $intResultados = 1;
            
            if($intResultados == 1) {
                //Procesar vehiculo          
                if($arDespachoControMT->EnvioVehiculo == 0) {
                    $intResultados = $this->enviarVehiculoDespacho($cliente, $intOrdDespacho);
                    if($intResultados == 1)
                        $arDespachoControMT->EnvioVehiculo = 1;
                }                
            }
            
            /*if($intResultados == 1) {
                //Procesar Guias          
                if($arDespachoControMT->EnvioGuias == 0) {
                    $intResultados = $this->enviarGuiasDespacho($cliente, $intOrdDespacho);
                    if($intResultados == 1)
                        $arDespachoControMT->EnvioGuias = 1;
                }                
            }*/            
            
            /*if($intResultados == 1) {     
                if($arDespachoControMT->EnvioManifiesto == 0) {
                    $intResultados = $this->enviarManifiestoDespacho($cliente, $intOrdDespacho);
                    if($intResultados == 1)
                        $arDespachoControMT->EnvioManifiesto = 1;
                }                
            }*/             

            if($intResultados == 1) {         
                if($arDespachoControMT->ExpedirRemesas == 0) {
                    $intResultados = $this->expedirRemesasDespacho($cliente, $intOrdDespacho);
                    if($intResultados == 1)
                        $arDespachoControMT->ExpedirRemesas = 1;
                }                
            }            

            if($intResultados == 1) {         
                if($arDespachoControMT->ExpedirManifiesto == 0) {
                    $intResultados = $this->expedirManifiestoDespacho($cliente, $intOrdDespacho);
                    if($intResultados == 1)
                        $arDespachoControMT->ExpedirManifiesto = 1;
                }                
            }            
            
            $arDespachoControMT->save();                      
            
        }
        $this->cargarErrores();
        $this->cargarDespachos();
    }        
            
    public function cargarErrores() {
        $arErrores = new ErroresWSRecord();
        $criteria = new TActiveRecordCriteria;            
        $criteria->OrdersBy['codigo'] = 'desc';
        $criteria->Limit = 20;
        $arErrores = ErroresWSRecord::finder()->FindAll($criteria);
        $this->DGErrores->DataSource = $arErrores;
        $this->DGErrores->DataBind();        
    }
    
    public function cargarDespachos() {
        $arDespachos = new DespachosControlMTRecord();
        $arDespachos = DespachosControlMTRecord::finder()->FindAllBy_EnvioManifiesto(0);
        $this->DGDespachos->DataSource = $arDespachos;
        $this->DGDespachos->DataBind();        
    }    
    
    public function EnviarDespacho($intOrdDespacho) {
        $objEnviarTerceros = new EnviarTerceros();
        $objEnviarVehiculo = new EnviarVehiculo();
        $objEnviarRemesas = new EnviarRemesas();
        $objEnviarManifiesto = new EnviarManifiesto();
        
        $arDespacho = new DespachosRecord();
        $arDespacho = DespachosRecord::finder()->FindByPk($intOrdDespacho);        
        $arDespachoControMT = new DespachosControlMTRecord();
        $arDespachoControMT = DespachosControlMTRecord::finder()->findByPk($intOrdDespacho);
        if($arDespachoControMT->EnvioPersona == 1) {
            if($arDespachoControMT->EnvioVehiculo == 1) {
                if($arDespachoControMT->EnvioGuias == 1) {
                    if($objEnviarManifiesto->EnviarManifiestoLocal($intOrdDespacho) == TRUE) {
                        $arDespachoControMT->EnvioManifiesto = 1;
                    }
                }
                else {
                    if($objEnviarRemesas->EnviarRemesasManifiesto($intOrdDespacho) == TRUE) {
                        $arDespachoControMT->EnvioGuias = 1;
                    }                    
                }
            }
            else {
                if($objEnviarVehiculo->EnviarVehiculoManifiesto($intOrdDespacho) == TRUE) {
                    $arDespachoControMT->EnvioVehiculo = 1;
                }                   
            }                           
        }
        else {
            if($objEnviarTerceros->EnviarTercerosManifiesto($intOrdDespacho) == TRUE) {
                $arDespachoControMT->EnvioPersona = 1;
            }                
        }
        $arDespachoControMT->save();                                                     
        $this->cargarErrores();
        $this->cargarDespachos();
    }
}

?>