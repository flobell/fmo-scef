<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Solicitar Ficha
 *
 * @author juanfd;
 */
class Application_Form_SolicitarFicha extends Fmo_Form_Abstract
{
    const E_FICHA      = 'txtFicha';
    const E_CODTARJETA = 'txtCodTarjeta';
    const E_BUSCAR     = 'btnBuscar';
    const E_NUEVABUS   = 'btnNuevaBusqueda';
    const E_EMITIR     = 'btnEmitir';

    private $_ficha      = null;
    private $_trabajador = null;

    public function __construct($ficha = '', $options = null) {
        $this->_ficha = $ficha;
        parent::__construct($options);
    }

    public function init() {
        $this->setName('Solicitar Ficha')
            ->setAction($this->getView()->url())
            ->setLegend('Solicitar Ficha');

        $datBas = new Fmo_DbTable_Rpsdatos_DatoBasico();

        $ficha = new Zend_Form_Element_Text(self::E_FICHA);
        $ficha->setLabel('Ficha:')
            ->setAttrib('size', '10')
            ->setAttrib('style', 'width: 50%;')
            ->setAttrib('class', 'form-control text-center')
            ->setAttrib('maxlength', '10')
            ->addValidator('StringLength', false, array('min' => 1, 'max' => 10, 'encoding' => $this->getView()->getEncoding()))
            ->addValidator('Db_RecordExists', true, array('table' => $datBas->info(Zend_Db_Table::NAME), 'field' => 'datb_nrotrab',
                'schema' => $datBas->info(Zend_Db_Table::SCHEMA),
                'messages' => array(Zend_Validate_Db_RecordExists::ERROR_NO_RECORD_FOUND => 'La ficha no existe para ningún trabajador.')))
            ->setRequired();
        $this->addElement($ficha);

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

        $personal   = new Fmo_Model_Personal();
        $trabajador = $personal->addFilterByFicha($this->_ficha)
                        ->findOne();

        if ($trabajador) {
            // Si la ficha es de pasante verificar si es de un pasante activo
            if (in_array($trabajador->{Fmo_Model_Personal::ID_NOMINA}, array('7','8')) && $trabajador->{Fmo_Model_Personal::ID_ACTIVIDAD} == 9) {
                $personal   = new Fmo_Model_Personal();
                $trabajador = $personal->addFilterByFicha($this->_ficha)
                        ->addFilterByActividadActivo()
                        ->findOne();
            }
        }

        $this->_trabajador = $trabajador;

        // Aplicando css de la empresa 
        $this->setCustomDecorators();
    }

    public function getFicha() {
        return $this->_ficha;
    }

    public function getTrabajador() {
        return $this->_trabajador;
    }
    
    public function getCedulaTrabajador()
    {
        return $this->_trabajador->cedula;
    }
    
    public function getFichaTrabajador()
    {
        return $this->_trabajador->ficha;
    }

    public function guardar($params)
    {
        $fichaTrab = new Application_Model_DbTable_FichaTrabajador();
        $fichaTrab->getDefaultAdapter()->beginTransaction();
        $usuario = Fmo_Model_Seguridad::getUsuarioSesion()->{Fmo_Model_Personal::CEDULA};

        try {
            $cedula = $this->_trabajador->cedula;

            if (!Application_Model_FichaTrabajador::fichaActiva($cedula)) {
                $hist   = Application_Model_DbTable_FichaTrabajador::findAllByColumn('cedula', $cedula);
                $motivo = (count($hist) > 0) ? 2 : 1;

                $row            = $fichaTrab->createRow();
                $row->cedula    = $cedula;
                $row->ficha     = $this->_trabajador->ficha;
                $row->cod_ficha = $params[self::E_CODTARJETA];
                $row->motivo    = $motivo;
                $row->usu_crea  = $usuario;
                $row->save();

                $fichaTrab->getDefaultAdapter()->commit();
                return $params[self::E_CODTARJETA];
            } else {
                throw new Exception('El trabajador ya tiene ficha activa', -2);
            }
        } catch (Exception $e) {
            $fichaTrab->getDefaultAdapter()->rollBack();
            throw new Exception($e->getMessage());
        }
    }

    public function validarTrabajador()
    {
        $trabajador = $this->_trabajador;
        $cedula     = $trabajador->cedula;
        $fecha      = new Zend_Date();
        $hoy        = $fecha->toString('yyyy-MM-dd');

        if (Application_Model_DbTable_Configuracion::getValidarAutorizados() && !Application_Model_Trabajador::esAutorizado($trabajador->ficha)) {
            throw new Exception('El trabajador no está entre los AUTORIZADOS.', -1);
        }

        if (Application_Model_Trabajador::esInactivo($cedula)) {
            throw new Exception('A los trabajadores SUSPENDIDOS o RETIRADOS no se les emite ficha', -1);
        }

        if (Application_Model_DbTable_Configuracion::getValidarReposo() && Application_Model_Falta::estaDeReposo($trabajador->cedula, $hoy)) {
            throw new Exception('A los trabajadores de REPOSO no se les emite ficha', -1);
        }

        if (Application_Model_DbTable_Configuracion::getValidarContratados() && Application_Model_Trabajador::esContratado($trabajador->cedula)) {
            throw new Exception('A los trabajadores CONTRATADOS no se les emite ficha', -1);
        }

        if (Application_Model_DbTable_Configuracion::getValidarJubilados() && Application_Model_Trabajador::esJubilado($cedula)) {
            throw new Exception('A la nómina JUBILADOS y PENSIONADOS no se les emite ficha', -1);
        }

        if (Application_Model_DbTable_Configuracion::getValidarPasantes() && Application_Model_Trabajador::esPasante($cedula)) {
            throw new Exception('A la nómina PASANTES no se les emite ficha', -1);
        }

        if (Application_Model_DbTable_Configuracion::getValidarDireccion() && !Application_Model_Trabajador::tieneDireccion($cedula)) {
            throw new Exception('A los trabajadores sin DIRECCIÓN REGISTRADA no se les emite ficha.', -1);
        }

        return true;
    }

    public function isValid($data) {
        if (parent::isValid($data)) {
            if (!empty($data[self::E_CODTARJETA])) {
                $codAsi = Application_Model_FichaTrabajador::codigoFichaAsignado($data[self::E_CODTARJETA]);

                if ($codAsi && $codAsi != $this->_trabajador->cedula) {
                    $this->getElement(self::E_CODTARJETA)
                        ->addError('Código de Tarjeta asignado a otro trabajador');
                }
            }
        }

        return !$this->hasErrors();
    }
}

//Tabla SADT: Direccion