<?php
/**
 * Auto generated by prado-cli.php on 2013-08-02 06:22:00.
 */
class InformacionEmpresaRecord extends TActiveRecord
{
	const TABLE='informacionempresa';

	public $Id;
	public $Nit;
	public $Nombre;
	public $Direccion;
	public $Telefono;
	public $Logo;
	public $NroPoliza;
	public $NitAseguradora;
	public $VencePoliza;
	public $DireccionTerritorial;
	public $Email;
	public $Aseguradora;
        public $CodRegionalMin;
        public $CodEmpresaMin;
      	public $ResolucionMinTransporte;


	public static function finder($className=__CLASS__)
	{
		return parent::finder($className);
	}
}
?>