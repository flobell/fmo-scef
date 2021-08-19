<?php
/**
 * Description of FichaTrabajador
 *
 * @author juanfd
 */
class Application_Model_FichaTrabajador
{
    public static function getAll($onlySelect = false)
    {
        $fichaTrab = new Application_Model_DbTable_FichaTrabajador();
        $motivo    = new Application_Model_DbTable_Motivo();
        $datBas    = new Fmo_DbTable_Rpsdatos_DatoBasico();

        $sql = $fichaTrab->select()
                ->setIntegrityCheck(false)
                ->from(array('ft' => $fichaTrab->info(Zend_Db_Table::NAME)),
                       array(
                            'id'              => 'ft.id',
                            'cedula'          => 'ft.cedula',
                            'ficha'           => 'ft.ficha',
                            'nombre'          => 'dbt.datb_nombre',
                            'apellido'        => 'dbt.datb_apellid',
                            'cod_ficha'       => 'ft.cod_ficha',
                            'cod_motivo'      => 'm.id',
                            'motivo'          => 'm.descripcion',
                            'activa'          => 'ft.activo',
                            'fecha_crea'      => 'ft.fecha_crea',
                            'fecha_creaf'     => new Zend_Db_Expr("TO_CHAR(ft.fecha_crea, 'dd/mm/yyyy hh:mi:AM')"),
                            'usu_crea'        => 'ft.usu_crea',
                            'ficha_usu_crea'  => 'dbc.datb_nrotrab',
                            'nombre_usu_crea' => new Zend_Db_Expr("CONCAT_WS(' ', dbc.datb_apellid, dbc.datb_nombre)"),
                            'fecha_mod'       => 'ft.fecha_mod',
                            'fecha_modf'      => new Zend_Db_Expr("TO_CHAR(ft.fecha_mod, 'dd/mm/yyyy hh:mi:AM')"),
                            'usu_mod'         => 'ft.usu_mod',
                            'ficha_usu_mod'   => 'dba.datb_nrotrab',
                            'nombre_usu_mod'  => new Zend_Db_Expr("CONCAT_WS(' ', dba.datb_apellid, dba.datb_nombre)")
                        ), $fichaTrab->info(Zend_Db_Table::SCHEMA))
                ->join(array('m' => $motivo->info(Zend_Db_Table::NAME)), 'ft.motivo = m.id', null, $motivo->info(Zend_Db_Table::SCHEMA))
                ->join(array('dbt' => $datBas->info(Zend_Db_Table::NAME)), 'ft.cedula = dbt.datb_cedula', null, $datBas->info(Zend_Db_Table::SCHEMA))
                ->join(array('dbc' => $datBas->info(Zend_Db_Table::NAME)), 'ft.usu_crea = dbc.datb_cedula', null, $datBas->info(Zend_Db_Table::SCHEMA))
                ->joinLeft(array('dba' => $datBas->info(Zend_Db_Table::NAME)), 'ft.usu_mod = dba.datb_cedula', null, $datBas->info(Zend_Db_Table::SCHEMA));
        return $onlySelect ? $sql : $fichaTrab->getAdapter()->fetchAll($sql);
    }
    
    public static function getByCedula($cedula)
    {
        $sql = self::getAll(true)->where('ft.cedula = ?', (int)$cedula)->order('ft.fecha_crea DESC');
        return $sql->getAdapter()->fetchAll($sql, array(), Zend_Db::FETCH_OBJ);
    }
    
    public static function getById($id)
    {
        $sql = self::getAll(true)->where('ft.id = ?', (int)$id)->order('ft.fecha_crea DESC');
        return $sql->getAdapter()->fetchRow($sql, array(), Zend_Db::FETCH_OBJ);
    }

    public static function fichaActiva($cedula)
    {
        $sql = self::getAll(true)->where('ft.cedula = ?', (int)$cedula)->where('ft.activo = true');
        return !$sql->getAdapter()->fetchRow($sql, array(), Zend_Db::FETCH_OBJ) ? false : true;
    }

    public static function codigoFichaAsignado($codigo, $activa = true)
    {
        $sql = self::getAll(true)
                ->where('ft.cod_ficha = ?', $codigo)
                ->where('ft.activo = ?', $activa);
        $trabajador = $sql->getAdapter()->fetchRow($sql, array(), Zend_Db::FETCH_OBJ);
        return $trabajador ? $trabajador->cedula : false;
    }
    
    
    //----Para Trabajadores Foraneos-----
    public static function getAllForaneo($onlySelect = false)
    {
        $fichaTrab  = new Application_Model_DbTable_FichaTrabajador();
        $motivo     = new Application_Model_DbTable_Motivo();
        $datBas     = new Fmo_DbTable_Rpsdatos_DatoBasico();
        $foraneo    = new Application_Model_DbTable_TrabajadorForaneo();

        $sql = $fichaTrab->select()
                ->setIntegrityCheck(false)
                ->from(array('ft' => $fichaTrab->info(Zend_Db_Table::NAME)),
                       array(
                            'id'              => 'ft.id',
                            'cedula'          => 'ft.cedula',
                            'ficha'           => 'ft.ficha',
                            'nombre'          => 'dbt.nombre',
                            'apellido'        => 'dbt.apellido',
                            'cod_ficha'       => 'ft.cod_ficha',
                            'cod_motivo'      => 'm.id',
                            'motivo'          => 'm.descripcion',
                            'activa'          => 'ft.activo',
                            'fecha_crea'      => 'ft.fecha_crea',
                            'fecha_creaf'     => new Zend_Db_Expr("TO_CHAR(ft.fecha_crea, 'dd/mm/yyyy hh:mi:AM')"),
                            'usu_crea'        => 'ft.usu_crea',
                            'ficha_usu_crea'  => 'dbc.datb_nrotrab',
                            'nombre_usu_crea' => new Zend_Db_Expr("CONCAT_WS(' ', dbc.datb_apellid, dbc.datb_nombre)"),
                            'fecha_mod'       => 'ft.fecha_mod',
                            'fecha_modf'      => new Zend_Db_Expr("TO_CHAR(ft.fecha_mod, 'dd/mm/yyyy hh:mi:AM')"),
                            'usu_mod'         => 'ft.usu_mod',
                            'ficha_usu_mod'   => 'dba.datb_nrotrab',
                            'nombre_usu_mod'  => new Zend_Db_Expr("CONCAT_WS(' ', dba.datb_apellid, dba.datb_nombre)")
                        ), $fichaTrab->info(Zend_Db_Table::SCHEMA))
                ->join(array('m' => $motivo->info(Zend_Db_Table::NAME)), 'ft.motivo = m.id', null, $motivo->info(Zend_Db_Table::SCHEMA))
                ->join(array('dbt' => $foraneo->info(Zend_Db_Table::NAME)), 'ft.cedula = dbt.cedula', null, $foraneo->info(Zend_Db_Table::SCHEMA))
                ->join(array('dbc' => $datBas->info(Zend_Db_Table::NAME)), 'ft.usu_crea = dbc.datb_cedula', null, $datBas->info(Zend_Db_Table::SCHEMA))
                ->joinLeft(array('dba' => $datBas->info(Zend_Db_Table::NAME)), 'ft.usu_mod = dba.datb_cedula', null, $datBas->info(Zend_Db_Table::SCHEMA));
        return $onlySelect ? $sql : $fichaTrab->getAdapter()->fetchAll($sql);
    }
    
    
    public static function getByCedulaForaneo($cedula)
    {
        $sql = self::getAllForaneo(true)->where('ft.cedula = ?', (int)$cedula)->order('ft.fecha_crea DESC');
        return $sql->getAdapter()->fetchAll($sql, array(), Zend_Db::FETCH_OBJ);
    }
    
    public static function fichaActivaForaneo($cedula)
    {
        $sql = self::getAllForaneo(true)->where('ft.cedula = ?', (int)$cedula)->where('ft.activo = true');
        return !$sql->getAdapter()->fetchRow($sql, array(), Zend_Db::FETCH_OBJ) ? false : true;
    }
    
    public static function getByIdForaneo($id)
    {
        $sql = self::getAllForaneo(true)->where('ft.id = ?', (int)$id)->order('ft.fecha_crea DESC');
        return $sql->getAdapter()->fetchRow($sql, array(), Zend_Db::FETCH_OBJ);
    }
    
    public static function codigoFichaAsignadoForaneo($codigo, $activa = true)
    {
        $sql = self::getAllForaneo(true)
                ->where('ft.cod_ficha = ?', $codigo)
                ->where('ft.activo = ?', $activa);
        $trabajador = $sql->getAdapter()->fetchRow($sql, array(), Zend_Db::FETCH_OBJ);
        return $trabajador ? $trabajador->cedula : false;
    }
    
}