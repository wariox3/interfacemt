<?php
/**
 * Auto generated by prado-cli.php on 2013-07-23 02:36:41.
 */
class CiudadesRecord extends TActiveRecord
{
	const TABLE='ciudades';

	public $IdCiudad;
	public $NmCiudad;
	public $IdDepartamento;
	public $Distancia;
	public $IdZona;
	public $CodMinTrans;

	public static function finder($className=__CLASS__)
	{
		return parent::finder($className);
	}
}
?>