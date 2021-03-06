<?php
/**
 * Test class for \Testy\Project.
 * Generated by PHPUnit on 2011-10-23 at 17:54:53.
 */
namespace Testy\Test;

class ProjectTest extends \PHPUnit_Framework_TestCase {

    /**
     * Project name
     *
     * @var string
     */
    const PROJECT_NAME = 'Testy_Test';

    /**
     *
     * @var \Testy\Project
     */
    protected $_object;

    /**
     * The test-config
     *
     * @var \stdClass
     */
    protected $_oConfig;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    public function setUp() {
        $this->_object = new \Testy\Project(self::PROJECT_NAME);
        $this->assertInstanceOf('\\Testy\\Project', $this->_object->setCommand(\Testy\Test\Helper\Command::getSuccess()));

        $this->_oConfig = new \stdClass();
        $this->_oConfig->test = 'cd /tmp';
        $this->_oConfig->path = '/tmp';
        $this->_oConfig->find = '*';
        $this->_oConfig->syntax = 'echo ' . \Testy\Project\Test\Runner::FILE_PLACEHOLDER;
    }

    /**
     * Test the should-repeat getter
     *
     * @param boolean $bExpected
     * @param mixed $mRepeatValue
     *
     * @dataProvider shouldRepeatProvider
     */
    public function testShouldRepeat($bExpected, $mRepeatValue = null) {
        $this->_oConfig->repeat = $mRepeatValue;

        $this->assertInstanceOf('\\Testy\\Project', $this->_object->config($this->_oConfig));
        $this->assertEquals($bExpected, $this->_object->shouldRepeat());
    }

    /**
     * Dataprovider for testShouldRepeat
     */
    public function shouldRepeatProvider() {
        return array(
            array(
                true
            ),
            array(
                true,
                true
            ),
            array(
                false,
                false
            )
        );
    }

    /**
     * Test the should-syntax-check getter
     *
     * @param boolean $bExpected
     * @param mixed $mRepeatValue
     *
     * @dataProvider shouldSyntaxCheckProvider
     */
    public function testShouldSyntaxCheck($bExpected, $mRepeatValue = null) {
        unset($this->_oConfig->syntax);
        $this->_oConfig->syntax = $mRepeatValue;

        $this->assertInstanceOf('\\Testy\\Project', $this->_object->config($this->_oConfig));
        $this->assertEquals($bExpected, $this->_object->shouldSyntaxCheck());
    }

    /**
     * Dataprovider for testShouldSyntaxCheck
     */
    public function shouldSyntaxCheckProvider() {
        return array(
            array(
                false
            ),
            array(
                false,
                true
            ),
            array(
                false,
                false
            ),
            array(
                true,
                'php -l $file'
            )
        );
    }

    /**
     * Test adding a notifier
     */
    public function testAddNotifier() {
        $this->assertInstanceOf('\\Testy\\Project', $this->_object->addNotifier($this->getMock('\\notifyy\\Adapter\\Stdout')));
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
            $this->_object->config(new \stdClass());
            $this->fail('An exception should be raised, when setting the test-config');
        }
        catch (\Exception $e) {
            $this->assertStringEndsWith(self::PROJECT_NAME, $e->getMessage());
        }
    }

    /**
     * Test the check command
     *
     * @depends testAddNotifier
     */
    public function testCheck() {
        $this->assertInstanceOf('\\Testy\\Project', $this->_object->setCommand(\Testy\Test\Helper\Command::getSuccess('1' . PHP_EOL . '2' . PHP_EOL . '3')));
        $this->assertInstanceOf('\\Testy\\Project', $this->_object->config($this->_oConfig));
        $this->assertInstanceOf('\\Testy\\Project', $this->_object->check(0));
        $this->assertNotEmpty($this->_object->getFiles());
    }

    /**
     * Test run
     *
     * @depends testCheck
     */
    public function testRun() {
        $this->assertInstanceOf('\\Testy\\Project', $this->_object->config($this->_oConfig));
        $this->assertInstanceOf('\\Testy\\Project', $this->_object->setFiles(array(
            __FILE__
        )));
        $this->assertInstanceOf('\\Testy\\Project', $this->_object->run());
    }

    /**
     * Test getting a config-hash
     *
     * @depends testRun
     */
    public function testGetProjectHash() {
        $this->assertEquals(32, strlen($this->_object->getProjectHash()));

        $oProject = new \Testy\Project('test');
        $this->assertNotEquals($oProject->getProjectHash(), $this->_object->getProjectHash());
        unset($oProject);
    }

    /**
     * Test getting a config-hash
     *
     * @depends testRun
     */
    public function testIsEnabled() {
        $this->assertTrue($this->_object->isEnabled());

        $oConfig = new \stdClass();
        $oConfig->test = 'cd /tmp';
        $oConfig->path = '/tmp';
        $oConfig->find = '*';
        $oConfig->enabled = false;

        $oProject = new \Testy\Project('test');
        $oProject->config($oConfig);
        $this->assertFalse($oProject->isEnabled());
        unset($oProject);
    }

    /**
     * The the creation of the find-command
     *
     * @param  \stdClass $oFixture
     * @param  string $sExpected
     *
     * @dataProvider getFindCommandProvider
     */
    public function testGetFindCommand(\stdClass $oFixture, $sExpected) {
        $oProject = new \Testy\Project('test');
        $oProject->config($oFixture);

        $iTime = time();
        $sReturn = $oProject->getFindCommand($oFixture->path, $iTime);
        $this->assertEquals(sprintf($sExpected, date($oProject::FIND_DATE_FORMAT, $iTime)), $sReturn);
        unset($oProject);
    }

    /**
     * Provider for testGetFindCommand
     *
     * @return array
     */
    public function getFindCommandProvider() {
        $aFixtures = array();

        $oConfig = new \stdClass();
        $oConfig->test = 'cd /tmp';
        $oConfig->path = '/tmp';
        $oConfig->find = '*';
        $aFixtures[] = array(
            $oConfig,
            'find /tmp -type f \( -name "*" \) -newermt "%s"'
        );

        $oConfig = new \stdClass();
        $oConfig->test = 'cd /tmp';
        $oConfig->path = '/tmp /etc';
        $oConfig->find = '*';
        $aFixtures[] = array(
            $oConfig,
            'find /tmp /etc -type f \( -name "*" \) -newermt "%s"'
        );

        $oConfig = new \stdClass();
        $oConfig->test = 'cd /tmp';
        $oConfig->path = '/tmp /etc';
        $oConfig->find = '*,*.php';
        $aFixtures[] = array(
            $oConfig,
            'find /tmp /etc -type f \( -name "*" -o -name "*.php" \) -newermt "%s"'
        );

        return $aFixtures;
    }

    /**
     * Test calling the notifiy method
     *
     * @depends testAddNotifier
     */
    public function testNotify() {
        $this->assertInstanceOf('\\Testy\\Project', $this->_object->notify('', ''));
    }

    /**
     * Test the lint-command
     *
     * @dataProvider lintProvider
     */
    public function testLint($bExpected, $sCommand = 'Success') {
        $this->_oConfig->syntax = 'test';
        $this->assertInstanceOf('\\Testy\\Project', $this->_object->config($this->_oConfig));
        $this->assertTrue($this->_object->shouldSyntaxCheck());

        $sCommand = 'get' . ucfirst($sCommand);
        $this->assertInstanceOf('\\Testy\\Project', $this->_object->setCommand(\Testy\Test\Helper\Command::$sCommand()));

        $oRunner = new \Testy\Project\Test\Runner($this->_object, array(
            __FILE__
        ), $this->_oConfig);
        $this->assertEquals($bExpected, $this->_object->lint($oRunner));
        unset($oRunner, $sCommand);
    }

    /**
     * Dataprovider for testLint
     *
     * @return array
     */
    public function lintProvider() {
        return array(
            array(
                true,
                'Success'
            ),
            array(
                false,
                'Failure'
            )
        );
    }
}
