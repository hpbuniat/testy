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
 * Run the watch loop
 *
 * @author Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @copyright2011-2012 Hans-Peter Buniat <hpbuniat@googlemail.com>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version Release: @package_version@
 * @link https://github.com/hpbuniat/testy
 */
class Testy_Watch {

    /**
     * The last-checks timestamp
     *
     * @var int
     */
    private $_iTimestamp;

    /**
     * The Stack of projects to check
     *
     * @var array
     */
    private $_aStack = array();

    /**
     * Init some defaults
     */
    public function __construct() {
        $this->_iTimestamp = time();
        $this->_aStack = array();
    }

    /**
     * Add a project to watch
     *
     * @param  Testy_Project $oProject
     *
     * @return Testy_Watch
     */
    public function add(Testy_Project $oProject) {
        $sName = $oProject->getName();
        if (empty($this->_aStack[$sName]) === true or ($oProject->getProjectHash() !== $this->_aStack[$sName]->getProjectHash())) {
            if ($oProject->isEnabled() === true) {
                $this->_aStack[$sName] = $oProject;
                $oProject->notify(Testy_AbstractNotifier::INFO, Testy_Project_Builder::INFO);
            }
            elseif (empty($this->_aStack[$sName]) !== true) {
                unset($this->_aStack[$sName]);
            }
        }

        return $this;
    }

    /**
     * Run the watch-loop
     *
     * @return Testy_Watch
     */
    public function loop() {
        $iTime = $this->_iTimestamp;
        $this->_iTimestamp = time();

        $oParallel = new Testy_Util_Parallel($this->_aStack);
        $oParallel->run(array(
            'check' => array(
                $iTime
            ),
            'run'
        ));

        unset($oParallel);

        return $this;
    }
}
