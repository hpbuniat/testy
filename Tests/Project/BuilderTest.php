<?php
/**
 * testy
 *
 * Copyright (c)2011-2012, Hans-Peter Buniat <hpbuniat@googlemail.com>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 * * Redistributions of source code must retain the above copyright
 * notice, this list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright
 * notice, this list of conditions and the following disclaimer in
 * the documentation and/or other materials provided with the
 * distribution.
 *
 * * Neither the name of Hans-Peter Buniat nor the names of his
 * contributors may be used to endorse or promote products derived
 * from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package testy
 * @author Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @copyright 2011-2012 Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */

/**
 * Test Project-Builder
 *
 * @author Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @copyright 2011-2012 Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version Release: @package_version@
 * @link https://github.com/hpbuniat/testy
 */
class Testy_Util_BuilderTest extends PHPUnit_Framework_TestCase {

    /**
     * An empty dummy config
     *
     * @var stdClass
     */
    protected $_oConfig;

    /**
     * Test-Project Name
     *
     * @var string
     */
    const PROJECT_NAME = 'foobar';

    /**
     * Setup
     */
    public function setUp() {
        $this->_oConfig = new stdClass();
        $this->_oConfig->test = 'phpunit';
    }

    /**
     * Test successful project creation
     */
    public function testBuildSuccess() {
        $oProject = Testy_Project_Builder::build(self::PROJECT_NAME, $this->_oConfig, array(
            $this->getMock('Testy_Notifier_Stdout')
        ));
        $this->assertInstanceOf('Testy_Project', $oProject);
        $this->assertInstanceOf('Testy_Util_Command', $oProject->getCommand());
        $this->assertEquals(self::PROJECT_NAME, $oProject->getName());
        unset($oProject);
    }

    /**
     * Test failure
     */
    public function testBuildFailed() {
        $oConfig = new stdClass();
        try {
            Testy_Project_Builder::build(self::PROJECT_NAME, $oConfig, array());
            $this->fail('an exception should have been thrown, if no test-command ist configured');
        }
        catch (Exception $e) {
            $this->assertStringEndsWith(self::PROJECT_NAME, $e->getMessage());
        }
    }
}
