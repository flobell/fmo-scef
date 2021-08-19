<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FichaTrabajador
 *
 * @author fmo16554
 */
class Application_Model_DbTable_TrabajadorForaneo extends Application_Model_DbTable_Abstract
{
    protected $_name = 'trabajador_foraneo';
    protected $_dependentTables = array();
        protected $_referenceMap    = array(
        'Empresa' => array(
            self::COLUMNS => 'id_empresa',
            self::REF_TABLE_CLASS => 'Application_Model_DbTable_Empresa',
            self::REF_COLUMNS => 'id',
        )
    );
}