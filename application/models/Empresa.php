<?php
/**
 * Description of FichaForaneo
 *
 * @author fmo16554
 */
class Application_Model_Empresa {
    
    
    public static function getAll($onlySelect = false)
    {
        $tblEmpresa = new Application_Model_DbTable_Empresa();
        
        $sql = $tblEmpresa->select()
                ->setIntegrityCheck(false)
                ->from(array('a' => $tblEmpresa->info(Zend_Db_Table::NAME)),
                       array(
                            'id'        => 'a.id',
                            'nombre'    => 'a.nombre',
                            'rif'       => 'a.ficha'
                        ), 
                        $tblEmpresa->info(Zend_Db_Table::SCHEMA)
                );
        return $onlySelect ? $sql : $tblEmpresa->getAdapter()->fetchAll($sql);
    }
    
}