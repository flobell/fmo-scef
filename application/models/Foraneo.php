<?php
/**
 * Description of FichaForaneo
 *
 * @author fmo16554
 */
class Application_Model_Foraneo {
    
    const SCHEMA = 'sch_scef';
    
    const ID                    = 'id';
    const CEDULA                = 'cedula';
    const FICHA                 = 'ficha';
    const NOMBRE                = 'nombre';
    const APELLIDO              = 'apellido';
    const ID_EMPRESA            = 'id_empresa';
    const EMPRESA               = 'empresa';
    const ESTADO                = 'estado';
    const FECHA_CREACION        = 'fecha_crea';
    const USUARIO_CREACION      = 'usu_crea';
    const FECHA_MODIFICACION    = 'fecha_mod';
    const USUARIO_MODIFICACION  = 'usu_mod';
        
    private $_select = null;
    private $_conn = null;
    private $_filters = array();
    private $_limit = null;
    
     /**
     * Constructor de la clase
     */
    public function __construct()
    {
        // objeto de tabla generica
        $table = new Zend_Db_Table();

        $this->_conn = $table->getAdapter();
        $this->_conn->setFetchMode(Zend_Db::FETCH_OBJ);

        // consulta general
        $this->_select = $this->_conn
        ->select()
        ->distinct()
        ->from(array('a' => 'trabajador_foraneo'),
            array(
                self::ID                    => 'a.id',
                self::CEDULA                => 'a.cedula',
                self::FICHA                 => 'a.ficha',
                self::NOMBRE                => 'a.nombre',
                self::APELLIDO              => 'a.apellido',
                self::ID_EMPRESA            => 'a.id_empresa',
                self::EMPRESA               => 'b.nombre',
                self::ESTADO                => 'a.estado',
                self::FECHA_CREACION        => 'a.fecha_crea',
                self::USUARIO_CREACION      => 'a.usu_crea',
                self::FECHA_MODIFICACION    => 'a.fecha_mod',
                self::USUARIO_MODIFICACION  => 'a.usu_mod',
            ),
            self::SCHEMA)
            ->joinLeft(array('b' => 'empresa'), 'a.id_empresa = b.id', null, self::SCHEMA);
  
                             
    }

    
    
    public static function getAll($onlySelect = false)
    {
        $tblTrabajadorForaneo= new Application_Model_DbTable_TrabajadorForaneo();
        $tblEmpresa = new Application_Model_DbTable_Empresa();
        
        $sql = $tblTrabajadorForaneo->select()
                ->setIntegrityCheck(false)
                ->from(array('a' => $tblTrabajadorForaneo->info(Zend_Db_Table::NAME)),
                       array(
                            'cedula'        => 'a.cedula',
                            'ficha'         => 'a.ficha',
                            'nombre'        => 'a.nombre',
                            'apellido'      => 'a.apellido',
                            'empresa'       => 'b.nombre',
                            'estado'        => 'a.estado',
                            'fecha_crea'    => 'a.fecha_crea',
                            'usu_crea'      => 'a.usu_crea',
                            'fecha_mod'     => 'a.fecha_mod',
                            'usu_mod'       => 'a.usu_mod' 
                        ), 
                        $tblTrabajadorForaneo->info(Zend_Db_Table::SCHEMA)
                )
                ->joinLeft(array('b' => $tblEmpresa->info(Zend_Db_Table::NAME)), 'b.id = a.id_empresa', null, $tblEmpresa->info(Zend_Db_Table::SCHEMA));

        return $onlySelect ? $sql : $tblTrabajadorForaneo->getAdapter()->fetchAll($sql);
    }
    
    
    /**
     * Método que agrega valores de busqueda por el tipo de actividad.
     *
     * @param mixed $campo Indica el campo o filtro de busqueda de la tabla.
     * @param string $igual Indica el operador a utilizar igual 'IN' o diferente 'NOT IN'.
     * @param mixed $valor Valor de la consulta.
     * @return Application_Model_Foraneo
     */
    private function _addFilterBy($campo, $igual, $valor)
    {
        $cond = '';
        $type = null;
        $op = $igual ? 'IN (?)' : 'NOT IN (?)';
        if (is_null($valor)) {
            $op = $igual ? 'IS NULL' : 'IS NOT NULL';
        }
        switch ($campo) {
            case self::ID:
                $cond = "a.id $op";
                break;
            case self::CEDULA:
                $cond = "a.cedula $op";
                $type = Zend_Db::INT_TYPE;
                break;
            case self::FICHA:
                $cond = "a.ficha $op";
                break;
            case self::NOMBRE:
                $cond = "a.nombre $op";
                break;
            case self::APELLIDO:
                $cond = "a.apellido $op";
                break;
            case self::ID_EMPRESA:
                $cond = "a.id_empresa $op";
                $type = Zend_Db::INT_TYPE;
                break;
            case self::ESTADO:
                $cond = "a.estado $op";
                break;
            case self::FECHA_CREACION:
                $cond = "a.fecha_crea $op";
                break;
            case self::USUARIO_CREACION:
                $cond = "i.usu_crea $op";
                break;
            case self::FECHA_MODIFICACION:
                $cond = "a.fecha_mod $op";
                break;
            case self::USUARIO_MODIFICACION:
                $cond = "a.usu_mod $op";
                break;
            default: // self::FILTRO_BUSCAR
                if (!empty($valor)) {
                    if (!is_array($valor)) {
                        $valor = (array) $valor;
                    }
                }
                break;
        }
        if (!empty($cond)) {
            if (!isset($this->_filters[$cond]['type'])) {
                $this->_filters[$cond]['type'] = $type;
            }
            $this->_filters[$cond]['value'][] = $valor === NULL || is_array($valor) ? $valor : (string) $valor;
        }
        return $this;
    }
    
    /**
     * Método para procesar la consulta y devolver la información completa de los trabajadores foraneos.
     *
     * @return array|object
     */
    private function _execute()
    {
        $datos = $this->_conn
                      ->fetchAll($this->getSelect());

        return $datos;
    }
    
    /**
     * Agrega un filtro para las busquedas por Cédula de Identidad.
     *
     * @param mixed $cedula CI N° del trabajar, puede ser un String, Integer o Array.
     * @param boolean $igual Indica que tipo de operador se utilizara; de inclusión (=, IN ó LIKE) o rechazo (<>, NOT IN, NOT LIKE).
     * @return Application_Model_Foraneos
     */
    public function addFilterByCedula($cedula, $igual = true)
    {
        return $this->_addFilterBy(self::CEDULA, $igual, $cedula);
    }
    
    
    /**
     * Agrega un filtro para las busquedas por Ficha
     *
     * @param mixed $ficha del trabajador, puede ser un String, Integer o Array.
     * @param boolean $igual Indica que tipo de operador se utilizara; de inclusión (=, IN ó LIKE) o rechazo (<>, NOT IN, NOT LIKE).
     * @return Application_Model_Foraneos
     */
    public function addFilterByFicha($ficha, $igual = true)
    {
        return $this->_addFilterBy(self::FICHA, $igual, $ficha);
    }

    
        /**
     * Método que devuelve la consulta utilizada para obtener información de sch_scef.
     *
     * @return Zend_Db_Select
     */
    public function getSelect()
    {
        if (!empty($this->_filters)) {
            // filtros
            foreach ($this->_filters as $condition => $data) {
                $this->_select->where($condition, Fmo_Util::arrayMbConvertCase($data['value']), $data['type']);
                unset($this->_filters[$condition]);
            }
        }

        // ordenamiento
        if (!empty($this->_orderBy)) {
            $this->_select->order($this->_orderBy);
            $this->_orderBy = null;
        }

        if (!empty($this->_limit)) {
            $this->_select->limit($this->_limit);
            $this->_limit = null;
        }

        return $this->_select;
    }
    
    /**
     * Devuelve un registro coincidente.
     *
     * @return object|null
     */
    public function findOne()
    {
        $this->_limit = 1;

        $datos = $this->_execute();

        return isset($datos[0]) ? $datos[0] : null;
    }
    
    /**
     * Devuelve todos registro coincidentes.
     *
     * @return array
     */
    public function findAll()
    {
        return $this->_execute();
    }
    
    public static function estaRegistrado($cedula)
    {
        $sql = self::getAll(true)->where('a.cedula = ?', (int)$cedula);
        return !$sql->getAdapter()->fetchRow($sql, array(), Zend_Db::FETCH_OBJ) ? false : true;
    }
    
    public static function esInactivo($cedula)
    {
        $inactivos  = array(0,9);
        $trabajador = addFilterByCedula($cedula)->findOne();
        return in_array($trabajador->estado, $inactivos);
    }

    
}