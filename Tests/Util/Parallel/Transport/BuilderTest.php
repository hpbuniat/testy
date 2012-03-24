<?php
/**
 * Test class for Testy_Util_Parallel_Transport_Builder.
 * Generated by PHPUnit on 2012-03-24 at 14:59:34.
 */
class Testy_Util_Parallel_Transport_BuilderTest extends PHPUnit_Framework_TestCase {

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
    }

    /**
     * @dataProvider buildProvider
     */
    public function testBuild($sTransport, $sExpected) {
        $oTransport = Testy_Util_Parallel_Transport_Builder::build($sTransport);
        $this->assertInstanceOf('Testy_Util_Parallel_TransportInterface', $oTransport);
        $this->assertInstanceOf($sExpected, $oTransport);
    }

    /**
     * Data provider for build-test
     *
     * @return array
     */
    public function buildProvider() {
        return array(
            array(
                'shared',
                Testy_Util_Parallel_Transport_Builder::TRANSPORT_SHARED
            ),
            array(
                'file',
                Testy_Util_Parallel_Transport_Builder::TRANSPORT_FILE
            ),
            array(
                'memcache',
                Testy_Util_Parallel_Transport_Builder::TRANSPORT_MEMCACHE
            ),
            array(
                Testy_Util_Parallel_Transport_Builder::TRANSPORT_DEFAULT,
                Testy_Util_Parallel_Transport_Builder::TRANSPORT_SHARED
            )
        );
    }

    /**
     * Test that a unknown transport throws an exception
     */
    public function testBuildException() {
        try {
            Testy_Util_Parallel_Transport_Builder::build('blafasel');
            $this->fail('An exception should have been thrown, when creating a unknown Transport');
        }
        catch (Testy_Util_Parallel_Transport_Exception $oException) {
            $this->assertEquals($oException->getMessage(), Testy_Util_Parallel_Transport_Exception::UNKNOWN_TRANSPORT);
        }
    }
}
