<?php
/**
 * Test class for Testy_Project.
 * Generated by PHPUnit on2011-2012-10-23 at 17:54:53.
 */
class Testy_ProjectTest extends PHPUnit_Framework_TestCase {

    /**
     * Project name
     *
     * @var string
     */
    const PROJECT_NAME = 'Testy_Test';

    /**
     * @var Testy_Project
     */
    protected $_object;

    /**
     * The test-config
     *
     * @var stdClass
     */
    protected $_oConfig;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->_object = new Testy_Project(self::PROJECT_NAME);
        $this->_oConfig = new stdClass();
        $this->_oConfig->test = 'cd /tmp';
        $this->_oConfig->path = '/tmp';
        $this->_oConfig->find = '*';
        $this->_oConfig->syntax = 'echo ' . Testy_Project_Test_Runner::FILE_PLACEHOLDER;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
    }

    /**
     * Test adding a notifier
     */
    public function testAddNotifier() {
        $this->assertInstanceOf('Testy_Project', $this->_object->addNotifier($this->getMock('Testy_Notifier_Stdout')));
    }

    /**
     * Test getting the project-name
     */
    public function testGetName() {
        $this->assertEquals(self::PROJECT_NAME, $this->_object->getName());
    }

    /**
     * Test setting a config
     */
    public function testConfig() {
        try {
            $this->_object->config(new stdClass());
            $this->fail('An exception should be raised, when setting the test-config');
        }
        catch(Exception $e) {
            $this->assertStringEndsWith(self::PROJECT_NAME, $e->getMessage());
        }
    }

    /**
     * Test the check command
     *
     * @depends testAddNotifier
     */
    public function testCheck() {
        $this->assertInstanceOf('Testy_Project', $this->_object->config($this->_oConfig));
        $this->assertInstanceOf('Testy_Project', $this->_object->check(0));
        $this->assertNotEmpty($this->_object->getFiles());
    }

    /**
     * Test run
     *
     * @depends testCheck
     */
    public function testRun() {
        $this->assertInstanceOf('Testy_Project', $this->_object->config($this->_oConfig));
        $this->assertInstanceOf('Testy_Project', $this->_object->setFiles(array(
            __FILE__
        )));
        $this->assertInstanceOf('Testy_Project', $this->_object->run());
    }

    /**
     * Test getting a config-hash
     *
     * @depends testRun
     */
    public function testGetProjectHash() {
        $this->assertEquals(32, strlen($this->_object->getProjectHash()));

        $oObject = new Testy_Project('test');
        $this->assertNotEquals($oObject->getProjectHash(), $this->_object->getProjectHash());
    }

    /**
     * Test getting a config-hash
     *
     * @depends testRun
     */
    public function testIsEnabled() {
        $this->assertTrue($this->_object->isEnabled());

        $oConfig = new stdClass();
        $oConfig->test = 'cd /tmp';
        $oConfig->path = '/tmp';
        $oConfig->find = '*';
        $oConfig->enabled = false;

        $oObject = new Testy_Project('test');
        $oObject->config($oConfig);
        $this->assertFalse($oObject->isEnabled());
    }

    /**
     * @depends testAddNotifier
     */
    public function testNotify() {
        $this->assertInstanceOf('Testy_Project', $this->_object->notify('', ''));
    }
}
