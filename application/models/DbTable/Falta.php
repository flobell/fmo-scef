<?php

/**
 * Description of Falta
 *
 * @author juanfd
 */
class Application_Model_DbTable_Falta extends Application_Model_DbTable_Abstract
{
    protected $_schema          = 'sch_rpsdatos';
    protected $_name            = 'fgn_sn_tfaltas';
    protected $_primary         = array('falt_cedula', 'falt_tpau', 'falt_fecini', 'falt_fecfin');
    protected $_secuence        = false;
    protected $_dependentTables = array();
    protected $_referenceMap    = array();
}