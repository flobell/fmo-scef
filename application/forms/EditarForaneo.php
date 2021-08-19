<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Editar Foraneo
 *
 * @author fmo16554;
 */
class Application_Form_EditarForaneo extends Fmo_Form_Abstract
{
    const E_CEDULA          = 'txtCedula';     
    const E_FICHA           = 'txtFicha';
    const E_NOMBRE          = 'txtNombre';
    const E_APELLIDO        = 'txtApellido';
    const E_EMPRESA         = 'selEmpresa';
    const E_ESTADO          = 'selEstado';
    const E_GUARDAR         = 'btnGuardar';
    const E_CANCELAR        = 'btnCancelar';
 
    public function init() {
        $this->setName('Editar Trabajador Foraneo')
            ->setAction($this->getView()->url())
            ->setLegend('Editar Trabajador Foraneo');
               
        $cedula = new Zend_Form_Element_Text(self::E_CEDULA);
        $cedula->setLabel('Cedula:')
            ->setAttrib('size', '10')
            ->setAttrib('style', 'width: 100%;')
            ->setAttrib('class', 'form-control')
            ->setAttrib('maxlength', '10')
            ->setAttrib('readonly','readonly')
            ->setRequired();
        $this->addElement($cedula);
               
        $ficha = new Zend_Form_Element_Text(self::E_FICHA);
        $ficha->setLabel('Ficha:')
            ->setAttrib('size', '10')
            ->setAttrib('style', 'width: 100%;')
            ->setAttrib('class', 'form-control')
            ->setAttrib('maxlength', '5')
            ->addValidator('Digits')
            ->addValidator('StringLength', false, array('min' => 1, 'max' => 5, 'encoding' => $this->getView()->getEncoding()))
            ->setRequired();
        $this->addElement($ficha);
        
        $nombre = new Zend_Form_Element_Text(self::E_NOMBRE);
        $nombre->setLabel('Nombre:')
            ->setAttrib('size', '10')
            ->setAttrib('style', 'width: 100%;')
            ->setAttrib('class', 'form-control')
            ->addValidator('StringLength', false, array('min' => 1, 'max' => 50, 'encoding' => $this->getView()->getEncoding()))
            ->addValidator('Alpha',TRUE)
            ->setRequired();
        $this->addElement($nombre);

        $apellido = new Zend_Form_Element_Text(self::E_APELLIDO);
        $apellido->setLabel('Apellido:')
            ->setAttrib('size', '10')
            ->setAttrib('style', 'width: 100%;')
            ->setAttrib('class', 'form-control')
            ->addValidator('StringLength', false, array('min' => 1, 'max' => 50, 'encoding' => $this->getView()->getEncoding()))
            ->addValidator('Alpha',TRUE)
            ->setRequired();
        $this->addElement($apellido);
        
        $empresa = new Zend_Form_Element_Select(self::E_EMPRESA);
        $empresa->setLabel('Empresa:')
            ->setAttrib('class', 'form-control')
            ->addMultiOption("",'Seleccione...')
            ->addMultiOptions(Application_Model_DbTable_Empresa::getPairsWithOrder('id', 'nombre', NULL, 'id'))
            ->setRequired();
        $this->addElement($empresa);
        
        $estado = new Zend_Form_Element_Select(self::E_ESTADO);
        $estado->setLabel('Estado:')
            ->setAttrib('class', 'form-control')
            ->addMultiOption('TRUE','Activo')
            ->addMultiOption('FALSE','Inactivo')
            ->setRequired();
        $this->addElement($estado);
        
        $guardar = new Zend_Form_Element_Button(self::E_GUARDAR);
        $guardar->setLabel('Guardar')
            ->setAttrib('class', 'btn btn-success')
            ->setAttrib('type', 'submit')
            ->setAttrib('style', 'margin-top:10px !important;')
            ->setValue(self::E_GUARDAR);
        $this->addElement($guardar);
        
        $cancelar = new Zend_Form_Element_Button(self::E_CANCELAR);
        $cancelar->setLabel('Cancelar')
            ->setAttrib('class', 'btn btn-danger')
            ->setAttrib('type', 'submit')
            ->setAttrib('style', 'margin-top:10px !important;')
            ->setValue(self::E_CANCELAR);
        $this->addElement($cancelar);
        
        // Aplicando css de la empresa 
        $this->setCustomDecorators();
    }
    
    public function guardar($params){
        
        $usuarioSiglado = Fmo_Model_Seguridad::getUsuarioSesion()->{Fmo_Model_Personal::SIGLADO};
        
        /*Transaccion para ficha_trabajador*/
        $tblTrabajadorForaneo = new Application_Model_DbTable_TrabajadorForaneo();
        $tblTrabajadorForaneo->getDefaultAdapter()->beginTransaction();

        try {

            $row                = $tblTrabajadorForaneo->findOneByColumn('cedula', $params[self::E_CEDULA]);
            $row->ficha         = $params[self::E_FICHA];
            $row->nombre        = $params[self::E_NOMBRE];
            $row->apellido      = $params[self::E_APELLIDO];
            $row->id_empresa    = $params[self::E_EMPRESA];
            $row->estado        = $params[self::E_ESTADO];
            $row->fecha_mod     = date('Y-m-d H:i:s');
            $row->usu_mod       = $usuarioSiglado;
            $row->save();

            $tblTrabajadorForaneo->getDefaultAdapter()->commit();

        } catch (Exception $ex) {
            $tblTrabajadorForaneo->getDefaultAdapter()->rollBack();
            throw new Exception($ex->getMessage());
        }
        
        return $params[self::E_CEDULA];
    }
    
    
    public function isValid($data) {
        if (parent::isValid($data) && !empty($data[self::E_FICHA])) {
            
            $mdlForaneo = new Application_Model_Foraneo();
            $trabajador = $mdlForaneo->addFilterByCedula($data[self::E_CEDULA])->findOne();
            if($trabajador->ficha != $data[self::E_FICHA]){
                if(Application_Model_TrabajadorForaneo::fichaAsignada($data[self::E_FICHA])) {
                $this->getElement(self::E_FICHA)
                ->addError('Ficha asignada a otro trabajador!');
                }
            }
            
            if (!is_numeric($data[self::E_FICHA])) {
                $this->getElement(self::E_FICHA)
                ->addError('Solo se admiten digitos (0 ~ 9)');  
            }

        }

        return !$this->hasErrors();
    }
}