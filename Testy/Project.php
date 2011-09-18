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
     * The projects command wrapper
     *
     * @var Testy_Util_Command
     */
    private $_oCommand;

    /**
     * The notifiers to use
     *
     * @param array
     */
    private $_aNotifiers;

    /**
     * Info-Text, if there are php-lint errors
     *
     * @var string
     */
    const LINT_ERROR = 'php-lint failed, supresing phpunit';

    /**
     * Init the project
     *
     * @param  string $sName
     */
    public function __construct($sName) {
        $this->_sName = $sName;
        $this->_oCommand = new Testy_Util_Command();
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
     */
    public function config(stdClass $oConfig) {
        $this->_sPath = $oConfig->path;
        $this->_oCommand->command($oConfig->test);
        if (isset($oConfig->find) === true) {
            $this->_sPattern = $oConfig->find;
        }

        return $this;
    }

    /**
     * Check if there were modifications
     *
     * @param  int $iLast Timestamp of last Check
     *
     * @return boolean
     */
    public function check($iLast = 0) {
        if ($iLast === 0) {
            $iLast = time();
        }

        $bStatus = false;
        $sDate = date('Ymd H:i:s', $iLast);
        $oCommand = new Testy_Util_Command('find ' . $this->_sPath . ' -type f -name "' . $this->_sPattern . '" -newermt "' . $sDate . '"');
        $sReturn = trim($oCommand->execute()->get());
        $aFiles = explode(PHP_EOL, $sReturn);

        if (empty($sReturn) !== true) {
            if ($this->_lint($aFiles) === true) {
                $bStatus = true;
            }
            else {
                $this->notify(Testy_AbstractNotifier::FAILED, self::LINT_ERROR);
            }
        }

        unset($oCommand, $sDate, $sReturn, $aFiles);
        return $bStatus;
    }

    /**
     * Run test and notify
     *
     * @return Testy_Project
     */
    public function run() {
        $this->_oCommand->execute();
        $this->notify(($this->_oCommand->isSuccess() === true) ? Testy_AbstractNotifier::SUCCESS : Testy_AbstractNotifier::FAILED, $this->_oCommand->get());

        return $this;
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
     * Lint the modified files
     *
     * @param  array $aFiles
     *
     * @return boolean
     */
    protected function _lint(array $aFiles = array()) {
        $bStatus = (empty($aFiles) === true) ? false : true;
        array_walk($aFiles, 'trim');

        foreach ($aFiles as $sFile) {
            $oCommand = new Testy_Util_Command('php -l ' . $sFile);
            if ($oCommand->execute()->isSuccess() !== true) {
                $bStatus = false;
            }

            unset($oCommand);
        }

        return $bStatus;
    }
}
