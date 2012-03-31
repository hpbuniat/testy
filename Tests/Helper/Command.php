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
 * Helper-Class to create Mocks of command-objects
 */
class Tests_Helper_Command {

    /**
     * Get a plain command-object mock
     *
     * @param  string $sReturn
     *
     * @return Testy_Util_Command
     */
    public static function getPlainMock($sReturn = '') {
        $oMockBuilder = new PHPUnit_Framework_MockObject_Generator();
        $oCommandMock = $oMockBuilder->getMock('Testy_Util_Command');
        $oCommandMock->expects(PHPUnit_Framework_TestCase::any())->method('execute')->will(PHPUnit_Framework_TestCase::returnSelf());
        $oCommandMock->expects(PHPUnit_Framework_TestCase::any())->method('reset')->will(PHPUnit_Framework_TestCase::returnSelf());
        $oCommandMock->expects(PHPUnit_Framework_TestCase::any())->method('setCommand')->will(PHPUnit_Framework_TestCase::returnSelf());
        $oCommandMock->expects(PHPUnit_Framework_TestCase::any())->method('get')->will(PHPUnit_Framework_TestCase::returnValue($sReturn));

        return $oCommandMock;
    }

    /**
     * Get a command-object, which will return true on isSuccess
     *
     * @param  string $sReturn
     *
     * @return Testy_Util_Command
     */
    public static function getSuccess($sReturn = '') {
        $oCommandMock = self::getPlainMock($sReturn);
        $oCommandMock->expects(PHPUnit_Framework_TestCase::any())->method('isSuccess')->will(PHPUnit_Framework_TestCase::returnValue(true));

        return $oCommandMock;
    }

    /**
     * Get a command-object, which will return false on isSuccess
     *
     * @param  string $sReturn
     *
     * @return Testy_Util_Command
     */
    public static function getFailure($sReturn = '') {
        $oCommandMock = self::getPlainMock($sReturn);
        $oCommandMock->expects(PHPUnit_Framework_TestCase::any())->method('isSuccess')->will(PHPUnit_Framework_TestCase::returnValue(false));

        return $oCommandMock;
    }
}