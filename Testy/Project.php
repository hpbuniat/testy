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
 * @package Testy
 * @author Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @copyright 2011 Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */

/**
 * A Test-Project
 *
 * @author Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @copyright 2011 Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version Release: @package_version@
 * @link https://github.com/hpbuniat/testy
 */
class Testy_Project {

    /**
     * The projects name
     *
     * @var string
     */
    private $_sName = '';

    /**
     * The path to check for modifications
     *
     * @var string
     */
    private $_sPath = null;

    /**
     * The find pattern to detect modification
     *
     * @var string
     */
    private $_sPattern = '*.php';

    /**
     * The projects config
     *
     * @var stdClass
     */
    private $_oConfig;

    /**
     * The notifiers to use
     *
     * @var array
     */
    private $_aNotifiers;

    /**
     * The list of modified files
     *
     * @var array
     */
    private $_aFiles = array();

    /**
     * Indicate, if the project is enabled
     *
     * @var boolean
     */
    private $_bEnabled = true;

    /**
     * Info-Text, if there are lint errors
     *
     * @var string
     */
    const LINT_ERROR = 'linting the changed files failed, suppressing test';

    /**
     * Info-Text, if the test-command is repeated
     *
     * @var string
     */
    const REPEAT = 'repeating test-command without a specific file';

    /**
     * Info-Text, if there is no test command given
     *
     * @var stirng
     */
    const MISSING_TEST_COMMAND = 'test-command missing for project %s';

    /**
     * Notify about detected modifications
     *
     * @var string
     */
    const INFO = 'Modifications detected, running tests ...';

    /**
     * Init the project
     *
     * @param  string $sName
     */
    public function __construct($sName = '') {
        $this->_sName = $sName;
        $this->_aNotifiers = array();
    }

    /**
     * Add a notifier
     *
     * @param Testy_AbstractNotifier $oNotifier
     *
     * @return Testy_Project
     */
    public function addNotifier(Testy_AbstractNotifier $oNotifier) {
        $this->_aNotifiers[] = $oNotifier;
        return $this;
    }

    /**
     * The the projects name
     *
     * @return string
     */
    public function getName() {
        return $this->_sName;
    }

    /**
     * Set the configuration
     *
     * @param  stdClass $oConfig
     *
     * @return Testy_Project
     *
     * @throws Testy_Exception If there are problems with the config
     */
    public function config(stdClass $oConfig) {
        $this->_sPath = (isset($oConfig->path) === true) ? $oConfig->path : '.';
        if (isset($oConfig->test) === false) {
            throw new Testy_Exception(sprintf(self::MISSING_TEST_COMMAND, $this->getName()));
        }

        if (isset($oConfig->find) === true) {
            $this->_sPattern = $oConfig->find;
        }

        if (isset($oConfig->enabled) === true) {
            $this->_bEnabled = ($oConfig->enabled == true);
        }

        $this->_oConfig = $oConfig;
        return $this;
    }

    /**
     * Return the config-hash
     *
     * @return string
     */
    public function getProjectHash() {
        return md5($this->_sName . serialize($this->_oConfig) . serialize(array_keys($this->_aNotifiers)));
    }

    /**
     * Check, if the project is enabled
     *
     * @return boolean
     */
    public function isEnabled() {
        return $this->_bEnabled;
    }

    /**
     * Check if there were modifications
     *
     * @param  int $iLast Timestamp of last Check
     *
     * @return Testy_Project
     */
    public function check($iLast = 0) {
        $sDate = date('Ymd H:i:s', $iLast);
        $oCommand = new Testy_Util_Command('find ' . $this->_sPath . ' -type f -name "' . $this->_sPattern . '" -newermt "' . $sDate . '"');
        $sReturn = trim($oCommand->execute()->get());

        $this->_aFiles = array();
        if (empty($sReturn) !== true) {
            $this->_aFiles = explode(PHP_EOL, $sReturn);
            array_walk($this->_aFiles, 'trim');
        }

        unset($oCommand, $sDate, $sReturn);
        return $this;
    }

    /**
     * Run test and notify
     *
     * @return Testy_Project
     */
    public function run() {
        if (empty($this->_aFiles) === true) {
            return $this;
        }

        $this->notify(Testy_AbstractNotifier::INFO, self::INFO);
        if (empty($this->_oConfig->syntax) === true or $this->_lint() === true) {
            $bRepeat = (empty($this->_oConfig->repeat) === true or $this->_oConfig->repeat != true);
            $oRunner = new Testy_Project_Test_Runner($this, $this->_aFiles, $this->_oConfig);
            try {
                $this->notify(Testy_AbstractNotifier::SUCCESS, $oRunner->run()->get());
                if ($bRepeat === true) {
                    $this->notify(Testy_AbstractNotifier::INFO, self::REPEAT);
                    $this->notify(Testy_AbstractNotifier::SUCCESS, $oRunner->repeat()->run()->get());
                }
            }
            catch (Testy_Project_Test_Exception $oException) {
                $this->notify(Testy_AbstractNotifier::FAILED, $oException->getMessage());
            }

            unset($oRunner);
        }

        return $this;
    }

    /**
     * Lint the changed files
     *
     * @return boolean
     */
    protected function _lint() {
        $bReturn = true;
        $oRunner = new Testy_Project_Test_Runner($this, $this->_aFiles, $this->_oConfig->syntax);
        try {
            $oRunner->run();
        }
        catch (Testy_Project_Test_Exception $oException) {
            $bReturn = false;
            $this->notify(Testy_AbstractNotifier::FAILED, self::LINT_ERROR);
            if (defined('VERBOSE') === true and VERBOSE === true) {
                Testy_TextUI_Output::info($oException->getMessage());
            }
        }

        unset($oRunner);
        return $bReturn;
    }

    /**
     * Notify all notifiers
     *
     * @param  string $sStatus The notification status
     * @param  string $sText The text to display
     *
     * @return Testy_Project
     */
    public function notify($sStatus, $sText) {
        foreach ($this->_aNotifiers as $oNotifier) {
            $oNotifier->notify($this, $sStatus, $sText);
        }

        return $this;
    }

    /**
     * Get the modified files
     *
     * @return array
     */
    public function getFiles() {
        return $this->_aFiles;
    }

    /**
     * Set modified files
     *
     * @return Testy_Project
     */
    public function setFiles(array $aFiles = array()) {
        $this->_aFiles = $aFiles;
        return $this;
    }

}
