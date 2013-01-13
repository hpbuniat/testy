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
 * Wrapper to execute specific methods to instances in parallel
 *
 * @author Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @copyright 2011-2012 Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version Release: @package_version@
 * @link https://github.com/hpbuniat/Testy
 */
class Testy_Util_Parallel_Execute {

    /**
     * The Test-Cases
     *
     * @var array
     */
    private $_aStack = array();

    /**
     * Running processes
     *
     * @var array
     */
    private $_aProc = array();

    /**
     * Number of parallel threads
     *
     * @var int
     */
    private $_iThreads = 0;

    /**
     * Start-Time
     *
     * @var array
     */
    private $_iStart = null;

    /**
     * The Transport to store data
     *
     * @var Testy_Util_Parallel_TransportInterface
     */
    private $_oTransport = null;

    /**
     * Number of threads (default)
     *
     * @var array
     */
    const THREADS = 4;

    /**
     * Cache-Dir
     *
     * @var string
     */
    const DIR = '/tmp/';

    /**
     * Init the Wrapper
     *
     * @param array $aStack
     */
    public function __construct(array $aStack = array(), Testy_Util_Parallel_TransportInterface $oTransport) {
        $this->_iStart = microtime(true);
        $this->_aStack = array();

        // convert stack to array with numeric keys
        foreach ($aStack as $oObject) {
            $this->_aStack[] = $oObject;
        }

        $this->_oTransport = $oTransport;
        $this->threads();
    }

    /**
     * Read processed Stack
     *
     * @return array
     */
    public function get() {
        return $this->_aStack;
    }

    /**
     * Set number of threads
     *
     * @param  int $iThreads Number of parallel Threads
     *
     * @return Testy_Util_Parallel_Execute
     */
    public function threads($iThreads = 0) {
        $this->_iThreads = (int) $iThreads;
        if ($this->_iThreads === 0) {
            $this->_iThreads = self::THREADS;
        }

        return $this;
    }

    /**
     * Get the thread-count
     *
     * @return int
     */
    public function getThreads() {
        return $this->_iThreads;
    }

    /**
     * Run
     *
     * @param  array $aMethods Methods to execute
     *
     * @return Testy_Util_Parallel_Execute
     */
    public function run(array $aMethods = array()) {
        $this->_oTransport->setup(array(
            'dir' => self::DIR
        ));
        foreach (array_keys($this->_aStack) as $iStack) {
            $iChildren = count($this->_aProc);
            if ($iChildren < $this->_iThreads or $this->_iThreads === 0) {
                $this->_aProc[$iStack] = pcntl_fork();
                if ($this->_aProc[$iStack] === -1) {
                    unset($this->_aProc[$iStack]);
                }
                elseif ($this->_aProc[$iStack] === 0) {
                    $this->_execute($aMethods, $iStack);
                }
            }

            while (count($this->_aProc) >= $this->_iThreads and $this->_iThreads !== 0) {
                $this->_wait()->_read();
            }
        }

        $this->_wait(true)->_read();
        $this->_oTransport->free();

        return $this;
    }

    /**
     * Execute a child
     *
     * @param  array $aMethods Methods to execute
     * @param  int $iStack Stack-Index
     *
     * @return Testy_Util_Parallel_Execute
     */
    private function _execute($aMethods, $iStack) {
        foreach ($aMethods as $mKey => $mValue) {
            $aParams = array();
            $sFunc = $mValue;
            if (is_numeric($mKey) !== true) {
                $sFunc = $mKey;
                $aParams = $mValue;
            }

            call_user_func_array(array(
                $this->_aStack[$iStack],
                $sFunc
            ), $aParams);
        }

        $this->_oTransport->write($iStack, gzcompress(serialize($this->_aStack[$iStack])));
        posix_kill(getmypid(), 9);
        return $this;
    }

    /**
     * Wait for runnings childs to finish
     *
     * @param  boolean $bAll
     *
     * @return Testy_Util_Parallel_Execute
     */
    private function _wait($bAll = false) {
        do {
            $iStatus = null;
            $iPid = pcntl_waitpid(-1, $iStatus, WNOHANG);
            $bUnset = false;
            foreach ($this->_aProc as $sChild => $iChild) {
                if ($iChild === $iPid) {
                    unset($this->_aProc[$sChild]);
                    $bUnset = true;
                }
            }

            if ($bUnset === false) {
                usleep(10000);
            }

            $iChildren = count($this->_aProc);
        }
        while ($iChildren > 0 and $bAll === true);

        return $this;
    }

    /**
     * Read the test-results from shared-memory
     *
     * @return Testy_Util_Parallel_Execute
     */
    private function _read() {
        foreach (array_keys($this->_aStack) as $iStack) {
            $mResult = $this->_oTransport->read($iStack);
            if (empty($mResult) !== true) {
                $this->_aStack[$iStack] = unserialize(gzuncompress($mResult));
            }
        }

        return $this;
    }
}