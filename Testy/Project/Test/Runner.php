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
 * @package Testy
 * @author Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @copyright 2011-2013 Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */

/**
 * Run the test-command
 *
 * @author Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @copyright 2011-2013 Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version Release: @package_version@
 * @link https://github.com/hpbuniat/testy
 */
class Testy_Project_Test_Runner {

    /**
     * The Test-Command
     *
     * @var string
     */
    protected $_sCommand = '';

    /**
     * The Test-Command, which was executed at last (testing purpose)
     *
     * @var string
     */
    protected $_sLastCommand = '';

    /**
     * The change-dir command, which is execuded before the test
     *
     * @var string
     */
    protected $_sChangeDir = '';

    /**
     * Sync-path
     *
     * @var stdClass
     */
    protected $_sSyncPath = null;

    /**
     * All modified files
     *
     * @var array
     */
    protected $_aFiles = array();

    /**
     * The current file
     *
     * @var string
     */
    protected $_sFile = '';

    /**
     * The current project
     *
     * @var Testy_Project
     */
    protected $_oProject;

    /**
     * Is this a repeated execution ?
     *
     * @var boolean
     */
    protected $_bRepeat = false;

    /**
     * The executions return
     *
     * @var string
     */
    protected $_sReturn = '';

    /**
     * The number of commands executed
     *
     * @var int
     */
    protected $_iCommands = 0;

    /**
     * Placeholder for a specific file (triggers Executor_One)
     *
     * @var string
     */
    const FILE_PLACEHOLDER = '$file';

    /**
     * Placeholder for a the current time
     *
     * @var string
     */
    const TIME_PLACEHOLDER = '$time';

    /**
     * Placeholder for the files mtime
     *
     * @var string
     */
    const MTIME_PLACEHOLDER = '$mtime';

    /**
     * Placeholder for the project name
     *
     * @var string
     */
    const PROJECT_PLACEHOLDER = '$project';

    /**
     * Create the runnter
     *
     * @param  Testy_Project $oProject
     * @param  array $aFiles
     * @param  stdClass $oConfig Project-Config
     */
    public function __construct(Testy_Project $oProject, array $aFiles, stdClass $oConfig) {
        $this->_oProject = $oProject;
        $this->_aFiles = $aFiles;

        $this->_sChangeDir = '';
        $this->_sCommand = $oConfig->test;
        if (isset($oConfig->test_dir) === true) {
            $this->_sChangeDir = $oConfig->test_dir;
        }

        $this->_sSyncPath = (isset($oConfig->sync_dir) === true and isset($oConfig->sync_dir->from) === true and isset($oConfig->sync_dir->to) === true) ? $oConfig->sync_dir : false;
        if (empty($this->_sSyncPath) !== true) {
            $this->_sChangeDir = str_replace($this->_sSyncPath->from, $this->_sSyncPath->to, $this->_sChangeDir);
        }
    }

    /**
     * Set the command & reset changedir
     *
     * @param  string $sCommand
     *
     * @return Testy_Project_Test_Runner
     */
    public function setCommand($sCommand) {
        $this->_sCommand = $sCommand;
        $this->_sChangeDir = '';

        return $this;
    }

    /**
     * Indicate, that this is a repeated execution
     *
     * @return Testy_Project_Test_Runner
     */
    public function repeat() {
        $this->_bRepeat = true;
        return $this;
    }

    /**
     * Execute the runnter
     *
     * @return Testy_Project_Test_Runner
     */
    public function run() {
        $bSingle = $this->executeSingle();
        foreach ($this->_aFiles as $this->_sFile) {
            $this->_execute($this->_getCommand($bSingle));
            if ($bSingle !== true) {
                break;
            }
        }

        return $this;
    }

    /**
     * Get the returned content
     *
     * @return string
     */
    public function get() {
        return $this->_sReturn;
    }

    /**
     * Get the number of executed commands
     */
    public function getCommands() {
        return $this->_iCommands;
    }

    /**
     * Get the last executed command
     *
     * @return string
     */
    public function getLastCommand() {
        return $this->_sLastCommand;
    }

    /**
     * Should the command be executed for each file
     *
     * @return boolean
     */
    public function executeSingle() {
        return (strpos($this->_sCommand, self::FILE_PLACEHOLDER) !== false and $this->_bRepeat !== true);
    }

    /**
     * Enrich the command with placeholders
     *
     * @param  boolean $bSingle
     *
     * @return string
     */
    protected function _getCommand($bSingle = false) {
        $sCommand = $this->_sCommand;
        if ($bSingle === true) {
            $oTestMapper = new Testy_Project_File_Mapper($sCommand, $this->_sFile);
            $sCommand = $oTestMapper->map()->get();
            unset($oTestMapper);
        }

        if (empty($this->_sSyncPath) !== true) {
            $sCommand = str_replace($this->_sSyncPath->from, $this->_sSyncPath->to, $sCommand);
        }

        $sCommand = trim(preg_replace('!{.*?}!i', '', $sCommand));
        if (empty($this->_sChangeDir) !== true) {
            $sCommand = sprintf('cd %s; %s', $this->_sChangeDir, $sCommand);
        }

        if (empty($this->_sSyncPath) !== true) {
            $sCommand = sprintf('rsync -azq %s %s; %s', $this->_sSyncPath->from, $this->_sSyncPath->to, $sCommand);
        }

        $aReplace = array(
            self::FILE_PLACEHOLDER => '',
            self::TIME_PLACEHOLDER => time(),
            self::MTIME_PLACEHOLDER => filemtime($this->_sFile),
            self::PROJECT_PLACEHOLDER => $this->_oProject->getName()
        );
        return str_replace(array_keys($aReplace), array_values($aReplace), $sCommand);
    }

    /**
     * Execute a command
     *
     * @param  string $sCommand
     *
     * @return boolean
     *
     * @throws Testy_Project_Test_Exception If the test fails
     */
    protected function _execute($sCommand) {
        $this->_iCommands++;
        $this->_sLastCommand = $sCommand;

        $oCommand = $this->_oProject->getCommand();
        $this->_sReturn = $oCommand->setCommand($this->_sLastCommand)->execute()->get();

        if ($oCommand->isSuccess() !== true) {
            throw new Testy_Project_Test_Exception($this->_sReturn);
        }

        return $oCommand->isSuccess();
    }
}
