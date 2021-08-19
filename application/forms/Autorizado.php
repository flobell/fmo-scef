<?php

/**
 * Description of Solicitar Ficha
 *
 * @author juanfd;
 */
class Application_Form_Autorizado extends Fmo_Form_Abstract
{
    const E_FICHA      = 'txtFicha';
    const E_REGISTRAR  = 'btnRegistrar';

    public function init() {
        $this->setName('Registrar Autorizado')
            ->setAction($this->getView()->url())
            ->setLegend('Registrar Autorizado');

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

        $eleE = new Zend_Form_Element_Button(self::E_REGISTRAR);
        $eleE->setLabel('Registrar')
                ->setAttrib('class', 'btn btn-success')
                ->setAttrib('type', 'submit')
                ->setAttrib('style', 'margin-top:10px !important;')
                ->setValue(self::E_REGISTRAR);
        $this->addElement($eleE);

        // Aplicando css de la empresa 
        $this->setCustomDecorators();
    }

    public function guardar($params)
    {
        $autorizado = new Application_Model_DbTable_Autorizado();
        $autorizado->getDefaultAdapter()->beginTransaction();
        $usuario    = Fmo_Model_Seguridad::getUsuarioSesion()->{Fmo_Model_Personal::CEDULA};

        try {
            // Buscar datos trabajador a agregar
            $personal   = new Fmo_Model_Personal();
            $trabajador = $personal->addFilterByFicha($params[self::E_FICHA])->findOne();

            if ($trabajador) {
                // Si la ficha es de pasante verificar si es de un pasante activo
                if (in_array($trabajador->{Fmo_Model_Personal::ID_NOMINA}, array('7','8')) && $trabajador->{Fmo_Model_Personal::ID_ACTIVIDAD} == 9) {
                    $personal   = new Fmo_Model_Personal();
                    $trabajador = $personal->addFilterByFicha($params[self::E_FICHA])
                            ->addFilterByActividadActivo()
                            ->findOne();
                }
            }

            $row            = $autorizado->createRow();
            $row->ficha     = $trabajador->{Fmo_Model_Personal::FICHA};
            $row->cedula    = $trabajador->{Fmo_Model_Personal::CEDULA};
            $row->nombre    = $trabajador->{Fmo_Model_Personal::NOMBRE} . ' ' . $trabajador->{Fmo_Model_Personal::APELLIDO};
            $row->usu_crea  = $usuario;
            $res            = $row->save();

            $autorizado->getDefaultAdapter()->commit();
            //return $res;
            return $trabajador;
        } catch (Exception $ex) {
            $autorizado->getDefaultAdapter()->rollBack();
            throw new Exception($ex->getMessage(), $ex->getCode());
        }
    }
}