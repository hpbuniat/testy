<?php
/**
 * Test class for Testy_ExceptionTest.
 * Generated by PHPUnit on2011-2012-12-03 at 18:39:21.
 */
class Testy_ExceptionTest extends PHPUnit_Framework_TestCase {

    /**
     * Test Exception-Creation
     */
    public function testCreation() {
        $oException = new Testy_Exception('TEST');
        $this->assertEquals('TEST', $oException->getMessage());
        $this->assertInstanceOf('Exception', $oException);
        $this->assertInstanceOf('Testy_Exception', $oException);
    }
}
