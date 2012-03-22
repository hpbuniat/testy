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
 * Notify via Growl
 *
 * @author Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @copyright2011-2012 Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version Release: @package_version@
 * @link https://github.com/hpbuniat/testy
 */

// @codeCoverageIgnoreStart
class Testy_Notifier_Growl extends Testy_AbstractNotifier {

    /**
     * The message to send
     *
     * @var string
     */
    protected $_sMessage = '';

    /**
     * Indicate registration status
     *
     * @var boolean
     */
    protected $_bRegistered = false;

    /**
     * Maximum message length
     *
     * @var int
     */
    protected $_iMessageLength = 256;

    /**
     * (non-PHPdoc)
     * @see Testy_AbstractNotifier::notify()
     */
    public function notify(Testy_Project $oProject, $sStatus, $sText) {
        $sName = Testy_TextUI_Command::NAME;
        $sProject = $oProject->getName();

        if ($this->_bRegistered !== true) {
            $this->_sMessage = pack('c2nc2', 1, 0, strlen($sName), 3, 3)
                             . $sName
                             . pack('n', strlen(self::SUCCESS)) . self::SUCCESS
                             . pack('n', strlen(self::INFO)) . self::INFO
                             . pack('n', strlen(self::FAILED)) . self::FAILED
                             . pack('c', 0)
                             . pack('c', 1)
                             . pack('c', 2);

            $this->_send();
            $this->_bRegistered = true;
        }

        $sText = $sStatus . PHP_EOL . PHP_EOL . trim($sText);
        $sText = $this->formatMessage($sText);

        $this->_sMessage = pack('c2n5', 1, 1, 0, strlen($sStatus), strlen($sProject), strlen($sText), strlen($sName))
                         . $sStatus . $sProject . $sText . $sName;
        $this->_send();

        return $this;
    }

    /**
     * Send a message via growl-protocol
     *
     * @return Testy_AbstractNotifier
     */
    private function _send() {
        if ($this->_oConfig instanceof stdClass) {
            $this->_sMessage .= pack('H32', md5($this->_sMessage . $this->_oConfig->password));

            $rSocket = fsockopen('udp://' . $this->_oConfig->host, $this->_oConfig->port);
            fwrite($rSocket, $this->_sMessage);
            fclose($rSocket);
        }

        return $this;
    }
}
// @codeCoverageIgnoreEnd