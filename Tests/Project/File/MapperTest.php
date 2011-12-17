<?php
/**
 * Test class for Testy_Project_File_Mapper.
 * Generated by PHPUnit on 2011-12-16 at 19:21:19.
 */
class Testy_Project_File_MapperTest extends PHPUnit_Framework_TestCase {

    /**
     * Test exception
     */
    public function testException() {
        try {
            new Testy_Project_File_Mapper();
            $this->fail('an exception should have been thrown');
        }
        catch (Testy_Exception $e) {
            $this->assertEquals(Testy_Project_File_Mapper::SOURCE_ERROR, $e->getMessage());
        }

        try {
            new Testy_Project_File_Mapper('cd ..', '/this/file/does/not/exist');
            $this->fail('an exception should have been thrown');
        }
        catch (Testy_Exception $e) {
            $this->assertEquals(Testy_Project_File_Mapper::SOURCE_ERROR, $e->getMessage());
        }

        try {
            $sFile = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Testy' . DIRECTORY_SEPARATOR . 'AbstractNotifier.php';
            $oMapper = new Testy_Project_File_Mapper('echo ' . Testy_Project_Test_Runner::FILE_PLACEHOLDER . ' {Testy|Tests} {.php|Test.php}', $sFile);
            $oMapper->map();
            $this->fail('an exception should have been thrown');
        }
        catch (Testy_Exception $e) {
            $this->assertEquals(sprintf(Testy_Project_File_Mapper::MISSING_TEST, $sFile), $e->getMessage());
        }
    }

    /**
     * @dataProvider providerFiles
     */
    public function testWorkflow($sFile, $sTestfile, $sCommand = '', $sExpectedCommand = '') {
        $oMapper = new Testy_Project_File_Mapper($sCommand, $sFile);
        $this->assertInstanceOf('Testy_Project_File_Mapper', $oMapper->map());
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
                'echo ' . Testy_Project_Test_Runner::FILE_PLACEHOLDER . ' {Testy|Tests} {.php|Test.php}',
                'echo ' . __FILE__
            ),
            array(
                __FILE__,
                __FILE__,
                'echo ' . Testy_Project_Test_Runner::FILE_PLACEHOLDER,
                'echo ' . __FILE__
            ),
            array(
                str_replace('Test.php', '.php', str_replace('Tests', 'Testy', __FILE__)),
                __FILE__,
                'echo ' . Testy_Project_Test_Runner::FILE_PLACEHOLDER . ' {Testy|Tests} {.php|Test.php}',
                'echo ' . __FILE__
            )
        );
    }
}
