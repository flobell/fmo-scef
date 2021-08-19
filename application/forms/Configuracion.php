<?php

/**
 * Formulario para editar la configuración de las variables del Sistema.
 *
 * @author Rafael Rodríguez - F8741 <rafaelars@ferrominera.gob.ve>
 */
class Application_Form_Configuracion extends Fmo_Form_Abstract
{

    const E_NOMBRE = 'nombreconfig';
    const E_VALOR = 'valorconfig';
    const E_TIPO = 'tipoconfig';
    const E_PERIODO = 'periodo';    
    const E_GUARDAR = 'guardar';
    const E_CANCELAR = 'cancelar';

    /**
     * Objeto del registro a modificar.
     * 
     * @var Zend_Db_Table_Row
     */
    private $config;
    
    /**
     * Valores de la matriz para datos lógicos.
     * 
     * @var array
     */
    public static $booleanOptions = array('TRUE' => 'Verdadero', 'FALSE' => 'Falso');
    
    /**
     * Valores del intervalo de tiempo
     * 
     * @var array
     */
    public static $intervalOptions = array('day' => 'Día', 'mon' => 'Mes', 'year' => 'Año');

    /**
     * Constructor del formulario
     * 
     * @param integer $codigo Código de la variable de configuración.
     * @param mixed $options
     */
    public function __construct($codigo, $options = null)
    {
        $this->config = Application_Model_DbTable_Configuracion::findOneById($codigo);
        parent::__construct($options);        
    }
    
    /**
     * Inicialización
     */
    public function init()
    {
        $this->setName('configform')
             ->setAction($this->getView()->url())
             ->setLegend('Modificación de la Variable de Configuración');

        $nomb = new Zend_Form_Element_Textarea(self::E_NOMBRE);
        $nomb->setLabel('Variable:')
             ->setAttrib('rows', 3)
             ->setAttrib('style', 'width: 98%;')
             ->addValidator('StringLength', false, array('min' => 1, 'max' => 256, 'encoding' => $this->getView()->getEncoding()))
             ->addFilter('StringTrim')
             ->setRequired()
             ->setValue($this->config->nombre);
        $this->addElement($nomb);
        
        /*
         * Crea los elementos de acuerdo al tipo de datos
         */
        switch ($this->config->tipo_dato) {
            case Application_Model_DbTable_Configuracion::T_BOOLEAN:
                $valo = new Zend_Form_Element_Radio(self::E_VALOR);
                $valo->setMultiOptions(self::$booleanOptions)
                     ->setValue($this->config->valor)
                     ->setSeparator('');
                break;
            case Application_Model_DbTable_Configuracion::T_DATE:
                $valo = new Fmo_Form_Element_DatePicker(self::E_VALOR);
                $valo->setValue(Fmo_Util::stringToZendDate($this->config->valor)->toString('dd/MM/yyyy'));
                break;
            case Application_Model_DbTable_Configuracion::T_TIMESTAMP_WITHOUT_TIME_ZONE:
                $valo = new Zend_Form_Element_Text(self::E_VALOR);                
                $valo->addValidator('Date', false, array('format' => 'dd/MM/yyyy HH:mm'))
                     ->setValue(Fmo_Util::stringToZendDate($this->config->valor, 'yyyy/MM/dd HH:mm ZZZZ')->toString('dd/MM/yyyy HH:mm'));
                break;
            case Application_Model_DbTable_Configuracion::T_TIME_WITHOUT_TIME_ZONE:
                $valo = new Zend_Form_Element_Text(self::E_VALOR);                
                $valo->addValidator('Date', false, array('format' => 'HH:mm'))
                     ->setValue(Fmo_Util::stringToZendDate($this->config->valor, 'HH:mm')->toString('HH:mm'));
                break;            
            case Application_Model_DbTable_Configuracion::T_FLOAT:
            case Application_Model_DbTable_Configuracion::T_NUMERIC:
            case Application_Model_DbTable_Configuracion::T_INTEGER:
            case Application_Model_DbTable_Configuracion::T_SMALLINT:
            case Application_Model_DbTable_Configuracion::T_INTERVAL:
                $valo = new Zend_Form_Element_Text(self::E_VALOR);
                $valo->setAttrib('maxlength', '5')
                     ->setAttrib('size', '15');
                if ($this->config->tipo_dato == Application_Model_DbTable_Configuracion::T_FLOAT || $this->config->tipo_dato == Application_Model_DbTable_Configuracion::T_NUMERIC) {
                    $valo->addValidator('Float', true);
                } else {
                    $valo->addValidator('Int', true)
                         ->addValidator('Between', false, array('min' => -10000, 'max' => 10000));
                }
                if ($this->config->tipo_dato == Application_Model_DbTable_Configuracion::T_INTERVAL) {
                    list($valorPeriodo, $tipoPeriodo) = explode(' ', str_replace(array('mons', 'days', 'years'), array('mon', 'day', 'year'), $this->config->valor));
                    $peri = new Zend_Form_Element_Radio(self::E_PERIODO);
                    $peri->setMultiOptions(self::$intervalOptions)
                         ->setLabel('Unidad de Tiempo:')
                         ->setRequired()
                         ->setSeparator('')
                         ->setValue($tipoPeriodo);
                    $this->addElement($peri);
                    $valo->setValue($valorPeriodo);
                } else {
                    $valo->setValue(Zend_Locale_Format::toNumber($this->config->valor));
                }
                break;
            case Application_Model_DbTable_Configuracion::T_LIST:
                $valo = new Zend_Form_Element_Select(self::E_VALOR);
                $valo->setValue($this->config->valor);
                foreach ($this->config->findDependentRowset('Application_Model_DbTable_ConfiguracionLista') as $row) {
                    $valo->addMultiOption($row->id_lista, $row->nombre);
                }
                break;
            case Application_Model_DbTable_Configuracion::T_ARRAY:
                $valo = new Zend_Form_Element_Textarea(self::E_VALOR);
                $valo->setAttrib('rows', '5')
                     ->setAttrib('style', 'width: 98%')
                     ->addValidator('StringLength', false, array('min' => 1, 'encoding' => $this->getView()->getEncoding()))
                     ->addFilter('StringTrim', array('charlist' => '{}'))
                     ->setValue($this->config->valor);
                break;
            default: // texto
                $valo = new Zend_Form_Element_Textarea(self::E_VALOR);
                $valo->setAttrib('rows', '5')
                     ->setAttrib('style', 'width: 98%')
                     ->addValidator('StringLength', false, array('min' => 1, 'max' => 2048, 'encoding' => $this->getView()->getEncoding()))
                     ->setValue($this->config->valor);
                break;
        }
        $valo->setLabel('Valor:')
             ->setDescription('El tipo de dato es ' . $this->getView()->stringToLower(Application_Model_DbTable_Configuracion::$tiposDatos[$this->config->tipo_dato]))
             ->setRequired();
        $this->addElement($valo);       
        
        $guardar = new Zend_Form_Element_Submit(self::E_GUARDAR);
        $guardar->setLabel('Guardar')
                ->setIgnore(true);
        $this->addElement($guardar);

        $cancelar = new Zend_Form_Element_Submit(self::E_CANCELAR);
        $cancelar->setLabel('Cancelar')
                 ->setIgnore(true);
        $this->addElement($cancelar);

        $this->setCustomDecorators();
    }
    
    /**
     * Método para guardar la información del formulario.
     * 
     * @return Application_Form_Configuracion
     */
    public function saveEditar(array $data)
    {
        $valid = false;
        if ($this->isValid($data)) {
            $this->config->nombre = $this->getValue(self::E_NOMBRE);
            $valorMensaje = $this->getValue(self::E_VALOR);                
            switch ($this->config->tipo_dato) {
                case Application_Model_DbTable_Configuracion::T_BOOLEAN:
                    $this->config->valor = $this->getValue(self::E_VALOR);
                    $valorMensaje = self::$booleanOptions[$this->getValue(self::E_VALOR)];
                    break;
                case Application_Model_DbTable_Configuracion::T_DATE:
                    $this->config->valor = Fmo_Util::stringToZendDate($this->getValue(self::E_VALOR), 'dd/MM/yyyy')->toString('yyyy-MM-dd');
                    break;
                case Application_Model_DbTable_Configuracion::T_TIMESTAMP_WITHOUT_TIME_ZONE:
                    $this->config->valor = Fmo_Util::stringToZendDate($this->getValue(self::E_VALOR), 'dd/MM/yyyy HH:mm:ss ZZZZ')->toString('yyyy-MM-dd HH:mm:ss');
                    break;
                case Application_Model_DbTable_Configuracion::T_TIME_WITHOUT_TIME_ZONE:
                    $this->config->valor = Fmo_Util::stringToZendDate($this->getValue(self::E_VALOR), 'HH:mm')->toString('HH:mm');
                    break;            
                case Application_Model_DbTable_Configuracion::T_INTERVAL:
                    $this->config->valor = new Zend_Db_Expr("CAST('{$this->getValue(self::E_VALOR)} {$this->getValue(self::E_PERIODO)}' AS INTERVAL)");
                    $valorMensaje .= ' ' . self::$intervalOptions[$this->getValue(self::E_PERIODO)];
                    break;
                case Application_Model_DbTable_Configuracion::T_FLOAT:
                case Application_Model_DbTable_Configuracion::T_NUMERIC:
                case Application_Model_DbTable_Configuracion::T_INTEGER:
                case Application_Model_DbTable_Configuracion::T_SMALLINT:
                    $this->config->valor = Zend_Filter::filterStatic($this->getValue(self::E_VALOR), 'LocalizedToNormalized');
                    break;
                case Application_Model_DbTable_Configuracion::T_LIST:
                    $this->config->valor = $this->getValue(self::E_VALOR);
                    $valorMensaje = $this->getElement(self::E_VALOR)->getMultiOption($this->getValue(self::E_VALOR));
                    break;
                case Application_Model_DbTable_Configuracion::T_ARRAY:
                    $this->config->valor = new Zend_Db_Expr("CAST('{" . str_replace(array('{', '}', "'", '"'), '', $this->getValue(self::E_VALOR)) . "}' AS TEXT[])");
                    break;
                default: // texto
                    $this->config->valor = $this->getValue(self::E_VALOR);
                    break;
            }        
            $this->config->save();
            $this->getMessageProcess()
                 ->add("Se actualizó satisfactoriamente la variable de configuración '{$this->getValue(self::E_NOMBRE)}' con el valor '{$valorMensaje}'");
            $valid = true;
        }
        return $valid;
    }
}