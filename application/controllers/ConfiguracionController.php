<?php

/**
 * Controlador para administrar la configuración.
 */
class ConfiguracionController extends My_Controller_Action_Abstract
{   
    /**
     * Parámetro con la variable de configuración
     */
    const P_CODIGO = 'codigo';
    
    /**
     * Listado
     */
    public function listadoAction()
    {
        $config = new Application_Model_DbTable_Configuracion();
        $this->view->datos = $this->paginator($config->select()->order('id'));
    }
    
    /**
     * Modificar
     */
    public function editarAction()
    {
        if ($this->getParam(Application_Form_Configuracion::E_CANCELAR)) {
            $this->redirect($this->urlDefault);
        }

        $form = new Application_Form_Configuracion(intval($this->getParam(self::P_CODIGO)));
        if ($this->getRequest()->isPost() && $form->saveEditar($this->getRequest()->getPost())) {
            $this->addMessageSuccessful($form->getMessageProcess()->getAll());
            $this->redirect($this->urlDefault);
        }

        $this->view->form = $form;
        $this->renderScript('form.phtml');
    }

    /**
     * Detalle
     */
    public function detalleAction()
    {
        try {
            $variable = Application_Model_DbTable_Configuracion::findOneById(intval($this->getParam(self::P_CODIGO)));
        } catch (Exception $ex) {
            $this->addMessageException($ex);
            $this->redirect($this->urlDefault);
        }
        $this->view->variable = $variable;
    }

    /**
     * Eliminar
     */
    public function eliminarAction()
    {
        $this->addMessageWarning('Operación no permitida: ' . __METHOD__);
        $this->redirect($this->urlDefault);        
    }

    /**
     * Nuevo
     */
    public function nuevoAction()
    {
        $this->addMessageWarning('Operación no permitida: ' . __METHOD__);
        $this->redirect($this->urlDefault);        
    }
}