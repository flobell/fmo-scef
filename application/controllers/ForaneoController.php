<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ForaneoController
 *
 * @author fmo16554
 */
class ForaneoController extends Fmo_Controller_Action_Abstract
{
    
    private function _bootstrap()
    {
        $this->view->bootstrap()->enable();
        $this->view->jQuery()->enable();
        $this->view->jQuery()->uienable();
    }
    
    public function buscarAction(){
        $this->_bootstrap();
        $foraneoActivo = false;
        $fichasTrab = null;
           
        if ($this->getParam(Application_Form_BuscarForaneo::E_NUEVABUS)) {
            $this->redirect('/default/foraneo/buscar');
        }

        $request = $this->getRequest();
        $cedula   = $request->getParam(Application_Form_BuscarForaneo::E_CEDULA);
        
        if (!empty($cedula)) {
            $form = new Application_Form_BuscarForaneo($cedula);
        } else {
            $form = new Application_Form_BuscarForaneo();
        }
        
        $foraneo = $form->getTrabajador() ? : null;
        $cedulaForm  = $form->getCedula();
        
        
        //Si existe trabajador entonces se realiza la busqueda
        if (!empty($cedulaForm) && !empty($foraneo)) {
            $form->getElement(Application_Form_BuscarForaneo::E_CEDULA)->setAttrib('readonly','readonly');
            $form->getElement(Application_Form_BuscarForaneo::E_BUSCAR)->setAttrib('disabled','disabled');
        } 
        
        
        // Si Emitir viene por la petición colocar validadores
        if ($this->getParam(Application_Form_BuscarForaneo::E_EMITIR)) {
            $codFichaElem = $form->getElement(Application_Form_BuscarForaneo::E_CODTARJETA);
            $codFichaElem->setRequired(true)
                ->addValidator('StringLength', false, array('min' => 1, 'max' => 20, 'encoding' => 'UTF-8'));                             
        }
               
        if ($request->isPost()) {
            $post = $request->getPost();
            $form->setDefaults($post);

            try {
        
                $btnEmitir = $this->getParam(Application_Form_BuscarForaneo::E_EMITIR);
                
                if ($this->getParam(Application_Form_BuscarForaneo::E_BUSCAR)){
                    //Si no existe trabajador entonces se registra
                    if(!empty($cedulaForm) && empty($foraneo) && $form->isValidBuscar($post)){

                        if($form->foraneoValido()){
                            $params = array('cedula' => $cedulaForm);
                            $this->_helper->redirector('registrar', 'foraneo', 'default', $params);
                        } 
                        else
                        {
                            $this->redirect('/default/foraneo/buscar');
                        }

                    }
                    
                    if (!empty($foraneo)) {
                        if(!Application_Model_FichaTrabajador::fichaActivaForaneo($cedulaForm)){
                            $this->addMessageInformation('¡El trabajador foraneo es apto para emisión de ficha!');
                        }
                    } 
                }
                
                if ($foraneo) {
                    $foraneoActivo = $form->foraneoActivo();
                }

                if ($this->getParam(Application_Form_BuscarForaneo::E_EMITIR) && $form->isValidEmitir($post)) {
                    $codigo = $form->emitirFicha($post);
                    $this->addMessageSuccessful('¡La Ficha de codigo: '.$codigo.' fue emitida con exito!');
                    $this->redirect('/default/foraneo/buscar');
                }

                
            } catch (Exception $ex) {
                $this->addMessageError($ex->getMessage());
            }
        }
        
        if ($foraneo) {
            $fichasTrab = Application_Model_FichaTrabajador::getByCedulaForaneo($foraneo->cedula);
        }

        $this->view->form        = $form;
        $this->view->trabajador  = $foraneo;
        $this->view->activo      = $foraneoActivo;
        $this->view->hist_fichas = $fichasTrab;
        
        
    }
    
    public function registrarAction(){
        $this->_bootstrap();
        $cedula = $this->_getParam('cedula');
        
        $form = new Application_Form_RegistrarForaneo();
        $form->getElement(Application_Form_RegistrarForaneo::E_CEDULA)->setValue($cedula);
        
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $post = $request->getPost();
            $form->setDefaults($post);

            try {

                if ($this->getParam(Application_Form_RegistrarForaneo::E_REGISTRAR) && $form->isValid($post)) {

                    $cedula = $form->registrar($post);
                    $this->addMessageSuccessful('¡Trabajador Foraneo C.I: '.$cedula.' registrado.');
                    $this->redirect('/default/foraneo/buscar');
                    //$this->forward('buscar', 'foraneo', 'default', Array(Application_Form_BuscarForaneo::E_CEDULA => $cedula));

                }
                
                if ($this->getParam(Application_Form_RegistrarForaneo::E_CANCELAR)) {
                    $this->redirect('/default/foraneo/buscar');
                }

            } catch (Exception $ex) {
                $this->addMessageError($ex->getMessage());
            }
        }
        
        $this->view->form = $form;
    }
    
    public function editarAction(){
        $this->_bootstrap();
        
        $cedula = $this->_getParam('cedula');
        
        $mdlForaneo = new Application_Model_Foraneo();
        $trabajador = $mdlForaneo->addFilterByCedula($cedula)->findOne();
        
        //Zend_Debug::dd($trabajador);
        
        $form = new Application_Form_EditarForaneo();
        //Seteando valores guardados
        $form->getElement(Application_Form_EditarForaneo::E_CEDULA)->setValue($trabajador->cedula);
        $form->getElement(Application_Form_EditarForaneo::E_FICHA)->setValue($trabajador->ficha);
        $form->getElement(Application_Form_EditarForaneo::E_NOMBRE)->setValue($trabajador->nombre);
        $form->getElement(Application_Form_EditarForaneo::E_APELLIDO)->setValue($trabajador->apellido);
        $form->getElement(Application_Form_EditarForaneo::E_EMPRESA)->setValue($trabajador->id_empresa);
        $form->getElement(Application_Form_EditarForaneo::E_ESTADO)->setValue($trabajador->estado ? 'TRUE': 'FALSE');
        
        $request = $this->getRequest();
        
        
        if ($request->isPost()) {
            $post = $request->getPost();
            $form->setDefaults($post);

            try {

                if ($this->getParam(Application_Form_EditarForaneo::E_GUARDAR) && $form->isValid($post)) {

                    $cedula = $form->guardar($post);
                    $this->addMessageSuccessful('¡Trabajador Foraneo C.I: '.$cedula.' actualizado.');
                    $this->redirect('/default/foraneo/buscar');
                    //$this->forward('buscar', 'foraneo', 'default', Array(Application_Form_BuscarForaneo::E_CEDULA => $cedula));

                }
                
                if ($this->getParam(Application_Form_EditarForaneo::E_CANCELAR)) {
                    $this->redirect('/default/foraneo/buscar');
                }

            } catch (Exception $ex) {
                $this->addMessageError($ex->getMessage());
            }
        }
        
        
        $this->view->form = $form;
        
    }
    
    public function inactivarAction()
    {
        $this->_bootstrap();
        $id      = $this->getParam(Application_Form_InactivarFichaForaneo::E_ID);
        $form    = new Application_Form_InactivarFichaForaneo($id);
        $request = $this->getRequest();

        if ($this->getParam(Application_Form_InactivarFicha::E_CANCELAR)) {
            $this->redirect('/default/foraneo/buscar');
        }

        $regFicha = $form->getRegistro();
        $ficha    = $regFicha->ficha;
        $nombre   = $regFicha->nombre . ' ' . $regFicha->apellido;

        if (!$regFicha->activa) {
            $this->addMessageWarning('¡La ficha del trabajador <b>FMO-' . $ficha . '</b> ' . $nombre . ' con Código de Tarjeta <b>' . $regFicha->cod_ficha . '</b> ya está inactiva!');
            $this->redirect('/default/foraneo/buscar');
        }

        try {
            if ($request->isPost()) {
                $post = $request->getPost();
                $form->setDefaults($post);

                if ($this->getParam(Application_Form_InactivarFicha::E_INACTIVAR) && $form->isValid($post)) {
                    $form->inactivar($post);
                    $this->addMessageSuccessful('¡La ficha del trabajador <b>FMO-' . $ficha . '</b> ' . $nombre . ' con Código de Tarjeta <b>' . $regFicha->cod_ficha . '</b> ha sido inactivada!');
                    $this->redirect('/default/foraneo/buscar');
                }
            }
        } catch (Exception $e) {
            $this->addMessageError($e->getMessage());
        }

        $this->view->form = $form;
    }
    
}
