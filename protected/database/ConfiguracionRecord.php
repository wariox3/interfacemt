<?php
/**
 * Auto generated by prado-cli.php on 2013-07-29 08:50:29.
 */
class ConfiguracionRecord extends TActiveRecord
{
	const TABLE='configuracion';

	public $Codigo;
	public $GuiaConsecutivo;
	public $ListaPreciosGeneral;
	public $UsuarioWS;
	public $ClaveWS;
        public $EmpresaWS;
        public $ServidorCorreo;
        public $UsaAutenticacion;
        public $UsaSSL;
        public $Puerto;
        public $FechaAfectada;
        public $HorasAfectacion;
        public $AfectarAntesDe;

	public static function finder($className=__CLASS__)
	{
		return parent::finder($className);
	}
}
?>