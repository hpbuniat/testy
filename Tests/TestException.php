<?php
/**
 * test case.
 */
class Testy_TestException extends PHPUnit_Framework_TestCase {

    /**
     * Test Exception-Creation
     */
    public function testCreation() {
        $oException = new Testy_Exception('TEST');
        $this->assertEquals('TEST', $oException->getMessage());
        $this->assertInstanceOf('Exception', $oException);
    }
}
