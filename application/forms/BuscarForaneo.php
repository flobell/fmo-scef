<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Solicitar Ficha Foranea
 *
 * @author fmo16554;
 */
class Application_Form_BuscarForaneo extends Fmo_Form_Abstract
{
    /*Buscar*/
    const E_CEDULA     = 'txtCedula';
    const E_BUSCAR     = 'btnBuscar';
    const E_NUEVABUS   = 'btnNuevaBusqueda';
    
    /*Emitir*/
    const E_CODTARJETA = 'txtCodTarjeta';
    const E_EMITIR     = 'btnEmitir';

    
    private $_cedula     = null;
    private $_trabajador = null;

    public function __construct($cedula = '', $options = null) {
        $this->_cedula = $cedula;
        parent::__construct($options);
    }

    public function init() {
        $this->setName('Buscar Foraneo')
            ->setAction($this->getView()->url())
            ->setLegend('Buscar Trabajador Foraneo');

        $tblFichaForaneos = new Application_Model_DbTable_TrabajadorForaneo();

        $cedula = new Zend_Form_Element_Text(self::E_CEDULA);
        $cedula->setLabel('Cedula:')
            ->setAttrib('size', '10')
            ->setAttrib('style', 'width: 100%;')
            ->setAttrib('class', 'form-control text-center')
            ->setAttrib('maxlength', '10')
            ->addValidator('StringLength', false, array('min' => 1, 'max' => 10, 'encoding' => $this->getView()->getEncoding()))
            ->addValidator('Digits', false)
            ->setRequired();
        $this->addElement($cedula);

        $codTarjeta = new Zend_Form_Element_Text(self::E_CODTARJETA);
        $codTarjeta->setLabel('Código Tarjeta:')
            ->setAttrib('size', '20')
            ->setAttrib('style', 'width: 50%;')
            ->setAttrib('class', 'form-control text-center')
            ->setAttrib('maxlength', '20');
        $this->addElement($codTarjeta);

        $eleB = new Zend_Form_Element_Button(self::E_BUSCAR);
        $eleB->setLabel('Buscar')
            ->setAttrib('class', 'btn btn-success')
            ->setAttrib('type', 'submit')
            ->setAttrib('style', 'margin-top:10px !important;')
            ->setValue(self::E_BUSCAR);
        $this->addElement($eleB);

        $eleC = new Zend_Form_Element_Button(self::E_NUEVABUS);
        $eleC->setLabel('Nueva Búsqueda')
            ->setAttrib('class', 'btn btn-primary')
            ->setAttrib('type', 'submit')
            ->setAttrib('style', 'margin-top:10px !important;')
            ->setValue(self::E_NUEVABUS);
        $this->addElement($eleC);

        $eleE = new Zend_Form_Element_Button(self::E_EMITIR);
        $eleE->setLabel('Emitir')
                ->setAttrib('class', 'btn btn-success')
                ->setAttrib('type', 'submit')
                ->setAttrib('style', 'margin-top:10px !important;')
                ->setValue(self::E_EMITIR);
        $this->addElement($eleE);

        $foraneos  = new Application_Model_Foraneo();
        $trabajador = $foraneos->addFilterByCedula($this->_cedula)->findOne();
        $this->_trabajador = $trabajador;

        // Aplicando css de la empresa 
        $this->setCustomDecorators();
    }

    public function getCedula() {
        return $this->_cedula;
    }

    public function getTrabajador() {
        return $this->_trabajador;
    }

    public function emitirFicha($params)
    {
        $tblFichaTrabajador = new Application_Model_DbTable_FichaTrabajador();
        $tblFichaTrabajador->getDefaultAdapter()->beginTransaction();
        
        $usuario = Fmo_Model_Seguridad::getUsuarioSesion()->{Fmo_Model_Personal::CEDULA};

        try {
            $cedula = $this->_trabajador->cedula;

            if (!Application_Model_FichaTrabajador::fichaActiva($cedula)) {
                $hist   = Application_Model_DbTable_FichaTrabajador::findAllByColumn('cedula', $cedula);
                $motivo = (count($hist) > 0) ? 2 : 1;

                $row            = $tblFichaTrabajador->createRow();
                $row->cedula    = $cedula;
                $row->ficha     = $this->_trabajador->ficha;
                $row->cod_ficha = $params[self::E_CODTARJETA];
                $row->motivo    = $motivo;
                $row->usu_crea  = $usuario;
                $row->foranet   = TRUE; 
                $row->save();

                $tblFichaTrabajador->getDefaultAdapter()->commit();
                
                return $params[self::E_CODTARJETA];
                
            } else {
                throw new Exception('El trabajador ya tiene ficha activa', -1);
            }
        } catch (Exception $ex) {
            $tblFichaTrabajador->getDefaultAdapter()->rollBack();
            throw new Exception($ex->getMessage());
        }
    }
    
    public function foraneoValido(){
        $cedula = $this->_cedula;
        
        $personal   = new Fmo_Model_Personal();
        
        //Si se encuentra en nomina
        if($personal->addFilterByCedula($cedula)->findOne()){
            //Es un trabajador activo de CVG Ferrominera Orinoco
            if (!Application_Model_Trabajador::esInactivo($cedula)) {
                throw new Exception('Foraneo Invalido!.<br>C.I: '.$cedula.' Es un trabajador <b>ACTIVO</b> de CVG Ferrominera Orinoco', -1);
            }
        }

        return true;
        
        
    }
    
    public function foraneoActivo()
    {
        $trabajador = $this->_trabajador;
        $cedula     = $trabajador->cedula;
        
        if (!Application_Model_TrabajadorForaneo::esInactivo($cedula)) {
            throw new Exception('Foraneo Inactivo!.<br>A los trabajadores foraneos <b>INACTIVOS</b> no se les emite ficha', -1);
        }
             
        return true;
    }
    
    public function isValidBuscar($data) {
        if(parent::isValid($data)){

        }

        return !$this->hasErrors();
    }
    
    public function isValidEmitir($data) {
        if (parent::isValid($data)) {
            if (!empty($data[self::E_CODTARJETA])) {
                $codAsiT = Application_Model_FichaTrabajador::codigoFichaAsignado($data[self::E_CODTARJETA]);

                if ($codAsiT && $codAsiT != $this->_trabajador->cedula) {
                    $this->getElement(self::E_CODTARJETA)
                        ->addError('Código de Tarjeta asignado a otro trabajador');
                }
                
                $codAsiF = Application_Model_FichaTrabajador::codigoFichaAsignadoForaneo($data[self::E_CODTARJETA]);

                if ($codAsiF && $codAsiF != $this->_trabajador->cedula) {
                    $this->getElement(self::E_CODTARJETA)
                        ->addError('Código de Tarjeta asignado a otro trabajador foraneo');
                }
            }
        }

        return !$this->hasErrors();
    }
}

//Tabla SADT: Direccion