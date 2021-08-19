<?php

/**
 * Description of FichaController
 *
 * @author juanfd
 */
class FichaController extends Fmo_Controller_Action_Abstract
{
    private $_solicitar = '/default/ficha/solicitar';

    private function _bootstrap()
    {
        $this->view->bootstrap()->enable();
        $this->view->jQuery()->enable();
        $this->view->jQuery()->uienable();
    }

    public function solicitarAction()
    {
        $this->_bootstrap();
        $trabValido = false;
        $fichasTrab = null;

        if ($this->getParam(Application_Form_SolicitarFicha::E_NUEVABUS)) {
            $this->redirect($this->_solicitar);
        }

        $request = $this->getRequest();
        $ficha   = $request->getParam(Application_Form_SolicitarFicha::E_FICHA);

        if (!empty($ficha)) {
            $form = new Application_Form_SolicitarFicha($ficha);
        } else {
            $form = new Application_Form_SolicitarFicha();
        }

        $trabajador = $form->getTrabajador() ? : null;
        $fichaForm  = $form->getFicha();

        if (!empty($fichaForm) && !empty($trabajador)) {
            $form->getElement(Application_Form_SolicitarFicha::E_FICHA)->setAttrib('readonly','readonly');
            $form->getElement(Application_Form_SolicitarFicha::E_BUSCAR)->setAttrib('disabled','disabled');
        }

        // Si Emitir viene por la petición colocar validadores
        if ($this->getParam(Application_Form_SolicitarFicha::E_EMITIR)) {
            $codFichaElem = $form->getElement(Application_Form_SolicitarFicha::E_CODTARJETA);
            $codFichaElem->setRequired(true)
                ->addValidator('StringLength', false, array('min' => 1, 'max' => 20, 'encoding' => 'UTF-8'));
        }

        if ($request->isPost()) {
            $post = $request->getPost();
            $form->setDefaults($post);

            try {
                if ($trabajador) {
                    $trabValido = $form->validarTrabajador();
                    $btnEmitir = $this->getParam(Application_Form_SolicitarFicha::E_EMITIR);

                    if ($this->getParam(Application_Form_SolicitarFicha::E_BUSCAR) && !Application_Model_FichaTrabajador::fichaActiva($form->getCedulaTrabajador())) {
                        $this->addMessageInformation('¡El trabajador es apto para emisión de ficha!');
                    } else if (empty($btnEmitir)) {
                        $this->addMessageWarning('¡El trabajador ya tiene ficha emitida!');
                    }
                }

                if ($form->isValid($post)) {
                    if (!$trabajador) {
                        throw new Exception('La ficha no está asociada a ningún trabajador activo', -1);
                    }

                    if ($this->getParam(Application_Form_SolicitarFicha::E_EMITIR)) {
                        $codigo = $form->guardar($post);
                        $this->addMessageSuccessful('¡Ficha emitida al trabajador (FMO-' . $this->getParam(Application_Form_SolicitarFicha::E_FICHA) . ') ' . $trabajador->apellido . ' ' . $trabajador->nombre . '!');
                        $this->addMessageSuccessful('Código Tarjeta: ' . $codigo);
                    }
                }
            } catch (Exception $e) {
                switch($e->getCode()) {
                    case -1: 
                        $this->addMessageError('Trabajador inválido');
                        break;
                    default: break;
                }

                $this->addMessageError($e->getMessage());
            }
        }

        if ($trabajador) {
            $fichasTrab = Application_Model_FichaTrabajador::getByCedula($trabajador->cedula);
        }

        $this->view->form        = $form;
        $this->view->trabajador  = $trabajador;
        $this->view->valido      = $trabValido;
        $this->view->hist_fichas = $fichasTrab;
    }

    public function inactivarAction()
    {
        $this->_bootstrap();
        $id      = $this->getParam(Application_Form_InactivarFicha::E_ID);
        $form    = new Application_Form_InactivarFicha($id);
        $request = $this->getRequest();

        if ($this->getParam(Application_Form_InactivarFicha::E_CANCELAR)) {
            $this->redirect($this->_solicitar);
        }

        $regFicha = $form->getRegistro();
        $ficha    = $regFicha->ficha;
        $nombre   = $regFicha->nombre . ' ' . $regFicha->apellido;

        if (!$regFicha->activa) {
            $this->addMessageWarning('¡La ficha del trabajador <b>FMO-' . $ficha . '</b> ' . $nombre . ' con Código de Tarjeta <b>' . $regFicha->cod_ficha . '</b> ya está inactiva!');
            $this->redirect($this->_solicitar);
        }

        try {
            if ($request->isPost()) {
                $post = $request->getPost();
                $form->setDefaults($post);

                if ($this->getParam(Application_Form_InactivarFicha::E_INACTIVAR) && $form->isValid($post)) {
                    $form->inactivar($post);
                    $this->addMessageSuccessful('¡La ficha del trabajador <b>FMO-' . $ficha . '</b> ' . $nombre . ' con Código de Tarjeta <b>' . $regFicha->cod_ficha . '</b> ha sido inactivada!');
                    $this->redirect($this->_solicitar);
                }
            }
        } catch (Exception $e) {
            $this->addMessageError($e->getMessage());
        }

        $this->view->form = $form;
    }
}