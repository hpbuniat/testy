<?php
/**
 * testy
 *
 * Copyright (c) 2011, Hans-Peter Buniat <hpbuniat@googlemail.com>.
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
 * @copyright 2011 Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */

/**
 * Test Command-Execution
 *
 * @author Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @copyright 2011 Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version Release: @package_version@
 * @link https://github.com/hpbuniat/testy
 */
class Testy_Util_CommandTest extends PHPUnit_Framework_TestCase {

    /**
     * Test Command-Setting via construct
     */
    public function testCommandConstruct() {
        $o = new Testy_Util_Command('dir');
        $this->assertInstanceOf('Testy_Util_Command', $o->execute());
        $this->asserttrue($o->isSuccess());
        $this->assertContains('testy.php', $o->get());
        $this->assertEquals(0, $o->status());
    }

    /**
     * Test Command-Setting via command-method
     */
    public function testCommandCommand() {
        $o = new Testy_Util_Command();
        $this->assertInstanceOf('Testy_Util_Command', $o->command('dir'));
        $this->assertInstanceOf('Testy_Util_Command', $o->execute());
        $this->asserttrue($o->isSuccess());
        $this->assertContains('testy.php', $o->get());
        $this->assertEquals(0, $o->status());
    }

    /**
     * Test Command-Setting via execute-method
     */
    public function testCommandFailure() {
        $o = new Testy_Util_Command();
        $this->assertInstanceOf('Testy_Util_Command', $o->execute('notExisting'));
        $this->assertfalse($o->isSuccess());
        $this->assertEquals(127, $o->status());
    }
}
