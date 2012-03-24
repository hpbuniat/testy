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
 * @package Testy
 * @author Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @copyright 2011-2012 Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */

/**
 * File-Mapper to find the Test that matches to the changed file
 *
 * @author Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @copyright 2011-2012 Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version Release: @package_version@
 * @link https://github.com/hpbuniat/testy
 */
class Testy_Project_File_Mapper {

    /**
     * The file
     *
     * @var string
     */
    protected $_sFile = '';

    /**
     * The test-file
     *
     * @var string
     */
    protected $_sTestFile = '';

    /**
     * The command, including the pattern
     *
     * @var string
     */
    protected $_sCommand = '';

    /**
     * The source file is not accessable
     *
     * @var string
     */
    const SOURCE_ERROR = 'The source-file does not exist!';

    /**
     * Message, if a test is missing
     *
     * @var string
     */
    const MISSING_TEST = 'The Test for %s is missing!';

    /**
     * Create the builder
     *
     * @param  string $sCommand The command to execute
     * @param  string $sFile The file
     *
     * @throws Testy_Exception If the file is not accessable
     */
    public function __construct($sCommand = null, $sFile = null) {
        if (empty($sFile) !== true and file_exists($sFile) === true) {
            $this->_sCommand = $sCommand;
            $this->_sFile = $sFile;
        }
        else {
            throw new Testy_Exception(self::SOURCE_ERROR);
        }
    }

    /**
     * Map the path
     *
     * @return Testy_Project_File_Mapper
     *
     * @throws Testy_Project_Test_Exception If no matching test is found
     */
    public function map() {
        $this->_sTestFile = $this->_sFile;

        $aMatch = array();
        if (preg_match_all(' !{([\.\w-/]+)\|([\.\w-/]+)}!i', $this->_sCommand, $aMatch) !== 0) {
            $aSearch = $aReplace = array();
            if (empty($aMatch[1]) !== true and empty($aMatch[2]) !== true) {
                $aSearch = $aMatch[1];
                $aReplace = $aMatch[2];
            }

            // if the changed file seems to be a test, do not replace
            $bIsTestFile = true;
            foreach ($aReplace as $sReplace) {
                if (strpos($this->_sFile, $sReplace) === false) {
                    $bIsTestFile = false;
                }
            }

            $sFile = ($bIsTestFile === true) ? $this->_sFile : str_replace($aSearch, $aReplace, $this->_sFile);
            if (file_exists($sFile) === true) {
                $this->_sTestFile = $sFile;
            }
            else {
                throw new Testy_Project_File_Exception(sprintf(self::MISSING_TEST, $this->_sFile));
            }
        }

        $this->_sCommand = trim(preg_replace('!{.*?}!i', '', $this->_sCommand));
        $this->_sCommand = str_replace(Testy_Project_Test_Runner::FILE_PLACEHOLDER, $this->_sTestFile, $this->_sCommand);

        return $this;
    }

    /**
     * Get the test-file
     *
     * @return string
     */
    public function getTestFile() {
        return $this->_sTestFile;
    }

    /**
     * Return the command
     *
     * @return stdClass
     */
    public function get() {
        return $this->_sCommand;
    }
}
