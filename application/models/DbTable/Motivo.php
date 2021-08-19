<?php
/**
 * Description of Motivos
 *
 * @author juanfd
 */
class Application_Model_DbTable_Motivo extends Application_Model_DbTable_Abstract
{
    protected $_name = 'motivo';
    protected $_dependentTables = array('Application_Model_DbTable_FichaTrabajador');
    protected $_referenceMap = array();
}