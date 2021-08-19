<?php

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2012-12-10 at 00:50:58.
 */
class Fmo_UtilTest extends PHPUnit_Framework_TestCase
{
    private static $_toMail;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $sistema = Zend_Registry::get('sistema');
        self::$_toMail = isset($sistema['mail']['analista']) ? $sistema['mail']['analista'] : 'fmo_sis_desa@ferrominera.com';
    }

    public function test_SendEmail()
    {
        $filename1 = uniqid(sys_get_temp_dir() . DIRECTORY_SEPARATOR) . '.txt';
        $fp = fopen($filename1, 'w');
        fwrite($fp, __CLASS__);
        fclose($fp);

        $at1 = new Zend_Mime_Part(file_get_contents($filename1));
        $at1->type = 'text/plain';
        $at1->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
        $at1->encoding = Zend_Mime::ENCODING_BASE64;
        $at1->filename = 'prueba.txt';

        $util = new Fmo_Util();
        $reflectionOfUtil = new ReflectionClass($util);
        $method = $reflectionOfUtil->getMethod('_sendEMail');
        $method->setAccessible(true);
        $args = array('rafaelars@ferrominera.com', 'Prueba _sendEmail', 'Hola _sendEmail', $at1);
        $this->assertInstanceOf('Zend_Mail', $method->invokeArgs($util, $args));

        $filename2 = uniqid(sys_get_temp_dir() . DIRECTORY_SEPARATOR) . '.txt';
        $fp2 = fopen($filename2, 'w');
        fwrite($fp2, __FILE__);
        fclose($fp2);

        $at2 = new Zend_Mime_Part(file_get_contents($filename2));
        $at2->type = 'text/plain';
        $at2->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
        $at2->encoding = Zend_Mime::ENCODING_BASE64;
        $at2->filename = 'prueba2.txt';

        $args[2] = '<h1>HOLA MUNDO</h1>';
        $args[3] = array($at1, $at2);
        $args[4] = true;

        $this->assertInstanceOf('Zend_Mail', $method->invokeArgs($util, $args));
        try {
            $method->invokeArgs($util, array(null, 'Prueba _sendEmail', 'Hola _sendEmail'));
            $this->assertFalse(true, 'No se debe admitir remitentes vacíos');
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true, $e->getMessage());
        }
        try {
            $method->invokeArgs($util, array('rafaelars@ferrominera.com', '', 'Hola _sendEmail'));
            $this->assertFalse(true, 'No se debe admitir asunto vacío');
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true, $e->getMessage());
        }
        try {
            $method->invokeArgs($util, array('rafaelars@ferrominera.com', 'Hola', null));
            $this->assertFalse(true, 'No se debe admitir texto vacío');
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true, $e->getMessage());
        }
        try {
            $method->invokeArgs($util, array('rafaelars@ferrominera.com', 'Hola', null));
            $this->assertFalse(true, 'No se debe admitir texto vacío');
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true, $e->getMessage());
        }
        $mailCfgOri = Zend_Registry::get('mail');
        $mailCfgCpy1 = $mailCfgOri;
        try {
            unset($mailCfgCpy1['charset']);
            Zend_Registry::set('mail', $mailCfgCpy1);
            $method->invokeArgs($util, $args);
            $this->assertFalse(true, "No se debe admitir 'charset' vacío");
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true, $e->getMessage());
        }
        Zend_Registry::set('mail', $mailCfgOri);
        $mailCfgCpy2 = $mailCfgOri;
        try {
            unset($mailCfgCpy2['defaultFrom']['email']);
            Zend_Registry::set('mail', $mailCfgCpy2);
            $method->invokeArgs($util, $args);
            $this->assertFalse(true, "No se debe admitir 'defaultFrom.email' vacío");
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true, $e->getMessage());
        }
        Zend_Registry::set('mail', $mailCfgOri);
        $mailCfgCpy3 = $mailCfgOri;
        try {
            unset($mailCfgCpy3['defaultFrom']['name']);
            Zend_Registry::set('mail', $mailCfgCpy3);
            $method->invokeArgs($util, $args);
            $this->assertFalse(true, "No se debe admitir 'defaultFrom.name' vacío");
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true, $e->getMessage());
        }
        Zend_Registry::set('mail', $mailCfgOri);
        unlink($filename1);
        unlink($filename2);
    }

    public function testGetTranslationUnits()
    {
        $this->assertNotEmpty(Fmo_Util::getTranslationUnits());
    }

    public function testTranslateAgeEnglighToSpanish()
    {
        $this->assertEquals('36 años 9 meses 22 días', Fmo_Util::translateAgeEnglighToSpanish('36 years 9 mons 22 days'));
        $this->assertEquals('36 años 9 meses 1 día', Fmo_Util::translateAgeEnglighToSpanish('36 years 9 mons 1 day'));
        $this->assertEquals('1 año 1 mes 1 día', Fmo_Util::translateAgeEnglighToSpanish('1 year 1 mon 1 day'));
    }

    /**
     * @covers Fmo_Util::sendEmailText
     * @todo   Implement testSendEmailText().
     */
    public function testSendEmailText()
    {
        $title = 'Prueba de ' . __METHOD__;
        $body = 'Esto es una prueba. Fecha de ejecución ' . date(DATE_W3C);
        $result = Fmo_Util::sendEmailText(self::$_toMail, $title, $body);
        $this->assertTrue($result instanceof Zend_Mail);
        try {
            Fmo_Util::sendEmailText(null, $title, $body);
            $this->assertTrue(false);
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true, $e->getMessage());
        }
        try {
            Fmo_Util::sendEmailText(self::$_toMail, null, $body);
            $this->assertTrue(false);
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true, $e->getMessage());
        }
        try {
            Fmo_Util::sendEmailText(self::$_toMail, $title, null);
            $this->assertTrue(false);
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @covers Fmo_Util::sendEmailHtml
     * @todo   Implement testSendEmailHtml().
     */
    public function testSendEmailHtml()
    {
        $title = 'Prueba de ' . __METHOD__;
        $body = '<p style="color: blue;"><b>Esto es una prueba. Fecha de ejecución ' . date(DATE_W3C) . '</b></p>';
        $result = Fmo_Util::sendEmailHtml(self::$_toMail, $title, $body);
        $this->assertTrue($result instanceof Zend_Mail);
        try {
            Fmo_Util::sendEmailHtml(null, $title, $body);
            $this->assertTrue(false);
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true, $e->getMessage());
        }
        try {
            Fmo_Util::sendEmailHtml(self::$_toMail, null, $body);
            $this->assertTrue(false);
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true, $e->getMessage());
        }
        try {
            Fmo_Util::sendEmailHtml(self::$_toMail, $title, null);
            $this->assertTrue(false);
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @covers Fmo_Util::replaceTextInArray
     */
    public function testReplaceTextInArray()
    {
        $this->assertEquals('Esto es una prueba 1, 2 y 3', Fmo_Util::replaceTextInArray(array('{uno}' => '1', '{dos}' => '2', '{tres}' => '3'), 'Esto es una prueba {uno}, {dos} y {tres}'));
    }

    /**
     * @covers Fmo_Util::calcularDomingoSemanaSanta
     * @todo   Implement testCalcularDomingoSemanaSanta().
     */
    public function testCalcularDomingoSemanaSanta()
    {
        $this->assertNotEmpty(Fmo_Util::calcularDomingoSemanaSanta());
        $this->assertEquals('31/03/2013', Fmo_Util::calcularDomingoSemanaSanta('2013')->toString(Zend_Date::DATES));
        $this->assertEquals('08/04/2012', Fmo_Util::calcularDomingoSemanaSanta('2012')->toString(Zend_Date::DATES));
        $this->assertEquals('10/04/1678', Fmo_Util::calcularDomingoSemanaSanta('1678')->toString(Zend_Date::DATES));
        $this->assertEquals('29/03/1750', Fmo_Util::calcularDomingoSemanaSanta('1750')->toString(Zend_Date::DATES));
        $this->assertEquals('31/03/1850', Fmo_Util::calcularDomingoSemanaSanta('1850')->toString(Zend_Date::DATES));
        $this->assertEquals('12/04/2150', Fmo_Util::calcularDomingoSemanaSanta('2150')->toString(Zend_Date::DATES));
        $this->assertEquals('21/04/2250', Fmo_Util::calcularDomingoSemanaSanta('2250')->toString(Zend_Date::DATES));
        try {
            Fmo_Util::calcularDomingoSemanaSanta('aaaaaaaaaaaa');
            $this->assertTrue(false);
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
        try {
            Fmo_Util::calcularDomingoSemanaSanta(1582);
            $this->assertTrue(false, 'No debe aceptar año menor o igual a 1584');
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true, $e->getMessage());
        }
        try {
            Fmo_Util::calcularDomingoSemanaSanta(2300);
            $this->assertTrue(false, 'No debe aceptar año mayor o igual a 2300');
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @covers Fmo_Util::arrayToObject
     * @todo   Implement testArrayToObject().
     */
    public function testArrayToObject()
    {
        $expected = new stdClass();
        $expected->uno = 1;
        $expected->dos = 'es un 2';
        $expected->tres = new stdClass();
        $expected->tres->cuatro = 'four';
        $this->assertEquals($expected, Fmo_Util::arrayToObject(array('uno' => 1, 'dos' => 'es un 2', 'tres' => array('cuatro' => 'four'))));
        $this->assertEquals('uno', Fmo_Util::arrayToObject('uno'));
        $this->assertEquals(new stdClass(), Fmo_Util::arrayToObject(array()));
    }

    /**
     * @covers Fmo_Util::arrayMbConvertCase
     * @todo   Implement testArrayMbConvertCase().
     */
    public function testArrayMbConvertCase()
    {
        $array = array('uno' => 'únó', 'dos' => 'döS', 'tres' => array('tRéñ al Mundo', 'fino', 2), true);
        $this->assertEquals(array('uno' => 'únó', 'dos' => 'dös', 'tres' => array('tréñ al mundo', 'fino', 2), true), Fmo_Util::arrayMbConvertCase($array, MB_CASE_LOWER));
        $this->assertEquals(array('uno' => 'ÚNÓ', 'dos' => 'DÖS', 'tres' => array('TRÉÑ AL MUNDO', 'FINO', 2), true), Fmo_Util::arrayMbConvertCase($array, MB_CASE_UPPER));
        $this->assertEquals(array('uno' => 'Únó', 'dos' => 'Dös', 'tres' => array('Tréñ Al Mundo', 'Fino', 2), true), Fmo_Util::arrayMbConvertCase($array, MB_CASE_TITLE));
    }

    /**
     * @covers Fmo_Util::arrayImplode
     * @todo   Implement testArrayImplode().
     */
    public function testArrayImplode()
    {
        $array = array('1' => 'UNO', 'dos', 'tres' => 'TRESSSS');
        $this->assertEquals('1,UNO;2,dos;tres,TRESSSS', Fmo_Util::arrayImplode(',', ';', $array));
        $this->assertEquals(null, Fmo_Util::arrayImplode(',', ';', array()));
    }

    /**
     * @covers Fmo_Util::left
     * @todo   Implement testLeft().
     */
    public function testLeft()
    {
        $this->assertEquals('HOL', Fmo_Util::left('HOLA MUNDO', 3));
        $this->assertEquals('HOLA MUNDO', Fmo_Util::left('HOLA MUNDO', 1000));
        $this->assertEquals('', Fmo_Util::left('HOLA MUNDO', -1));
        $this->assertEquals('', Fmo_Util::left('HOLA MUNDO', 0));
        $this->assertEquals('', Fmo_Util::left(null, 3));
        $this->assertEquals('HñL', Fmo_Util::left('HñLA MUNDO', 3));
    }

    /**
     * @covers Fmo_Util::right
     * @todo   Implement testRight().
     */
    public function testRight()
    {
        $this->assertEquals('NDO', Fmo_Util::right('HOLA MUNDO', 3));
        $this->assertEquals('HOLA MUNDO', Fmo_Util::right('HOLA MUNDO', 1000));
        $this->assertEquals('', Fmo_Util::right('HOLA MUNDO', -1));
        $this->assertEquals('', Fmo_Util::right('HOLA MUNDO', 0));
        $this->assertEquals('', Fmo_Util::right(null, 3));
        $this->assertEquals('NDóÑ', Fmo_Util::right('HOLA MUNDóÑ', 4));
    }

    /**
     * @covers Fmo_Util::arrayRenameKey
     * @todo   Implement testArrayRenameKey().
     */
    public function testArrayRenameKey()
    {
        $this->assertEquals(array('uno' => 'UNO', 'dos' => 2), Fmo_Util::arrayRenameKey(array('uno1' => 'UNO', 'dos' => 2), 'uno1', 'uno'));
        $this->assertFalse(Fmo_Util::arrayRenameKey(array('uno1' => 'UNO', 'dos' => 2), 'uno2', 'uno'));
    }

    /**
     * @covers Fmo_Util::stringToZendDate
     * @todo   Implement testStringToZendDate().
     */
    public function testStringToZendDate()
    {
        $this->assertEquals(new Zend_Date('2013-12-24 16:52:34'), Fmo_Util::stringToZendDate('2013-12-24 16:52:34'));
        $this->assertEquals(new Zend_Date(date(DATE_W3C), Zend_Date::W3C), Fmo_Util::stringToZendDate(date(DATE_W3C), Zend_Date::W3C));
    }

    /**
     * @covers Fmo_Util::fileClassNameExists
     * @todo   Implement testFileClassNameExists().
     */
    public function testFileClassNameExists()
    {
        $this->assertTrue(Fmo_Util::fileClassNameExists('Zend_Date'));
        $this->assertFalse(Fmo_Util::fileClassNameExists('Zend_DateNo'));
    }

    /**
     * @covers Fmo_Util::dateDiff
     * @todo   Implement testDateDiff().
     */
    public function testDateDiff()
    {
        $expected = array('second' => 1, 'minute' => 1, 'hour' => 1, 'day' => 1,
                          'month' => 1, 'year' => 1);
        $date1 = new Zend_Date();
        $date2 = new Zend_Date();
        $date2->addYear(1)
              ->addMonth(1)
              ->addDay(1)
              ->addHour(1)
              ->addMinute(1)
              ->addSecond(1);
        $this->assertEquals($expected, Fmo_Util::dateDiff($date1->getTimestamp(), $date2->getTimestamp()));
        $this->assertEquals($expected, Fmo_Util::dateDiff($date2->getTimestamp(), $date1->getTimestamp()));
    }

    /**
     * @covers Fmo_Util::edad
     * @todo   Implement testEdad().
     */
    public function testEdad()
    {
        $this->assertEquals(Fmo_Util::edad('1976-3-13', '2012-5-13'), '36 años 2 meses');
        $this->assertEquals(Fmo_Util::edad('1976-3-13', '2009-5-27'), '33 años 2 meses 14 días');
        $this->assertEquals(Fmo_Util::edad('1976-3-13', '1976-3-14'), '1 día');
        $this->assertEquals(Fmo_Util::edad('1976-3-13', '1977-3-13'), '1 año');
        $this->assertEquals(Fmo_Util::edad('1976-3-13', '1976-4-13'), '1 mes');
        $this->assertNotEmpty(Fmo_Util::edad('1976-3-13'));
    }

    /**
     * @covers Fmo_Util::stringToUpper
     * @todo   Implement testStringToUpper().
     */
    public function testStringToUpper()
    {
        $this->assertEquals('ÁÉÍÓÚÑ', Fmo_Util::stringToUpper('áéíóúñ'));
    }

    /**
     * @covers Fmo_Util::stringToLower
     * @todo   Implement testStringToLower().
     */
    public function testStringToLower()
    {
        static $expected = 'hola múndo';
        $this->assertEquals($expected, Fmo_Util::stringToLower('hola múndo'));
        $this->assertEquals($expected, Fmo_Util::stringToLower('hOlA MÚnDo'));
        $this->assertEquals($expected, Fmo_Util::stringToLower('HOLA MÚNDO'));
        $this->assertEquals($expected, Fmo_Util::stringToLower($expected));
    }

    /**
     * @covers Fmo_Util::stringToTitle
     * @todo   Implement testStringToTitle().
     */
    public function testStringToTitle()
    {
        static $expected = 'Hola Mundo';
        $this->assertEquals($expected, Fmo_Util::stringToTitle('hola mundo'));
        $this->assertEquals($expected, Fmo_Util::stringToTitle('hOlA MunDo'));
        $this->assertEquals($expected, Fmo_Util::stringToTitle('HOLA MUNDO'));
        $this->assertEquals($expected, Fmo_Util::stringToTitle($expected));
    }

    /**
     * @covers Fmo_Util::pgArrayParse
     * @todo   Implement testPgArrayParse().
     */
    public function testPgArrayParse()
    {
        $this->assertEmpty(Fmo_Util::pgArrayParse(null));
        $this->assertEmpty(Fmo_Util::pgArrayParse('prueba'));
        $this->assertEquals(array('UNO', 'DOS', 'tres'), Fmo_Util::pgArrayParse(Zend_Db_Table::getDefaultAdapter()->fetchOne("SELECT ARRAY['UNO', 'DOS', 'tres'] AS \"array\"")));
        $this->assertEquals(array(array('UNO', 'DOS', 'tres'), array('CUATRO', 'cinCO', 'Seis')), Fmo_Util::pgArrayParse(Zend_Db_Table::getDefaultAdapter()->fetchOne("SELECT ARRAY[['UNO', 'DOS', 'tres'], ['CUATRO', 'cinCO', 'Seis']] AS array")));
    }

    /**
     * @covers Fmo_Util::splitTextWordsToArray
     * @todo   Implement testSplitTextWordsToArray().
     */
    public function testSplitTextWordsToArray()
    {
        $this->assertEquals(Fmo_Util::splitTextWordsToArray('¡Hola Mundo!'), array('¡Hola', 'Mundo!'));
    }

    /**
     * @covers Fmo_Util::implodeToTextSpanish
     * @todo   Implement testImplodeToTextSpanish().
     */
    public function testImplodeToTextSpanish()
    {
        $this->assertEmpty(Fmo_Util::implodeToTextSpanish(array()));
        $this->assertEquals('lista', Fmo_Util::implodeToTextSpanish(array('lista')));
        $this->assertEquals('lista e iluminación', Fmo_Util::implodeToTextSpanish(array('lista', 'iluminación')));
        $this->assertEquals('lista u otra', Fmo_Util::implodeToTextSpanish(array('lista', 'otra'), FALSE));
        $this->assertEmpty(Fmo_Util::implodeToTextSpanish(array('uno', array('dos'))));
        $this->assertEquals('uno, dos e HIjo', Fmo_Util::implodeToTextSpanish(array('uno', 'dos', 'HIjo')));
        $this->assertEquals('uno, dos e hIjo', Fmo_Util::implodeToTextSpanish(array('uno', 'dos', 'hIjo')));
        $this->assertEquals('uno, dos y Ermitaño', Fmo_Util::implodeToTextSpanish(array('uno', 'dos', 'Ermitaño')));
        $this->assertEquals('uno, dos u Hormiga', Fmo_Util::implodeToTextSpanish(array('uno', 'dos', 'Hormiga'), FALSE));
        $this->assertEquals('uno, dos y tres', Fmo_Util::implodeToTextSpanish(array('uno', 'dos', 'tres')));
    }

    /**
     * @covers Fmo_Util::getUriSoap
     * @todo   Implement testGetUriSoap().
     */
    public function testGetUriSoap()
    {
        $view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view;
        $expected = $view->serverUrl($view->baseUrl(Fmo_Model_Seguridad::PATH_WSDL));
        $this->assertEquals($expected, Fmo_Util::getUriSoap());
    }

    /**
     * @covers Fmo_Util::getFileInfo
     * @todo   Implement testGetFileInfo().
     */
    public function testGetFileInfo()
    {
        $uid = uniqid();
        $filename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "$uid.txt";
        $fp = fopen($filename, 'w');
        fwrite($fp, 'Esto es una prueba');
        fclose($fp);
        $actual = Fmo_Util::getFileInfo($filename);

        $expect = array(
            'file' => $filename,
            'filename' => $uid,
            'contents' => 'Esto es una prueba',
            'md5' => '77fb12b14cce4338f6c6873946d22da6',
            'size' => 18,
            'basename' => "$uid.txt",
            'dirname' => sys_get_temp_dir(),
            'extension' => 'txt',
            'mimetype' => 'text/plain'
        );
        unlink($filename);
        $this->assertEquals($expect, $actual);
        $this->assertFalse(Fmo_Util::getFileInfo('no_existe.txt'));
    }

}
