<?php

class Despachos extends TPage {
    public function OnInit($param) {
        parent::OnInit($param);
        if (!$this->IsPostBack) {
        }
    }
    
    public function Procesar () {
        echo "Hola";
    }
}

?>