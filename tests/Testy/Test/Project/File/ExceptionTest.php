<?php
/**
 * Test class for ExceptionTest.
 * Generated by PHPUnit on 2011-12-03 at 18:39:21.
 */
namespace Testy\Test\Project\File;

class ExceptionTest extends \PHPUnit_Framework_TestCase {

    /**
     * Test Exception-Creation
     */
    public function testCreation() {
        $oException = new \Testy\Project\File\Exception('TEST');
        $this->assertEquals('TEST', $oException->getMessage());
        $this->assertInstanceOf('\\Exception', $oException);
        $this->assertInstanceOf('\\Testy\\Exception', $oException);
    }
}
