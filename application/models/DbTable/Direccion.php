<?php

/**
 * Description of Dirección
 *
 * @author juanfd
 */
class Application_Model_DbTable_Direccion extends Application_Model_DbTable_Abstract
{
    protected $_schema          = 'sch_sadt';
    protected $_name            = 'direccion';
    protected $_primary         = 'cedula';
    protected $_secuence        = false;
    protected $_dependentTables = array();
    protected $_referenceMap    = array();
}