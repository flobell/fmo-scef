<?php
/**
 * Description of FichaTrabajador
 *
 * @author juanfd
 */
class Application_Model_DbTable_FichaTrabajador extends Application_Model_DbTable_Abstract
{
    protected $_name = 'ficha_trabajador';
    protected $_dependentTables = array();
    protected $_referenceMap    = array(
        'Motivo' => array(
            self::COLUMNS => 'motivo',
            self::REF_TABLE_CLASS => 'Application_Model_DbTable_Motivo',
            self::REF_COLUMNS => 'id',
        ),
        'Trabajador' => array(
            self::COLUMNS => 'cedula',
            self::REF_TABLE_CLASS => 'Fmo_DbTable_Rpsdatos_DatoBasico',
            self::REF_COLUMNS => 'datb_cedula',
        ),
        /*
        'CreadoPor' => array(
            self::COLUMNS => 'usu_crea',
            self::REF_TABLE_CLASS => 'Fmo_DbTable_Rpsdatos_DatoBasico',
            self::REF_COLUMNS => 'datb_cedula',
        ),
        'AnuladoPor' => array(
            self::COLUMNS => 'motivo',
            self::REF_TABLE_CLASS => 'Application_Model_DbTable_Motivo',
            self::REF_COLUMNS => 'id',
        ),
        */
    );
}