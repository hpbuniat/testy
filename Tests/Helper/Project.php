<?php
/**
 * testy
 *
 * Copyright (c) 2011-2013, Hans-Peter Buniat <hpbuniat@googlemail.com>.
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
 * @copyright 2011-2013 Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */

/**
 * Helper-Class to create Mocks of projects-objects
 */
class Tests_Helper_Project {

    /**
     * Get a plain command-object mock
     *
     * @return Testy_Project
     */
    public static function getPlainMock() {
        $oMockBuilder = new PHPUnit_Framework_MockObject_Generator();
        $oMock = $oMockBuilder->getMock('Testy_Project');
        $oMock->expects(PHPUnit_Framework_TestCase::any())->method('getName')->will(PHPUnit_Framework_TestCase::returnValue(Testy_TextUI_Command::NAME));
        $oMock->expects(PHPUnit_Framework_TestCase::any())->method('getProjectHash')->will(PHPUnit_Framework_TestCase::returnValue(md5(rand())));

        return $oMock;
    }

    /**
     * Get a command-object, which will return true on isSuccess
     *
     * @return Testy_Project
     */
    public static function getEnabled() {
        $oMock = self::getPlainMock();
        $oMock->expects(PHPUnit_Framework_TestCase::any())->method('isEnabled')->will(PHPUnit_Framework_TestCase::returnValue(true));

        return $oMock;
    }

    /**
     * Get a command-object, which will return false on isSuccess
     *
     * @return Testy_Project
     */
    public static function getDisabled() {
        $oMock = self::getPlainMock();
        $oMock->expects(PHPUnit_Framework_TestCase::any())->method('isEnabled')->will(PHPUnit_Framework_TestCase::returnValue(false));

        return $oMock;
    }
}
