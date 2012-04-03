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
 * Parallel-Transport for File
 *
 * @author Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @copyright 2011-2012 Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version Release: @package_version@
 * @link https://github.com/hpbuniat/Testy
 */
class Testy_Util_Parallel_Transport_File implements Testy_Util_Parallel_TransportInterface {

    /**
     * The directory to use
     *
     * @var string
     */
    private $_sDir = '/dev/null';

    /**
     * The file-prefix
     *
     * @var string
     */
    const PREFIX = '_parallel_';

    /**
     * (non-PHPdoc)
     * @see Testy_Util_Parallel_TransportInterface::setup()
     */
    public function setup(array $aOptions = array()) {
        if (empty($aOptions['dir']) !== true and is_dir($aOptions['dir']) === true) {
            $sUniqueId = uniqid(self::PREFIX);
            $this->_sDir = $aOptions['dir'] . $sUniqueId;
            mkdir($this->_sDir, 0744, true);

            return $this;
        }

        throw new Testy_Util_Parallel_Transport_Exception(Testy_Util_Parallel_Transport_Exception::SETUP_ERROR);
    }

    /**
     * (non-PHPdoc)
     * @see Testy_Util_Parallel_TransportInterface::read()
     */
    public function read($sId) {
        $mReturn = false;
        $sFile = $this->_sDir . DIRECTORY_SEPARATOR . self::PREFIX . $sId;
        if (file_exists($sFile) === true) {
            $mReturn = file_get_contents($sFile);
        }

        return $mReturn;
    }

    /**
     * (non-PHPdoc)
     * @see Testy_Util_Parallel_TransportInterface::write()
     */
    public function write($sId, $mData) {
        $sFile = $this->_sDir . DIRECTORY_SEPARATOR . self::PREFIX . $sId;
        file_put_contents($sFile, $mData);

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see Testy_Util_Parallel_TransportInterface::free()
     */
    public function free() {
        $oIter = new DirectoryIterator($this->_sDir);
        foreach ($oIter as $oFile) {
            if ($oFile->isFile() === true) {
                unlink($oFile->getPathname());
            }
        }

        rmdir($this->_sDir);

        return $this;
    }
}