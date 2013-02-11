<?php
/**
 * Test class for \Testy\Project\File\Mapper.
 * Generated by PHPUnit on 2011-12-16 at 19:21:19.
 */
namespace Testy\Test\Project\File;

class MapperTest extends \PHPUnit_Framework_TestCase {

    /**
     * Test exception
     */
    public function testException() {
        try {
            new \Testy\Project\File\Mapper();
            $this->fail('an exception should have been thrown');
        }
        catch (\Testy\Exception $oSourceException) {
            $this->assertEquals(\Testy\Project\File\Mapper::SOURCE_ERROR, $oSourceException->getMessage());
        }

        try {
            new \Testy\Project\File\Mapper('cd ..', '/this/file/does/not/exist');
            $this->fail('an exception should have been thrown');
        }
        catch (\Testy\Exception $oNotFoundException) {
            $this->assertEquals(\Testy\Project\File\Mapper::SOURCE_ERROR, $oNotFoundException->getMessage());
        }

        try {
            $sFile = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Helper' . DIRECTORY_SEPARATOR . 'Project.php';
            $oMapper = new \Testy\Project\File\Mapper('echo ' . \Testy\Project\Test\Runner::FILE_PLACEHOLDER . ' {.php|Test.php}', $sFile);
            $oMapper->map();
            $this->fail('an exception should have been thrown');
        }
        catch (\Testy\Project\File\Exception $oMissinTestException) {
            $this->assertEquals(sprintf(\Testy\Project\File\Mapper::MISSING_TEST, $sFile), $oMissinTestException->getMessage());
        }
    }

    /**
     * @dataProvider providerFiles
     */
    public function testWorkflow($sFile, $sTestfile, $sCommand = '', $sExpectedCommand = '') {
        $oMapper = new \Testy\Project\File\Mapper($sCommand, $sFile);
        $this->assertInstanceOf('\\Testy\\Project\\File\\Mapper', $oMapper->map());
        $this->assertEquals($oMapper->get(), $sExpectedCommand);
        $this->assertEquals($oMapper->getTestFile(), $sTestfile);
        unset($oMapper);
    }

    /**
     * Dataprovider for testWorkflow
     */
    public function providerFiles() {
        return array(
            array(
                __FILE__,
                __FILE__,
            ),
            array(
                __FILE__,
                __FILE__,
                'echo ' . \Testy\Project\Test\Runner::FILE_PLACEHOLDER . ' {Testy|tests/Testy/Test} {.php|Test.php}',
                'echo ' . __FILE__
            ),
            array(
                __FILE__,
                __FILE__,
                'echo ' . \Testy\Project\Test\Runner::FILE_PLACEHOLDER,
                'echo ' . __FILE__
            ),
            array(
                str_replace('Test.php', '.php', str_replace('tests', 'src', str_replace('Test/', '', __FILE__))),
                __FILE__,
                'echo ' . \Testy\Project\Test\Runner::FILE_PLACEHOLDER . ' {src|tests} {Testy|Testy/Test} {.php|Test.php}',
                'echo ' . __FILE__
            )
        );
    }
}
