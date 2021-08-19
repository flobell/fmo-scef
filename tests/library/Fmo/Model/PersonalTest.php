<?php

/**
 * Test class for Fmo_Model_Personal.
 * Generated by PHPUnit on 2013-01-09 at 15:02:36.
 */
class Fmo_Model_PersonalTest extends PHPUnit_Framework_TestCase
{

    /**
     * @covers Fmo_Model_Personal::findOneByFicha
     * @todo Implement testFindOneByFicha().
     */
    public function testFindOneByFicha()
    {
        $actual = Fmo_Model_Personal::findOneByFicha('8741');
        $this->assertEquals(12130304, $actual->{Fmo_Model_Personal::CEDULA});
    }

    /**
     * @covers Fmo_Model_Personal::findOneBySiglado
     * @todo Implement testFindOneBySiglado().
     */
    public function testFindOneBySiglado()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Fmo_Model_Personal::findOneByCedula
     * @todo Implement testFindOneByCedula().
     */
    public function testFindOneByCedula()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Fmo_Model_Personal::findByUserSession
     * @todo Implement testFindByUserSession().
     */
    public function testFindByUserSession()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Fmo_Model_Personal::findOneByCorreoElectronico
     * @todo Implement testFindOneByCorreoElectronico().
     */
    public function testFindOneByCorreoElectronico()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

}

?>