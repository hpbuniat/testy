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
 * Abstract for cacheable models
 *
 * @author Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @copyright 2011 Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version Release: @package_version@
 * @link https://github.com/hpbuniat/Testy
 */
abstract class Testy_Util_Cacheable {

    /**
     * Cached data
     *
     * @var mixed
     */
    protected $_mCache;

    /**
     * Cache-Id
     *
     * @var string
     */
    protected $_sId;

    /**
     * Cache-File name
     *
     * @var string
     */
    protected $_sFile;

    /**
     * Cache-Dir
     *
     * @var string
     */
    const DIR = '/tmp/Testy/';

    /**
     * How to generate the id
     *
     * @return Testy_Util_Cacheable
     */
    abstract protected function _id();

    /**
     * How to get the data if no cache-entry
     *
     * @return Testy_Util_Cacheable
     */
    abstract protected function _get();

    /**
     * Generate the cache-file name
     *
     * @return Testy_Util_Cacheable
     */
    protected function _file() {
        $this->_sFile = self::DIR . $this->_sId;
        return $this;
    }

    /**
     * Get the data
     *
     * @return mixed
     */
    public function get() {
        if (file_exists($this->_sFile) === true) {
            $this->_mCache = unserialize(file_get_contents($this->_sFile));
        }
        else {
            $this->_get();
            $this->write();
        }

        return $this->_mCache;
    }

    /**
     * Write the data to cache
     *
     * @return Testy_Util_Cacheable
     */
    public function write() {
        if (empty($this->_mCache) !== true) {
            if (is_dir(self::DIR) !== true) {
                mkdir(self::DIR, 0744, true);
            }

            file_put_contents($this->_sFile, serialize($this->_mCache));
        }

        return $this;
    }
}