<?php
/**
 * Auto generated by prado-cli.php on 2013-07-23 09:31:59.
 */
class TercerosRecord extends TActiveRecord
{
	const TABLE='terceros';

	public $IDTercero;
	public $TpDoc;
	public $RazonSocial;
	public $Nombre;
	public $Apellido1;
	public $Apellido2;
	public $Direccion;
	public $Telefono;
	public $IdCiudad;
	public $IdCliente;
	public $Email;
	public $Inactivo;
        public $Celular;
        public $Plazo;
        public $DigitoVerificacion;
        public $IdFormaPago;
        public $IdCentroCostos;
        public $IdAsesor;
        public $CondicionComercial;
        public $ActualizadoWebServices;
        public $Ciudad; //Array de tipo (Conceptos)
        public static $RELATIONS = array(
            'Ciudad' => array(self::BELONGS_TO, 'CiudadesRecord', 'IdCiudad'),
        );
        
	public static function finder($className=__CLASS__)
	{
		return parent::finder($className);
	}
}
?>
