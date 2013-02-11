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
namespace Testy\Util;


/**
 * Base class to execute commands as process
 *
 * @author Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @copyright 2011-2013 Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version Release: @package_version@
 * @link https://github.com/hpbuniat/Testy
 */
class Command {

    /**
     * Process return status
     *
     * @var int
     */
    protected $_iStatus = 0;

    /**
     * The command to execute
     *
     * @var string
     */
    protected $_sCommand = '';

    /**
     * The return value
     *
     * @var string
     */
    protected $_sReturn = '';

    /**
     * Create a command-object
     *
     * @param string $sCommand
     */
    public function __construct($sCommand = null) {
        $this->setCommand($sCommand);
    }

    /**
     * Setter for the command
     * - will implicitly call reset
     *
     * @param  string $sCommand
     *
     * @return $this
     */
    public function setCommand($sCommand = null) {
        if (empty($sCommand) !== true) {
            $this->reset();
            $this->_sCommand = $sCommand;
        }

        return $this;
    }

    /**
     * Reset the command wrapper
     *
     * @return $this
     */
    public function reset() {
        $this->_iStatus = 0;
        $this->_sCommand = '';
        $this->_sReturn = '';
        return $this;
    }

    /**
     * Execute a command and read the output
     *
     * @param  string $sCommand
     *
     * @return $this
     */
    public function execute($sCommand = null) {
        $this->setCommand($sCommand);
        if (defined('VERBOSE') === true and VERBOSE === true) {
            \Testy\TextUI\Output::info($this->_sCommand);
        }

        $rCommand = popen($this->_sCommand, 'r');
        $this->_sReturn = '';
        while (feof($rCommand) !== true) {
            $this->_sReturn .= fread($rCommand, 4096);
        }

        $this->_iStatus = pclose($rCommand);
        if (defined('VERBOSE') === true and VERBOSE === true) {
            \Testy\TextUI\Output::info($this->_iStatus);
        }

        return $this;
    }

    /**
     * Get the output
     *
     * @return string
     */
    public function get() {
        return $this->_sReturn;
    }

    /**
     * Did the execution exit with success code?
     *
     * @return boolean
     */
    public function isSuccess() {
        return ($this->_iStatus === 0);
    }

    /**
     * Get the raw exit status-code
     *
     * @return int
     */
    public function status() {
        return $this->_iStatus;
    }
}
