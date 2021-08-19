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
class Application_Form_RegistrarForaneo extends Fmo_Form_Abstract
{
    const E_CEDULA          = 'txtCedula';     
    const E_FICHA           = 'txtFicha';
    const E_NOMBRE          = 'txtNombre';
    const E_APELLIDO        = 'txtApellido';
    const E_EMPRESA         = 'selEmpresa';
    const E_ESTADO          = 'selEstado';
    const E_REGISTRAR        = 'btnRegistrar';
    const E_CANCELAR        = 'btnCancelar';
 
    public function init() {
        $this->setName('Nuevo Trabajador Foraneo')
            ->setAction($this->getView()->url())
            ->setLegend('Nuevo Trabajador Foraneo');
               
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
            ->addValidator('Alpha',TRUE)
            ->setRequired();
        $this->addElement($nombre);

        $apellido = new Zend_Form_Element_Text(self::E_APELLIDO);
        $apellido->setLabel('Apellido:')
            ->setAttrib('size', '10')
            ->setAttrib('style', 'width: 100%;')
            ->setAttrib('class', 'form-control')
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
        
        $registrar = new Zend_Form_Element_Button(self::E_REGISTRAR);
        $registrar->setLabel('Registrar')
            ->setAttrib('class', 'btn btn-success')
            ->setAttrib('type', 'submit')
            ->setAttrib('style', 'margin-top:10px !important;')
            ->setValue(self::E_REGISTRAR);
        $this->addElement($registrar);
        
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
    
    public function registrar($params){
        
        $usuarioSiglado = Fmo_Model_Seguridad::getUsuarioSesion()->{Fmo_Model_Personal::SIGLADO};
        
        /*Transaccion para ficha_trabajador*/
        $tblTrabajadorForaneo = new Application_Model_DbTable_TrabajadorForaneo();
        $tblTrabajadorForaneo->getDefaultAdapter()->beginTransaction();
        
        try {

            if (!Application_Model_Foraneo::estaRegistrado($params[self::E_CEDULA])) {

                $row                = $tblTrabajadorForaneo->createRow();
                $row->cedula        = $params[self::E_CEDULA];
                $row->ficha         = $params[self::E_FICHA];
                $row->nombre        = $params[self::E_NOMBRE];
                $row->apellido      = $params[self::E_APELLIDO];
                $row->id_empresa    = $params[self::E_EMPRESA];
                $row->estado        = $params[self::E_ESTADO];
                $row->usu_crea      = $usuarioSiglado;
                $row->save();
                
                $tblTrabajadorForaneo->getDefaultAdapter()->commit();
                
            } else {
                throw new Exception('El trabajador foraneo ya esta registrado', -1);
            }
        } catch (Exception $ex) {
            $tblTrabajadorForaneo->getDefaultAdapter()->rollBack();
            throw new Exception($ex->getMessage());
        }
        
        return $params[self::E_CEDULA];
    }
    
    public function isValid($data) {
        if (!empty($data[self::E_FICHA]) && parent::isValid($data) ) {
            
            if(Application_Model_TrabajadorForaneo::fichaAsignada($data[self::E_FICHA])) {
                $this->getElement(self::E_FICHA)->addError('Ficha asignada a otro trabajador!');
            }
            
        }

        return !$this->hasErrors();
    }
}