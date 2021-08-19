<?php
/**
 * Description of Empresa
 *
 * @author fmo16554
 */
class Application_Model_DbTable_Empresa extends Application_Model_DbTable_Abstract
{
    protected $_name = 'empresa';
    protected $_dependentTables = array('Application_Model_DbTable_TrabajadorForaneo');
    protected $_referenceMap = array();
}