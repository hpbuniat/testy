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
 * Notify to dbus
 *
 * @author Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @copyright 2011-2012 Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version Release: @package_version@
 * @link https://github.com/hpbuniat/testy
 */

// @codeCoverageIgnoreStart
class Testy_Notifier_Dbus extends Testy_AbstractNotifier {

    /**
     * The DBus-Proxy
     *
     * @var DBusObject
     */
    private $_oProxy = null;

    /**
     * Create the dbus-object & -proxy
     */
    public function __construct() {
        if (extension_loaded('dbus') === true) {
            $oDbus = new Dbus(Dbus::BUS_SESSION, true);
            $this->_oProxy = $oDbus->createProxy("org.freedesktop.Notifications", "/org/freedesktop/Notifications", "org.freedesktop.Notifications");
        }
    }

    /**
     * (non-PHPdoc)
     * @see Testy_AbstractNotifier::notify()
     */
    public function notify(Testy_Project $oProject, $sStatus, $sText) {
        if (extension_loaded('dbus') === true and $this->_oProxy instanceof DBusObject) {
            $this->_oProxy->Notify(
                Testy_TextUI_Command::NAME,
                new DBusUInt32(0),
                '',
                $oProject->getName() . ': ' . $sStatus,
                $sText,
                new DBusArray(DBus::STRING, array()),
                new DBusDict(DBus::VARIANT, array()),
                -1
            );
        }

        return $this;
    }
}
// @codeCoverageIgnoreEnd
