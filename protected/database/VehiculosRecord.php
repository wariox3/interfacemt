<?php
/**
 * Auto generated by prado-cli.php on 2013-07-23 09:32:28.
 */
class VehiculosRecord extends TActiveRecord
{
	const TABLE='vehiculos';

	public $IdPlaca;
	public $PlacaRemolque;
	public $Modelo;
	public $ModeloRep;
	public $Motor;
	public $NroEjes;
	public $Chasis;
	public $Serie;
	public $PesoVacio;
	public $Capkilos;
	public $CapVol;
	public $Cel;
	public $RegNalCarga;
	public $IdAseguradora;
	public $Soat;
	public $IdAfiliadora;
	public $TOperacion;
	public $IdTenedor;
	public $IdPropietario;
	public $Comentarios;
	public $IdMarca;
	public $IdColor;
	public $IdLinea;
	public $IdCarroceria;
	public $VehConfiguracion;
	public $VenceSoat;
	public $VenceTOperacion;
	public $RevFisicoMec;
	public $Inactivo;
	public $FhIngreso;
	public $ImagenVehiculo;
	public $TipoCombustible;

	public static function finder($className=__CLASS__)
	{
		return parent::finder($className);
	}
}
?>