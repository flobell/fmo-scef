<?php

/**
 * Clase DbTable para administrar la configuración del Sistema
 *
 * @author Rafael Rodríguez <rafaelars@ferrominera.com>
 * @author Juan Durán <juanfd@ferrominera.com>
 */
class Application_Model_DbTable_Configuracion extends Application_Model_DbTable_Abstract
{
    /**
     * Tipo de dato BOOLEAN
     */
    const T_BOOLEAN = 'BOOLEAN';
    
    /**
     * Tipo de dato INTEGER
     */
    const T_INTEGER = 'INTEGER';
    
    /**
     * Tipo de dato NUMERIC
     */
    const T_NUMERIC = 'NUMERIC';
    
    /**
     * Tipo de dato SMALLINT
     */
    const T_SMALLINT = 'SMALLINT';
    
    /**
     * Tipo de dato FLOAT
     */
    const T_FLOAT = 'FLOAT';
    
    /**
     * Tipo de dato DATE
     */
    const T_DATE = 'DATE';
    
    /**
     * Tipo de dato TIMESTAMP WITHOUT TIME ZONE
     */
    const T_TIMESTAMP_WITHOUT_TIME_ZONE = 'TIMESTAMP WITHOUT TIME ZONE';
    
    /**
     * Tipo de dato TIME WITHOUT TIME ZONE
     */
    const T_TIME_WITHOUT_TIME_ZONE = 'TIME WITHOUT TIME ZONE';
    
    /**
     * Tipo de dato INTERVAL
     */
    const T_INTERVAL = 'INTERVAL';
    
    /**
     * Tipo de dato TEXT
     */
    const T_TEXT = 'TEXT';
    
    /**
     * Tipo de dato LIST
     */
    const T_LIST = 'LIST';
    
    /**
     * Tipo de dato ARRAY
     */
    const T_ARRAY = 'ARRAY';

    const CODIGO_AUTORIZADOS = 1;
    const CODIGO_CONTRATADOS = 2;
    const CODIGO_JUBILADOS   = 3;
    const CODIGO_PASANTES    = 4;
    const CODIGO_DIRECCION   = 5;
    const CODIGO_REPOSO      = 6;

    /**
     * Configuración inicial
     */
    public function init() 
    {
        parent::init();
        $this->_name = 'configuracion';
        $this->addReference('Lista', array('id', 'valor'), 'Application_Model_DbTable_ConfiguracionLista', array('id_configuracion', 'id_lista'))
             ->setDependentTables(array('Application_Model_DbTable_ConfiguracionLista',  'Application_Model_DbTable_ImpresoraConfiguracion'));
    }
    
    /**
     * Lista de tipos de datos
     * 
     * @var array
     */
    public static $tiposDatos = array(
        self::T_BOOLEAN => 'Lógico',
        self::T_DATE => 'Fecha',
        self::T_TIME_WITHOUT_TIME_ZONE => 'Hora',
        self::T_TIMESTAMP_WITHOUT_TIME_ZONE => 'Fecha y Hora',
        self::T_INTERVAL => 'Intervalo de Tiempo',
        self::T_INTEGER => 'Entero',
        self::T_SMALLINT => 'Entero Pequeño',
        self::T_FLOAT => 'Real',            
        self::T_NUMERIC => 'Númerico',            
        self::T_TEXT => 'Texto',
        self::T_LIST => 'Lista',
        self::T_ARRAY => 'Vector',
    );

    /**
     * Convierte una cadena de texto al valor que corresponde.
     * 
     * @param string $tipoDato Tipo de dato
     * @param string $valor Valor a convertir
     * @return string|integer|boolean|float|Zend_Date
     */
    private static function convertirTipoDatos($tipoDato, $valor)
    {
        switch ($tipoDato) {
            case self::T_INTEGER:
            case self::T_SMALLINT:
                $valor = Zend_Filter::filterStatic($valor, 'Int');
                break;
            case self::T_BOOLEAN:
                $valor = Zend_Filter::filterStatic($valor, 'Boolean', array('options' => array('type' => Zend_Filter_Boolean::FALSE_STRING)));
                break;
            case self::T_NUMERIC:
                $valor = (double) $valor;
                break;
            case self::T_FLOAT:
                $valor = (float) $valor;
                break;
            case self::T_DATE:
            case self::T_TIMESTAMP_WITHOUT_TIME_ZONE:
                $valor = Fmo_Util::stringToZendDate($valor);
                break;
            case self::T_TIME_WITHOUT_TIME_ZONE:
                $valor = Fmo_Util::stringToZendDate($valor, 'HH:mm');
                break;
            case self::T_ARRAY:
                $valor = Fmo_Util::pgArrayParse($valor);
                break;
            default:
                $valor = (string) $valor;
                break;
        }
        return $valor;
    }

    /**
     * Devuelve el valor del código de la configuración buscado.
     * 
     * @param integer $codigo Código a buscar
     * @return string|integer|boolean|float|Zend_Date
     */
    private static function getValor($codigo)
    {
        $config = self::findOneById($codigo);
        return self::convertirTipoDatos($config->tipo_dato, $config->valor);
    }

    /**
     * Devuelve si está activa la conexión con Nómina
     * 
     * @return boolean
     */
    public static function getValidarAutorizados()
    {
        return self::getValor(self::CODIGO_AUTORIZADOS);
    }
    
    public static function getValidarContratados()
    {
        return self::getValor(self::CODIGO_CONTRATADOS);
    }
    
    public static function getValidarJubilados()
    {
        return self::getValor(self::CODIGO_JUBILADOS);
    }

    public static function getValidarPasantes()
    {
        return self::getValor(self::CODIGO_PASANTES);
    }

    public static function getValidarDireccion()
    {
        return self::getValor(self::CODIGO_DIRECCION);
    }

    public static function getValidarReposo()
    {
        return self::getValor(self::CODIGO_REPOSO);
    }
}