<?php
/**
 * Auto generated by prado-cli.php on 2013-07-27 11:23:30.
 */
class DespachosControlMTRecord extends TActiveRecord
{
	const TABLE='despachos_control_mt';

	public $OrdDespacho;
	public $ManifiestoInterno;
	public $EnvioPersona;
	public $EnvioVehiculo;
        public $EnvioGuias;
        public $EnvioManifiesto;
        public $ExpedirRemesas;
        public $ExpedirManifiesto;
        public $Enviado;
        public $NoReportado;
        public $Cumplido;
        public $NumeroCumplidoRemesa;
        public $NumeroCumplidoManifiesto;
        public $NoCumplido;
        
        
	public static function finder($className=__CLASS__)
	{
		return parent::finder($className);
	}
}

class DespachosControlMTExtRecord extends DespachosControlMTRecord{
    
    public $FhExpedicion;
}
?>