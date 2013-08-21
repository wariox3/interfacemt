<?php
prado::using("Application.pages.herramientas.ProcesarPersonas");
prado::using("Application.pages.herramientas.ProcesarVehiculo");
prado::using("Application.pages.herramientas.ProcesarGuias");
prado::using("Application.pages.herramientas.General");
prado::using("Application.pages.herramientas.ProcesarManifiesto");
prado::using("Application.pages.herramientas.ExpedirRemesas");
prado::using("Application.pages.herramientas.ExpedirManifiesto");

prado::using("Application.pages.herramientas.EnviarTerceros");
prado::using("Application.pages.herramientas.EnviarVehiculo");
prado::using("Application.pages.herramientas.EnviarRemesas");

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
        $criteria->Limit = 20;
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
        $objProcesarPersonas = new ProcesarPersonas();        
        $arDespacho = new DespachosRecord();
        $arDespacho = DespachosRecord::finder()->FindByPk($intOrdDespacho);        
        $arVehiculo = new VehiculosRecord();
        $arVehiculo = VehiculosRecord::finder()->findByPk($arDespacho->IdVehiculo);              
        $intResultados = $objProcesarPersonas->EnviarTercero($cliente, $arVehiculo->IdTenedor);                                
        if($intResultados == 1) {
            $intResultados = $objProcesarPersonas->EnviarTercero($cliente, $arVehiculo->IdPropietario);            
            if($intResultados == 1) {            
                $intResultados = $objProcesarPersonas->EnviarTercero($cliente, $arVehiculo->IdAseguradora);                        
                if($intResultados == 1) {
                    $intResultados = $objProcesarPersonas->EnviarTercero($cliente, $arDespacho->IdConductor);
                    if($intResultados == 1) {
                         $arGuias = new GuiasRecord();
                         $arGuias = $arGuias->DevClientesGuias($intOrdDespacho);
                         foreach ($arGuias as $arGuias) {
                             if($intResultados == 1) {                                         
                                     $intResultados = $objProcesarPersonas->EnviarTercero($cliente, $arGuias->Cuenta);            
                             }                    
                         }
                     }                    
                }                
            }
        }              
        return $intResultados;
    }
    
    public function enviarVehiculoDespacho($cliente, $intOrdDespacho) {
        $intNumeroIntentos = 5;
        $objProcesarVehiculos = new ProcesarVehiculo();        
        $arDespacho = new DespachosRecord();
        $arDespacho = DespachosRecord::finder()->FindByPk($intOrdDespacho);        
        $intResultados = 3;
        $intIntentos = 0;        
        while ($intResultados == 3 && $intIntentos <= $intNumeroIntentos) { 
            $intResultados = $objProcesarVehiculos->EnviarVehiculo($cliente, $arDespacho->IdVehiculo);            
            $intIntentos++;
        }
        if($intResultados == 3) 
            General::InsertarErrorWS(1, "Vehiculos", $arDespacho->IdVehiculo, "Al insertar vehiculo " . $intIntentos. " intentos, error de conexion con el servidor del ministerio");
        
        return $intResultados;
    }
    
    public function enviarGuiasDespacho($cliente, $intOrdDespacho) {
        $objProcesarGuias = new ProcesarGuias();        
        $arDespacho = new DespachosRecord();
        $arDespacho = DespachosRecord::finder()->FindByPk($intOrdDespacho);                
        $intResultados = $objProcesarGuias->EnviarGuias($cliente, $intOrdDespacho);                    
        return $intResultados;
    }    
    
    public function enviarManifiestoDespacho($cliente, $intOrdDespacho) {
        
        $objProcesarManifiesto = new ProcesarManifiesto();        
        $arDespacho = new DespachosRecord();
        $arDespacho = DespachosRecord::finder()->FindByPk($intOrdDespacho);                
        $intResultados = $objProcesarManifiesto->EnviarManifiesto($cliente, $intOrdDespacho);            
        if($intResultados == 3) 
            General::InsertarErrorWS(1, "Manifiesto", $intOrdDespacho, "Al insertar manifiesto error de conexion con el servidor del ministerio");
        
        return $intResultados;
    }    
    
    public function expedirRemesasDespacho($cliente, $intOrdDespacho) {
        $objExpedirRemesas = new ExpedirRemesas();                       
        $intResultados = $objExpedirRemesas->EnviarExpedirRemesas($cliente, $intOrdDespacho);                    
        return $intResultados;
    }        
    
    public function expedirManifiestoDespacho($cliente, $intOrdDespacho) {
        $objExpedirManifiesto = new ExpedirManifiesto();                       
        $intResultados = $objExpedirManifiesto->EnviarManifiesto($cliente, $intOrdDespacho);                    
        return $intResultados;
    }     
    
    public function EnviarDespacho($intOrdDespacho) {
        $objEnviarTerceros = new EnviarTerceros();
        $objEnviarVehiculo = new EnviarVehiculo();
        $objEnviarRemesas = new EnviarRemesas();
        $arDespacho = new DespachosRecord();
        $arDespacho = DespachosRecord::finder()->FindByPk($intOrdDespacho);        
        $arDespachoControMT = new DespachosControlMTRecord();
        $arDespachoControMT = DespachosControlMTRecord::finder()->findByPk($intOrdDespacho);
        if($arDespachoControMT->EnvioPersona == 1) {
            if($arDespachoControMT->EnvioVehiculo == 1) {
                if($objEnviarRemesas->EnviarRemesasManifiesto($intOrdDespacho)) {
                    $arDespachoControMT->EnvioGuias = 1;
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