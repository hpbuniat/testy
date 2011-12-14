<?php
/**
 * Test class for Testy_Project_Test_Runner.
 * Generated by PHPUnit on 2011-12-03 at 18:39:21.
 */
class Testy_Project_Test_RunnerTest extends PHPUnit_Framework_TestCase {
    /**
     * Project name
     *
     * @var string
     */
    const PROJECT_NAME = 'Testy_Test';

    /**
     * @var Testy_Project_Test_Runner
     */
    protected $_object;

    /**
     * @var Testy_Project
     */
    protected $_oProject;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $oConfig = new stdClass();
        $oConfig->test = 'cd /tmp; echo ' . Testy_Project_Test_Runner::FILE_PLACEHOLDER;
        $oConfig->path = '/tmp';
        $oConfig->find = '*';

        $this->_oProject = new Testy_Project(self::PROJECT_NAME);
        $this->_oProject->config($oConfig);
        $this->_object = new Testy_Project_Test_Runner($this->_oProject, array(
            __FILE__
        ), '.');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
    }

    /**
     * Test setting repeat
     */
    public function testRepeat() {
        $this->assertInstanceOf('Testy_Project_Test_Runner', $this->_object->repeat());
    }

    /**
     * Test running the runnter
     */
    public function testRun() {
        $this->assertInstanceOf('Testy_Project_Test_Runner', $this->_object->run());
        $this->assertEquals(1, $this->_object->getCommands());
    }

    /**
     * Test getting the result
     */
    public function testGet() {
        $this->assertEquals('', $this->_object->get());
    }

    /**
     * Test multiple files
     */
    public function testMultiple() {
        $aFiles = array(
            __FILE__,
            __DIR__ . DIRECTORY_SEPARATOR . 'TestException.php'
        );
        $oRunner = new Testy_Project_Test_Runner($this->_oProject, $aFiles, '.');

        $oRunner->run();
        $this->assertEquals(count($aFiles), $oRunner->getCommands());
    }
}
