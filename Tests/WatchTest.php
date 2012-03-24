<?php

/**
 * Test class for Testy_Watch.
 * Generated by PHPUnit on 2011-10-22 at 23:00:50.
 */
class Testy_WatchTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Testy_Watch
     */
    protected $_object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->_object = new Testy_Watch();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
    }

    /**
     * Test simple add call
     */
    public function testAdd() {
        $oMock = $this->getMockBuilder('Testy_Project')->getMock();
        $oMock->expects($this->any())->method('isEnabled')->will($this->returnValue(true));

        $this->assertInstanceOf('Testy_Watch', $this->_object->add($oMock));
    }

    /**
     * Test the loop-call
     */
    public function testLoop() {
        $oMock = $this->getMockBuilder('Testy_Util_Parallel_Transport_File')->getMock();
        $this->assertInstanceOf('Testy_Watch', $this->_object->loop($oMock, 2));
    }
}
