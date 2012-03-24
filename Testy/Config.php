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
 * Config-Builder
 *
 * @author Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @copyright 2011-2012 Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version Release: @package_version@
 * @link https://github.com/hpbuniat/testy
 */
class Testy_Config {

    /**
     * The config-file
     *
     * @var string
     */
    protected $_sFile = '';

    /**
     * The config-files mtime
     *
     * @var int
     */
    protected $_iMtime = 0;

    /**
     * The config
     *
     * @var stdClass
     */
    protected $_oConfig;

    /**
     * Indicate an updated config
     *
     * @var boolean
     */
    protected $_bUpdate = false;

    /**
     * Error when there is no config-file found or the config contains syntax-errors
     *
     * @var string
     */
    const ERROR = 'Error while reading the configuration!';

    /**
     * Info, if the config has been refreshed
     *
     * @var string
     */
    const REFRESH = 'The configuration has been refreshed!';

    /**
     * Create the builder
     *
     * @param  string $sFile
     */
    public function __construct($sFile = null) {
        if (empty($sFile) !== true and file_exists($sFile) === true) {
            $this->_sFile = $sFile;
        }
        else {
            throw new Testy_Exception(self::ERROR);
        }
    }

    /**
     * Create the config
     *
     * @return Testy_Config
     */
    protected function _read() {
        if ($this->_check() !== false) {
            $oConfig = json_decode(file_get_contents($this->_sFile));
            if (($this->_oConfig instanceof stdClass) !== true and empty($oConfig) === true) {
                throw new Testy_Exception(self::ERROR);
            }
            else {
                $this->_oConfig = $oConfig;
                if (defined('VERBOSE') === true and VERBOSE === true) {
                    Testy_TextUI_Output::info(self::REFRESH);
                }
            }
        }

        return $this;
    }

    /**
     * Check, if the config was updated
     *
     * @return int | false
     */
    protected function _check() {
        $iMtime = filemtime($this->_sFile);
        $this->_bUpdate = false;
        if ($this->_iMtime !== $iMtime) {
            $this->_iMtime = $iMtime;
            $this->_bUpdate = true;
            return $this->_iMtime;
        }

        return false;
    }

    /**
     * Was the config updated?
     *
     * @return boolean
     */
    public function wasUpdated() {
        return $this->_bUpdate;
    }

    /**
     * Return the config
     *
     * @return stdClass
     */
    public function get() {
        return $this->_read()->_oConfig;
    }
}
