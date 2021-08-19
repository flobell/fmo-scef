<?php

require_once 'PHPExcel/IOFactory.php';

/**
 * Description of FacturacionMasiva
 *
 * @author juanfd
 */
class Application_Form_AutorizadoMasivo extends Fmo_Form_Abstract
{
    const E_ARCHIVO        = 'archivoMasivo';
    const E_CARGAR_ARCHIVO = 'btnCargarArchivo';

    public function init()
    {
        $this->setName('Carga Autorizados Masivo')
            ->setAction($this->getView()->url())
            ->setEnctype(self::ENCTYPE_MULTIPART)
            ->setLegend('Carga Autorizados Masivo');

        $arc = new Zend_Form_Element_File(self::E_ARCHIVO);
        $arc->setLabel('Archivo:')
            ->addValidator('File_Size', false, array('min' => '1kB', 'max' => '8MB'))
            //->addValidator('File_MimeType', false, 'application/vnd.ms-excel,application/vnd.ms-office,application/vnd.oasis.opendocument.spreadsheet')
            ->addValidator('File_Extension', false, 'xls,ods,xlsx')
            ->setDescription('Seleccione un archivo de Hoja de Cálculo (formatos válidos: xls y ods)')
            ->setRequired();
        $this->addElement($arc);

        $cargarArc = new Zend_Form_Element_Button(self::E_CARGAR_ARCHIVO);
        $cargarArc->setLabel('Cargar Archivo')
                ->setAttrib('class', 'btn btn-success')
                ->setAttrib('type', 'submit')
                ->setAttrib('style', 'margin-top:10px !important;')
                //->setAttrib('disabled', 'disabled')
                ->setAttrib('onclick', "$('#pCargar').css('display', 'none');$('#loading').css('display', 'block');")
                ->setValue('Cargar Archivo');
        $this->addElement($cargarArc);

        $this->setCustomDecorators();
    }

    public function cargarArchivo()
    {
        try {
            ini_set('max_execution_time', 0);
            //ini_set('memory_limit', '2048M');
            $result  = array();
            $archivo = $this->getElement(self::E_ARCHIVO);

            if ($archivo->receive()) {
                $xls = PHPExcel_IOFactory::load($archivo->getFileName());
                $registros = $xls->setActiveSheetIndex()->toArray();

                if (empty($registros[0][0]) || empty($registros[2][2])) {
                    $archivo->addError('El archivo no contiene registros.');
                    throw new Exception('El archivo no contiene registros.', -1);
                } else {
                    $usuario    = Fmo_Model_Seguridad::getUsuarioSesion()->{Fmo_Model_Personal::CEDULA};
                    $fecha      = new Zend_Date();
                    $ahora      = $fecha->toString('yyyy-MM-dd h:m:s');
                    $contReal   = 0;
                    $cont       = 0;
                    $contBuenos = 0;
                    $contMalos  = 0;

                    try {
                        $autorizado = new Application_Model_DbTable_Autorizado();
                        Zend_Db_Table::getDefaultAdapter()->beginTransaction();

                        foreach($registros as $trabajador) {
                            $contReal++;

                            if ($contReal > 2 && !empty($trabajador[1])) {
                                $cont++;
                                $row = $autorizado->createRow();

                                $ficha = (string)$trabajador[1];

                                if (!empty($ficha)) {
                                    $validado = $this->autorizadoValido($ficha);

                                    if ($validado !== true) {
                                        $result['errors'][] = array(
                                            'registro' => $contReal,
                                            'ficha'    => $ficha,
                                            'error'    => $validado
                                        );

                                        $contMalos++;
                                    } else {
                                        $contBuenos++;
                                        $row->ficha = $ficha;
                                        $row->cedula = $trabajador[2];
                                        $row->nombre = $trabajador[3];
                                        $row->tipo_acceso = $trabajador[4];
                                        $row->vehiculo = $trabajador[5];
                                        $row->estado = $trabajador[6];
                                        $row->municipio = $trabajador[7];
                                        $row->parroquia = $trabajador[8];
                                        $row->urb_sector = $trabajador[9];
                                        $row->punto_referencia = $trabajador[10];
                                        $row->tlf_cel = $trabajador[11];
                                        $row->tlf_cas = $trabajador[12];
                                        $row->tlf_ofi = $trabajador[13];
                                        $row->sector = $trabajador[14];
                                        $row->avenida = $trabajador[15];
                                        $row->calle_manzana = $trabajador[16];
                                        $row->tipo_vivienda = $trabajador[17];
                                        $row->nro_casa = $trabajador[18];
                                        $row->nro_edificio = $trabajador[19];
                                        $row->nro_piso = $trabajador[20];
                                        $row->localidad = $trabajador[21];
                                        $row->usu_crea = $usuario;
                                        $row->fecha_crea = $ahora;
                                        $row->save();
                                    }
                                } else {
                                    break;
                                }
                            }
                        }

                        $result['total']     = $cont;
                        $result['correctos'] = $contBuenos;
                        $result['errados']   = $contMalos;

                        Zend_Db_Table::getDefaultAdapter()->commit();
                    } catch (Exception $e) {
                        Zend_Db_Table::getDefaultAdapter()->rollBack();
                        throw new Exception($e->getMessage(), $e->getCode());
                    }
                }
            }  else {
                $archivo->addError('Falló la carga del archivo.');
            }

            // Eliminar archivo procesado
            //unlink($archivo->getFileName());

            return $result;
        } catch(Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    public function autorizadoValido($ficha)
    {
        $personal = new Fmo_Model_Personal();
        $trab     = $personal->addFilterByFicha($ficha)->findOne();

        if (empty($trab)) {
            return 'La ficha no pertenece a ningún trabajador.';
        }

        if (Application_Model_Trabajador::esAutorizado($ficha)) {
            return 'El trabajador ya existe entre los autorizados.';
        }

        return true;
    }
}