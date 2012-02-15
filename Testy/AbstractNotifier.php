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
 * @copyright2011-2012 Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */

/**
 * Notifier-Abstract
 *
 * @author Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @copyright2011-2012 Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version Release: @package_version@
 * @link https://github.com/hpbuniat/testy
 */
abstract class Testy_AbstractNotifier {

    /**
     * Message for successful-tests
     *
     * @var string
     */
    const SUCCESS = 'Success';

    /**
     * Message for failed-tests
     *
     * @var string
     */
    const FAILED = 'Failed';

    /**
     * Message for Infos
     *
     * @var string
     */
    const INFO = 'Info';

    /**
     * Maximum message length
     *
     * @var int
     */
    protected $_iMessageLength = 0;

    /**
     * The notifiers configuration
     *
     * @var stdClass
     */
    protected $_oConfig;

    /**
     * Init a Notifier
     *
     * @param  stdClass $oConfig
     */
    public function __construct(stdClass $oConfig = null) {
        $this->_oConfig = $oConfig;
    }

    /**
     * Format the message
     *
     * @param  string $sText
     *
     * @return strings
     */
    public function formatMessage($sText) {
        if ($this->_iMessageLength > 0 and strlen($sText) > $this->_iMessageLength) {
            $sText = substr($sText, 0, $this->_iMessageLength);
        }

        return preg_replace('!\\033\[\d{1,2}(;\d{1,2})?m!i', '', $sText);
    }

    /**
     * Execute the notification
     *
     * @param  Testy_Project $oProject
     * @param  string $sStatus
     * @param  string $sText
     *
     * @return Testy_AbstractNotifier
     */
    abstract public function notify(Testy_Project $oProject, $sStatus, $sText);
}