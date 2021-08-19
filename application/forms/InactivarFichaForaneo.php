<?php
/**
 * Description of Inactivar Ficha Foraneo
 *
 * @author fmo16554;
 */
class Application_Form_InactivarFichaForaneo extends Fmo_Form_Abstract
{
    const E_ID         = 'id';
    const E_MOTIVO     = 'cboMotivo';
    const E_CANCELAR   = 'btnCancelar';
    const E_INACTIVAR  = 'btnInactivar';

    private $_fichaTrab  = null;
    private $_trabajador = null;

    public function __construct($id, $options = null) {
        $this->_fichaTrab = Application_Model_FichaTrabajador::getByIdForaneo($id);
        $personal = new Application_Model_Foraneo();

        $this->_trabajador = $personal->addFilterByCedula($this->_fichaTrab->cedula)->findOne();
        parent::__construct($options);
    }

    public function init() {
        $this->setName('Inactivar Ficha')
            ->setAction($this->getView()->url())
            ->setLegend('Inactivar Ficha');

        $cboMotivo = new Zend_Form_Element_Select(self::E_MOTIVO);
        $cboMotivo->setLabel('Motivo de Inactivación:')
                ->addMultiOption('', 'Seleccione')
                ->addMultiOptions(Application_Model_DbTable_Motivo::getPairsWithOrder('id', 'descripcion', 'id > 2', 'id'))
                ->setAttrib('class', 'form-control')
                ->setRequired();
        $this->addElement($cboMotivo);

        $eleC = new Zend_Form_Element_Button(self::E_CANCELAR);
        $eleC->setLabel('Cancelar')
            ->setAttrib('class', 'btn btn-default')
            ->setAttrib('type', 'submit')
            ->setAttrib('style', 'margin-top:10px !important;')
            ->setValue(self::E_CANCELAR);
        $this->addElement($eleC);

        $eleI = new Zend_Form_Element_Button(self::E_INACTIVAR);
        $eleI->setLabel('Inactivar')
                ->setAttrib('class', 'btn btn-danger')
                ->setAttrib('type', 'submit')
                ->setAttrib('style', 'margin-top:10px !important;')
                ->setValue(self::E_INACTIVAR);
        $this->addElement($eleI);

        // Aplicando css de la empresa 
        $this->setCustomDecorators();
    }

    public function getRegistro() {
        return $this->_fichaTrab;
    }

    public function getTrabajador() {
        return $this->_trabajador;
    }

    public function inactivar($params)
    {
        $fichaTrab = Application_Model_DbTable_FichaTrabajador::findOneById($this->_fichaTrab->id);
        Zend_Db_Table::getDefaultAdapter()->beginTransaction();
        $usuario = Fmo_Model_Seguridad::getUsuarioSesion()->{Fmo_Model_Personal::CEDULA};

        try {
            $cedula = $this->_trabajador->cedula;
            $ahora  = new Zend_Date();

            if (Application_Model_FichaTrabajador::fichaActivaForaneo($cedula)) {
                $fichaTrab->motivo    = $params[self::E_MOTIVO];
                $fichaTrab->activo    = 'FALSE';
                $fichaTrab->usu_mod   = $usuario;
                $fichaTrab->fecha_mod = $ahora->toString('yyyy-MM-dd HH:mm');
                $fichaTrab->save();

                Zend_Db_Table::getDefaultAdapter()->commit();
                return $fichaTrab;
            } else {
                throw new Exception('El trabajador ya tiene ficha inactiva', -1);
            }
        } catch (Exception $e) {
            Zend_Db_Table::getDefaultAdapter()->rollBack();
            throw new Exception($e->getMessage());
        }
    }
    
}