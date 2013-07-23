<?php
class General{    
    public function CrearConexion () {                
        $cliente = "";
        try {
            $cliente = new SoapClient("http://72.167.52.87:8080/ws/svr008w.dll/wsdl/IBPMServices");
            return $cliente;
        } catch (Exception $e) {
            return "Error al conectar el servicio: " . $e;            
        }        
    }
}

?>