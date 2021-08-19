<?php
/**
 * Description of Trabajador Foraneo
 *
 * @author fmo16554
 */
class Application_Model_TrabajadorForaneo
{
    public static function esInactivo($cedula)
    {
        $inactivos  = array(0,9);
        $personal   = new Application_Model_Foraneo();
        $trabajador = $personal->addFilterByCedula($cedula)->findOne();
        return in_array($trabajador->estado, $inactivos);
    }
    
    public static function fichaAsignada($ficha)
    {
        
        $personal   = new Fmo_Model_Personal();
        if($personal->addFilterByFicha($ficha)->findOne()) return TRUE;
        
        $personalForaneo  = new Application_Model_Foraneo();
        if($personalForaneo->addFilterByFicha($ficha)->findOne()) return TRUE;
       
        return FALSE;
    }
    
    /*
    public static function esContratado($cedula)
    {
        $datBas = new Fmo_DbTable_Rpsdatos_DatoBasico();
        $sql = $datBas->select()
                ->setIntegrityCheck(false)
                ->from(array('db' => $datBas->info(Zend_Db_Table::NAME)),
                       array(
                            'cedula'          => 'db.datb_cedula',
                        ), $datBas->info(Zend_Db_Table::SCHEMA))
                ->where("db.datb_cedula = ?", $cedula)
                ->where("db.datb_tpotrab = 6")
                ->where("db.datb_fecincr >= now()");

        $trab = $datBas->getAdapter()->fetchRow($sql);
        return !empty($trab);
    }

    public static function esPasante($cedula)
    {
        $pasantes   = array(7,8);
        $personal   = new Fmo_Model_Personal();
        $trabajador = $personal->addFilterByCedula($cedula)->findOne();
        return in_array($trabajador->id_nomina, $pasantes);
    }

    public static function esJubilado($cedula)
    {
        $jubilados  = array(10,11,12);
        $personal   = new Fmo_Model_Personal();
        $trabajador = $personal->addFilterByCedula($cedula)->findOne();
        return in_array($trabajador->id_nomina, $jubilados);
    }



    public static function esAutorizado($ficha)
    {
        $autorizado = count(Application_Model_DbTable_Autorizado::findOneByColumn('ficha', $ficha));
        return $autorizado > 0;
    }*/
}