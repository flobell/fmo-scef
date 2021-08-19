<?php
/**
 * Description of Trabajador
 *
 * @author juanfd
 */
class Application_Model_Falta
{
    public static function estaDeReposo($cedula, $fecha = null)
    {
        $faltas = new Application_Model_DbTable_Falta();
        $reposo = array(1, 2, 3, 37, 64, 65, 66, 67);

        if ($fecha == null) {
            $ahora = new Zend_Date();
            $fecha = $ahora->toString('yyyy-MM-dd');
        }

        $sql = $faltas->select()
                ->setIntegrityCheck(false)
                ->from(array('f' => $faltas->info(Zend_Db_Table::NAME)),
                       array(
                            'cedula' => 'f.falt_cedula',
                        ), $faltas->info(Zend_Db_Table::SCHEMA))
                ->where("f.falt_cedula = ?", $cedula)
                ->where('f.falt_tpau::INTEGER IN (?)',  array_map(function($value) {
                        return $value;
                    }, $reposo))
                ->where("?::DATE BETWEEN TO_DATE(f.falt_fecini, 'yyyy-mm-dd')  AND TO_DATE(f.falt_fecfin, 'yyyy-mm-dd')", $fecha);

        $trab = $faltas->getAdapter()->fetchRow($sql);

        return !empty($trab);
    }
}